<?php
session_start();
session_destroy();

include_once('init.php');
require_once ('generis/includes/ClearFw/clearbricks/common/lib.l10n.php');

//instantiate the installator
$installator = new tao_install_Installator(array(
	'root_path' 	=> $root,
	'install_path'	=> dirname(__FILE__)
));

// Process the system configuration tests 
$configTests = $installator->processTests();

//get the settings form
$container = new tao_install_form_Settings();
$myForm = $container->getForm();

$error = null;
$errorTrace = '';

if(!$myForm->isSubmited() && tao_install_utils_System::isTAOInstalled()){
	$error = "TAO already installed! If you continue, you will reinstall TAO and erase your data.";
}

//once the form is posted and valid
$installed = false;
if($myForm->isSubmited() && $myForm->isValid()){
	
	//get the posted values 	
	$formValues = $myForm->getValues();
	
	try{	//if there is any issue during the install, a tao_install_utils_Exception is thrown
		
		
		$installator->install($formValues);
		$installator->configWaterPhoenix($formValues);
		$moduleUrl = $myForm->getValue('module_url');
		$backendLink = tao_install_utils_Links::buildBackendLink($moduleUrl);
		$frontendLink = tao_install_utils_Links::buildFrontendLink($moduleUrl);
		
		$installed = true;
	}
	catch(tao_install_utils_Exception $ie){
	
		//we display the exception message to the user
		$error = $ie->getMessage(); 
		$errorTrace= trim($ie->getTraceAsString()); 
	}
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>TAO Install</title>
	<link rel="stylesheet" type="text/css" media="screen" href="../views/css/custom-theme/jquery-ui-1.8.custom.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="../views/css/style.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="../views/css/layout.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="../views/css/form.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="./res/tao.css"/> 
	<script type="text/javascript" src="../views/js/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="./res/tao.js"></script>
</head>
<body>
<div id="content" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<? if($installed):?>
	<div id="success">
	  <h1>Installation successfuly completed!</h1>
	  <a href="<?= $backendLink ?>" title="TAO backend"><img src="../views/img/tao_logo_big.png" title="Access to the TAO platform" alt="Access to the TAO platform"/></a>
	  <p>
 	  Click on the logo above to access the TAO platform. Use the login and password that corresponds to the previously
	  created Super User.</p>
	  <ul>
	    <li>Link to the back office(administrators): <a href="<?= $backendLink ?>" title="TAO back office"><?= $backendLink ?></a></li>
		<li>Link to the test front office (test takers): <a href="<?= $frontendLink ?>" title="TAO front office"><?= $frontendLink ?></a></li>
	  </ul>
	</div>
	<?else: ?>
		<?if(!is_null($error)):?>
			<div id="error">
				<div><?= $error?></div>
				<pre><?=$errorTrace?></pre>
			</div>
		<?endif?>
	<div id="title" class="ui-widget-header ui-corner-all">TAO Install</div>
	<div class="section">
	<div class="ui-widget ui-widget-header ui-state-default  ui-corner-top">1 - System Configuration</div>
	<div class="ui-widget ui-widget-content ui-corner-bottom">
		<table>
			<thead>
				<tr>
					<th class="ui-state-default ui-th-column ui-th-ltr leading test">Test</th>
					<th class="ui-state-default ui-th-column ui-th-ltr validity">Valid</th>
					<th class="ui-state-default ui-th-column ui-th-ltr trailing message">Message</th>
				</tr>
			</thead>
			<tbody>
			<?foreach($configTests as $test):?>
				<? $isOptional = !($test['title'] != 'Suhosin patch check') ?>
				<tr class="<?= ($test['valid']) ? 'valid' : (($isOptional) ? 'optional' : 'invalid'); ?>">
					<td><?=$test['title']?></td>
					<td class="validity"><img src="img/<?= ($test['valid'])?'accept' : (($test['unknow'] === true) ? 'unknown' : (($isOptional) ? 'warning' : 'exclamation'))?>.png"/></td>
					<td><?=$test['message']?></td>
				</tr>
			<?endforeach?>
			</tbody>
		</table>
	</div>
	</div>
	<div class="section">
		<div class="ui-widget ui-widget-header ui-state-default  ui-corner-top">2 - Installation Form</div>
		<div id="install-form" class="ui-widget ui-widget-content ui-corner-bottom">
			<?=$container->getForm()->render()?>
		</div>
	</div>
	<? endif; ?>
</div>
</body>
</html>