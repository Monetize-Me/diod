<?php
include_once(__DIR__.'/ServerSpaceCheck.php');

/** 
* @class ServerInodeCheck
*/

class ServerInodeCheck extends ServerSpaceCheck {
	protected $_option = "--output=ipcent,iavail,target";
}

