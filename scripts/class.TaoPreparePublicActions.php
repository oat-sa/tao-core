<?php

error_reporting(E_ALL);

/**
 * TAO - tao/scripts/class.TaoPreparePublicActions.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 06.03.2012, 07:58:48 with ArgoUML PHP module
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage scripts
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_scripts_Runner
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('tao/scripts/class.Runner.php');

/* user defined includes */
// section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684A-includes begin
// section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684A-includes end

/* user defined constants */
// section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684A-constants begin
// section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684A-constants end

/**
 * Short description of class tao_scripts_TaoPreparePublicActions
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage scripts
 */
class tao_scripts_TaoPreparePublicActions
    extends tao_scripts_Runner
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method preRun
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function preRun()
    {
        // section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684C begin
        // section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684C end
    }

    /**
     * Short description of method run
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function run()
    {
        // section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684E begin
        
        // delete old Instances
		$moduleClass = new core_kernel_classes_Class(CLASS_ACL_MODULE);
    	foreach ($moduleClass->getInstances() as $res) {
    		$res->delete();
    	}
    	$actionClass = new core_kernel_classes_Class(CLASS_ACL_ACTION);
    	foreach ($actionClass->getInstances() as $res) {
    		$res->delete();
    	}
    	
    	$taoManager = new core_kernel_classes_Resource(INSTANCE_ROLE_TAOMANAGER);
        $taoManager->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS));
        
    	
    	foreach (common_ext_ExtensionsManager::singleton()->getInstalledExtensions() as $extension) {
			// this also adds TaoManager to the Modules
			tao_helpers_funcACL_Model::spawnExtensionModel($extension);
		}
		tao_helpers_funcACL_funcACL::buildRolesByActions();
        // section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684E end
    }

    /**
     * Short description of method postRun
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function postRun()
    {
        // section 127-0-1-1--570b06ee:135e6b6b680:-8000:0000000000006850 begin
        // section 127-0-1-1--570b06ee:135e6b6b680:-8000:0000000000006850 end
    }

} /* end of class tao_scripts_TaoPreparePublicActions */

?>