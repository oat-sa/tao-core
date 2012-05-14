<?php
require_once dirname(__FILE__) . '/TestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

class FuncACLTestCase extends UnitTestCase {
	public function testFuncACL() {
		//Create role
		$suUri = "http://localhost/mytao.rdf#superUser";
		$tmroleUri = "http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole";
		$name = 'testcase';
		$roleUri = tao_models_classes_funcACL_RoleService::singleton()->add($name);
		$testRole = new core_kernel_classes_Resource($roleUri);
		$this->assertEqual($name, $testRole->getLabel());
		//Login
		$suRes = new core_kernel_classes_Resource($suUri);
		$login = $suRes->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_LOGIN))->__toString();
		$pw = $suRes->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_PASSWORD))->__toString();
		$srv = tao_models_classes_UserService::singleton();
		$srv->loginUser($login, $pw);
		//Attach role to superuser
		tao_models_classes_funcACL_RoleService::singleton()->attachUser($suUri, $roleUri);
		//Has the role added ?
		$roles = tao_models_classes_funcACL_RoleService::singleton()->getRoles();
		foreach ($roles as $r) {
			if ($r['label'] == $name) $this->assertTrue($r['selected']);
		}
		//Remove taoManager role
		tao_models_classes_funcACL_RoleService::singleton()->unattachUser($suUri, $tmroleUri);
		//Check if removed
		$tmRole = new core_kernel_classes_Resource($tmroleUri);
		$roles = tao_models_classes_funcACL_RoleService::singleton()->getRoles();
		foreach ($roles as $r) {
			if ($r['label'] == $tmRole->getLabel()) $this->assertFalse($r['selected']);
		}
		//Test uri creation
		$emauri = "http://www.tao.lu/Ontologies/taoFuncACL.rdf#a_taoTests_Tests_editTest";
		$makeemauri = tao_models_classes_funcACL_AccessService::singleton()->makeEMAUri('taoTests', 'Tests', 'editTest');
		$makeemaurimod = tao_models_classes_funcACL_AccessService::singleton()->makeEMAUri('taoTests', 'Tests');
		$this->assertEqual($emauri, $makeemauri);
		//Test access without access
		$this->assertFalse(tao_helpers_funcACL_funcACL::hasAccess('taoTests', 'Tests', 'editTest'));
		//Add access for an actions
		tao_models_classes_funcACL_ActionAccessService::singleton()->add($roleUri, $makeemauri);
		//Test the action with the access
		$this->assertTrue(tao_helpers_funcACL_funcACL::hasAccess('taoTests', 'Tests', 'editTest'));
		//Remove access for the actions
		tao_models_classes_funcACL_ActionAccessService::singleton()->remove($roleUri, $makeemauri);
		//Test access without access
		$this->assertFalse(tao_helpers_funcACL_funcACL::hasAccess('taoTests', 'Tests', 'editTest'));
		//Test access via module
		tao_models_classes_funcACL_ModuleAccessService::singleton()->add($roleUri, $makeemaurimod);
		$this->assertTrue(tao_helpers_funcACL_funcACL::hasAccess('taoTests', 'Tests', 'editTest'));
		//Remove access via module
		tao_models_classes_funcACL_ModuleAccessService::singleton()->remove($roleUri, $makeemaurimod);
		$this->assertFalse(tao_helpers_funcACL_funcACL::hasAccess('taoTests', 'Tests', 'editTest'));

		//Rattach taoManager role to user
		tao_models_classes_funcACL_RoleService::singleton()->attachUser($suUri, $tmroleUri);
		//Unattach role to user
		tao_models_classes_funcACL_RoleService::singleton()->unattachUser($suUri, $roleUri);
		//Remove role
		tao_models_classes_funcACL_RoleService::singleton()->remove($roleUri);
	}
}
?>
