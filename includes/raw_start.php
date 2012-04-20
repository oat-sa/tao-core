<?php
/**
 * RAW Bootstraping
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
if(PHP_SAPI == 'cli'){
	$_SERVER['HTTP_HOST'] = 'http://localhost';
	$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__).'/../../..';
}

require_once 'class.Bootstrap.php';

$bootStrap = new BootStrap('tao', array('session_name' => TestRunner::SESSION_KEY));
$bootStrap->start();
?>