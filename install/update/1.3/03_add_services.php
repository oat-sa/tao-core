<?php
core_control_FrontController::connect(SYS_USER_LOGIN, SYS_USER_PASS, DATABASE_NAME);
$dbWarpper = core_kernel_classes_DbWrapper::singleton(DATABASE_NAME);
$namespace = core_kernel_classes_Session::singleton()->getNameSpace();

loadSqlReplaceNS('../../wfEngine/install/db/services.sql',$dbWarpper->dbConnector, $namespace);
?>