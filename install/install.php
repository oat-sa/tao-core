<?php

/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */


if (isset($_SERVER['CONFIG_PATH'])) {
	define('CONFIG_PATH',$_SERVER['CONFIG_PATH']);
} else {
	define('CONFIG_PATH',dirname(__FILE__).'/../../generis/common');
}

require_once CONFIG_PATH.'/config.php.in';
require_once INCLUDES_PATH.'/adodb/adodb-exceptions.inc.php';
require_once INCLUDES_PATH.'/adodb/adodb.inc.php';

if (empty($_POST))
{
	include_once 'index.php';
}
else {

	$param = $_POST["param"];
	install($param);

}


function install($param){
	$message = '';
	if(!isset($param['dbhost']) || $param['dbhost'] == ''){
		$message .= urlencode("The field <b>Database Hostname</b> is mandatory.")."<br/>";
		header('Location:http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?message='.$message);
		exit(0);
	}
	if(!isset($param['dbuser']) || $param['dbuser'] == ''){
		$message .= urlencode("The field <b>Database User</b> is mandatory.")."<br/>";
		header('Location:http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?message='.$message);
		exit(0);
	}
	if(!isset($param['dbpass']) /*|| $param['dbpass'] == ''*/){
		$message .= urlencode("The field <b>Database Password</b> is mandatory.")."<br/>";
		header('Location:http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?message='.$message);
		exit(0);
	}
	if(!isset($param['moduleName']) || $param['moduleName'] == ''){
		$message .= urlencode("The field <b>Module Name</b> is mandatory.")."<br/>";
		header('Location:http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?message='.$message);
		exit(0);
	}
	if(!isset($param['login']) || $param['login'] == ''){
		$message .= urlencode("The field <b>Super User Login</b> is mandatory.")."<br/>";
		header('Location:http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?message='.$message);
		exit(0);
	}
	if(!isset($param['pass']) || $param['pass'] == ''){
		$message .= urlencode("The field <b>Super User Password</b> is mandatory.")."<br/>";
		header('Location:http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?message='.$message);
		exit(0);
	}
	if(!isset($param['passc']) || $param['passc'] == ''){
		$message .= urlencode("The field <b>Super User Confirm Password</b> is mandatory.")."<br/>";
		header('Location:http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?message='.$message);
		exit(0);
	}
	if($param['passc'] != $param['pass']){
		$message .= urlencode("<b>Password</b> do not match")."<br/>";
		header('Location:http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?message='.$message);
		exit(0);
	}



	// Does config.php.in exist?
	$config_in = CONFIG_PATH.'/config.php.in';
	if (!is_file($config_in)) {
		throw new Exception(sprintf('File %s does not exist.',$config_in));
	}
	try {
		$con = &NewADOConnection($param["dbdriver"]);
//		$con->debug = true;
		$con->Connect($param["dbhost"], $param["dbuser"], $param["dbpass"]);
		$con->Execute('DROP DATABASE IF EXISTS '. $param["moduleName"]. ' ;');
		$con->Execute('CREATE DATABASE '.  $param["moduleName"] . ' ;');
		$con->Execute('USE '. $param["moduleName"] . ';');

		loadSql('db/tao-trsfr.sql',$con);
		echo "DataBase created : <b>". $param["moduleName"] . "</b><br/>";

		$nameSpace="http://".$_SERVER['HTTP_HOST']."/middleware/".$param["moduleName"].".rdf";
		$lastName = $param['lastName'];
		$firstName = $param['firstName'];
		$email = $param["email"];
		$company = $param["company"];
		$login = $param["login"];
		$pass = md5($param['pass']);
		$lg = $param["lg"];
		
		$sql = "INSERT INTO `settings` (`key`, `value`) VALUES ('NameSpace', '$nameSpace');";
		$con->Execute($sql) or die("NameSpace configuration error");
		echo 'Namespace configured : <b>'. $nameSpace .'</b><br/>';
		
		$sql = "INSERT INTO `settings` (`key`, `value`) VALUES ('Deflg', '$lg');";
		$con->Execute($sql) or die("Deflg configuration error");
		echo 'Default Language configured : <b>'. $lg .'</b><br/>';
		
		$sql = "INSERT INTO `settings` (`key`, `value`) VALUES ('Timeout', '99');";
		$con->Execute($sql) or die("Timeout configuration error");
		echo 'Timeout  configured : <b>99 </b><br/>';
		
		$sql = "INSERT INTO `settings` (`key`, `value`) VALUES ('Moduletype', 'resource');";
		$con->Execute($sql) or die("Moduletype configuration error");
		echo 'Moduletype  configured : <b>resource</b><br/>';


		$query = "	INSERT INTO `user` ( `login` , `password` , `admin` , `usergroup` , `LastName` , `FirstName` , 
				`E_Mail` , `Company` , `Deflg` , `enabled` ) VALUES ('$login', '$pass', '1', 'admin', '$lastName', 
				'$firstName', '$email', '$company', '$lg', '0')";
		$con->Execute($query) or die("User configuration error");
		echo "User created : <b>" . $login ."</b><br/>";

		$con->Execute("INSERT INTO `models` VALUES ('8', '".$nameSpace."', '".$nameSpace."#')");

	} catch (exception $e) {
		$message .= urlencode("<b>Problem found </b> : <br/>". str_split($e->getMessage(),100) . "<br/>");
		header('Location:http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].
							'?message='.$message . 
							'&dbhost='.urlencode($param["dbhost"]) .
							'&dbname='. urlencode($param["moduleName"]) .
							'&dbuser='. urlencode($param["dbuser"]) . 
							'&dbpass='. urlencode($param["dbpass"]) .
							'&dbdriver='. urlencode($param["dbdriver"]) .
							'&pass='. urlencode($param["pass"]).
							'&login='. urlencode($param["login"]).
							'&passc='. urlencode($param["passc"]).
							'&company='. urlencode($param["company"]).
							'&moduleName='. urlencode($param["moduleName"]).
							'&lastName='. urlencode($param["lastName"]).
							'&firstName='. urlencode($param["firstName"]).
							'&lg='. urlencode($param["lg"]).
							'&email='. urlencode($param["email"])

		);
		var_dump($e);
		adodb_backtrace($e->gettrace());
	}


	$config = file_get_contents($config_in);


	writeConfigValue('DATABASE_LOGIN', $param["dbuser"],$config);
	writeConfigValue('DATABASE_PASS', $param["dbpass"],$config);
	writeConfigValue('DATABASE_URL', $param["dbhost"],$config);
	writeConfigValue('SGBD_DRIVER', $param["dbdriver"],$config);
	writeConfigValue('DATABASE_NAME', $param["moduleName"],$config);


	//TODO
	$filename = CONFIG_PATH.'/config.php';
	$fp = @fopen($filename,'wb');
	if ($fp === false) {
		throw new Exception(sprintf('Cannot write %s file.',$filename));
	}
	fwrite($fp,$config);
	fclose($fp);

	echo 'File ' . $filename .' written <br/>';
	

	$extensions = array('tao', 'taoDelivery' , 'taoGroups', 'taoItems' , 'taoResults', 'taoSubjects' , 'taoTests' );
	foreach ($extensions as $ext) {
		$config = file_get_contents( EXTENSION_PATH. '/'. $ext.'/includes/config.php.sample');
		$filename = EXTENSION_PATH. '/'. $ext.'/includes/config.php';
		$fp = @fopen($filename,'wb');
		if ($fp === false) {
			throw new Exception(sprintf('Cannot write %s file.',$filename));
		}
		fwrite($fp,$config);
		fclose($fp);
	
		echo 'File ' . $filename .' written <br/>';
		echo 'Extension : <b>' . $ext. '</b> instaled<br/>';
	}
}

function writeConfigValue($name,$val,&$str)
{
	$val = str_replace("'","\'",$val);
	$str = preg_replace('/(\''.$name.'\')(.*?)$/ms','$1,\''.$val.'\');',$str);
}

function loadSql($pFile, $con) {
	if ($file = @fopen($pFile, "r")){
		$ch = "";

		while (!feof ($file)){
			$line = utf8_decode(fgets($file));

			if (isset($line[0]) && ($line[0] != '#') && ($line[0] != '-')){
				$ch = $ch.$line;
			}
		}

		$requests = explode(";", $ch);
		unset($requests[count($requests)-1]);
		foreach($requests as $request){
			$con->Execute($request);

		}

		fclose($file);
	}
	else{
		die("File not found".$pFichier);
	}
}
?>
