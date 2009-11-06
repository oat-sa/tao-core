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
	
	/**
	 * get the current item class regarding the classUri' request parameter
	 * @return core_kernel_classes_Class the item class
	 */
	protected function getCurrentClass(){
		$classUri = tao_helpers_Uri::decode($this->getRequestParameter('classUri'));
		if(is_null($classUri) || empty($classUri)){
			throw new Exception("No valid class uri found");
		}
		
		return  new core_kernel_classes_Class($classUri);
	}

	public function addClassProperty(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		
		$index = $this->getRequestParameter('index');
		if(!$index){
			$index = 1;
		}
		
		$class = $this->getCurrentClass();
		
		$myForm = tao_helpers_form_GenerisFormFactory::propertyEditor(
			$class->createProperty(),
			tao_helpers_form_FormFactory::getForm('property_'.$index),
			$index
		);
		
		$this->setData('data', $myForm->renderElements());
		$this->setView('blank.tpl');
	}	
	
	public function getInstances(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		
		$clazz = $this->getCurrentClass();
		
		$instances = array();
		if(!is_null($clazz)){
			foreach($clazz->getInstances() as $instance){
				$instances[tao_helpers_Uri::encode($instance->uriResource)] = $instance->getLabel();
			}
		}
		echo json_encode($instances);
	}
}
?>