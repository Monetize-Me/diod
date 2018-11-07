<?php

include_once(__DIR__.'/../lib/interfaces/CheckerInterface.php');
include_once(__DIR__.'/../lib/ClassError.php');
include_once(__DIR__.'/PhpErrorCheck.php');

/** 
* @class ApacheErrorCheck
*/
class ApacheErrorCheck extends ClassError implements CheckerInterface{

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
		$confs = glob("/etc/apache2/sites-enabled/*.conf");
		foreach($confs as $conf) {
			$contents = file_get_contents($conf);
			$contents = explode("</VirtualHost>", $contents);
			foreach($contents as $content) {
				$currentSite = "";
				$error_log = "";
				$access_log = "";
				$content = explode("\n", $content);
				foreach($content as $line) {
					$line = trim($line);
					if(strpos($line, 'ServerName ')>-1 && $line[0] != "#") {
						$currentSite = trim(str_replace('ServerName', '', $line));
					}

					if(strpos($line, 'ServerAlias ')>-1 && $line[0] != "#" && $currentSite == "") {
						$currentSite = trim(str_replace('ServerAlias', '', $line));
					}//we do not enter here in any occasion

					if(strpos($line, 'ErrorLog ') >-1 && $line[0] != "#") {
						if(empty($currentSite)) {
							$currentSite = 'default';
						}

						$error_log = trim(str_replace('ErrorLog', '', $line));
					}

					if(strpos($line, 'CustomLog ') >-1 && $line[0] != "#") {
						if(empty($currentSite)) {
							$currentSite = 'default';
						}
						$access_log = explode(' ', trim(str_replace('CustomLog', '', $line)))[0];
					}
				}


				if($currentSite == "" && !array_key_exists('default', $this->_data)) {
					$currentSite = "default";
				}

				if($currentSite != "") {
					$this->_data[$currentSite] = array(
						"error_log" => $error_log,
						"access_log" => $access_log
					);
				}

			}
		}

		

		if(array_key_exists('default', $this->_data) && isset($this->_data['default']['error_log'])) {
			foreach($this->_data as $key => $value) {
				if($key == 'default') continue;
				if(!array_key_exists('error_log', $value) || empty($value['error_log'])) {
					$this->_data[$key]['error_log'] = $this->_data['default']['error_log'];
				}

				if(!array_key_exists('access_log', $value) || empty($value['access_log'])) {
					$this->_data[$key]['access_log'] = $this->_data['default']['access_log'];
				}
			}
		}



		$errCheck = [];
		$filenames = [];
		foreach ($this->_data as $key => $value) {
			$arg_arr = explode('/',$value['error_log']);
			$file_name = end($arg_arr);
			array_pop($arg_arr);
			$path = implode('/', $arg_arr);



			if(array_key_exists($file_name, $filenames) == false) {
				$errCheck = new PhpErrorCheck([
					'path'=> $path,
					'file'=> $file_name,
					'class' => 'ApacheErrorCheck', 
					'regex' => '/^\[([a-z]{2,3} [a-z]{2,3} [0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}\.[0-9]{1,10} [0-9]{4})\] \[authz_core:error/i' 
				], $this->_debug);

				$tmp = $errCheck->readData();
				if(is_array($tmp) && array_key_exists('PhpErrorCheck', $tmp)) {
					$filenames[$file_name] = $tmp["PhpErrorCheck"][0];
					$this->_data[$key] = array_merge($filenames[$file_name], $this->_data[$key]);
				}
			} else {
				$this->_data[$key] = array_merge($filenames[$file_name], $this->_data[$key]);
			}
		}

		$tmp = ["ApacheErrorCheck"=>[]];
		foreach($this->_data as $key => $value){
			$value["key"] = $key;
			$tmp["ApacheErrorCheck"][] = $value; 
		}
		$this->_data = $tmp;
	}
}
