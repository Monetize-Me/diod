<?php

include_once(__DIR__.'/../lib/interfaces/CheckerInterface.php');
include_once(__DIR__.'/../lib/ClassError.php');

/**
* @class ServiceStatusCheck
*/

class ServiceStatusCheck extends ClassError implements CheckerInterface{

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
		exec("systemctl --type=service --state=running --no-pager --no-legend list-units", $running);
		foreach($running as &$run) {
			preg_match('/^([@a-zA-Z0-9\.\-_]+)[\s]+/i', $run, $matches);
			$run = str_replace('.service', '', $matches[1]);
		}

		$services = glob("/etc/init.d/*");
		foreach($services as $service) {
			$perms = fileperms($service);

			if(($perms & 0x0001) && !(($perms & 0x0200))) {
				$serviceName = str_replace('/etc/init.d/', '', $service);

				$this->_data[$serviceName][] = [
					"name" => $serviceName,
					"running" => in_array($serviceName, $running)
				];
			}
		}
		//exec('service --status-all', $output, $test);
		///die(var_dump($service));
		/*foreach($output as $o) {
			// [ ? ]  umountfs
			preg_match('/^ \[ ([\?\+\-]) \]\s+([a-z0-9]+)/i', $o, $matches);
			var_dump($matches);
		}*/
	}
}
