<?php
/**
 * AccessControlFC class
 * TODO AccessControlFC class documentation.
 *
 * @author Jehan Bihin
 */
class AccessControlFC extends AdvancedFC
{
	public function loadModule()
	{
		$resolver = new Resolver();
		$action	= $resolver->getAction();
		$module	= $resolver->getModule();

		$context = Context::getInstance();
		$context->setModuleName($module);
		$context->setActionName($action);

		if (!tao_helpers_funcACL_funcACL::hasAccess(common_ext_ExtensionsManager::singleton()->getCurrentExtensionName(), $module, $action)) {
    	$context->setActionName('noAccess');
		}

		parent::loadModule();
	}
}
?>