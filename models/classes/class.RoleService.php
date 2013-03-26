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
    implements core_kernel_users_RolesManagement
{

    /**
     * the core user service
     *
     * @access public
     * @var Service
     */
    protected $generisUserService = null;

    /**
     * the class of the target role
     *
     * @access public
     * @var Class
     */
    private $roleClass = null;


    /**
     * constructor, call initRole
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    protected function __construct()
    {
    	parent::__construct();
		$this->generisUserService = core_kernel_users_Service::singleton();
		
		$this->initRole();
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
    	$this->roleClass = new core_kernel_classes_Class(CLASS_ROLE);
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
        
        if(!empty($uri)){
        	$returnValue = new core_kernel_classes_Resource($uri);
        }

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
        
        $returnValue = $this->roleClass;

        return $returnValue;
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

        $rolesProperty = new core_kernel_classes_Property(PROPERTY_USER_ROLES);
    	foreach ($users as $u){
    		$u = ($u instanceof core_kernel_classes_Resource) ? $u : new core_kernel_classes_Resource($u);
    		$u->removePropertyValues($rolesProperty);
    		
    		// assign the new role.
    		$u->setPropertyValue($rolesProperty, $role);
    	}
        
    	$returnValue = true;

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

        $filters = array(PROPERTY_USER_ROLES => $role->getUri());
        $options = array('like' => false, 'recursive' => true);
        
        $userClass = new core_kernel_classes_Class(CLASS_GENERIS_USER);
        $results = $userClass->searchInstances($filters, $options);
        
        $returnValue = array_keys($results);

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

		$returnValue = parent::createInstance($clazz, $label);
		$roleClass = new core_kernel_classes_Class($returnValue);
		$roleClass->setSubClassOf(new core_kernel_classes_Class(CLASS_GENERIS_USER));

        return $returnValue;
    }
    
    public function addRole($label, $includedRoles = null){
		return $this->generisUserService->addRole($label, $includedRoles);
	}

	public function removeRole(core_kernel_classes_Resource $role){
		return $this->generisUserService->removeRole($role);
	}
	
	public function getIncludedRoles(core_kernel_classes_Resource $role){
		return $this->generisUserService->getIncludedRoles($role);
	}
	
	public function includeRole( core_kernel_classes_Resource $role,  core_kernel_classes_Resource $roleToInclude){
		$this->generisUserService->includeRole($role, $roleToInclude);
	}
}

?>