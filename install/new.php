<?php
$rootDir = dir(dirname(__FILE__).'/../../');
$root = realpath($rootDir->path).'/';
set_include_path(get_include_path() . PATH_SEPARATOR . $root);

function __autoload($class_name) {
	$path = str_replace('_', '/', $class_name);
	$file =  'class.' . basename($path). '.php';
    require_once  dirname($path) . '/' . $file;
}
require_once('tao/helpers/class.Display.php');
require_once('tao/helpers/class.Uri.php');

$installator = new tao_install_Installator();
$configTests = $installator->processTests();

$container = new tao_install_form_Settings();
$myForm = $container->getForm();
if($myForm->isSubmited() && $myForm->isValid()){
	
	$formValues = $myForm->getValues();
	
	try{
		// 1 Test DB connection (done by the constructor)
		$dbCreator = new tao_install_utils_DbCreator(
			$formValues['db_host'],
			$formValues['db_user'],
			$formValues['db_pass'],
			$formValues['db_driver']
		);
		
		// 2 Load the database schema
		$dbCreator->load(dirname(__FILE__).'/db/tao.sql', array('DATABASE_NAME' => $formValues['db_name']));
		
		// 3 insert local namespace
		$dbCreator->execute("INSERT INTO `models` VALUES ('8', '{$formValues['module_namespace']}', '{$formValues['module_namespace']}#')");
		
		// 4 generis config files
		$generisConfigWriter = new tao_install_utils_ConfigWriter(
			$root.'generis/common/config.php.in',
			$root.'generis/common/config.php'
		);
		$generisConfigWriter->createConfig();
		$generisConfigWriter->writeConstants(array(
			'DATABASE_LOGIN'	=> $formValues['db_user'],
			'DATABASE_PASS' 	=> $formValues['db_pass'],
			'DATABASE_URL'	 	=> $formValues['db_host'],
			'SGBD_DRIVER' 		=> $formValues['db_driver'],
			'DATABASE_NAME' 	=> $formValues['db_name'],
			'LOCAL_NAMESPACE'	=> $formValues['module_namespace'],
			'ROOT_PATH'			=> $root,
			'ROOT_URL'			=> $formValues['module_url'],
			'DEFAULT_LANG'		=> $formValues['module_lang'],
			'DEBUG_MODE'		=> ($formValues['module_mode'] == 'debug') ? true : false
		));
		
		//now we can run the extensions bootstrap
		require_once $root . 'generis/common/inc.extension.php';
		
		// 5 create the config for the loaded extensions
		$extensionManager = common_ext_ExtensionsManager::singleton();
		$extensions = $extensionManager->getInstalledExtensions();
		foreach($extensions as $extensionId => $extension){
			if($extensionId == 'generis') continue; 	//generis is the root and has been installed above 
			$myConfigWriter = new tao_install_utils_ConfigWriter(
				$root.$extensionId.'/includes/config.php.sample',
				$root.$extensionId.'/includes/config.php'
			);
			$myConfigWriter->createConfig();
		}
		
		
		// 6 Insert the extensions models
		$models = tao_install_utils_ModelCreator::getModelsFromExtensions($extensions);
		
		$modelCreator = new tao_install_utils_ModelCreator($formValues['module_namespace']);
		//$modelCreator->insertModelFile("http://www.tao.lu/Ontologies/TAO.rdf#", $root.'tao/models/ontology/tao.rdf');
		foreach($models as $ns => $modelFile){
			echo  "trying to insert $modelFile into $ns <br>";
			if($modelCreator->insertModelFile($ns, $modelFile)){
				echo  " $modelFile inserted<br><br>";
			}
		}
		
	}
	catch(tao_install_utils_Exception $ie){
		echo "<h3 style='color:red;'>";
		echo $ie->getMessage();
		echo "</h3>";
		echo "<pre>";
		echo $ie->getTraceAsString();
		echo "</pre><br />";
	}
	
//	$userData = array();
//	$userData['login'] 			= $formValues['user_login'];
//	$userData['password'] 		= $formValues['user_pass1'];
//	$userData['userLastName']	= $formValues['user_lastname'];
//	$userData['userFirstName']	= $formValues['user_firstname'];
//	$userData['userMail']		= $formValues['user_email'];
//	$userData['userDefLg']		= $formValues['module_lang'];
//	$userData['userUILg']		= $formValues['module_lang'];
//	
//	$modelCreator = new tao_install_utils_ModelCreator();
//	$modelCreator->insertSuperUser($userData);
}
?>
<h2>Work in progress...</h2>
<style>
div{
	margin-bottom:15px;
}
div.form-group{
	border:solid grey 1px;
	font-weight:bold;
}
div.form-group > div{
	font-weight:normal;
	margin-top:5px;
}
input, .form-elt-container, select{
	position:absolute;
	left:200px;
}
.form-help{
	margin-top:5px;
	display:block;
	font-size:11px;
	font-style:italic;
}
</style>
<table border='1'>
	<thead>
		<tr>
			<th>Test</th>
			<th>Valid</th>
			<th>Message</th>
		</tr>
	</thead>
	<tbody>
	<?foreach($configTests as $test):?>
		<tr>
			<td><?=$test['title']?></td>
			<td><?=($test['valid'])?'yes':(($test['unknow'] === true)?'unknow':'no')?></td>
			<td><?=$test['message']?></td>
		</tr>
	<?endforeach?>
	</tbody>
</table>
<br />
<hr />
<?=$container->getForm()->render()?>