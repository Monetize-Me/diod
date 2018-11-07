<?php

include_once(__DIR__.'/../lib/interfaces/CheckerInterface.php');
include_once(__DIR__.'/../lib/ClassError.php');

class MysqlStatsCheck extends ClassError implements CheckerInterface{

	private $_data = [];
	private $_host = "";
	private $_user = "";
	private $_password = "";

	public function __construct(array $settings, $debug = 0){
		$this->setDebug($debug);
		$this->_settings = $settings;
		if($this->checkSettings()){
			$this->run();
		}
	}

	public function _checkSettings(){
		$this->_host = $this->mandatory('host');
		$this->_user = $this->mandatory('user');
		$this->_password = $this->mandatory('password');
		$this->_database = $this->mandatory('database');

		return $this->hasError();
	}

	public function _readData(){
		return $this->_data;
	}

	public function _run(){
		$mysqli = new mysqli($this->_host, $this->_user, $this->_password, $this->_database);	

		if ($mysqli->connect_errno) {
				$this->addErrors();
			return $this->hasError();  
			// $mysqli->connect_error);
		}
	/*

	
	SET GLOBAL userstat=1;
Commands

Userstat provides the following new FLUSH and SHOW commands.

FLUSH Commands

These commands discard the information stored in the specified information schema table.

FLUSH TABLE_STATISTICS
FLUSH INDEX_STATISTICS
FLUSH USER_STATISTICS
FLUSH CLIENT_STATISTICS
SHOW commands

These commands are another way to display the information stored in the information schema tables. WHERE clauses are accepted. LIKE clauses are accepted but ignored.

SHOW CLIENT_STATISTICS
SHOW USER_STATISTICS
SHOW INDEX_STATISTICS
SHOW TABLE_STATISTICS



SET GLOBAL performance_schema=1 (not sure)

UPDATE performance_schema.setup_consumers SET ENABLED = 'YES';
UPDATE performance_schema.setup_instruments SET ENABLED = 'YES', TIMED = 'YES';


SHOW ENGINE INNODB STATUS


SET GLOBAl profiling = 1;

SHOW PROFILE ALL;




SHOW VARIABLES LIKE 'max_connections'; + show processlist = % connec

	*/




		$mysqli->query("GET DIAGNOSTICS CONDITION 2 @sqlstate = RETURNED_SQLSTATE, @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;");

		if(!($result = $mysqli->query("SELECT @sqlstate, @errno, @text;"))) {
			$this->addError();
			return $this->hasError();
		} else {
			$this->_data['errors'] = [];
			while ($row = $result->fetch_object()){
				$this->_data['errors'][] = (array)$row;
			}
		}
		


		if(!($result = $mysqli->query("SHOW COUNT(*) as nbErrors ERRORS"))) {
			$this->addError();
						return $this->hasError();
		} else {
			$this->_data['nbErrors'] = 0;
						while ($row = $result->fetch_object()){
								//$this->_data['nbErrors'] = ((array)$row)['nbErrors'];
						}
		}
		

		$mysqli->close();
	}
}
