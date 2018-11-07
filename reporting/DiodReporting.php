<?php

include_once(__DIR__.'/../lib/interfaces/ReportingInterface.php');
include_once(__DIR__.'/ActionReporting.php');
include_once(__DIR__.'/../lib/Conditions.php');

/**
* @class DiodReporting
*/
class DiodReporting extends Mysql implements ReportingInterface
{
	const GREEN = 1; //1
	const YELLOW = 2;//2
	const RED = 4;// 4

	protected $_settings = [];
	protected $cronId = 0;
	protected $title = "";
	protected $url = "";

	/**
	* @function __construct
	* @param name text required
	* @param title text required
	* @param url text required
	* @param diodStatus enum('green','yellow','red') required
	*/
	public function __construct($checker, array $settings, $debug = 0)
	{
		$this->setDebug($debug);
		if($this->checkDebug(MadnessDebug::DEBUG_REPORT)) {
			$this->displayDebug("[REPORT] Constructor ".get_class($this), MadnessDebug::DEBUG_REPORT);
		}
		parent::__construct();
		// $this->getter = $getter
		$this->_checker = $checker;
		$this->_settings = $settings;

		if($this->checkSettings()){
			$this->read();
		}
	}

	public function _checkSettings()
	{
		$this->cronId = $this->mandatory('cronId');
		$this->title = $this->mandatory('title');
		$this->url = $this->mandatory('url');
		unset($this->_settings['cronId']);
		unset($this->_settings['title']);
		unset($this->_settings['url']);
		if(is_array($this->_settings) && isset($this->_settings[0])){
			foreach ($this->_settings as $settsKey => $settsValue) {
				$this->mandatory('diodStatus', $settsValue);
			}
		}
		return !$this->hasError();
	}

	public function readableConsts($val){
		switch($val){
			case DiodReporting::GREEN:
				return "green";
			case DiodReporting::RED:
				return "red";
			case DiodReporting::YELLOW:
				return "yellow";
		}
	}
	public function _read(){
		$data = $this->_checker->readData();

		$savedSatus = DiodReporting::GREEN;
		foreach ($data as $groupKey => $items) {
			$currentStatus = DiodReporting::GREEN;
			$cond_true_count = 0;
			$conditions = new Conditions($this->_checker, $this->_debug);
			$return_values = [];

			foreach ($items as $index => $itemValue) {
				usort($this->_settings, function($a,$b){
					return ($b['diodStatus'] > $a['diodStatus'])?  -1: 1;
				});

				$foundKey = false;
				foreach ($this->_settings as $settsKey => $settsValue) {
					foreach ($settsValue['conditions'] as $settsCondKey => $settsCondValue) {
						$cond_true_count = 0;
						if($settsCondKey == $groupKey || $settsCondKey == "*"){
							$foundKey = true;

							if($this->checkDebug(MadnessDebug::DEBUG_CONDITION)) {
								$this->displayDebug("[CONDITION] Matched the key [".$groupKey."]", MadnessDebug::DEBUG_CONDITION);
							}

							$settsCondValue['args'][] = $groupKey;
							$settsCondValue['args'][] = $index;
							$settsCondValue['args'][] = $itemValue;
							if(call_user_func_array(array($conditions, $settsCondValue['condition']), $settsCondValue['args']) == true){
								if($this->checkDebug(MadnessDebug::DEBUG_CONDITION)) {
									$this->displayDebug("[CONDITION] True:".var_export($settsCondValue['args'], true), MadnessDebug::DEBUG_CONDITION);
								}
								$cond_true_count++;
								$return_values[] = $settsCondValue;
							}
						} else {
							if($this->checkDebug(MadnessDebug::DEBUG_CONDITION)) {
								$this->displayDebug("[CONDITION] Looking for [".$settsCondKey."] But we have [".$groupKey."] maybe the next one", MadnessDebug::DEBUG_CONDITION);
							}
						}
					}

					if($this->checkDebug(MadnessDebug::DEBUG_CONDITION)) {
						$this->displayDebug("[CONDITION] count valid : ".$cond_true_count."  count settings :  ".count($settsValue['conditions'])."      ".var_export($settsValue['conditions'], true), MadnessDebug::DEBUG_CONDITION);
					}

					if($cond_true_count == count($settsValue['conditions'])){
						$currentStatus = $settsValue['diodStatus'];
						if($currentStatus > $savedSatus) {
							$savedSatus = $currentStatus;
						}
	 					if(array_key_exists('mail',$settsValue)){
	 						    $body = $this->title." is currently: ".$this->readableConsts($savedSatus);

	 							$mail = new SendMailReporting($this->_checker, array('mail'=>$settsValue['mail']));
	 							$mail->sendMailNoCondition($body);
	 					}
					}

				}


				var_dump($groupKey."     ======     ".$savedSatus);
				if($foundKey){

	 				$this	->query("INSERT INTO DiodReporting (`title`,`key`,`name`,`color`,`created_at`, `url`) VALUES('".$this->escape_string($this->title)."','".$this->escape_string($groupKey)."','".$this->escape_string(get_class($this->_checker))."','".$this->escape_string($savedSatus)."', NOW(), '".$this->escape_string($this->url)."') ON DUPLICATE KEY UPDATE `title` = '".$this->escape_string($this->title)."', color='".$savedSatus."', updated_at = NOW(), url = '".$this->escape_string($this->url)."' ");
 				}

			}


				// if(DiodReporting::RED &  $this->_settings['diodStatus']){
				// 	die('DIODED');
				// }elseif(DiodReporting::YELLOW &  $this->_settings['diodStatus']){

				// }else{
				// 	//GREEN STUFF
				// }
		}


	}


}
