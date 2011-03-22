<?php

include_once('init.php');

$response = array(
	'connected' => false
);

$attendeds = array(
	'db_driver',
    'db_host',
    'db_name',
    'db_user',
    'db_pass'
);
$found = 0;
foreach($attendeds as $attended){
	if(!isset($_POST[$attended]) || trim($_POST[$attended]) == ''){
		echo json_encode($response);
		exit();
	}
	else{
		$found++;
	}
}

if(count($attendeds) != $found){
	echo json_encode($response);
	exit();
}

function customError($severity, $message, $file, $line){
	if($severity == E_WARNING){
		if(preg_match("/mysql_connect/", $message)){
			return true;
		}
	}
	return false;
}


try{
	set_error_handler('customError');
	new tao_install_utils_DbCreator($_POST['db_host'], $_POST['db_user'], $_POST['db_pass'], $_POST['db_driver']);
	restore_error_handler();
	
	$response['connected'] = true;
}
catch(Exception $e){
	$response['connected'] = false;
}


echo json_encode($response);
exit();
?>