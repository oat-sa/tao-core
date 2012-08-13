<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/funcACL/class.funcACL.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 30.07.2012, 11:55:05 with ArgoUML PHP module
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Jehan Bihin
 * @package tao
 * @subpackage helpers_funcACL
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--b28769d:135f11069cc:-8000:0000000000003859-includes begin
// section 127-0-1-1--b28769d:135f11069cc:-8000:0000000000003859-includes end

/* user defined constants */
// section 127-0-1-1--b28769d:135f11069cc:-8000:0000000000003859-constants begin
// section 127-0-1-1--b28769d:135f11069cc:-8000:0000000000003859-constants end

/**
 * Short description of class tao_helpers_funcACL_funcACL
 *
 * @access public
 * @author Jehan Bihin
 * @package tao
 * @subpackage helpers_funcACL
 */
class tao_helpers_funcACL_funcACL
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Array for the Actions/Modules with Roles
     *
     * @access public
     * @since 2.2
     * @var mixed
     */
    public static $rolesByActions = null;

    // --- OPERATIONS ---

    /**
     * Test if the Module and Action of this module is accessible by the current
     * (session), via roles
     *
     * @access public
     * @author Jehan Bihin
     * @param  string extension
     * @param  string module
     * @param  string action
     * @return boolean
     * @since 2.2
     */
    public static function hasAccess($extension, $module, $action)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--b28769d:135f11069cc:-8000:000000000000385B begin

		if (is_null($extension) || is_null($module) || is_null($action)) {
			$context = Context::getInstance();
			//if (is_null($extension)) $extension = tao_models_classes_TaoService::singleton()->getCurrentExtension();
			if (is_null($extension)) {
				$extension = $context->getExtensionName();
			}
			if (is_null($module)) {
				$module	= $context->getModuleName();
			}
			if (is_null($action)) {
				$action	= $context->getActionName();
			}
		}

		//Get the Roles of the current User
		$roles = core_kernel_classes_Session::singleton()->getUserRoles();

		//Get the access list (reversed)
		$reverse_access = self::getRolesByActions();

		//Find the Module and, if necessary, the Action
		/*$ns = "http://www.tao.lu/Ontologies/taoFuncACL.rdf#"; //m_taoItems_Items
		$nsa = $ns.'a_'.$extension.'_'.$module.'_'.$action;
		$nsm = $ns.'m_'.$extension.'_'.$module;*/

		//Test if we have a role giving access
		$roles[] = new core_kernel_classes_Class(CLASS_ROLE_BASEACCESS);
		foreach ($roles as $role) {
			if (isset($reverse_access[$extension]) && isset($reverse_access[$extension][$module])) {
				if (in_array($role->getUri(), $reverse_access[$extension][$module]['roles'])
					|| (isset($reverse_access[$extension][$module]['actions'][$action])
						&& in_array($role->getUri(), $reverse_access[$extension][$module]['actions'][$action]))) {
					$returnValue = true;
					break;
				}
			}
		}
		if (!$returnValue) {
			common_Logger::i('Access denied to '.$extension.'::'.$module.'::'.$action.' for user '.
				'\''.core_kernel_classes_Session::singleton()->getUserLogin().'\'');
		}
        // section 127-0-1-1--b28769d:135f11069cc:-8000:000000000000385B end

        return (bool) $returnValue;
    }

    /**
     * get the array of Actions/Modules with associted Roles
     *
     * @access public
     * @author Jehan Bihin
     * @return array
     * @since 2.2
     */
    public static function getRolesByActions()
    {
        $returnValue = array();

        // section 127-0-1-1--299b9343:13616996224:-8000:000000000000389B begin
		if (is_null(self::$rolesByActions)) {
			try {
				self::$rolesByActions = tao_models_classes_cache_FileCache::singleton()->get('RolesByActions');
			}
			catch (tao_models_classes_cache_NotFoundException $e) {
				common_Logger::i('read roles by action failed, recalculating');
				self::$rolesByActions = self::buildRolesByActions();
			}
		}
		$returnValue = self::$rolesByActions;
        // section 127-0-1-1--299b9343:13616996224:-8000:000000000000389B end

        return (array) $returnValue;
    }

    /**
     * Create the array of Actions/Modules and give the associated Roles
     *
     * @access public
     * @author Jehan Bihin
     * @return mixed
     * @since 2.2
     */
    public static function buildRolesByActions()
    {
        // section 127-0-1-1--299b9343:13616996224:-8000:000000000000389D begin
		$reverse_access = array();
		// alternate:

		self::$rolesByActions = null;
		$roles = new core_kernel_classes_Class(CLASS_ROLE); //before : CLASS_ROLE_BACKOFFICE

		foreach ($roles->getInstances(true) as $role) {
			$moduleAccess = $role->getPropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS));
			$actionAccess = $role->getPropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS));
			foreach ($moduleAccess as $moduleURI) {
				$moduleRessource = new core_kernel_classes_Resource($moduleURI);
				$arr = $moduleRessource->getPropertiesValues(array(
					new core_kernel_classes_Property(PROPERTY_ACL_MODULE_ID),
					new core_kernel_classes_Property(PROPERTY_ACL_MODULE_EXTENSION)
				));
				if (!isset($arr[PROPERTY_ACL_MODULE_ID]) || !isset($arr[PROPERTY_ACL_MODULE_EXTENSION])) {
					common_Logger::e('Module '.$moduleURI.' not found for role '.$role->getLabel());
					continue;
				}
				$ext = (string)current($arr[PROPERTY_ACL_MODULE_EXTENSION]);
				$mod = (string)current($arr[PROPERTY_ACL_MODULE_ID]);
				if (!isset($reverse_access[$ext])) {
					$reverse_access[$ext] = array();
				}
				if (!isset($reverse_access[$ext][$mod])) {
					$reverse_access[$ext][$mod] = array('actions' => array(), 'roles' => array());
				}
				$reverse_access[$ext][$mod]['roles'][] = $role->getUri();
			}
			foreach ($actionAccess as $actionURI) {
				$actionRessource = new core_kernel_classes_Resource($actionURI);
				$arr = $actionRessource->getPropertiesValues(array(
					new core_kernel_classes_Property(PROPERTY_ACL_ACTION_ID),
					new core_kernel_classes_Property(PROPERTY_ACL_ACTION_MEMBEROF)
				));
				if (!isset($arr[PROPERTY_ACL_ACTION_ID]) || !isset($arr[PROPERTY_ACL_ACTION_MEMBEROF])) {
					common_Logger::e('Action '.$actionURI.' not found or incomplete');
					continue;
				}
				$act = (string)current($arr[PROPERTY_ACL_ACTION_ID]);
				// @todo cache module id/extension
				$moduleRessource = current($arr[PROPERTY_ACL_ACTION_MEMBEROF]);
				$arr = $moduleRessource->getPropertiesValues(array(
					new core_kernel_classes_Property(PROPERTY_ACL_MODULE_ID),
					new core_kernel_classes_Property(PROPERTY_ACL_MODULE_EXTENSION)
				));
				$ext = (string)current($arr[PROPERTY_ACL_MODULE_EXTENSION]);
				$mod = (string)current($arr[PROPERTY_ACL_MODULE_ID]);
				if (!isset($reverse_access[$ext])) {
					$reverse_access[$ext] = array();
				}
				if (!isset($reverse_access[$ext][$mod])) {
					$reverse_access[$ext][$mod] = array('actions' => array(), 'roles' => array());
				}
				if (!isset($reverse_access[$ext][$mod][$act])) {
					$reverse_access[$ext][$mod]['actions'][$act] = array();
				}
				$reverse_access[$ext][$mod]['actions'][$act][] = $role->getUri();
			}
		}

		/*
		$modc = new core_kernel_classes_Class(CLASS_ACL_MODULE);
		$actc = new core_kernel_classes_Class(CLASS_ACL_ACTION);
		$roles = new core_kernel_classes_Class(CLASS_ROLE); //before : CLASS_ROLE_BACKOFFICE
		$propACLModuleId = new core_kernel_classes_Property(PROPERTY_ACL_MODULE_ID);
        $propACLModuleExt = new core_kernel_classes_Property(PROPERTY_ACL_MODULE_EXTENSION);
        $propACLActionId = new core_kernel_classes_Property(PROPERTY_ACL_ACTION_ID);

		foreach ($modc->getInstances() as $mod) {
			$values = $mod->getPropertiesValues(array(
				$propACLModuleId,
				$propACLModuleExt
			));
			$label = (string)array_pop($values[PROPERTY_ACL_MODULE_ID]);
			$extension = (string)array_pop($values[PROPERTY_ACL_MODULE_EXTENSION]);
			$modules[] = array('id' => $mod->getUri(), 'label' => $label, 'extension' => $extension);
			$lbla = explode('_', $label);
			$label = array_pop($lbla);
			if (!isset($reverse_access[$extension])) {
				$reverse_access[$extension] = array();
			}
			if (!isset($reverse_access[$extension][$label])) {
				$reverse_access[$extension][$label] = array('actions' => array(), 'roles' => array());
			}

			//Roles
			foreach ($roles->searchInstances(array(PROPERTY_ACL_MODULE_GRANTACCESS => $mod->getUri()), array('like' => false, 'recursive' => true)) as $r) {
				$reverse_access[$extension][$label]['roles'][] = $r->getUri();
			}

			//Actions
			foreach ($actc->searchInstances(array(PROPERTY_ACL_ACTION_MEMBEROF => $mod->getUri()), array('like' => false, 'recursive' => true)) as $act) {
				$labela = $act->getUniquePropertyValue($propACLActionId)->__toString();
				$lbla = explode('_', $labela);
				$labela = array_pop($lbla);
				$reverse_access[$extension][$label]['actions'][$labela] = array();

				foreach ($roles->searchInstances(array(PROPERTY_ACL_ACTION_GRANTACCESS => $act->getUri()), array('like' => false, 'recursive' => true)) as $r) {
					$reverse_access[$extension][$label]['actions'][$labela][] = $r->getUri();
				}
			}
		}
*/
		tao_models_classes_cache_FileCache::singleton()->put($reverse_access, 'RolesByActions');
		return $reverse_access;
        // section 127-0-1-1--299b9343:13616996224:-8000:000000000000389D end
    }

    /**
     * Clear Cache
     *
     * @access public
     * @author Jehan Bihin
     * @return mixed
     * @since 2.2
     */
    public static function removeRolesByActions()
    {
        // section 127-0-1-1-5382e8cb:136ab734ff6:-8000:0000000000003908 begin
			tao_models_classes_cache_FileCache::singleton()->remove('RolesByActions');
			self::$rolesByActions = null;
        // section 127-0-1-1-5382e8cb:136ab734ff6:-8000:0000000000003908 end
    }

    /**
     * Returns the roles that grant access to a specific action
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource action
     * @return array
     */
    public static function getRolesByAction( core_kernel_classes_Resource $action)
    {
        $returnValue = array();

        // section 127-0-1-1--1ccb663f:138d70cdc8b:-8000:0000000000003B65 begin
	    // @todo: don't use uri
        $uri = explode('#', $action->getUri());
		$uri = explode('_', $uri[1], 4);
		$ext = $uri[1];
		$mod = $uri[2];
		$act = $uri[3];
		$cache = self::getRolesByActions();
		if (isset($cache[$ext][$mod]['actions'][$act])) {
			foreach ($cache[$ext][$mod]['actions'][$act] as $role) {
				$returnValue[] = new core_kernel_classes_Resource($role);
			}
		}
        // section 127-0-1-1--1ccb663f:138d70cdc8b:-8000:0000000000003B65 end

        return (array) $returnValue;
    }

    /**
     * Returns the roles that grant access to a specific module
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource module
     * @return array
     */
    public static function getRolesByModule( core_kernel_classes_Resource $module)
    {
        $returnValue = array();

        // section 127-0-1-1--1ccb663f:138d70cdc8b:-8000:0000000000003B68 begin
        // @todo: don't use uri
        $uri = explode('#', $module->getUri());
		$uri = explode('_', $uri[1]);
		$ext = $uri[1];
		$mod = $uri[2];
		$cache = self::getRolesByActions();
		if (isset($cache[$ext][$mod]['roles'])) {
			foreach ($cache[$ext][$mod]['roles'] as $role) {
				$returnValue[] = new core_kernel_classes_Resource($role);
			}
		}
        // section 127-0-1-1--1ccb663f:138d70cdc8b:-8000:0000000000003B68 end

        return (array) $returnValue;
    }

} /* end of class tao_helpers_funcACL_funcACL */

?>