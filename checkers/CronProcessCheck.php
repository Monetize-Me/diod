<?php

include_once(__DIR__.'/../lib/interfaces/CheckerInterface.php');
include_once(__DIR__.'/../lib/ClassError.php');

/** 
* @class CronProcessCheck
*/
class CronProcessCheck extends ClassError implements CheckerInterface{
	
	private $_path = "";
	private $_data = [];
	protected $_settings = []; 
	  
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
			$this->addError($this->_path.' should be a path for '.__CLASS__);
		}

		return !$this->hasError();
 	}

	public function _readData(){
		return $this->_data;
	}

	public function _run(){
		exec('ps -eo lstart,cmd | grep batch.php', $output_arr);
		array_shift($output_arr);
		foreach($output_arr as &$proceses)
		{
			$instanceRunning = 1;

			$proceses = explode(' ', trim($proceses));
			$fileEXT = pathinfo(end($proceses));
			if(array_key_exists('extension', $fileEXT) && $fileEXT['extension'] != "php"){
				continue;
			}
			$time = strtotime(implode(" ", array_slice($proceses, 0, 4)));
			$func = str_replace("monetizeme/batch/Controller/","", end($proceses));
			if($func == 'batch.php') continue;
			if(array_key_exists($func, $this->_data) == true)
			{
				$instanceRunning += $this->_data[$func]['instanceRunning'];
			}
			$this->_data[$func] = array("func"=>$func, "stime"=>$time, "instanceRunning"=>$instanceRunning);
		}

		if (is_dir($this->_path)) {
			if ($dh = opendir($this->_path)) {
				while (($dir = readdir($dh)) !== false) {

					if(is_dir($this->_path."/".$dir) && $dir != '.' && $dir != '..'){
						chdir($this->_path."/".$dir);
						$KOfiles = glob('*.KO');
						$OKfiles = glob('*.OK');
						foreach ($KOfiles as $key => $value) {
							$key = $dir."/".str_replace(".KO", "", $value);
							if(!array_key_exists($key, $this->_data)) {
								$this->_data[$key] = array('instanceRunning'=>0);
							}
							$mtime = filemtime($value);
							$this->_data[$key] = array_merge($this->_data[$key], array("lastTimeKO"=>$mtime, 'lastTime'=>$mtime));					
						}
						foreach ($OKfiles as $key => $value) {
							$key = $dir."/".str_replace(".OK", "", $value);
							$mtime = filemtime($value);
							if(!array_key_exists($key, $this->_data)) {
								$this->_data[$key] = array('instanceRunning'=>0, 'lastTime'=> $mtime);
							}else if(array_key_exists('lastTime', $this->_data[$key])){
								if($mtime > $this->_data[$key]['lastTime']){
									$this->_data[$key]['lastTime'] = $mtime;
								}
							}else{
								$this->_data[$key]['lastTime'] = $mtime;
							}
							$this->_data[$key] = array_merge($this->_data[$key], array("lastTimeOK"=>$mtime));					
						}
					}		
				}
				closedir($dh);
			}
		}
		$tmp = ["CronProcessCheck"=>[]];
		foreach($this->_data as $key => $value){
			$value["func"] = $key;
			$tmp["CronProcessCheck"][] = $value; 
		}
		$this->_data = $tmp;

	}




}

