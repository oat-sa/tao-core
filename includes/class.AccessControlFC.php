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
		$context = Context::getInstance();

		if (!tao_helpers_funcACL_funcACL::hasAccess($context->getExtensionName(), $context->getModuleName(), $context->getActionName())) {
    		$context->setActionName('noAccess');
		}

		parent::loadModule();
	}
}
?>