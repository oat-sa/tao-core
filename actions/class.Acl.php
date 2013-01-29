<?php
/**
 * This controller provide the actions to manage the ACLs
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage actions
 *
 */
class tao_actions_Acl extends tao_actions_CommonModule {

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
		$rolesc = new core_kernel_classes_Class(CLASS_ROLE);
		$roles = array();
		foreach ($rolesc->getInstances(true) as $id => $r) {
			$roles[] = array('id' => $id, 'label' => $r->getLabel());
		}

		$this->setData('roles', $roles);
		$this->setView('roles/list.tpl');
	}

	public function getModules() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
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
					$moduleActions = tao_helpers_funcACL_ActionModel::getActions(new core_kernel_classes_Resource($rban[$enom]['modules'][$mnom]['uri']));
					if ($aaccess > 0 && $aaccess == count($moduleActions)) {
						$rban[$enom]['modules'][$mnom]['have-allaccess'] = true;
					}
				}
				//By modules, not by the extension directly
				if ($access > 0) $rban[$enom]['have-access'] = true;
			}
			echo json_encode($rban);
		}
	}

	public function getActions() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
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
	}

	public function getAttachedModuleRoles() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$module = new core_kernel_classes_Resource($this->getRequestParameter('module'));
			$roles = array();
			foreach (tao_helpers_funcACL_funcACL::getRolesByModule($module) as $role) {
				$roles[] = array('uri' => $role->getUri(), 'label' => $role->getLabel());
			}
			echo json_encode(array('roles' => $roles));	
		}
	}

	public function getAttachedActionRoles() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$action = new core_kernel_classes_Resource($this->getRequestParameter('action'));
			$roles = array();
			foreach (tao_helpers_funcACL_funcACL::getRolesByAction($action) as $role) {
				$roles[] = array('uri' => $role->getUri(), 'label' => $role->getLabel());
			}
			echo json_encode(array('roles' => $roles));	
		}
	}

	public function removeExtensionAccess() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$role = $this->getRequestParameter('role');
			$uri = $this->getRequestParameter('uri');
			$extensionService = tao_models_classes_funcACL_ExtensionAccessService::singleton();
			$extensionService->remove($role, $uri);
			echo json_encode(array('uri' => $uri));	
		}
	}

	public function addExtensionAccess() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$role = $this->getRequestParameter('role');
			$uri = $this->getRequestParameter('uri');
			$extensionService = tao_models_classes_funcACL_ExtensionAccessService::singleton();
			$extensionService->add($role, $uri);
			echo json_encode(array('uri' => $uri));
		}
	}

	public function removeModuleAccess() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$role = $this->getRequestParameter('role');
			$uri = $this->getRequestParameter('uri');
			$moduleService = tao_models_classes_funcACL_ModuleAccessService::singleton();
			$moduleService->remove($role, $uri);
			echo json_encode(array('uri' => $uri));	
		}
	}

	public function addModuleAccess() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$role = $this->getRequestParameter('role');
			$uri = $this->getRequestParameter('uri');
			$moduleService = tao_models_classes_funcACL_ModuleAccessService::singleton();
			$moduleService->add($role, $uri);
			echo json_encode(array('uri' => $uri));	
		}
	}

	public function removeActionAccess() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$role = $this->getRequestParameter('role');
			$uri = $this->getRequestParameter('uri');
			$actionService = tao_models_classes_funcACL_ActionAccessService::singleton();
			$actionService->remove($role, $uri);
			echo json_encode(array('uri' => $uri));	
		}
	}

	public function addActionAccess() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$role = $this->getRequestParameter('role');
			$uri = $this->getRequestParameter('uri');
			$actionService = tao_models_classes_funcACL_ActionAccessService::singleton();
			$actionService->add($role, $uri);
			echo json_encode(array('uri' => $uri));	
		}
	}

	public function moduleToActionAccess() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$role = $this->getRequestParameter('role');
			$uri = $this->getRequestParameter('uri');
			$actionService = tao_models_classes_funcACL_ActionAccessService::singleton();
			$actionService->moduleToActionAccess($role, $uri);
			echo json_encode(array('uri' => $uri));	
		}
	}

	public function moduleToActionsAccess() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$role = $this->getRequestParameter('role');
			$uri = $this->getRequestParameter('uri');
			$actionService = tao_models_classes_funcACL_ActionAccessService::singleton();
			$actionService->moduleToActionsAccess($role, $uri);
			echo json_encode(array('uri' => $uri));	
		}
	}

	public function actionsToModuleAccess() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$role = $this->getRequestParameter('role');
			$uri = $this->getRequestParameter('uri');
			$moduleService = tao_models_classes_funcACL_ModuleAccessService::singleton();
			$moduleService->actionsToModuleAccess($role, $uri);
			echo json_encode(array('uri' => $uri));	
		}
	}

	public function getRoles() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$useruri = tao_helpers_Uri::decode($this->getRequestParameter('useruri'));
			$roleService = tao_models_classes_funcACL_RoleService::singleton();
			$roles = $roleService->getRoles($useruri);
			echo json_encode($roles);
		}
	}

	public function attachRole() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$roleuri = tao_helpers_Uri::decode($this->getRequestParameter('roleuri'));
			$useruri = tao_helpers_Uri::decode($this->getRequestParameter('useruri'));
			$roleService = tao_models_classes_funcACL_RoleService::singleton();
			$roleService->attachUser($useruri, $roleuri);
			echo json_encode(array('success' => true, 'id' => tao_helpers_Uri::encode($roleuri)));
		}
	}

	public function unattachRole() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$roleuri = tao_helpers_Uri::decode($this->getRequestParameter('roleuri'));
			$useruri = tao_helpers_Uri::decode($this->getRequestParameter('useruri'));
			$roleService = tao_models_classes_funcACL_RoleService::singleton();
			$roleService->unattachUser($useruri, $roleuri);
			echo json_encode(array('success' => true, 'id' => tao_helpers_Uri::encode($roleuri)));	
		}
	}

	public function addRole() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$name = $this->getRequestParameter('name');
			$roleService = tao_models_classes_funcACL_RoleService::singleton();
			$uri = $roleService->add($name);
			echo json_encode(array('success' => true, 'name' => $name, 'uri' => $uri));
		}
	}

	public function editRole() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$name = $this->getRequestParameter('name');
			$uri = $this->getRequestParameter('uri');
			$roleService = tao_models_classes_funcACL_RoleService::singleton();
			$roleService->edit($uri, $name);
			echo json_encode(array('success' => true, 'name' => $name, 'uri' => $uri));
		}
	}

	public function deleteRole() {
		if (!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		else{
			$uri = $this->getRequestParameter('uri');
			$roleService = tao_models_classes_funcACL_RoleService::singleton();
			$roleService->remove($uri);
			echo json_encode(array('success' => true, 'uri' => $uri));
		}
	}
}
?>