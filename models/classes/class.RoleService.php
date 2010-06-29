<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/models/classes/class.RoleService.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 09.06.2010, 12:05:07 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/models/classes/class.Service.php');

/* user defined includes */
// section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F61-includes begin
// section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F61-includes end

/* user defined constants */
// section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F61-constants begin
// section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F61-constants end

/**
 * Short description of class tao_models_classes_RoleService
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */
class tao_models_classes_RoleService
    extends tao_models_classes_Service
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute generisUserService
     *
     * @access public
     * @var Service
     */
    public $generisUserService = null;

    /**
     * Short description of attribute roleClass
     *
     * @access public
     * @var Class
     */
    public $roleClass = null;

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function __construct()
    {
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F71 begin
        
    	parent::__construct();
		$this->generisUserService = core_kernel_users_Service::singleton();
		
		$this->initRole();
    	
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F71 end
    }

    /**
     * Short description of method initRole
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initRole()
    {
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F73 begin
        
    	$this->roleClass = new core_kernel_classes_Class(CLASS_ROLE_TAOMANAGER);
    	
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F73 end
    }

    /**
     * Short description of method getRole
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
     * Short description of method getRoleClass
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
     * Short description of method deleteRole
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource role
     * @return boolean
     */
    public function deleteRole( core_kernel_classes_Resource $role)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F88 begin
        
        if(!is_null($role)){
        	$returnValue = $role->delete();
        }
        
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F88 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method setRoleToUsers
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource role
     * @param  array users
     * @return boolean
     */
    public function setRoleToUsers( core_kernel_classes_Resource $role, $users = array())
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F78 begin
        
        if(!is_null($role)){
	    	//get all users who have the following role:
	    	$allUsers = $this->getUsers($role);
			
			foreach($allUsers as $user){
				//delete the current role
				$returnValue = core_kernel_impl_ApiModelOO::singleton()->removeStatement($user, RDF_TYPE, $role->uriResource, '');
			}
			
			$done = 0;
			foreach($users as $userUri){
				$userInstance = new core_kernel_classes_Resource($userUri);
				if($userInstance->setPropertyValue(new core_kernel_classes_Property(RDF_TYPE), $role->uriResource)){
					$done++;
				}
			}
			if($done == count($users)){
				$returnValue = true;
			}
        }
        
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F78 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method getUsers
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource role
     * @return array
     */
    public function getUsers( core_kernel_classes_Resource $role)
    {
        $returnValue = array();

        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F7D begin
        
        if(!is_null($role)){
        	$userClass = new core_kernel_classes_Class($role->uriResource);	
    		$returnValue = array_keys($userClass->getInstances(true));
        }
        
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F7D end

        return (array) $returnValue;
    }

    /**
     * Short description of method getUserRoles
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource user
     * @return array
     */
    public function getUserRoles( core_kernel_classes_Resource $user)
    {
        $returnValue = array();

        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F80 begin
        
   		if(!is_null($user)){
			
			$allowedRoles = $this->roleClass->getInstances(true);
			
			$roles = $user->getPropertyValues(new core_kernel_classes_Property(RDFS_TYPE));
			foreach($roles as $role){
				if(array_key_exists($role, $allowedRoles)){
					$returnValue[] = new core_kernel_classes_Resource($role);
				}
			}
		}
        
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F80 end

        return (array) $returnValue;
    }

    /**
     * Short description of method checkUserRole
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource user
     * @param  Class role
     * @return boolean
     */
    public function checkUserRole( core_kernel_classes_Resource $user,  core_kernel_classes_Class $role = null)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F83 begin
        
        if(!is_null($user)){
        
	        if(is_null($role)){
	        	$role = $this->roleClass;
	        }
	        
			$userRoleCollection = $user->getPropertyValuesCollection(new core_kernel_classes_Property(RDF_TYPE));
			
			$acceptedRole =  array_merge(array($role->uriResource) , array_keys($role->getInstances(true))); 
			foreach ($userRoleCollection->getIterator()  as $userRole){
				$returnValue = in_array($userRole->uriResource, $acceptedRole);
				if($returnValue){
					break;
				}
			}
        }
			
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F83 end

        return (bool) $returnValue;
    }

} /* end of class tao_models_classes_RoleService */

?>