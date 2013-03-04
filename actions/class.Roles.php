<?php
/**
 * Role Controller provide actions performed from url resolution
 * 
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoGroups
 * @subpackage actions
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class tao_actions_Roles extends tao_actions_TaoModule {
	
	
	protected $authoringService = null;
	protected $forbidden = array();
	
	/**
	 * constructor: initialize the service and the default data
	 * @return Role
	 */
	public function __construct()
	{
		
		parent::__construct();
		
		//the service is initialized by default
		$this->service = tao_models_classes_RoleService::singleton();
		$this->defaultData();
		
		$this->setSessionAttribute('currentSection', 'role');
	}
	
/*
 * conveniance methods
 */
	
	/**
	 * get the selected group from the current context (from the uri and classUri parameter in the request)
	 * @return core_kernel_classes_Resource $group
	 */
	protected function getCurrentInstance()
	{
		$uri = tao_helpers_Uri::decode($this->getRequestParameter('uri'));
		if(is_null($uri) || empty($uri)){
			throw new Exception("No valid uri found");
		}
		
		$clazz = $this->getCurrentClass();
		$role = $this->service->getRole($uri);
		if(is_null($role)){
			throw new Exception("No role found for the uri {$uri}");
		}
		
		return $role;
	}
	
	/**
	 * get the main class
	 * @return core_kernel_classes_Classes
	 */
	protected function getRootClass()
	{
		return $this->service->getRoleClass();
	}
	
/*
 * controller actions
 */
	
	/**
	*	forbidden to edit class and create subclass
	*/
	
	/**
	*	index:
	*/
	public function index()
	{
		$this->removeSessionAttribute('uri');
		$this->removeSessionAttribute('classUri');
		
		$this->setData('section', $this->getSessionAttribute('currentSection'));
		$this->setView('roles/index.tpl');
	}
	
	/**
	 * Edit a group instance
	 * @return void
	 */
	public function editRole()
	{
		$clazz = $this->getCurrentClass();
		$role = $this->getCurrentInstance();
		
		$formContainer = new tao_actions_form_Role($clazz, $role);
		$myForm = $formContainer->getForm();
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				$binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($role);
				$role = $binder->bind($myForm->getValues());

				core_kernel_users_Cache::removeIncludedRoles($role); // flush cache for this role.
				
				$this->setSessionAttribute('showNodeUri', tao_helpers_Uri::encode($role->uriResource));
				$this->setData('message', __('Role saved'));
				$this->setData('reload', true);
			}
		}
		$this->setData('users', json_encode(array_map('tao_helpers_Uri::encode', $this->service->getUsers($role) )));
		$this->setData('uri', tao_helpers_Uri::encode($role->uriResource));
		$this->setData('classUri', tao_helpers_Uri::encode($clazz->uriResource));
		$this->setData('formTitle', 'Edit Role');
		$this->setData('myForm', $myForm->render());
		$this->setView('roles/form.tpl');
	}

	/**
	 * Delete a group or a group class
	 * @return void
	 */
	public function delete()
	{
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$deleted = false;
			if($this->getRequestParameter('uri')){
				
				$role = $this->getCurrentInstance();
			
				if(!in_array($role->getUri(), $this->forbidden)){
						//check if no user is using this role:
						$userClass = new core_kernel_classes_Class(CLASS_GENERIS_USER);
						$options = array('recursive' => true, 'like' => false);
						$filters = array(PROPERTY_USER_ROLES => $role->getUri());
						$users = $userClass->searchInstances($filters, array());
						if(empty($users)){
							//delete role here:
							$deleted = $this->service->deleteRole($role);
							core_kernel_users_Cache::removeIncludedRoles($role);
						}else{
							//set message error
							throw new Exception(__('This role is still given to one or more users. Please remove the role to these users first.'));
						}
				}else{
					throw new Exception($role->getLabel() . ' could not be deleted');
				}
			}
			
			echo json_encode(array('deleted' => $deleted));	
		}
	}
	
	public function getUsers()
	{
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$userService = tao_models_classes_UserService::singleton();
			echo json_encode($userService->toTree(new core_kernel_classes_Class(CLASS_TAO_USER), array()));	
		}
	}
	
	/**
	 * save from the checkbox tree the users to link with 
	 * @return void
	 */
	public function saveUsers()
	{
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$saved = false;
			$role = $this->getCurrentInstance();
			$userRolesProperty = new core_kernel_classes_Property(PROPERTY_USER_ROLES);
			
			// Detect users selected to be given the role.
			$detectedUsers = array(); // URIs of detected users.
			foreach($this->getRequestParameters() as $key => $value){
				if(preg_match("/^instance_/", $key)){
					array_push($detectedUsers, tao_helpers_Uri::decode($value));
				}
			}
			
			// get the users currently associated to the target role.
			$userClass = new core_kernel_classes_Class(CLASS_GENERIS_USER);
			$options = array('recursive' => true, 'like' => false);
			$filters = array($userRolesProperty->getUri() => $role->getUri());
			$users = $userClass->searchInstances($filters, $options);
			
			// Remove role to some users if not selected anymore.
			foreach ($users as $u){	
				if (!in_array($u->getUri(), $detectedUsers)){
					// if the user has the role but is not in the selected users
					// remove the role from him.
					$u->removePropertyValues($userRolesProperty, array('pattern' => $role->getUri()));
				}
			}
			
			if(true === $this->service->setRoleToUsers($role, $detectedUsers)){
				$saved = true;
			}
	
			echo json_encode(array('saved'	=> $saved));	
		}
	}
	
	public function addInstance()
	{
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$instance = $this->service->createInstance($this->service->getRoleClass());
			if(!is_null($instance) && $instance instanceof core_kernel_classes_Resource){
				echo json_encode(array(
					'label'	=> $instance->getLabel(),
					'uri' 	=> tao_helpers_Uri::encode($instance->uriResource)
				));
			}	
		}
	}
	
	public function editRoleClass()
	{
		$this->removeSessionAttribute('uri');
		$this->index();
	}
	
}
?>