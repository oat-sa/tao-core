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
		foreach ($rolesc->getInstances(true) as $id => $r) {
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
		$role = $this->getRequestParameter('role');
		$uri = explode('#', $this->getRequestParameter('module'));
		$uri = explode('_', $uri[1]);
		$ext = $uri[1];
		$mod = $uri[2];
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$rban = array('actions' => array(), 'bymodule' => false);
		$access = 0;
		$allaccess = false;
		if (in_array($role, $rba[$ext][$mod]['roles'])) $rban['bymodule'] = true;
		foreach ($rba[$ext][$mod]['actions'] as $anom => $r) {
			$rban['actions'][$anom] = array('have-access' => false, 'uri' => tao_models_classes_funcACL_AccessService::singleton()->makeEMAUri($ext, $mod, $anom));
			if (in_array($role, $r)) $rban['actions'][$anom]['have-access'] = true;
		}
		echo json_encode($rban);
	}

	public function getAttachedModuleRoles() {
		$uri = explode('#', $this->getRequestParameter('module'));
		$uri = explode('_', $uri[1]);
		$ext = $uri[1];
		$mod = $uri[2];
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roles = array();
		foreach ($rba[$ext][$mod]['roles'] as $uri) {
			$label = explode('#', $uri);
			$roles[] = array('uri' => $uri, 'label' => $label[1]);
		}
		echo json_encode(array('roles' => $roles));
	}

	public function getAttachedActionRoles() {
		$uri = explode('#', $this->getRequestParameter('action'));
		$uri = explode('_', $uri[1]);
		$ext = $uri[1];
		$mod = $uri[2];
		$act = $uri[3];
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roles = array();
		foreach ($rba[$ext][$mod]['actions'][$act] as $uri) {
			$label = explode('#', $uri);
			$roles[] = array('uri' => $uri, 'label' => $label[1]);
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
		$roles = tao_models_classes_funcACL_RoleService::singleton()->getRoles();
		echo json_encode($roles);
	}

	public function attachRole() {
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$roleuri = $this->getRequestParameter('roleuri');
		$useruri = tao_helpers_Uri::decode($this->getRequestParameter('useruri'));
		tao_models_classes_funcACL_RoleService::singleton()->attachUser($useruri, $roleuri);
		echo json_encode(array('success' => true));
	}

	public function unattachRole() {
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$roleuri = $this->getRequestParameter('roleuri');
		$useruri = tao_helpers_Uri::decode($this->getRequestParameter('useruri'));
		tao_models_classes_funcACL_RoleService::singleton()->unattachUser($useruri, $roleuri);
		echo json_encode(array('success' => true));
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