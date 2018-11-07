<?php
include_once(__DIR__."/ClassError.php");


class Mysql extends classError {
	protected $mysql;

	public function __construct() {
		$this->mysql = new mysqli("127.0.0.1","mmadmin","DB48fRt15vdY","diod");
		if (mysqli_connect_errno()) {
			$this->addError("Failed to connect to MySQL: " . mysqli_connect_error());
		}
		return $this;
	}

	public function __call($name, $args = []) {
		if(method_exists($this->mysql, $name)) {
			if(($this->checkDebug(MadnessDebug::DEBUG_MYSQL)) && $name == "query") {
				$this->displayDebug("[MYSQL] [QUERY] ".$args[0], MadnessDebug::DEBUG_MYSQL);
				$ret = call_user_func_array(array($this->mysql, $name), $args);
				if($ret === false) {
					$this->displayDebug("[MYSQL] [QUERY] [ERROR SQL QUERY] ".$args[0], MadnessDebug::DEBUG_MYSQL);
					$this->displayDebug("[MYSQL] [QUERY] [ERROR MESSAGE] ".$this->mysql->error, MadnessDebug::DEBUG_MYSQL);
				}

				return $ret;
			} else {
				return 	call_user_func_array(array($this->mysql, $name), $args);
			}
		} else {
			return parent::__call($name, $args);
		}
	}

	public function __get($name) {
		return $this->mysql->$name;
	}

	public function __set($name, $value) {
		if($this->mysql)$this->mysql->$name = $value;
	}

	public function __destruct() {
		if($this->mysql)$this->mysql->close();
	}
}
