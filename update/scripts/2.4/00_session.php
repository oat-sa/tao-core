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
/*
 * This update script adds two new constants in the Generis configuration file
 * for session handling.
 */
$matches = array();
$path = dirname(__FILE__) . '/../../../../generis/';
if (($realpath = realpath($path)) !== false){
	$path = $realpath;
}

if (($content = @file_get_contents($path)) !== false){
	$instanceName = 'tao-' . rand(1000, 9999);
	$sessionName = tao_install_Installator::generateSessionName();
	
	$content .= "\n";
	$content .= "# platform identification\n";
	$content .= "define('GENERIS_INSTANCE_NAME', '${newInstance}');\n";
	$content .= "define('GENERIS_SESSION_NAME', '${sessionName}');\n";
	
	if (file_put_contents($path, $content) === false){
		die("An error occured while writing the Generis configuration file located at '${path}'.\n"
			. "Please make sure it exists and that you have the correct permissions.");
	}
}
else{
	die("The Generis configuration file located at '${path}' cannot be read.\n"
		. "Please make sure it exists and that you have the correct permissions.");
}
?>