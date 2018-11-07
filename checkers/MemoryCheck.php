<?php

include_once(__DIR__.'/../lib/interfaces/CheckerInterface.php');
include_once(__DIR__.'/../lib/ClassError.php');

/** 
* @class MemoryCheck
*/
class MemoryCheck extends ClassError implements CheckerInterface{

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
		$stats = file('/proc/meminfo');
		$this->_data['MemoryCheck'] = array(array());
		foreach($stats as $stat) {
			preg_match('/^([a-z0-9_\(\)]+):\s+([0-9]+)\s+([a-z]+)/i', $stat, $o);
			$this->_data['MemoryCheck'][0][$o[1]] = intval($o[2]);
			//$this->_data['MemoryCheck'][0][$o[1].'_kind'] = $o[3];
		}

		if(array_key_exists("MemTotal", $this->_data['MemoryCheck'][0]) && array_key_exists('MemFree', $this->_data['MemoryCheck'][0])) {
			$total = $this->_data['MemoryCheck'][0]['MemTotal'];
			$free = $this->_data['MemoryCheck'][0]['MemFree'];
			$this->_data['MemoryCheck'][0]['used'] = intval(($total == 0 ? $total : 100-(($free/$total)*100)));
			//$this->_data['MemoryCheck'][0]['used_kind'] = '%';
		} else {
			$this->_data['MemoryCheck'][0]['used'] = -1;
			//$this->_data['MemoryCheck'][0]['used_kind'] = '%';
		}

	}
}
