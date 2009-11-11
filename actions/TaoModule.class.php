<?php
abstract class TaoModule extends CommonModule {
	
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

	abstract public function getMetaData();
	abstract public function saveComment();
}
?>