<?php
include_once(__DIR__.'/../lib/interfaces/CheckerInterface.php');
include_once(__DIR__.'/../lib/Mysql.php');

/** 
* @class PhpErrorCheck
*/
class PhpErrorCheck extends Mysql implements CheckerInterface{

	private $_data = [];
	private $_path = "";
	private $_file = ""; //rest.mntzm.com.log - 2018745630
	private $_lastPosition;
	private $_regex = '';
	protected $_settings = [];
	
	/**
	* @function __construct
	* @param path text required
	* @param file text required
	* @param regex text required
	*/
	public function __construct(array $settings, $debug = 0){
		$this->setDebug($debug);
		parent::__construct();

		$this->_settings = $settings;

		if($this->checkSettings()){
			$this->run();
		}
	}

	public function _getLastPosition(){
		$table = 'save_tell';
		$data = $this->query("SELECT tell FROM $table WHERE file_name = '".$this->escape_string($this->_path."/".$this->_file)."' ");
		$tell = 0;
		while($row = $data->fetch_assoc()){
			$tell = $row['tell'];
		}
		$this->_lastPosition = $tell;
	}

	public function _checkSettings(){
		$this->_path = $this->mandatory("path");
		$this->_file = $this->mandatory("file");
		$this->_regex = $this->mandatory("regex");
		if(!is_dir($this->_path)){
			$this->addError("the path isn't a real directory");
		}

		return !$this->hasError();
	}

	public function _readData(){
		return $this->_data;
	}

	public function _run(){
		$this->getLastPosition();

		$keyFile = $this->_path."/".$this->_file;
		$fp = fopen($keyFile, 'r');
		fseek($fp, 0, SEEK_END);

		$endOfFilePos = ftell($fp);

		if($this->_lastPosition > $endOfFilePos) {
			$this->_lastPosition = 0; //log rotate dedection
		}

		fseek($fp, $this->_lastPosition);
		$errorPerMinutes[$keyFile] = [];
		while (($buffer = fgets($fp)) !== false) {
			if(preg_match($this->_regex, $buffer, $matches)) {
					if(array_key_exists('class', $this->_settings)){
						$date = explode('.',$matches[1]);
						$key = date("Y-m-d h:i",strtotime($date[0]));
					}else{
						$key = date("Y-m-d h:i",strtotime($matches[1]));
					}

					if(!array_key_exists($key, $errorPerMinutes[$keyFile])) {
							$errorPerMinutes[$keyFile][$key] = 0;
					}
					$errorPerMinutes[$keyFile][$key]++;
			}
		}
		// KEEP FOR FUTURE REFERENCE		
		// $query = preg_replace_callback('#(?<facet>\w+):(?<value>(.(?!(' . implode('|', self::FACETS) . '):))+)#', function($m) use($that, &$params, &$countryCode, &$filters)
		// ]
		// KEEP FOR FUTURE REFERENCE		
		
		$avg = 0;
		if(($cnt = count($errorPerMinutes[$keyFile])) > 0) {
			$avg = array_sum($errorPerMinutes[$keyFile]) / $cnt;
		}
		 $errArr = [
			'average' => $avg,
			'tell' => $this->_lastPosition = ftell($fp),
			'file' => $keyFile
		];
		$this->_data = array("PhpErrorCheck"=>array($errArr));

		fclose($fp);

		$this->setLastPosition();

	}

	public function _setLastPosition(){
		$table = 'save_tell';
		$this->query("INSERT INTO $table SET file_name = '".$this->escape_string($this->_path."/".$this->_file)."', tell = '".$this->escape_string($this->_lastPosition)."' ON DUPLICATE KEY UPDATE tell = '".$this->escape_string($this->_lastPosition)."';");
			
	}





}
