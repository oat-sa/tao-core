<?php

$root = dir(dirname(__FILE__).'/../../');
set_include_path(get_include_path() . PATH_SEPARATOR . $root->path);

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