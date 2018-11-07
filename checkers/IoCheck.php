<?php

include_once(__DIR__.'/../lib/interfaces/CheckerInterface.php');
include_once(__DIR__.'/../lib/ClassError.php');

/** 
* @class IoCheck
*/

class IoCheck extends ClassError implements CheckerInterface{

	private $_data = [];

	/**
	* @function __construct
	*/
	public function __construct(array $settings, $debug = 0){
		$this->setDebug($debug);
		$this->_settings = $settings;
		if($this->checkSettings()){
			$this->run();
		}
	}

	public function _checkSettings(){
		return true;
	}

	public function _readData(){
		return $this->_data;
	}

	public function _run(){
		exec("iostat -c", $stats);
		foreach($stats as $k => $stat) {
			if(strpos($stat, "avg-cpu: ") > -1) {
				$stat = trim(str_replace("avg-cpu: ", "", $stat));
				preg_match_all('/([%a-z]+)/i', $stat, $keys);


				$stat = trim($stats[++$k]);
				preg_match_all('/([%0-9\.]+)/i', $stat, $values);
				$this->_data = array("IoCheck" => array(array_combine(end($keys), end($values))));
				break;
			}
		}
	}
}
