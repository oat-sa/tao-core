<?php
require_once dirname(__FILE__) . '/TaoTestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

class FuncACLTestCase extends UnitTestCase {
	
	public function testFuncACL() {
		$userService = core_kernel_users_Service::singleton();
		$roleService = tao_models_classes_funcACL_RoleService::singleton();
		$baseRole = new core_kernel_classes_Resource(INSTANCE_ROLE_BACKOFFICE);
		$testRole = $userService->addRole('testrole', $baseRole);
		$user = $userService->addUser('testcase', md5('testcase'));
		
		$srv = tao_models_classes_UserService::singleton();
		$this->assertTrue($userService->login('testcase', md5('testcase'), new core_kernel_classes_Resource(INSTANCE_ROLE_GENERIS)));

		// -- Test uri creation
		$emauri = FUNCACL_NS . '#a_tao_Users_add';
		$emaurimod = FUNCACL_NS . '#m_tao_Users';
		$makeemauri = tao_models_classes_funcACL_AccessService::singleton()->makeEMAUri('tao', 'Users', 'add');
		$makeemaurimod = tao_models_classes_funcACL_AccessService::singleton()->makeEMAUri('tao', 'Users');
		$this->assertEqual($emauri, $makeemauri);
		$this->assertEqual($emaurimod, $makeemaurimod);
		
		// -- Try to access a restricted action
		$this->assertFalse(tao_helpers_funcACL_funcACL::hasAccess('tao', 'Users', 'add'));
		
		// -- Try to access a unrestricted action
		// (BACKOFFICE has access to the backend login action because it includes the TAO Role)
		$this->assertTrue(tao_helpers_funcACL_funcACL::hasAccess('tao', 'Main', 'login'));
		
		// -- Try to access an action that does not exist.
		$this->assertFalse(tao_helpers_funcACL_funcACL::hasAccess('tao', 'Unknown', 'action'));
		
		// -- Try to access a unrestricted action
		// Add access for this action to the Manager role.
		tao_models_classes_funcACL_ActionAccessService::singleton()->add($testRole->getUri(), $makeemauri);
		
		// Add the Manager role the the currently tested user
		$roleService->attachUser($user->getUri(), $testRole->getUri());
		
		// Logoff/login, to refresh roles cache
		$this->assertTrue($srv->loginUser('testcase', md5('testcase')));
		
		// Ask for access
		$this->assertTrue(tao_helpers_funcACL_funcACL::hasAccess('tao', 'Users', 'add'));

		// Remove the access to this action from the Manager role
		tao_models_classes_funcACL_ActionAccessService::singleton()->remove($testRole->getUri(), $makeemauri);
		
		// We should not have access anymore to this action with the Manager role
		$this->assertFalse(tao_helpers_funcACL_funcACL::hasAccess('tao', 'Users', 'add'));
		
		// -- Give access to the entire module and try to access the previously tested action
		tao_models_classes_funcACL_ModuleAccessService::singleton()->add($testRole->getUri(), $makeemaurimod);
		$this->assertTrue(tao_helpers_funcACL_funcACL::hasAccess('tao', 'Users', 'add'));
		
		// -- Remove the entire module access and try again
		tao_models_classes_funcACL_ModuleAccessService::singleton()->remove($testRole->getUri(), $makeemaurimod);
		$this->assertFalse(tao_helpers_funcACL_funcACL::hasAccess('tao', 'Users', 'add'));
		
		// reset
		tao_models_classes_funcACL_ModuleAccessService::singleton()->add($testRole->getUri(), $makeemaurimod);
		
		// Unattach role from user
		tao_models_classes_funcACL_RoleService::singleton()->unattachUser($user->getUri(), $testRole->getUri());
		
		
		$userService->removeUser($user);
		$userService->removeRole($testRole);
	}
	
	public function testACLCache(){
		$moduleUri = tao_models_classes_funcACL_AccessService::singleton()->makeEMAUri('tao', 'Users');
		$module = new core_kernel_classes_Resource($moduleUri);
		$this->assertTrue($module->exists());
		
		tao_helpers_funcACL_Cache::cacheModule($module);
		$moduleCache = tao_helpers_funcACL_Cache::retrieveModule($module);
	}
}
?>
