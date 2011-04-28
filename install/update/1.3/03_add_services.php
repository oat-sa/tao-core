<?php
core_control_FrontController::connect(SYS_USER_LOGIN, SYS_USER_PASS, DATABASE_NAME);
$dbWarpper = core_kernel_classes_DbWrapper::singleton(DATABASE_NAME);
$namespace = core_kernel_classes_Session::singleton()->getNameSpace();

function loadSqlReplaceNS($pFile, $con, $namespace) {
	if ($file = @fopen($pFile, "r")) {
		$ch = "";

		while (!feof($file)) {
			$line = utf8_decode(fgets($file));
			if (isset($line[0]) && ($line[0] != '#') && ($line[0] != '-')) {
			$ch = $ch . $line;
			}
		}

		$requests = explode(";", $ch);
		unset($requests[count($requests) - 1]);
		foreach ($requests as $request) {
			$request = str_replace("##NAMESPACE", $namespace, $request);
			$request = str_replace("{ROOT_PATH}", $_SERVER['DOCUMENT_ROOT'], $request);
			$con->Execute($request);
		}
		
		fclose($file);
	} else {
		die("File not found" . $pFichier);
	}
}

loadSqlReplaceNS('../../wfEngine/install/db/services.sql',$dbWarpper->dbConnector, $namespace);
?>