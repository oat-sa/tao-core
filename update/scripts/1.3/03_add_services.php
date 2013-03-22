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
core_control_FrontController::connect(SYS_USER_LOGIN, SYS_USER_PASS, DATABASE_NAME);
$dbWarpper = core_kernel_classes_DbWrapper::singleton();
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