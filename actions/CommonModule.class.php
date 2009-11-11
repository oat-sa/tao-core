<?php
abstract class CommonModule extends Module {

	protected $service = null;
	
	protected function defaultData(){
		$context = Context::getInstance();
		if($this->hasSessionAttribute('currentExtension')){
			$this->setData('extension', $this->getSessionAttribute('currentExtension'));
			$this->setData('module',  $context->getModuleName());
			$this->setData('action',  $context->getActionName());
			
			if($this->getRequestParameter('showNodeUri')){
				$this->setSessionAttribute("showNodeUri", $this->getRequestParameter('showNodeUri'));
			}
			if($this->getRequestParameter('uri') || $this->getRequestParameter('classUri')){
				if($this->getRequestParameter('uri')){
					$this->setSessionAttribute('uri', $this->getRequestParameter('uri'));
				}
				else{
					unset($_SESSION[SESSION_NAMESPACE]['uri']);
				}
				if($this->getRequestParameter('classUri')){
					$this->setSessionAttribute('classUri', $this->getRequestParameter('classUri'));
				}
				else{
					unset($_SESSION[SESSION_NAMESPACE]['classUri']);
				}
			}
		}
		else{
			unset($_SESSION[SESSION_NAMESPACE]['uri']);
			unset($_SESSION[SESSION_NAMESPACE]['classUri']);
		}
		
	}
}
?>