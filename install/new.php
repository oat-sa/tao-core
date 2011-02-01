<?php
// install bootstrap
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


// Process the system configuration tests 
$installator = new tao_install_Installator();
$configTests = $installator->processTests();

//instantiate the settings form
$container = new tao_install_form_Settings();
$myForm = $container->getForm();

//once the form is posted and valid
if($myForm->isSubmited() && $myForm->isValid()){
	
	//WE CAN INSTALL TAO
	
	$formValues = $myForm->getValues();
	
	try{	//if there is any issue a tao_install_utils_Exception is thrown
		
		
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
		
		$modelCreator = new tao_install_utils_ModelCreator($formValues['module_namespace']);
		
		// 6 Insert the extensions models
		$models = tao_install_utils_ModelCreator::getModelsFromExtensions($extensions);
		foreach($models as $ns => $modelFile){
			$modelCreator->insertModelFile($ns, $modelFile);
		}
		
		// 7 Insert Sample Data
		$modelCreator->insertLocalModelFile(dirname(__FILE__).'/ontology/sample.rdf');
		
		// 8 Insert Super User
		$modelCreator->insertSuperUser(array(
			'login'			=> $formValues['user_login'],
			'password'		=> md5($formValues['user_pass1']),
			'userLastName'	=> $formValues['user_lastname'],
			'userFirstName'	=> $formValues['user_firstname'],
			'userMail'		=> $formValues['user_email'],
			'userDefLg'		=> 'http://www.tao.lu/Ontologies/TAO.rdf#Lang'.strtoupper($formValues['module_lang']),
			'userUILg'		=> 'http://www.tao.lu/Ontologies/TAO.rdf#Lang'.strtoupper($formValues['module_lang'])
		));
		
		// 9 Secure the install for production mode
		if($formValues['module_mode'] == 'production'){
			
			// 9.1 Remove Generis User
			$dbCreator->execute("DELETE FROM statements WHERE subject = 'http://www.tao.lu/Ontologies/TAO.rdf#installator' AND modelID=6");
			
			// 9.2 Protect TAO dist
 			$shield = new tao_install_utils_Shield(array_keys($extensions));
 			$shield->disableRewritePattern(array("!/test/", "!/doc/"));
 			$shield->protectInstall();
		}
		
		echo "<h1 style='color:green;'>DONE</h1>";
		exit;
		
	}
	catch(tao_install_utils_Exception $ie){
		echo "<h3 style='color:red;'>";
		echo $ie->getMessage();
		echo "</h3>";
		echo "<pre>";
		echo $ie->getTraceAsString();
		echo "</pre><br />";
	}
	
//	
}
?>
<h2>TAO Install</h2>
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
table{
	border-collapse: collapse;
}
table td, table th{
	border: solid 1px #aaa;
	padding:3px;
}
</style>
<h3> 1 - System Configuration</h3>
<table>
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

<h3> 2 - Installation Form</h3>
<?=$container->getForm()->render()?>
