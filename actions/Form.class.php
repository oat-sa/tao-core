<?php
class Form extends Module {
	
	public function addClassProperty(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		
		$index = $this->getRequestParameter('index');
		if(!$index){
			$index = 1;
		}
		$renderMode = $this->getRequestParameter('renderMode');
		
		$myForm = tao_helpers_form_GenerisFormFactory::propertyEditor(
			new core_kernel_classes_Property(),
			tao_helpers_form_FormFactory::getForm('tmp', $renderMode),
			$index,
			$renderMode
		);
		
		$this->setData('data', $myForm->renderElements());
		$this->setView('blank.tpl');
	}
}
?>