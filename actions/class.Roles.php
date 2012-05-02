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
		$rolesc = new core_kernel_classes_Class(CLASS_ROLE_BACKOFFICE);
		$roles = array();
		foreach ($rolesc->getInstances(true) as $id => $r) {
			$label = explode('#', $id);
			$roles[] = array('id' => $id, 'label' => $label[1]);
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
		//$this->redirect(_url('index', 'Main', 'tao', array('extension' => 'users', 'message' => $message)));
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
			$rban['actions'][$anom] = array('have-access' => false, 'uri' => $this->makeEMAUri($ext, $mod, $anom));
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
		$uri = explode('#', $this->getRequestParameter('uri'));
		list($type, $ext) = explode('_', $uri[1]);
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roler = new core_kernel_classes_Class($role);
		foreach ($rba[$ext] as $modn => $mod) {
			$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS), array('pattern' => $this->makeEMAUri($ext, $modn)));
			//Delete roles for actions
			foreach ($rba[$ext][$modn]['actions'] as $actn => $roles) {
				$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS), array('pattern' => $this->makeEMAUri($ext, $modn, $actn)));
			}
		}
		tao_helpers_funcACL_funcACL::removeRolesByActions();
		echo json_encode(array('uri' => $uri));
	}

	public function addExtensionAccess() {
		$role = $this->getRequestParameter('role');
		$uri = explode('#', $this->getRequestParameter('uri'));
		list($type, $ext) = explode('_', $uri[1]);
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roler = new core_kernel_classes_Class($role);
		foreach ($rba[$ext] as $modn => $mod) {
			$roler->setPropertyValue(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS), $this->makeEMAUri($ext, $modn));
			//Delete roles for actions
			foreach ($rba[$ext][$modn]['actions'] as $actn => $roles) {
				$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS), array('pattern' => $this->makeEMAUri($ext, $modn, $actn)));
			}
		}
		tao_helpers_funcACL_funcACL::removeRolesByActions();
		echo json_encode(array('uri' => $uri));
	}

	public function removeModuleAccess() {
		$role = $this->getRequestParameter('role');
		$uri = explode('#', $this->getRequestParameter('uri'));
		list($type, $ext, $mod) = explode('_', $uri[1]);
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roler = new core_kernel_classes_Class($role);
		$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS), array('pattern' => $this->getRequestParameter('uri')));
		//Delete roles for actions
		foreach ($rba[$ext][$mod]['actions'] as $actn => $roles) {
			$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS), array('pattern' => $this->makeEMAUri($ext, $mod, $actn)));
		}
		tao_helpers_funcACL_funcACL::removeRolesByActions();
		echo json_encode(array('uri' => $uri));
	}

	public function addModuleAccess() {
		$role = $this->getRequestParameter('role');
		$uri = explode('#', $this->getRequestParameter('uri'));
		list($type, $ext, $mod) = explode('_', $uri[1]);
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roler = new core_kernel_classes_Class($role);
		$roler->setPropertyValue(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS), $this->getRequestParameter('uri'));
		//Delete roles for actions
		foreach ($rba[$ext][$mod]['actions'] as $actn => $roles) {
			$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS), array('pattern' => $this->makeEMAUri($ext, $mod, $actn)));
		}
		tao_helpers_funcACL_funcACL::removeRolesByActions();
		echo json_encode(array('uri' => $uri));
	}

	public function removeActionAccess() {
		$role = $this->getRequestParameter('role');
		$uri = explode('#', $this->getRequestParameter('uri'));
		list($type, $ext, $mod, $act) = explode('_', $uri[1]);
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roler = new core_kernel_classes_Class($role);
		$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS), array('pattern' => $this->getRequestParameter('uri')));
		tao_helpers_funcACL_funcACL::removeRolesByActions();
		echo json_encode(array('uri' => $uri));
	}

	public function addActionAccess() {
		$role = $this->getRequestParameter('role');
		$uri = explode('#', $this->getRequestParameter('uri'));
		list($type, $ext, $mod, $act) = explode('_', $uri[1]);
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roler = new core_kernel_classes_Class($role);
		$roler->setPropertyValue(new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS), $this->getRequestParameter('uri'));
		tao_helpers_funcACL_funcACL::removeRolesByActions();
		echo json_encode(array('uri' => $uri));
	}

	public function moduleToActionAccess() {
		$role = $this->getRequestParameter('role');
		$uri = explode('#', $this->getRequestParameter('uri'));
		list($type, $ext, $mod, $act) = explode('_', $uri[1]);
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roler = new core_kernel_classes_Class($role);
		$propa = new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS);
		$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS), array('pattern' => $this->makeEMAUri($ext, $mod)));
		foreach ($rba[$ext][$mod]['actions'] as $actn => $roles) {
			if ($act != $actn) $roler->setPropertyValue($propa, $this->makeEMAUri($ext, $mod, $actn));
		}
		tao_helpers_funcACL_funcACL::removeRolesByActions();
		echo json_encode(array('uri' => $uri));
	}

	private function makeEMAUri($ext, $mod = null, $act = null) {
		$uri = 'http://www.tao.lu/Ontologies/taoFuncACL.rdf#';
		if (!is_null($act)) $type = 'a';
		else if (!is_null($mod)) $type = 'm';
		else $type = 'e';
		$uri .= $type.'_'.$ext;
		if (!is_null($mod)) $uri .= '_'.$mod;
		if (!is_null($act)) $uri .= '_'.$act;
		return $uri;
	}

	public function moduleToActionsAccess() {
		$role = $this->getRequestParameter('role');
		$uri = explode('#', $this->getRequestParameter('uri'));
		list($type, $ext, $mod) = explode('_', $uri[1]);
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roler = new core_kernel_classes_Class($role);
		$propa = new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS);
		$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS), array('pattern' => $this->makeEMAUri($ext, $mod)));
		foreach ($rba[$ext][$mod]['actions'] as $actn => $roles) {
			$roler->setPropertyValue($propa, $this->makeEMAUri($ext, $mod, $actn));
		}
		tao_helpers_funcACL_funcACL::removeRolesByActions();
		echo json_encode(array('uri' => $uri));
	}

	public function actionsToModuleAccess() {
		$role = $this->getRequestParameter('role');
		$uri = explode('#', $this->getRequestParameter('uri'));
		list($type, $ext, $mod) = explode('_', $uri[1]);
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roler = new core_kernel_classes_Class($role);
		$propa = new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS);
		$roler->setPropertyValue(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS), $this->makeEMAUri($ext, $mod));
		foreach ($rba[$ext][$mod]['actions'] as $actn => $roles) {
			$roler->removePropertyValues($propa, array('pattern' => $this->makeEMAUri($ext, $mod, $actn)));
		}
		tao_helpers_funcACL_funcACL::removeRolesByActions();
		echo json_encode(array('uri' => $uri));
	}

	public function getRoles() {
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		//Get the Roles of the current User (duplicate src : class.funcACL.php)
		$s = core_kernel_classes_Session::singleton();
		$userClass = new core_kernel_classes_Class(CLASS_GENERIS_USER);
		$search = $userClass->searchInstances(array(PROPERTY_USER_LOGIN => $s->getUser()), array('recursive' => true));
		$userRes = new core_kernel_classes_Resource(key($search));

		$rolesc = new core_kernel_classes_Class(CLASS_ROLE_BACKOFFICE);
		$roles = array();
		foreach ($rolesc->getInstances(true) as $id => $r) {
			$label = explode('#', $id);
			$nrole = array('id' => $id, 'label' => $label[1], 'selected' => false);
			//Selected
			foreach ($userRes->getTypes() as $uri => $t) {
				if ($uri == $id) $nrole['selected'] = true;
			}
			$roles[] = $nrole;
		}
		echo json_encode($roles);
	}

	public function setRoles() {
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		echo json_encode("");
	}

	public function attachRole() {
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$roleuri = $this->getRequestParameter('roleuri');
		$useruri = tao_helpers_Uri::decode($this->getRequestParameter('useruri'));
		$userRes = new core_kernel_classes_Resource($useruri);
		$userRes->setPropertyValue(new core_kernel_classes_Property(RDF_TYPE), $roleuri);
		echo json_encode(array('success' => true));
	}

	public function unattachRole() {
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$roleuri = $this->getRequestParameter('roleuri');
		$useruri = tao_helpers_Uri::decode($this->getRequestParameter('useruri'));
		$userRes = new core_kernel_classes_Resource($useruri);
		$userRes->removePropertyValues(new core_kernel_classes_Property(RDF_TYPE), array('pattern' => $roleuri));
		echo json_encode(array('success' => true));
	}
}
?>