<?php
include_once(__DIR__.'/../lib/interfaces/CheckerInterface.php');
include_once(__DIR__.'/../lib/ClassError.php');

/** 
* @class ServerSpaceCheck
*/
class ServerSpaceCheck extends ClassError implements CheckerInterface{
	
	protected $_path = "";
	protected $_data = [];
	protected $_option = "--output=pcent,avail,target";

	/**
	* @function __construct
	* @param path text required
	*/
	public function __construct(array $settings, $debug = 0){
		$this->setDebug($debug);
		$this->_settings = $settings;
		
		if($this->checkSettings()){
			$this->run();
		}
	}

	public function _checkSettings(){
		$this->_path = $this->mandatory('path');
		
		if(!is_dir($this->_path)){
			$this->addError("Your directory isn't exists");
		}
		return !$this->hasError();
	}

	public function _readData(){
		return $this->_data;
	}

	public function _run(){
		$space = [];
		exec('df '.$this->_option, $output_arr);
		array_shift($output_arr);
				
		foreach ($output_arr as &$k) {
			 $k = explode(' ', trim($k));
			 $k = array_values(array_filter($k));
			 if(!isset($k[2]))  continue;
			 $space['ServerSpaceCheck'][] = [
				'drive' => $k[2],
				'availableSpace' =>intval($k[1]),
				'usedSpace' =>intval($k[0])
			 ];
		}
		$this->_data = $space;
	}
}
