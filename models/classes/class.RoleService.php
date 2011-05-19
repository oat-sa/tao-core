<?php

error_reporting(E_ALL);

/**
 * This class provide service on user roles management
 *
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
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
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
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
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
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
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
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
     * Initialize the allowed role.
     * To be overriden.
     *
     * @access protected
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @return mixed
     */
    protected function initRole()
    {
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F73 begin
        
    	$this->roleClass = new core_kernel_classes_Class(CLASS_ROLE_TAOMANAGER);
    	
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F73 end
    }

    /**
     * Get the Role matching the uri
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
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
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
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
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
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
     * assign a role to a list of users
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
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
			
			foreach($allUsers as $userUri){
				$userInstance = new core_kernel_classes_Resource($userUri);
				
				//delete the current role
				foreach($this->getUserRoles($userInstance) as $userRole){
					$userInstance->removeType(new core_kernel_classes_Class($userRole->uriResource));
				}
			}
			
			$roleClass = new core_kernel_classes_Class($role->uriResource);
			
			$done = 0;
			foreach($users as $userUri){
				$userInstance = new core_kernel_classes_Resource($userUri);
				if($userInstance->setType($roleClass)){
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
     * get the users who have the role in parameter
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
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
     * get the roles of a user
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  Resource user
     * @return array
     */
    public function getUserRoles( core_kernel_classes_Resource $user)
    {
        $returnValue = array();

        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F80 begin
        
   		if(!is_null($user)){
			
			$allowedRoles = $this->roleClass->getInstances(true);
			foreach($user->getType() as $role){
				if(array_key_exists($role->uriResource, $allowedRoles)){
					$returnValue[] = new core_kernel_classes_Resource($role->uriResource);
				}
			}
		}
        
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F80 end

        return (array) $returnValue;
    }

    /**
     * check if a user has a role
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
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
	        
			$userRoles = $user->getType();
			
			$acceptedRole =  array_merge(array($role->uriResource) , array_keys($role->getInstances(true))); 
			foreach ($userRoles  as $userRole){
				$returnValue = in_array($userRole->uriResource, $acceptedRole);
				if($returnValue){
					break;
				}
			}
        }
			
        // section 127-0-1-1-7f226444:12902c0ab92:-8000:0000000000001F83 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method createInstance
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  Class clazz
     * @param  string label
     * @return core_kernel_classes_Resource
     */
    public function createInstance( core_kernel_classes_Class $clazz, $label = '')
    {
        $returnValue = null;

        // section 10-13-1--128-2f4f67cd:13003c0785f:-8000:0000000000002E3B begin
		$returnValue = parent::createInstance($clazz, $label);
		$roleClass = new core_kernel_classes_Class($returnValue->uriResource);
		$roleClass->setSubClassOf(new core_kernel_classes_Class(CLASS_GENERIS_USER));
        // section 10-13-1--128-2f4f67cd:13003c0785f:-8000:0000000000002E3B end

        return $returnValue;
    }

} /* end of class tao_models_classes_RoleService */

?>