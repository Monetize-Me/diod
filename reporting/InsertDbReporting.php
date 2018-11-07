<?php
include_once(__DIR__.'/ActionReporting.php');
include_once(__DIR__.'/../lib/Mysql.php');

/** 
* @class InsertDbReporting
*/
class InsertDbReporting extends ActionReporting 
{
	private $_cronId;
	
	/** 
	* @function __construct
	* @param  name text mandatory
	* @param  setts select mandatory
	*/
	public function __construct($checker, array $settings, $debug = 0) {
		$this->setDebug($debug);
		parent::__construct($checker, $settings, $debug);
	}

	public function _checkSettings()
	{
		$this->_cronId = $this->mandatory("cronId");
		
		$that = $this;
		$this->_settings['action'] = function($data, $groupKey, $index, $condition) use($that) {
			$that->mysqlExistsTable($data, $groupKey, $index);
			$that->mysqlInsertData($data, $groupKey, $index);
		};
		return parent::_checkSettings();
	}

	public function _cleaner($str){
		return preg_replace('/[^a-z0-9]+/i', '', $str);
	}

	public function _generateColumn($value) {
		switch (gettype($value)) {
			case 'boolean':
				return 'TINYINT(1) NOT NULL default 0';
			case 'integer':
				if($value > 2147483647)
				{
					return 'BIGINT(20) NULL default NULL';
				}
				return 'INT NULL default NULL';
			case 'double':
				return 'DOUBLE (10,2) NULL default 0.00';
			case 'string':
				return 'VARCHAR(2048) NULL default \'\' ';
		}
		return false;
	}

	public function _mysqlExistsTable($data, $groupKey, $index){
		$table = get_class($this->_checker);


		$field_type = [
			'id' => " int(11) NOT NULL auto_increment",
			'key' => " varchar(100) NOT NULL default ''",
			'cron_id' => " int(11) NOT NULL default 0",
			'created_at' => " TIMESTAMP default CURRENT_TIMESTAMP"
		];

		foreach ($data as $key => $value) {
			$column_name = $this->cleaner($key);
			if(($column = $this->generateColumn($value)) != false) {
				if(!in_array($column_name, $field_type)) {
					$field_type[$column_name] = $column;
				}
			}
		}

		//check if the table is exsist
		$isExists = $this->query("SELECT * FROM  information_schema.columns WHERE table_schema = 'diod' 
		AND table_name = '".$this->escape_string($table)."'");
		//if yes
		if($isExists->num_rows >= 1) {
			//Alter

			$current = [];
			while ($row = $isExists->fetch_assoc()) {
				$current[$row['COLUMN_NAME']] = 1;
			}

			foreach($field_type as $column => $v) {
				if(array_key_exists($column, $current) == false) {
					$this->query("ALTER TABLE diod.`".$this->escape_string($table)."` ADD `".$this->escape_string($column)."` ".$v.";");
				}
			}
		} else {
			//if no
			$q_string= "CREATE TABLE IF NOT EXISTS `".$this->escape_string($table)."` (";
			foreach ($field_type as $key => $value) {
				$q_string .= "`".$this->escape_string($key)."` ".$value.","; 
			}
			$q_string.= " PRIMARY KEY  (`id`),";
			$q_string.= " KEY  (`key`())";
			$this->query($q_string);
		}
	}

	public function _mysqlInsertData($data, $groupKey, $index){
		$table = get_class($this->_checker);
		
		$toBeInserted = [
			'key' => $this->escape_string($groupKey),
                        'cron_id' => $this->escape_string($this->_cronId)
		];
		
		foreach ($data as $key => $value) {
			$toBeInserted[$this->cleaner($key)] = $this->escape_string($value);
		}
		

		$q_str = "INSERT INTO `$table` SET";
		$i = 0;
		foreach ($toBeInserted as $key => $value) {
			$q_str .= ($i==0?"":",")." `$key` = '$value'";
                        $i++;
		}
		$this->query($q_str);
		unset($toBeInserted);
	} 

}
