<?php

include_once(__DIR__.'/../lib/interfaces/CheckerInterface.php');
include_once(__DIR__.'/../lib/ClassError.php');


/** 
* @class MemcachedSizeCheck
*/
class MemcachedSizeCheck extends ClassError implements CheckerInterface{

	private $_data = [];
	private $_ip = "";
	private $_port = "";

	/**
	* @function __construct
	* @param ip number required
	* @param port number required
	*/
	public function __construct(array $settings, $debug = 0){
		$this->setDebug($debug);
		$this->_settings = $settings;
		if($this->checkSettings()){
			$this->run();
		}
	}
	public function _checkSettings(){
		$this->_port = $this->get('port', '11211');
		$this->_ip = $this->mandatory('ip');
		return !$this->hasError();
	}

	public function _readData(){
		return $this->_data;
	}

	public function _run(){
		$bytes = 0;
		$limit_bytes = 0;
		exec('echo "stats" | nc -w 1 '.$this->_ip.' '.$this->_port.'', $stats);
		foreach($stats as $stat) {
			if(strpos($stat, 'STAT bytes ') > -1) {
				$bytes = intval(str_replace('STAT bytes ', '', $stat));
			}

			if(strpos($stat, 'STAT limit_maxbytes ') > -1) {
				$limit_bytes = intval(str_replace('STAT limit_maxbytes', '', $stat));
			}			
		}
		
		if($limit_bytes > 0) {
			$this->_data['MemcachedSizeCheck'] = array(array("value"=>($bytes/$limit_bytes)*100));
		} else {
			$this->_data['MemcachedSizeCheck'] = array(array("value"=>-1));
		}

		// var_dump($this->_data);
	}
}
