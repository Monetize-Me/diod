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

$cronjob = new CronjobHistory();
$cronId = $cronjob->addHistory();



$result = array(
	//  'ioCheck' => new IoCheck([]),
	  // 'memoryCheck' => new MemoryCheck([]),
	//  'cpuCheck' => new CpuCheck([]),
	// 'memcachedSizeCheck' => new MemcachedSizeCheck(
	// 	[
	// 		'port' => '11211',
	// 		'ip' => '127.0.0.1'
	// 	]
	// ),
	// 'serviceStatusCheck' => new ServiceStatusCheck([]),
	/*'errorsLogPhpCheck' => new PhpErrorCheck([
		'path' => '/home/appdeploy/www/log/',
		'file' => 'rest.mntzm.com.log',
		'regex' => '/^\[([0-9]{1,2}\-[a-z]{2,3}-[0-9]{4} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}) [a-z\/]+\] \[cupidphp\\\common\\\Debug::error/i'
		// 'lastPosition' => 2018745630
	]),*/
	// 'cronProcessCheck' => new CronProcessCheck([
	// 	'path' => '/home/appdeployy/www/publisher/diod/results'
	// ]),
	 // 'backupCheck' => new BackupCheck([
	 // 	'path' => '/data/backup/db/',
		// 'names' => array('--'=>"backup checked")
	 // ]),
	// 'mysqlStatsCheck' => new MysqlStatsCheck([
	// 	'host' => 'localhost',
	// 	'user' => 'mmadmin',
	// 	'password' => 'DB48fRt15vdY',
	// 	'database' => 'apimixv2'
	// ]),
	 // 'apacheErrorCheck' => new ApacheErrorCheck([]),
	'serverSpaceCheck' => new ServerSpaceCheck(['path'=>'/'])
);

// $setts = [
// 	'used'=>['condition'=>'gt', 'args'=>[95, 'value']]
// ];//MemoryCheck - working

// $setts = [
// 	'conditions'=>[
// 		soundex('00-00-00-00.sql.gz')=>['condition'=>'compareWithPrevious', 'args'=>[10, 'filesize','percent', 'gt']]
// 	]
// ];//BackupCheck - working
// $setts = [
// 	'conditions'=>[
// 		'apache' => ['condition'=>'eq','args'=> ['apache','service']],
// 		'running'=> ['condition'=>'eq','args'=>[1,'running']]
// 	]
// ];
// $setts = [
// 	'conditions'=>[
// 		"%user"=>['condition'=>'gt', 'args'=>[2, "%user"]],
// 		"%idle"=>['condition'=>'gt', 'args'=>[92, "%idle"]]

// 	]
// ];//ioCheck good LVL_1 STRING
$setts = [
	'conditions'=>[
		"0000-00-00.sql.gz"=>['condition'=>'gt', 'args'=>[11,'filesize']]
	],
	"mail"=>"kroum.manoilov@akktis.com"
];

die(var_dump($result));
foreach ($result as $key => $value) {
var_dump($value);
die;
	//die;
	// $dbINSERTED = new InsertDbReporting($value, ['cronId'=>$cronId]);
	// $sendMail = new SendMailReporting($value, $setts);
}



// $serverSpaceCheck = new serverSpaceCheck(['path'=>'/']); //WORKS

// $cronProcessCheck = new cronProcessCheck(['path'=>'/home/appdeploy/www/publisher-dist/releases/current/diod/results']);
// $errorsCheck = new errorsCheck(['path'=>'/home/appdeploy/www/log']);

// $setts = [
// 	Diod::RED=>[
// 	soundex('00000000.sql')=>['condition'=>'compareWithPrevious', 'args'=>[10, 'filesize','percent', 'gt']]
// 	]
// ];
// $setts1 = [
// 	Diod::RED=> [
// 	'serverSpace'=>['condition'=>'compareSpace', 'args'=>[80,'usedSpace','gte']]
// 	]
// ];
// $setts2 = [
// 	Diod::RED=>[
// 	'cronCheck'=>['condition'=>'cronCheck', 'args'=>['startTime', 'command']]
// 	]
// ];
// $setts3 = [
// 	Diod::RED=>[
// 		'errorCheck'=>['condition'=>'calcErrors', 'args'=>[2018745630,'errCheck', 'lte']]
// 	]
// ];
// $diod = new Diod($apacheErrorCheck, $setts);
// $diod = new Diod($serverSpaceCheck, $setts1);
// $diod = new Diod($cronProcessCheck, $setts2);
// $diod = new Diod($errorsCheck, $setts3);
