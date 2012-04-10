<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/funcACL/class.funcACL.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 19.03.2012, 08:02:29 with ArgoUML PHP module
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
return true;
		if (is_null($extension) || is_null($module) || is_null($action)) {
			$resolver = new Resolver();
			//if (is_null($extension)) $extension = tao_models_classes_TaoService::singleton()->getCurrentExtension();
			if (is_null($extension)) {
				$b = basename(ROOT_URL);
				$triple = explode('/', substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $b) + strlen($b) + 1));
				$extension = $triple[0];
			}
			if (is_null($module)) $module	= $resolver->getModule();
			if (is_null($action)) $action	= $resolver->getAction();
		}

		//Let access to Main
		if (in_array(strtolower($module), array('main'))) return true;

		//Get the Roles of the current User
		$s = core_kernel_classes_Session::singleton();
		$userClass = new core_kernel_classes_Class(CLASS_GENERIS_USER);
		$search = $userClass->searchInstances(array(PROPERTY_USER_LOGIN => $s->getUser()), array('recursive' => true));
		$userRes = new core_kernel_classes_Resource(key($search));

		//Get the access list (reversed)
		$reverse_access = self::getRolesByActions();

		//Find the Module and, if necessary, the Action
		/*$ns = "http://www.tao.lu/Ontologies/taoFuncACL.rdf#"; //m_taoItems_Items
		$nsa = $ns.'a_'.$extension.'_'.$module.'_'.$action;
		$nsm = $ns.'m_'.$extension.'_'.$module;*/

		//Test if we have a role giving access
		foreach ($userRes->getTypes() as $uri => $t) {
			if (isset($reverse_access[$extension]) && isset($reverse_access[$extension][$module])) {
				if (in_array($uri, $reverse_access[$extension][$module]['roles']) || (isset($reverse_access[$extension][$module]['actions'][$action]) && in_array($uri, $reverse_access[$extension][$module]['actions'][$action]))) {
					$returnValue = true;
					break;
				}
			}
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
		if (!is_null(self::$rolesByActions)) $returnValue = self::$rolesByActions;
		else {
			try {
				$returnValue = tao_models_classes_cache_FileCache::singleton()->get('RolesByActions');
			}
			catch (tao_models_classes_cache_NotFoundException $e) {
				$returnValue = self::buildRolesByActions();
				self::$rolesByActions = $returnValue;
			}
		}
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
		$cp = new core_kernel_classes_Property(CLASS_ACL_MODULE);
		$ap = new core_kernel_classes_Property(CLASS_ACL_ACTION);
		$roles = new core_kernel_classes_Class(CLASS_ROLE_BACKOFFICE);

		foreach ($modc->getInstances() as $id => $m) {
			$mod = new core_kernel_classes_Class($id);
			$label = $mod->getPropertiesValues(array($cp, new core_kernel_classes_Property("http://www.tao.lu/Ontologies/taoFuncACL.rdf#moduleIdentifier")));
			$extension = $mod->getPropertiesValues(array($cp, new core_kernel_classes_Property("http://www.tao.lu/Ontologies/taoFuncACL.rdf#moduleExtension")));
			$modules[] = array('id' => $id, 'label' => current(current(current($label))), 'extension' => current(current(current($extension))));
			$label = array_pop(explode('_', current(current(current($label)))));
			$extension = current(current(current($extension)));
			if (!isset($reverse_access[$extension])) $reverse_access[$extension] = array();
			if (!isset($reverse_access[$extension][$label])) $reverse_access[$extension][$label] = array('actions' => array(), 'roles' => array());

			//Roles
			foreach ($roles->searchInstances(array("http://www.tao.lu/Ontologies/taoFuncACL.rdf#grantAccessModule" => $id)) as $r) {
				$reverse_access[$extension][$label]['roles'][] = $r->getUri();
			}

			//Actions
			foreach ($actc->searchInstances(array("http://www.tao.lu/Ontologies/taoFuncACL.rdf#actionMemberOf" => $id), array()) as $act) {
				$labela = $act->getPropertiesValues(array($ap, new core_kernel_classes_Property("http://www.tao.lu/Ontologies/taoFuncACL.rdf#actionIdentifier")));
				$labela = array_pop(explode('_', current(current(current($labela)))));
				$reverse_access[$extension][$label]['actions'][$labela] = array();

				foreach ($roles->searchInstances(array("http://www.tao.lu/Ontologies/taoFuncACL.rdf#grantAccessAction" => $id)) as $r) {
					$reverse_access[$extension][$label]['actions'][$labela][] = $r->getUri();
				}
			}
		}

		tao_models_classes_cache_FileCache::singleton()->put($reverse_access, 'RolesByActions');
		return $reverse_access;

		//var_dump($reverse_access);
        // section 127-0-1-1--299b9343:13616996224:-8000:000000000000389D end
    }

} /* end of class tao_helpers_funcACL_funcACL */

?>