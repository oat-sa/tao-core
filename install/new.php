<?php

// -- Install bootstrap
$rootDir = dir(dirname(__FILE__).'/../../');
$root = realpath($rootDir->path).'/';
set_include_path(get_include_path() . PATH_SEPARATOR . $root);

function __autoload($class_name) {
	$path = str_replace('_', '/', $class_name);
	$file =  'class.' . basename($path). '.php';
    require_once  dirname($path) . '/' . $file;
}
require_once ('tao/helpers/class.Display.php');
require_once ('tao/helpers/class.Uri.php');
require_once ('generis/includes/ClearFw/clearbricks/common/lib.l10n.php');


// --

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

//once the form is posted and valid
if($myForm->isSubmited() && $myForm->isValid()){
	
	//get the posted values 	
	$formValues = $myForm->getValues();
	
	try{	//if there is any issue during the install, a tao_install_utils_Exception is thrown
		
		
		$installator->install($formValues);
		
		
		$installator->configWaterPhoenix($formValues);
		
		//DONE if no exception has been thrown
		echo "<h1 style='color:green;'>DONE</h1>";
		exit;
		
	}
	catch(tao_install_utils_Exception $ie){

		//we display the exception message to the user
		?>
		<div id="error">
			<div><?= $ie->getMessage(); ?></div>
			<pre><?= trim($ie->getTraceAsString()); ?></pre>
		</div>
		<?
	}
	
//	
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
	<link rel="stylesheet" type="text/css" media="screen" href="newtao.css"/> 
</head>
<body>
<div id="content" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
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
	</body>
</div>
</html>