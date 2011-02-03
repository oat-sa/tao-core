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
require_once('tao/helpers/class.Display.php');
require_once('tao/helpers/class.Uri.php');
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
