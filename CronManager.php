<?php
spl_autoload_register(function ($class) {
	if(preg_match('/Check$/', $class)== true){
		include_once(__DIR__.'/checkers/' . $class . '.php');		
	}elseif(preg_match('/Reporting$/', $class) == true){
		include_once(__DIR__.'/reporting/' . $class . '.php');		
	}else{
		include_once(__DIR__.'/lib/'.$class.'.php');
	}
});

// include_once(__DIR__.'/lib/Mysql.php');

class CronManager extends Mysql 
{
	public function __construct($debug = 0) {
		$this->setDebug($debug);
		parent::__construct();
		$this->run();
	}
	
	protected function _run(){
		$cronjob = new CronjobHistory();
		$cronId = $cronjob->addHistory();

		$data = $this->query("SELECT * FROM cronManager WHERE FROM_UNIXTIME(UNIX_TIMESTAMP(last_run)+recurrence_time) <= NOW() AND enable = 1");
		// $this->query("UPDATE cronManager SET last_run = NOW() WHERE id IN( SELECT * FROM (SELECT x.id FROM cronManager as x WHERE FROM_UNIXTIME(UNIX_TIMESTAMP(x.last_run)+x.recurrence_time) <= NOW()) as y)");
		while ($row = $data->fetch_assoc()){
			if(!is_array($settings = json_decode($row['checker_settings'], true))){
				$this->addError('Your settings is not in valid JSON! for cronManager id : #'.$row["id"]);
		}else{
				$checker = new $row['checker_name']($settings, $this->_debug);
				$reports = json_decode($row['report'],true);
				if(is_array($reports)) {
					foreach ($reports as $key => $value) {
						$value['setts']['cronId'] = $cronId;
						$report = new $value['name']($checker, $value['setts'], $this->_debug);
					}
				} else {
					$this->addError('Your report settings are false, for cronManager id : #'.$row["id"]);
				}
 			}
		}	
	}

}
$debug_mode = 0;
if (php_sapi_name() == "cli" && isset($argv[1])) {
	$debug_mode = intval($argv[1]);

} elseif(array_key_exists('debug', $_GET)) {
	$debug_mode = intval($_GET['debug']);
}
$cronManager = new CronManager($debug_mode);
$cronManager->displayDebug("\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n");
