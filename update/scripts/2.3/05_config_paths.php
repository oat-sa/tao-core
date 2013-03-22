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
function tao2223_change_roots($inputFile, $outputFile, $forceLinux = false){
	if (!is_readable($inputFile)){
		die("'${inputFile}' is not readable by the current process.");
	}
	else if (!is_writable($outputFile)){
		die("'${outputFile}' is not writable by the current process.");
	}
	else{
		$input = file_get_contents($inputFile);
		$matches = array();
		
		// -- ROOT_PATH -- Add a trailing slash/backslash depending on the operating system.
		if (preg_match("/define\s*\(\s*'ROOT_PATH'\s*,\s*'(.+)'\s*\)\s*;/", $input, $matches) !== false){
			$path = $matches[1];
			$match = $matches[0];
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && !$forceLinux){
				// The string was not addslashed and might not contain a valid trailing slash.
				// Replace slashes by backslashes.
				$path = str_replace('/', '\\', $path);
				$last = substr($path, -1);
				if ($last != '\\'){
					$path = $path . '\\';
				}
				
				$path = addslashes($path);
				$path = str_replace('\\\\\\\\', '\\\\', $path);
			}
			else{
				// Linux system.
				$last = substr($path, -1);
				if ($last != '/'){
					$path = $path . '/';
				}
			}

			// We replace in the input.
			$input = str_replace($match, "define('ROOT_PATH', '${path}');\n", $input);
			
			// -- ROOT_URL -- Add a traling slash.
			if (preg_match("/define\s*\(\s*'ROOT_URL'\s*,\s*'(.+)'\s*\)\s*;/", $input, $matches) !== false){
				$path = $matches[1];
				$match = $matches[0];
				$last = substr($path, -1);
				if ($last != '/'){
					$path = $path . '/';
				}
				
				// We replace in the input.
				$input = str_replace($match, "define('ROOT_URL', '${path}');\n", $input);
				
				file_put_contents($outputFile, $input);
			}
			else{
				die("'ROOT_URL' constant not found in '${inputFile}'.");
			}
		}
		else{
			die("'ROOT_PATH' constant not found in '${inputFile}'.");
		}
	}
}

tao2223_change_roots(dirname(__FILE__) . '/../../../../generis/common/conf/generis.conf.php',
					 dirname(__FILE__) . '/../../../../generis/common/conf/generis.conf.php');
?>