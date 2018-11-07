<?php
include_once(__DIR__.'/../lib/interfaces/ReportingInterface.php');
include_once(__DIR__.'/../lib/ClassError.php');
include_once(__DIR__.'/../lib/Conditions.php');

class ActionReporting extends Mysql implements ReportingInterface{
	
	protected $_checker;
	protected $_settings = [];
	protected $_action;
	protected $_conditions = [];
	
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
		if((is_array($this->_settings)) && ($this->_checker->hasError() == false) && (array_key_exists('action', $this->_settings)) && (is_callable($this->_settings['action']))){
			$this->_action = $this->_settings['action'];
		}else{
			$this->addError("Action not set!");
		}
		return !$this->hasError();
	}

	public function _checkConditions($data, $groupKey, $index)
	{
		$cond_true_count = 0;
		$conditions = new Conditions($this->_checker, $this->_debug);
		$return_values = [];
	

		if(empty($this->_settings['conditions'])){
			$this->_settings['conditions'] = [];
		}
		foreach ($this->_settings['conditions'] as $settsKey => $settsValue) {
			if($settsKey == $groupKey || $settsKey == "*"){	
				if($this->checkDebug(MadnessDebug::DEBUG_CONDITION)) {
					$this->displayDebug("[CONDITION] Matched the key [".$groupKey."]", MadnessDebug::DEBUG_CONDITION);
				}
				$settsValue['args'][] = $groupKey;
				$settsValue['args'][] = $index;
				$settsValue['args'][] = $data;
				if(call_user_func_array(array($conditions, $settsValue['condition']), $settsValue['args']) == true){
					$cond_true_count++;
					$return_values[] = $settsValue; 	
				}
			} else {
				if($this->checkDebug(MadnessDebug::DEBUG_CONDITION)) {
					$this->displayDebug("[CONDITION] Looking for [".$settsKey."] But we have [".$groupKey."] maybe the next one", MadnessDebug::DEBUG_CONDITION);
				}
			}
		}

		if($this->checkDebug(MadnessDebug::DEBUG_CONDITION)) {
			$this->displayDebug("[CONDITION] count valid : ".$cond_true_count."  count settings :  ".count($this->_settings['conditions'])."      ".var_export($this->_settings['conditions'], true), MadnessDebug::DEBUG_CONDITION);
		}
		
		if($cond_true_count == count($this->_settings['conditions'])){
			call_user_func($this->_action, $data, $groupKey, $index, $return_values);
		}else{
			echo 'Checks are false, nothing to send'."\n";
		}

	}

	public function _traverse(array $data, $groupKey)
	{	
		foreach ($data as $key => $dataValue) {
	 		$this->checkConditions($dataValue, $groupKey, $key);
		}
	}

	public function _read()
	{
		$data = $this->_checker->readData();
		foreach ($data as $key => $value) {
			$this->traverse($value, $key);
		}
	}//endread
}
