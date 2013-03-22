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


class tao_update_DbUpdator
{
    
    public function updateDb($updateData,$version,$scriptNumber){
   	
    	$dbConnector = new tao_install_utils_DbCreator(
    			$updateData['db_host'],
    			$updateData['db_user'],
    			$updateData['db_pass'],
    			$updateData['db_driver']);
   		$dbConnector->setDatabase($updateData['db_name']);
    	
    	/*
    	 * 1 - Update generis config file with the new one
    	*/
    	
    	common_Logger::d('Writing db config', 'update');
    	$dbConfigWriter = new tao_install_utils_ConfigWriter(
    			GENERIS_PATH.'/common/conf/sample/db.conf.php',
    			GENERIS_PATH.'/common/conf/db.conf.php'
    	);
    	$dbConfigWriter->createConfig();
    	$dbConfigWriter->writeConstants(array(
			'DATABASE_LOGIN'	=> $updateData['db_user'],
			'DATABASE_PASS' 	=> $updateData['db_pass'],
			'DATABASE_URL'	 	=> $updateData['db_host'],
			'SGBD_DRIVER' 		=> $updateData['db_driver'],
			'DATABASE_NAME' 	=> $updateData['db_name']
    	));
    	
    	common_Logger::d('Writing generis config', 'update');
    	$generisConfigWriter = new tao_install_utils_ConfigWriter(
    			GENERIS_PATH.'/common/conf/sample/generis.conf.php',
    			GENERIS_PATH.'/common/conf/generis.conf.php'
    	);
    	$localNS = null;
    	$result = $dbConnector->execute('SELECT "modelURI" FROM models WHERE "modelID" = 8; ');
    	if($result->FieldCount() == 1){
    		$nsTable = $result->FetchRow();
    		$localNS = $nsTable[0];
    	}
    	else {
    		//error
    		throw new Exception('could not found previous TAO Local Namespace');
    	}
    	
    	


    	$generisConfigWriter->createConfig();
    	$generisConfigWriter->writeConstants(array(
    			'LOCAL_NAMESPACE'	=> $localNS,
    			'ROOT_PATH'			=> TAO_UPDATE_PATH,
    			'ROOT_URL'			=> preg_replace("/\/$/", '', $updateData['module_url']),
    			'DEFAULT_LANG'		=> 'EN',
    			'DEBUG_MODE'		=> ($updateData['module_mode'] == 'debug') ? true : false
    	));

    	session_destroy();
    	
    	require_once GENERIS_PATH.'/common/inc.extension.php';
    	require_once TAO_UPDATE_PATH.'/tao/includes/raw_start.php';
    	
    	
    	/*
    	 * 3 - Apply update scripts if they are existing
    	*/
    	
    	//get the files to launch to update TAO
    	
    	$dbCreator = new tao_install_utils_DbCreator(
    			$updateData['db_host'],
    			$updateData['db_user'],
    			$updateData['db_pass'],
    			$updateData['db_driver']);
    	$dbCreator->setDatabase($updateData['db_name']);
    	
    	
		if($version == false){
			$version = '2.2';
		}

    	$pattern = dirname(__FILE__).'/scripts/'.$version.'/';
    	if(file_exists($pattern) && is_dir($pattern)){
    	
    		if(isset ($scriptNumber) && $scriptNumber !== false){
    			$pattern .= $scriptNumber;
    		}
    		$pattern .= '*';
    	
    		$updateFiles = array();
    		foreach(glob($pattern) as $path){
    			$updateFiles[basename($path)] = $path;
    		}
    		//sort them by number
    		ksort($updateFiles);
    		
    		foreach($updateFiles as $file => $path){
    			common_Logger::d('Try to load update file : ' . $file, 'UPDATE');
    			//import rdf files
    			if(preg_match("/\.rdf$/", $file)){
    				try{
    					common_Logger::d('Try load update RDF file', 'UPDATE');
    					
    					//extract namespace from the file
    					$xml = simplexml_load_file($path);
    					$attrs = $xml->attributes('xml', true);
    					if(!isset($attrs['base']) || empty($attrs['base'])){
    						throw new Exception('The namespace of the rdf file to import has to be defined with the "xml:base" attribute of the ROOT node');
    					}
    					$ns = (string) $attrs['base'];
    					$modelCreator = new tao_install_utils_ModelCreator($ns);
    					//import the model in the ontology
    					$modelCreator->insertModelFile($ns, $path);
    					common_Logger::d('Success file ' . $file . ' inserted' , 'UPDATE');
    				}
    				catch(Exception $e){
    					common_Logger::e('Fail updated ' .$file , 'UPDATE');
    					
    					
    				}
    			}
    	
    			//execute php files
    			if(preg_match("/\.php$/", $file)){
    				common_Logger::d('Try load update PHP file', 'UPDATE');
    				$this->output[] = "running $file";
    				include $path;
    				common_Logger::d('Success file ' . $file . ' inserted' , 'UPDATE');
    			}
    	
    			//execute SQL queries
    			if(preg_match("/\.sql$/", $file)){
    				common_Logger::d('Try load update SQL file', 'UPDATE');
    				try{
    					// destroy the wrapper if it has been patched
    					//$dbWrapper = core_kernel_classes_DbWrapper::singleton();
    					//unset ($dbWrapper);
    					//core_kernel_classes_DbWrapper::singleton()->load($path);
    					$dbCreator->load($path);
    					common_Logger::d('Success file ' . $file . ' inserted' , 'UPDATE');
    				}catch(Exception $e){
    					common_Logger::e('Fail updated ' .$file , 'UPDATE');
    				}
    			}
    			
    			common_Logger::d('Success running Updated script ' . $file , 'UPDATE');
    		}
    	}
    	
    	// Insert stored procedures for the selected driver if they are found.
    	if(stripos($updateData['db_driver'], 'postgres') !== false) {
    		// postgres driver can be postgres, postgres7, postgres8, ...
    		$procDbDriver = 'postgres';
    	}else{
    		$procDbDriver = $updateData['db_driver'];
    	}
    	
    	
    	$storedProcedureFile = dirname(__FILE__).'/db/tao_stored_procedures_'.$procDbDriver.'.sql';
    	if (file_exists($storedProcedureFile) && is_readable($storedProcedureFile)){
    		common_Logger::i('Installing stored procedures for '.$procDbDriver, 'INSTALL');
    		$sqlParserClassName = 'tao_install_utils_' . ucfirst($procDbDriver) . 'ProceduresParser';
    		$dbCreator->setSQLParser(new $sqlParserClassName());
    		$dbCreator->load($storedProcedureFile);
    	}

    	/*
    	 *  6 - Create the version file
    	*/
    	common_Logger::d('Creating version file for TAO', 'UPDATE');
    	file_put_contents(ROOT_PATH.'version', TAO_VERSION);
    	
    	common_Logger::d('Clear cache funcACL role accesses', 'UPDATE');
    	tao_helpers_funcACL_funcACL::removeRolesByActions();
    	
    	common_Logger::i('Instalation completed', 'UPDATE');
    	
    	
    	
    }
    
}