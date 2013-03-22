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
include_once('init.php');

$response = array(
	'connected' => false
);

$attendeds = array(
	'db_driver',
    'db_host',
    'db_name',
    'db_user',
    'db_pass'
);
$found = 0;
foreach($attendeds as $attended){
	if($attended != 'db_pass' && (!isset($_POST[$attended]) || trim($_POST[$attended]) == '')){
		echo json_encode($response);
		exit();
	}
	else{
		$found++;
	}
}

if(count($attendeds) != $found){
	echo json_encode($response);
	exit();
}

function customError($severity, $message, $file, $line){
	if($severity == E_WARNING){
		if(preg_match("/mysql_connect/", $message) || preg_match("/pg_connect/", $message)){
			return true;
		}
	}
	return false;
}


try{
	set_error_handler('customError');
	new tao_install_utils_DbCreator($_POST['db_host'], $_POST['db_user'], $_POST['db_pass'], $_POST['db_driver']);
	restore_error_handler();
	$response['connected'] = true;
}
catch(Exception $e){
	$response['connected'] = false;
}


echo json_encode($response);
exit();
?>