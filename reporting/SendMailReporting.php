<?php
include_once(__DIR__.'/ActionReporting.php');

class SendMailReporting extends ActionReporting
{
	private $_mail = "";
	protected $_conditions = [];
	public function __construct($checker, array $settings, $debug = 0) {
		$this->setDebug($debug);
		parent::__construct($checker, $settings, $debug);
	}

	public function _checkSettings()
	{
		$that = $this;
		$this->_mail = $this->mandatory('mail');
		$this->_conditions = $this->mandatory('conditions');
		$this->_settings['action'] = function($data, $groupKey, $index, $condition) use($that) {
			$that->sendMail($condition);
		};
		return parent::_checkSettings();
	}
	public function _sendMailNoCondition($mail_content){

		$class_name = get_class($this->_checker);

		return mail($this->_mail, "[MADNESS] ".$class_name, $mail_content);

	}

	public function _sendMail(array $conditions){
		$mail_content = "";
		$class_name = get_class($this->_checker);
		foreach ($conditions as $condition) {
			$conditionFunc = $condition['condition'];
			$conditionCheckValue = $condition['args'][0];
			$conditionCheckKey = $condition['args'][1];
			$data = end($condition['args']);

				if(array_key_exists('key', $data)){
					$log_name = $data['key'];
				}else{
					$log_name = "";
				}

				$mail_content .= $class_name." is "." [".$data[$conditionCheckKey]."] ".$conditionFunc." ".$conditionCheckValue."<br> FOR ".$log_name;
		}
		return mail($this->_mail, "[MADNESS] ".$class_name, $mail_content);

	}

}
