<?php
core_control_FrontController::connect(SYS_USER_LOGIN, SYS_USER_PASS, DATABASE_NAME);
$session = core_kernel_classes_Session::singleton();
$dbWrapper = core_kernel_classes_DbWrapper::singleton();
$dbWrapper->execSql("UPDATE statements SET l_language = ? WHERE predicate = 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent'",
                    array($session->defaultLg));
?>