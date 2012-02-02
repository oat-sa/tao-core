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
	$error = "TAO already installed! If you continue, you will reinstall TAO and maybe erase your data.";
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
		$installed = true;
		$taoUrl = _url('index', 'Main', 'tao'); 
	}
	catch(tao_install_utils_Exception $ie){
	
		//we display the exception message to the user
		$error = $ie->getMessage(); 

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
	<?php if(!$installed): ?>
	<script type="text/javascript" src="./res/tao.js"></script>
	<? endif; ?>
</head>
<body>
<div id="content" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<? if($installed):?>
	<div id="success">
	  <h1>Installation successfuly completed!</h1>
	  <a href="<?=  $taoUrl ?>" title="TAO backend"><img src="../views/img/tao_logo_big.png" title="Access to the TAO platform" alt="Access to the TAO platform"/></a>
	  <p>
 	  Click on the logo above to access the TAO platform. Use the login and password that corresponds to the previously
	  created Super User.</p>
	  <ul>
                  <li><?= __('Back Office (test creator)') ?>: <a href="<?= _url('index', 'Main', 'tao') ?>" title="<?= __('TAO back office') ?>"><?= __('TAO Back Office') ?></a></li>
                  <li><?= __('Test Front Office (test takers)') ?>: <a href="<?= _url('index', 'DeliveryServerAuthentification', 'taoDelivery') ?>" title="<?= __('TAO front office') ?>"><?= __('TAO Delivery Server') ?></a></li>
                  <li><?= __('Process Front Office') ?>: <a href="<?= _url('index', 'Authentication', 'wfEngine') ?>" title="<?= __('TAO front office') ?>" title="<?= __('TAO workflow engine') ?>"><?= __('TAO Workflow Engine') ?></a></li>
	  </ul>
	</div>
	<?else: ?>
		<?if(!is_null($error)):?>
			<div id="error" class="ui-widget ui-corner-all">
				<div><?= $error?></div>
				<?if(isset($errorTrace) && !empty($errorTrace)): ?>
					<pre><?=$errorTrace?></pre>
				<?endif; ?>
			</div>
		<?endif?>
	<div id="title" class="ui-widget-header ui-corner-all">TAO Install</div>
	<div class="section">
	<div id="mandatory-config-header"  class="ui-widget ui-widget-header ui-state-default ui-corner-top">
		1 - System Configuration
		<img src="res/fold.png" class="expander" alt="More..." title="More..."/>
	</div>
	<div id="mandatory-config-list" class="ui-widget ui-widget-content ui-corner-bottom">
		<table>
			<thead>
				<tr>
					<th class="ui-state-default ui-th-column ui-th-ltr leading test">Test</th>
					<th class="ui-state-default ui-th-column ui-th-ltr validity">Validity</th>
					<th class="ui-state-default ui-th-column ui-th-ltr trailing message">Message</th>
				</tr>
			</thead>
			<tbody>
			<?$optionalTestsTitles = array('Suhosin patch check',
										   'PHP SVN extension check'); ?>
			<?$optionalests = array(); ?>
			<?foreach($configTests as $test):?>
				<? if (!in_array($test['title'], $optionalTestsTitles)): ?>
				<tr class="<?= ($test['valid']) ? 'valid' : 'invalid'; ?>">
					<td><?=$test['title']?></td>
					<td class="validity"><img src="img/<?= ($test['valid'])?'accept' : (($test['unknow'] === true) ? 'unknown' : 'exclamation')?>.png"/></td>
					<td><?=$test['message']?></td>
				</tr>
				<?php else: ?>
				<?php $optionalTests[] = $test; ?>
				<?php endif; ?>
			<?endforeach?>
			</tbody>
		</table>
	</div>
	</div>
	
	<div class="section">
	<div id="optional-config-header" class="ui-widget ui-widget-header ui-state-default ui-corner-top ui-corner-bottom">
		2 - Optional System Configuration
		<img src="res/unfold.png" class="expander" alt="More..." title="More..."/>
	</div>
	<div id="optional-config-list" class="ui-widget ui-widget-content ui-corner-bottom">
		<table>
			<thead>
				<tr>
					<th class="ui-state-default ui-th-column ui-th-ltr leading test">Test</th>
					<th class="ui-state-default ui-th-column ui-th-ltr validity">Validity</th>
					<th class="ui-state-default ui-th-column ui-th-ltr trailing message">Message</th>
				</tr>
			</thead>
			<tbody>
			<?foreach($optionalTests as $test):?>
				<tr class="<?= ($test['valid']) ? 'valid' : 'invalid optional'; ?>">
					<td><?=$test['title']?></td>
					<td class="validity"><img src="img/<?= ($test['valid'])?'accept' : (($test['unknow'] === true) ? 'unknown' : 'warning')?>.png"/></td>
					<td><?=$test['message']?></td>
				</tr>
			<?endforeach?>
			</tbody>
		</table>
	</div>
	</div>
	
	<div class="section">
		<div class="ui-widget ui-widget-header ui-state-default  ui-corner-top">3 - Installation Form</div>
		<div id="install-form" class="ui-widget ui-widget-content ui-corner-bottom">
			<?=$container->getForm()->render()?>
		</div>
	</div>
	<? endif; ?>
</div>
</body>
</html>