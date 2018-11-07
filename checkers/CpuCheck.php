<?php

include_once(__DIR__.'/../lib/interfaces/CheckerInterface.php');
include_once(__DIR__.'/../lib/ClassError.php');

/** 
* @class CpuCheck 
*/

class CpuCheck extends ClassError implements CheckerInterface{

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
		exec("grep 'cpu ' /proc/stat | awk '{usage=($2+$4)*100/($2+$4+$5)} END {print usage}'", $stat);
		if(is_array($stat) && count($stat) == 1) {
			$this->_data['CpuCheck'] = array(array("value"=>doubleval($stat[0])));
		} else {
			$this->_data['CpuCheck'] = array(array("value"=>doubleval(-1)));
		}
	}
}
