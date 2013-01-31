<?php
include_once dirname(__FILE__) . '/../../includes/raw_start.php';
if (!$_POST['uri']) {
	// missing parameters
	common_Logger::w('Missing param \'uri\'');
	return false;
}

$user = new core_kernel_classes_Resource($_POST['uri']);
$success = $user->delete();
echo json_encode($success);