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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php

error_reporting(E_ALL);

/**
 * Helper to read/write the action/module model
 * of tao from/to the ontology
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_funcACL
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--1875a6a1:137e65726c7:-8000:0000000000003B18-includes begin
// section 127-0-1-1--1875a6a1:137e65726c7:-8000:0000000000003B18-includes end

/* user defined constants */
// section 127-0-1-1--1875a6a1:137e65726c7:-8000:0000000000003B18-constants begin
// section 127-0-1-1--1875a6a1:137e65726c7:-8000:0000000000003B18-constants end

/**
 * Helper to read/write the action/module model
 * of tao from/to the ontology
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_funcACL
 */
class tao_helpers_funcACL_Model
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * creates a model of the modules and their actions
     * of an extentions in the ontology to be used to assign access rights
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  Extension extension
     * @return mixed
     */
    public static function spawnExtensionModel( common_ext_Extension $extension)
    {
        // section 127-0-1-1--1875a6a1:137e65726c7:-8000:0000000000003B19 begin
    	common_Logger::i('Spawning Module/Action model for extension '.$extension->getID());
		
    	foreach ($extension->getAllModules() as $moduleClass) {
			//Introspection, get public method
			try {
				$reflector = new ReflectionClass($moduleClass);
				$actions = array();
				foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
					if (!$m->isConstructor() && !$m->isDestructor() && is_subclass_of($m->class, 'Module') && $m->name != 'setView') {
						$actions[] = $m->name;
					}
				}
				if (count($actions) > 0) {
					$moduleName = substr($moduleClass, strrpos($moduleClass, '_') + 1);
					$module = self::addModule($extension->getID(), $moduleName);
					foreach ($actions as $action) {
						self::addAction($module, $action);
					}
				}
			}
			catch (ReflectionException $e) {
				echo $e->getLine().' : '.$e->getMessage()."\n";
			}
		}
        // section 127-0-1-1--1875a6a1:137e65726c7:-8000:0000000000003B19 end
    }

    /**
     * adds a module to the ontology
     * and grant access to the role taomanager
     *
     * @access private
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string extension
     * @param  string name
     * @return core_kernel_classes_Resource
     */
    private static function addModule($extension, $name)
    {
        $returnValue = null;

        // section 127-0-1-1--1875a6a1:137e65726c7:-8000:0000000000003B1C begin
        $moduleClass = new core_kernel_classes_Class(CLASS_ACL_MODULE);
        $specialURI = FUNCACL_NS.'#'.'m_'.$extension.'_'.$name;
        $returnValue = $moduleClass->createInstance($name,'',$specialURI);
         $returnValue->setPropertiesValues(array(
        	PROPERTY_ACL_MODULE_EXTENSION	=> $extension,
        	PROPERTY_ACL_MODULE_ID			=> $name
        ));
        // section 127-0-1-1--1875a6a1:137e65726c7:-8000:0000000000003B1C end

        return $returnValue;
    }

    /**
     * adds an action to the ontology
     *
     * @access private
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  Resource module
     * @param  string action
     * @return core_kernel_classes_Resource
     */
    private static function addAction( core_kernel_classes_Resource $module, $action)
    {
        $returnValue = null;

        // section 127-0-1-1--1875a6a1:137e65726c7:-8000:0000000000003B1F begin
        $actionClass = new core_kernel_classes_Class(CLASS_ACL_ACTION);

        list($prefix, $extensionName, $moduleName) = explode('_', substr($module->getUri(), strrpos($module->getUri(), '#')));
        $specialURI = FUNCACL_NS.'#'.'a_'.$extensionName.'_'.$moduleName.'_'.$action;
        $returnValue = $actionClass->createInstance($action,'',$specialURI);
        $returnValue->setPropertiesValues(array(
        	PROPERTY_ACL_ACTION_MEMBEROF	=> $module,
        	PROPERTY_ACL_ACTION_ID			=> $action
        ));
        // section 127-0-1-1--1875a6a1:137e65726c7:-8000:0000000000003B1F end

        return $returnValue;
    }

    /**
     * returns the modules of an extension from the ontology
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string extensionID
     * @return array
     */
    public static function getModules($extensionID)
    {
        $returnValue = array();

        // section 127-0-1-1--1ccb663f:138d70cdc8b:-8000:0000000000003B59 begin
        $moduleClass = new core_kernel_classes_Class(CLASS_ACL_MODULE);
		$returnValue = $moduleClass->searchInstances(array(
			PROPERTY_ACL_MODULE_EXTENSION	=> $extensionID
		), array( 
			'like'	=> false
		));
        // section 127-0-1-1--1ccb663f:138d70cdc8b:-8000:0000000000003B59 end

        return (array) $returnValue;
    }

    /**
     * returns the actions of a module from the ontology
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  Resource module
     * @return array
     */
    public static function getActions( core_kernel_classes_Resource $module)
    {
        $returnValue = array();

        // section 127-0-1-1--1ccb663f:138d70cdc8b:-8000:0000000000003B5C begin
        $moduleClass = new core_kernel_classes_Class(CLASS_ACL_ACTION);
		$returnValue = $moduleClass->searchInstances(array(PROPERTY_ACL_ACTION_MEMBEROF => $module));
        // section 127-0-1-1--1ccb663f:138d70cdc8b:-8000:0000000000003B5C end

        return (array) $returnValue;
    }

} /* end of class tao_helpers_funcACL_Model */

?>