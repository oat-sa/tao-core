<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/funcACL/class.ActionModelCreator.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 13.06.2012, 17:15:31 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
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
 * Short description of class tao_helpers_funcACL_ActionModelCreator
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers_funcACL
 */
class tao_helpers_funcACL_ActionModelCreator
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method spawnExtensionModel
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
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
					if (!$m->isConstructor() && !$m->isDestructor() && is_subclass_of($m->class,'module') && $m->name != 'setView') {
						$actions[] = $m->name;
					}
				}
				if (count($actions) > 0) {
					$moduleName = substr($moduleClass, strrpos($moduleClass, '_') +1);
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
     * Short description of method addModule
     *
     * @access private
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string extension
     * @param  string name
     * @return core_kernel_classes_Resource
     */
    private static function addModule($extension, $name)
    {
        $returnValue = null;

        // section 127-0-1-1--1875a6a1:137e65726c7:-8000:0000000000003B1C begin
        $moduleClass = new core_kernel_classes_Class(CLASS_ACL_MODULE);
        /*
        $returnValue = $moduleClass->createInstanceWithProperties(array(
        	PROPERTY_ACL_MODULE_EXTENSION	=> $extension, 
        	PROPERTY_ACL_MODULE_ID			=> $name
        ))
        */;
        $specialURI = FUNCACL_NS.'#'.'m_'.$extension.'_'.$name;
        $returnValue = $moduleClass->createInstance($name,'',$specialURI);
         $returnValue->setPropertiesValues(array(
        	PROPERTY_ACL_MODULE_EXTENSION	=> $extension,
        	PROPERTY_ACL_MODULE_ID			=> $name
        ));
        
        // @todo solve this differently:
        $taoManager = new core_kernel_classes_Resource(CLASS_ROLE_TAOMANAGER);
        $taoManager->setPropertyValue(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS), $returnValue->getUri());
        // section 127-0-1-1--1875a6a1:137e65726c7:-8000:0000000000003B1C end

        return $returnValue;
    }

    /**
     * Short description of method addAction
     *
     * @access private
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource module
     * @param  string action
     * @return core_kernel_classes_Resource
     */
    private static function addAction( core_kernel_classes_Resource $module, $action)
    {
        $returnValue = null;

        // section 127-0-1-1--1875a6a1:137e65726c7:-8000:0000000000003B1F begin
        $actionClass = new core_kernel_classes_Class(CLASS_ACL_ACTION);
        /*
        $returnValue = $actionClass->createInstanceWithProperties(array(
        	PROPERTY_ACL_ACTION_MEMBEROF	=> $module,
        	PROPERTY_ACL_ACTION_ID			=> $action
        ));
        */
        // hack
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

} /* end of class tao_helpers_funcACL_ActionModelCreator */

?>