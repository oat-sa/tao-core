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

error_reporting(E_ALL);

/**
 * This class provide service on user roles management
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * The Service class is an abstraction of each service instance. 
 * Used to centralize the behavior related to every servcie instances.
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/models/classes/class.GenerisService.php');

/* user defined includes */
// section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F61-includes begin
// section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F61-includes end

/* user defined constants */
// section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F61-constants begin
// section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F61-constants end

/**
 * This class provide service on user roles management
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */
class tao_models_classes_RoleService
    extends tao_models_classes_GenerisService
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * the core user service
     *
     * @access public
     * @var Service
     */
    public $generisUserService = null;

    /**
     * the class of the target role
     *
     * @access public
     * @var Class
     */
    public $roleClass = null;

    // --- OPERATIONS ---

    /**
     * constructor, call initRole
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    protected function __construct()
    {
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F71 begin
        
    	parent::__construct();
		$this->generisUserService = core_kernel_users_Service::singleton();
		
		$this->initRole();
    	
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F71 end
    }

    /**
     * Initialize the allowed role.
     * To be overriden.
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    protected function initRole()
    {
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F73 begin
        
    	$this->roleClass = new core_kernel_classes_Class(CLASS_ROLE);
    	
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F73 end
    }

    /**
     * Get the Role matching the uri
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string uri
     * @return core_kernel_classes_Resource
     */
    public function getRole($uri)
    {
        $returnValue = null;

        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F75 begin
        
        if(!empty($uri)){
        	$returnValue = new core_kernel_classes_Resource($uri);
        }
        
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F75 end

        return $returnValue;
    }

    /**
     * get the target role class
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return core_kernel_classes_Class
     */
    public function getRoleClass()
    {
        $returnValue = null;

        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F93 begin
        
        $returnValue = $this->roleClass;
        
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F93 end

        return $returnValue;
    }

    /**
     * remove a role
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource role
     * @return boolean
     */
    public function deleteRole( core_kernel_classes_Resource $role)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F88 begin
        
        if(!is_null($role)){
        	core_kernel_users_Cache::removeIncludedRoles($role);
        	
        	// The whole ACL cache must be flushed.
        	/*
        	 * @todo optimize this by
        	 * - removing only cache files related to the role.
        	 * - having a cache of which module includes wich role.
        	 */ 
        	tao_helpers_funcACL_Cache::flush();
        	$returnValue = $role->delete();
        }
        
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F88 end

        return (bool) $returnValue;
    }

    /**
     * assign a role to a list of users
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource role
     * @param  array users
     * @return boolean
     */
    public function setRoleToUsers( core_kernel_classes_Resource $role, $users = array())
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F78 begin
        $rolesProperty = new core_kernel_classes_Property(PROPERTY_USER_ROLES);
    	foreach ($users as $u){
    		$u = ($u instanceof core_kernel_classes_Resource) ? $u : new core_kernel_classes_Resource($u);
    		$u->removePropertyValues($rolesProperty);
    		
    		// assign the new role.
    		$u->setPropertyValue($rolesProperty, $role);
    	}
        
    	$returnValue = true;
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F78 end

        return (bool) $returnValue;
    }

    /**
     * get the users who have the role in parameter
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource role
     * @return array
     */
    public function getUsers( core_kernel_classes_Resource $role)
    {
        $returnValue = array();

        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F7D begin 
        $filters = array(PROPERTY_USER_ROLES => $role->getUri());
        $options = array('like' => false, 'recursive' => true);
        
        $userClass = new core_kernel_classes_Class(CLASS_GENERIS_USER);
        $results = $userClass->searchInstances($filters, $options);
        
        $returnValue = array_keys($results);
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F7D end

        return (array) $returnValue;
    }

    /**
     * Short description of method createInstance
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Class clazz
     * @param  string label
     * @return core_kernel_classes_Resource
     */
    public function createInstance( core_kernel_classes_Class $clazz, $label = '')
    {
        $returnValue = null;

        // section 10-13-1--128-2f4f67cd:13003c0785f:-8000:0000000000002E3B begin
		$returnValue = parent::createInstance($clazz, $label);
		$roleClass = new core_kernel_classes_Class($returnValue);
		$roleClass->setSubClassOf(new core_kernel_classes_Class(CLASS_GENERIS_USER));
        // section 10-13-1--128-2f4f67cd:13003c0785f:-8000:0000000000002E3B end

        return $returnValue;
    }

} /* end of class tao_models_classes_RoleService */

?>