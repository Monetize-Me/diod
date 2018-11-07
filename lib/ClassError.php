<?php

class classError extends MadnessDebug {

	/** @var Holds the single instance of the class */
	private static $_instance = null;

	private $errors = [];
	private $success = true;


	public function addError($str = '') {
		if($this->checkDebug(MadnessDebug::DEBUG_ERROR)) {
			$this->displayDebug("\e[38;5;63m[MESSAGE]\e[38;5;255m ".$str);
		}
		$this->errors[] = $str;
		$this->success = false;
	}
	// This is a singleton class. Use getInstance() to load the class.
	public static function getInstance() {
		if ( null == self::$_instance ) {
        	    self::$_instance = new self;
        	}
        	return self::$_instance;
    	}

	// construct
	function __construct() {

	}

	public function mandatory($key, $data = NULL) {
		if(empty($this->_settings) || !is_array($this->_settings)) {
			$this->addError('The settings isn\'t set');
			return false;
		}
		if(is_array($data)){
			if(array_key_exists($key, $data)){
				return $data[$key];
			}else{
				$this->addError('The field '.$key.' is mandatory for '.get_called_class());
			}
		}else{		
			if(array_key_exists($key, $this->_settings)) {
				return $this->_settings[$key];
			} else {
				$this->addError('The field '.$key.' is mandatory for '.get_called_class());
			}
		}
		return false;
	}

	public function get($key, $default = "") {
		if(empty($this->_settings) || !is_array($this->_settings)) {
			$this->addError('The settings isn\'t set');
			return false;
		}

		if(array_key_exists($key, $this->_settings)) {
                        return $this->_settings[$key];
		}
		return $default;
	}


	// Shows success message if any.
	public function hasSuccess() {
		return !$this->hasError();;
	}


	// show errors, if any
	public function getAllErrors() {
		return $this->errors;
	}

	// boolen if there is an error
	public function hasError() {
		return !(count($this->errors) == 0 && $this->success);	
	}
}
