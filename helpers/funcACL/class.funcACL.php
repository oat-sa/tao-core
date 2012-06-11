<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/funcACL/class.funcACL.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 13.04.2012, 13:59:01 with ArgoUML PHP module
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
			$userRes = tao_models_classes_UserService::singleton()->getCurrentUser();
			common_Logger::i('Access denied to '.$extension.'::'.$module.'::'.$action.' for '.
				(isset($userRes) ? 'User '.$userRes->getLabel() : 'anonymous')
				.' with roles: '.implode(',', $roles));
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
		self::$rolesByActions = null;

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

} /* end of class tao_helpers_funcACL_funcACL */

?>