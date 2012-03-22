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
		$resolver = new Resolver();
		//if (is_null($extension)) $extension = tao_models_classes_TaoService::singleton()->getCurrentExtension();
		if (is_null($extension)) {
			$b = basename(ROOT_URL);
			$triple = explode('/', substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $b) + strlen($b) + 1));
			$extension = $triple[0];
		}
		if (is_null($module)) $module	= $resolver->getModule();
		if (is_null($action)) $action	= $resolver->getAction();

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
		$ns = "http://www.tao.lu/Ontologies/taoFuncACL.rdf#";
		var_dump($reverse_access);

		//Test if we have a role giving access
		foreach ($userRes->getTypes() as $uri => $t) {
			//var_dump($uri);
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
			$returnValue = tao_models_classes_FileCache::singleton()->get('RolesByActions');
			if (is_null($returnValue)) $returnValue = self::buildRolesByActions();
			self::$rolesByActions = $returnValue;
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

		$roles = new core_kernel_classes_Class(CLASS_ROLE_BACKOFFICE);
		foreach ($roles->getInstances() as $roleuri => $r) {
			$role = new core_kernel_classes_Resource($roleuri);
			$access = $role->getPropertiesValues(array(new core_kernel_classes_Property("http://www.tao.lu/Ontologies/taoFuncACL.rdf#grantAccessModule"), new core_kernel_classes_Property("http://www.tao.lu/Ontologies/taoFuncACL.rdf#grantAccessAction")));
			foreach ($access as $uri => $as) {
				foreach ($as as $a) {
					if (!isset($reverse_access[$a->uriResource])) $reverse_access[$a->uriResource] = array($roleuri);
					else if (!in_array($roleuri, $reverse_access[$a->uriResource])) $reverse_access[$a->uriResource][] = $roleuri;
				}
			}
		}

		tao_models_classes_FileCache::singleton()->put($reverse_access, 'RolesByActions');
		return $reverse_access;

		//var_dump($reverse_access);
        // section 127-0-1-1--299b9343:13616996224:-8000:000000000000389D end
    }

} /* end of class tao_helpers_funcACL_funcACL */

?>