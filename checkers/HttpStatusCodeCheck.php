<?php
include_once(__DIR__.'/../lib/interfaces/CheckerInterface.php');
include_once(__DIR__.'/../lib/Mysql.php');

/**
* @class HttpStatusCodeCheck
*/
class HttpStatusCodeCheck extends Mysql implements CheckerInterface{

	private $_url = [];
  private $_statusCode = 0;
	protected $_settings = [];

	/**
	* @function __construct
	* @param url text required
	*/
	public function __construct(array $settings, $debug = 0){
		$this->setDebug($debug);
		parent::__construct();

		$this->_settings = $settings;

		if($this->checkSettings()){
			$this->run();
		}
	}


	public function _checkSettings(){
		$this->_url= $this->mandatory("url");
		return !$this->hasError();
	}

	public function _readData(){
		return $this->_data;
	}

	public function _run(){

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_HEADER=> true,
        CURLOPT_NOBODY=> true,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $this->_url,
        CURLOPT_FOLLOWLOCATION => true,

    ));
    $resp = curl_exec($curl);
		$err  = curl_error($curl);
	  $curl_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if(!empty($curl_status) && $curl_status != "" && empty($err)){
        $this->_statusCode = $curl_status;
    }

    curl_close($curl);
    $statusCodes = [
     'code' => $this->_statusCode,
     'url' => $this->_url
    ];
		$this->_data = array("HttpStatusCodeCheck"=>array($statusCodes));
		$this->insertLastStatus();

	}

	public function _insertLastStatus(){
		$table = 'HttpStatusCodeCheck';
		$this->query("INSERT INTO $table SET key = '".$this->_url."', code = '".$this->_statusCode."' ON DUPLICATE KEY UPDATE code = '".$this->_statusCode."';");

	}





}
