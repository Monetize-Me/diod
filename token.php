<?php
include_once('./lib/MadnessDebug.php');
include_once('./checkers/CronProcessCheck.php');
include_once('./checkers/ServiceStatusCheck.php');
$rc = new ReflectionClass('CronProcessCheck');
$ssc = new ReflectionClass('ServiceStatusCheck');
var_dump($rc->getDocComment());
var_dump($ssc->getDocComment());
/*$docComments = array_filter(token_get_all(implode("\n",$file)), function($entry)
    {
	return true;
        return $entry[0] == T_COMMENT;
    });

die(var_dump($docComments));*/
