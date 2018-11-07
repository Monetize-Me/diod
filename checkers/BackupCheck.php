<?php 
include_once(__DIR__.'/../lib/interfaces/CheckerInterface.php');
include_once(__DIR__.'/../lib/ClassError.php');


/** 
* @class BackupCheck 
*/
class BackupCheck extends ClassError implements CheckerInterface{
	
	private $_path = "";
	private $_data = [];
	private $_names = [];
	protected $_settings = [];

	/**
	* @function __construct
	* @param path string required
	* @param names string required
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
		$this->_names = $this->mandatory('names');
		if(!is_dir($this->_path)){
			$this->addError("The path is not set!");
		}
		return !$this->hasError();
	}

	public function _readData(){
		return $this->_data;
	}

	public function _run(){
		$files = scandir($this->_path, SCANDIR_SORT_ASCENDING);
		$this->_data = $this->fileGroup($files);
		unset($files);

	}
	public function _groupKey($file){
		return preg_replace('/(([0-9]+)|(\.[a-z0-9]{1,4}))/i','', $file);
	}
	private function fileGroup($files){
		$group = [];
		foreach($files as $file){
			if($file == '.' || $file == '..') continue;

			$groupKey = $this->groupKey($file);
			if(!isset($group[$groupKey])){
				$group[$groupKey] = [];
			}
			$fileStat = stat($this->_path."/".$file);
			$group[$groupKey][] = [
					'name' => (array_key_exists($groupKey, $this->_names) ? $this->_names[$groupKey] : $groupKey),	
					'file'=>$file, 
					'filemodifieddate'=>$fileStat['mtime'], 
					'filesize'=>$fileStat['size'], 
					'filecreationdate'=>$fileStat['ctime'],
					'filegroup'=>$fileStat['gid'],
					'fileowner'=>$fileStat['uid'],
					'fileaccessdate'=>$fileStat['atime']
			];
		}
		// var_dump($group);
		// die;
		return $group;		
	}

}
