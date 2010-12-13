<?php
/**
 * Bootstraping
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * 
 * @todo create a proper class to bootstrap by context
 */

$request = new Request();
if($request->hasParameter('session_id')){
 	session_id($request->getParameter('session_id'));
}
session_start();

require_once dirname(__FILE__). "/config.php";

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_PATH);

require_once dirname(__FILE__). "/constants.php";
include_once dirname(__FILE__). '/prepend.php';

require_once(BASE_PATH.'/helpers/class.Uri.php');
require_once(BASE_PATH.'/helpers/class.Display.php');
?>
