<?php
/**
 * This controller provide the actions to manage the application roles
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class tao_actions_Roles extends tao_actions_CommonModule {

	/**
	 * Constructor performs initializations actions
	 * @return void
	 */
	public function __construct(){
		parent::__construct();
		$this->defaultData();
	}

	/**
	 * Show the list of roles
	 * @return void
	 */
	public function index(){
		//$this->setData('data', __('list the roles'));
		$rolesc = new core_kernel_classes_Class(CLASS_ROLE);
		$roles = array();
		foreach ($rolesc->searchInstances(array(PROPERTY_ROLE_ISSYSTEM => GENERIS_FALSE), array('recursive' => 1)) as $id => $r) {
			//$label = explode('#', $id);
			$roles[] = array('id' => $id, 'label' => $r->getLabel());
		}

		$this->setData('roles', $roles);
		$this->setView('roles/list.tpl');
		//echo core_kernel_classes_DbWrapper::singleton()->getNrOfQueries();
	}

	/**
	 * provide the roles list data via json
	 * @return void
	 */
	public function data(){


		//echo json_encode($response);
	}

	/**
	 * Remove a role
	 * @return vois
	 */
	public function delete(){
		//$this->redirect(_url('index', 'Main', 'tao', array('structure' => 'users', 'message' => $message)));
	}

	/**
	 * Form to add a role
	 * @return void
	 */
	public function add(){

	}

	/**
	 * Form to edit a role
	 * @return  void
	 */
	public function edit(){

	}

	public function getModules() {
		$role = $this->getRequestParameter('role');
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$rban = array();
		foreach ($rba as $enom => $e) {
			$access = 0;
			$rban[$enom] = array('modules' => array(), 'have-access' => false, 'have-allaccess' => false, 'uri' => 'http://www.tao.lu/Ontologies/taoFuncACL.rdf#e_'.$enom);
			foreach ($e as $mnom => $m) {
				$rban[$enom]['modules'][$mnom] = array('have-access' => false, 'have-allaccess' => false, 'uri' => 'http://www.tao.lu/Ontologies/taoFuncACL.rdf#m_'.$enom.'_'.$mnom);
				if (in_array($role, $m['roles'])) {
					$rban[$enom]['modules'][$mnom]['have-allaccess'] = true;
					$access++;
				}
				//Have any access in actions ?
				$aaccess = 0;
				foreach ($m['actions'] as $act => $r) if (in_array($role, $r)) $aaccess++;
				if ($aaccess > 0) {
					$rban[$enom]['modules'][$mnom]['have-access'] = true;
					$access++;
				}
				if ($aaccess > 0 && $aaccess == count($m['actions'])) {
					$rban[$enom]['modules'][$mnom]['have-allaccess'] = true;
				}
			}
			//By modules, not by the extension directly
			if ($access > 0) $rban[$enom]['have-access'] = true;
		}
		echo json_encode($rban);
	}

	public function getActions() {
		$role = new core_kernel_classes_Resource($this->getRequestParameter('role'));
		$module = new core_kernel_classes_Resource($this->getRequestParameter('module'));

		$actions = array();
		foreach (tao_helpers_funcACL_ActionModel::getActions($module) as $action) {
			$actions[$action->getLabel()] = array(
				'uri'			=> $action->getUri(),
				'have-access'	=> in_array($role, tao_helpers_funcACL_funcACL::getRolesByAction($action))
			);
		}
		ksort($actions);
		echo json_encode(array(
			'actions'	=> $actions,
			'byModule'	=> in_array($role, tao_helpers_funcACL_funcACL::getRolesByModule($module))
		));
	}

	public function getAttachedModuleRoles() {
		$module = new core_kernel_classes_Resource($this->getRequestParameter('module'));
		$roles = array();
		foreach (tao_helpers_funcACL_funcACL::getRolesByModule($module) as $role) {
			$roles[] = array('uri' => $role->getUri(), 'label' => $role->getLabel());
		}
		echo json_encode(array('roles' => $roles));
	}

	public function getAttachedActionRoles() {
		$action = new core_kernel_classes_Resource($this->getRequestParameter('action'));
		$roles = array();
		foreach (tao_helpers_funcACL_funcACL::getRolesByAction($action) as $role) {
			$roles[] = array('uri' => $role->getUri(), 'label' => $role->getLabel());
		}
		echo json_encode(array('roles' => $roles));
	}

	public function removeExtensionAccess() {
		$role = $this->getRequestParameter('role');
		$uri = $this->getRequestParameter('uri');
		tao_models_classes_funcACL_ExtensionAccessService::singleton()->remove($role, $uri);
		echo json_encode(array('uri' => $uri));
	}

	public function addExtensionAccess() {
		$role = $this->getRequestParameter('role');
		$uri = $this->getRequestParameter('uri');
		tao_models_classes_funcACL_ExtensionAccessService::singleton()->add($role, $uri);
		echo json_encode(array('uri' => $uri));
	}

	public function removeModuleAccess() {
		$role = $this->getRequestParameter('role');
		$uri = $this->getRequestParameter('uri');
		tao_models_classes_funcACL_ModuleAccessService::singleton()->remove($role, $uri);
		echo json_encode(array('uri' => $uri));
	}

	public function addModuleAccess() {
		$role = $this->getRequestParameter('role');
		$uri = $this->getRequestParameter('uri');
		tao_models_classes_funcACL_ModuleAccessService::singleton()->add($role, $uri);
		echo json_encode(array('uri' => $uri));
	}

	public function removeActionAccess() {
		$role = $this->getRequestParameter('role');
		$uri = $this->getRequestParameter('uri');
		//TODO if acces is given by Module, transform byActions before
		tao_models_classes_funcACL_ActionAccessService::singleton()->remove($role, $uri);
		echo json_encode(array('uri' => $uri));
	}

	public function addActionAccess() {
		$role = $this->getRequestParameter('role');
		$uri = $this->getRequestParameter('uri');
		tao_models_classes_funcACL_ActionAccessService::singleton()->add($role, $uri);
		echo json_encode(array('uri' => $uri));
	}

	public function moduleToActionAccess() {
		$role = $this->getRequestParameter('role');
		$uri = $this->getRequestParameter('uri');
		tao_models_classes_funcACL_ActionAccessService::singleton()->moduleToActionAccess($role, $uri);
		echo json_encode(array('uri' => $uri));
	}

	public function moduleToActionsAccess() {
		$role = $this->getRequestParameter('role');
		$uri = $this->getRequestParameter('uri');
		tao_models_classes_funcACL_ActionAccessService::singleton()->moduleToActionsAccess($role, $uri);
		echo json_encode(array('uri' => $uri));
	}

	public function actionsToModuleAccess() {
		$role = $this->getRequestParameter('role');
		$uri = $this->getRequestParameter('uri');
		tao_models_classes_funcACL_ModuleAccessService::singleton()->actionsToModuleAccess($role, $uri);
		echo json_encode(array('uri' => $uri));
	}

	public function getRoles() {
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$useruri = tao_helpers_Uri::decode($this->getRequestParameter('useruri'));
		$roles = tao_models_classes_funcACL_RoleService::singleton()->getRoles($useruri);
		echo json_encode($roles);
	}

	public function attachRole() {
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$roleuri = tao_helpers_Uri::decode($this->getRequestParameter('roleuri'));
		$useruri = tao_helpers_Uri::decode($this->getRequestParameter('useruri'));
		tao_models_classes_funcACL_RoleService::singleton()->attachUser($useruri, $roleuri);
		echo json_encode(array('success' => true, 'id' => tao_helpers_Uri::encode($roleuri)));
	}

	public function unattachRole() {
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$roleuri = tao_helpers_Uri::decode($this->getRequestParameter('roleuri'));
		$useruri = tao_helpers_Uri::decode($this->getRequestParameter('useruri'));
		tao_models_classes_funcACL_RoleService::singleton()->unattachUser($useruri, $roleuri);
		echo json_encode(array('success' => true, 'id' => tao_helpers_Uri::encode($roleuri)));
	}

	public function addRole() {
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$name = $this->getRequestParameter('name');
		$uri = tao_models_classes_funcACL_RoleService::singleton()->add($name);
		echo json_encode(array('success' => true, 'name' => $name, 'uri' => $uri));
	}

	public function editRole() {
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$name = $this->getRequestParameter('name');
		$uri = $this->getRequestParameter('uri');
		tao_models_classes_funcACL_RoleService::singleton()->edit($uri, $name);
		echo json_encode(array('success' => true, 'name' => $name, 'uri' => $uri));
	}

	public function deleteRole() {
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$uri = $this->getRequestParameter('uri');
		tao_models_classes_funcACL_RoleService::singleton()->remove($uri);
		echo json_encode(array('success' => true, 'uri' => $uri));
	}
}
?>