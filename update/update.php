<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php
require_once dirname(__FILE__).'/../includes/raw_start.php';

//get the settings form
$container = new tao_update_form_Settings();
$myForm = $container->getForm();

//instantiate the updator
$updator = new tao_update_Updator();
//is updated
$updated = false;
//is error
$error = null;

if($myForm->isSubmited() && $myForm->isValid()){

	//get the posted form
	$formValues = $myForm->getValues();
	$updateOutput = array();
	if ($formValues['update'] == 1) {

		try{	//if there is any issue during the update, a tao_update_utils_Exception is thrown

			foreach ($updator->getUpdatesDetails() as $updateDetail){
				$updator->update($updateDetail['version']);
				$updateOutput = array_merge($updateOutput, $updator->getOutput());
			}
			$updated = true;
			session_destroy();
		}
		catch(tao_update_utils_Exception $ie){

			//we display the exception message to the user
			$error = $ie->getMessage();
			$updateOutput = array_merge($updateOutput, $updator->getOutput());
		}
	}
}

//instantiate the installator to get system configuration requirements for this new version of TAO
$installator = new tao_update_Installator(array(
	'root_path' 	=> ROOT_PATH,
	'install_path'	=> dirname(__FILE__)
));

// Process the system configuration tests
$configTests = $installator->processTests();

// Check if update are available
$availableUpdate = $updator->checkUpdate();
$updateDetails = array();
// Get available update details
if ($availableUpdate){
	$updateDetails = $updator->getUpdatesDetails();
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>TAO Update</title>

	<link rel="stylesheet" type="text/css" media="screen" href="../views/css/custom-theme/jquery-ui-1.8.22.custom.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="./views/js/jquery.jqGrid-4.1.0/css/ui.jqgrid.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="../views/css/style.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="../views/css/layout.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="../views/css/form.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="./res/tao.css"/>

	<script type="text/javascript" src="../views/js/jquery-1.8.0.min.js"></script>
	<script type="text/javascript" src="../views/js/jquery-ui-1.8.23.custom.min.js"></script>
	<script type="text/javascript" src="../locales/<?= DEFAULT_LANG ?>/messages_po.js"></script>
	<script type="text/javascript" src="../views/js/i18n.js"></script>
	<script type="text/javascript" src="./views/js/jquery.jqGrid-4.1.0/js/i18n/grid.locale-en.js"></script>
	<script type="text/javascript" src="./views/js/jquery.jqGrid-4.1.0/js/jquery.jqGrid.min.js"></script>
	<script type="text/javascript" src="./views/js/Updater.js"></script>

	<script type="text/javascript">
        $(function(){
            root_url = '<?= ROOT_URL?>';
            var myUpdater = new UpdaterClass('update-grid');
			<?foreach($updateDetails as $key=>$updateDetail):?>
				myUpdater.addRowData(<?=$key?>, <?=json_encode($updateDetail)?>);
			<?endforeach;?>

			$('#see_trace').click(function(){
				$('#trace').dialog({title:'Update Trace', height: 400, width:800});
			});
        });
	</script>
</head>
<body>
<div id="content" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<?if($updated):?>
	<div id="success">
	  <h1>Update successfuly completed!</h1>
	  <img src="../views/img/tao_logo_big.png" title="Access to the TAO platform" alt="Access to the TAO platform"/>
	  <p>Go back to the <a href="<?= _url('index', 'Main', 'tao') ?>" title="<?= __('TAO back office') ?>"><?= __('TAO Back Office') ?></a></p>
	  <p>If you want to see the log file of this update click <a id="see_trace" href="#">here</a></p>
	</div>
	<div id="trace" class="ui-helper-hidden" >
		<table>
			<?foreach($updateOutput as $key=>$o):?>
				<tr><td><?=$key?></td><td><?=$o?></td></tr>
			<?endforeach;?>
		</table>
	</div>
	<?else:?>
		<?if(!is_null($error)):?>
			<div id="error">
				<div>An error occured : <br/><?= $error?></div>
				<p><?= __('If you want to see the log file of this update click')?> <a id="see_trace" href="#"><?= __('here')?></a></p>
				<div id="trace" class="ui-helper-hidden">
					<table>
						<?foreach($updateOutput as $o):?>
							<tr><td><?= $o ?></td></tr>
						<?endforeach;?>
					</table>
				</div>
			</div>
		<?endif;?>
	<div id="title" class="ui-widget-header ui-corner-all">TAO Update</div>
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
							<td class="validity"><img src="../install/img/<?= ($test['valid'])?'accept' : (($test['unknow'] === true) ? 'unknown' : (($isOptional) ? 'warning' : 'exclamation'))?>.png"/></td>
							<td><?=$test['message']?></td>
						</tr>
					<?endforeach?>
					</tbody>
				</table>
			</div>
			</div>
			<div class="section">
				<div class="ui-widget ui-widget-header ui-state-default  ui-corner-top">2 - Update Form</div>
				<div id="update-form" class="ui-widget ui-widget-content ui-corner-bottom">

					<div id="updating-container" class="ui-widget-content ui-corner-bottom">

						<?if($availableUpdate):?>
						<div id="available_update-container" class="ext-home-container ui-state-highlight">
							<span><?=__('Updates are available. Click on update to update your version of TAO')?></span>
							<br/><br/>
							<div id="update-table-container">
								<table id="update-grid"></table>
							</div>
						</div>

						<div id="update-form" class="ui-widget ui-widget-content ui-corner-bottom">
							<?=$container->getForm()->render()?>
						</div>
						<?else:?>
						<div id="no_update-container" class="ext-home-container ui-state-highlight">
							<span><?=__('No update available')?></span>
						</div>
						<?endif;?>

					</div>
			</div>
		</div>
	</div>
	<?endif;?>
</div>
</body>
</html>