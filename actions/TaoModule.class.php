<?php
/**
 * The TaoModule is an abstract controller, 
 * the tao children extensions Modules should extends the TaoModule to beneficiate the shared methods.
 * It regroups the methods that can be applied on any extension (the rdf:Class managment for example)
 */
abstract class TaoModule extends CommonModule {


	
	/**
	 * All tao module must have a method to get the meta data of the selected resource
	 * Display the metadata. 
	 * @return void
	 */
	abstract public function getMetaData();
	
	/**
	 * All tao module must have a method to save the comment field of the selected resource
	 * @return json response {saved: true, comment: text of the comment to refresh it}
	 */
	abstract public function saveComment();
	
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

	/**
	 * Edit a class using the GenerisFormFactory::classEditor
	 * Manage the form submit by saving the class
	 * @param core_kernel_classes_Class    $clazz
	 * @param core_kernel_classes_Resource $resource
	 * @return tao_helpers_form_Form the generated form
	 */
	protected function editClass(core_kernel_classes_Class $clazz, core_kernel_classes_Resource $resource){
		$myForm = tao_helpers_form_GenerisFormFactory::classEditor($clazz, $resource);
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				$classValues = array();
				$propertyValues = array();
				foreach($myForm->getValues() as $key => $value){
					if(preg_match("/^class_/", $key)){
						$classKey =  tao_helpers_Uri::decode(str_replace('class_', '', $key));
						$classValues[$classKey] =  tao_helpers_Uri::decode($value);
					}
					if(preg_match("/^property_/", $key)){
						if(isset($_POST[$key])){
							$key = str_replace('property_', '', $key);
							$propNum = substr($key, 0, 1 );
							$propKey = tao_helpers_Uri::decode(str_replace($propNum.'_', '', $key));
							$propertyValues[$propNum][$propKey] = tao_helpers_Uri::decode($value);
						}
						else{
							$key = str_replace('property_', '', $key);
							$propNum = substr($key, 0, 1 );
							if(!isset($propertyValues[$propNum])){
								$propertyValues[$propNum] = array();
							}
						}
					}
				}
				
				$clazz = $this->service->bindProperties($clazz, $classValues);
				
				foreach($propertyValues as $propNum => $properties){
					if(isset($_POST['propertyUri'.$propNum]) && count($properties) == 0){
						
						//delete property mode
						foreach($clazz->getProperties() as $classProperty){
							if($classProperty->uriResource == tao_helpers_Uri::decode($_POST['propertyUri'.$propNum])){
								$classProperty->delete();
								break;
							}
						}
					}
					else{
						if($_POST["propertyMode{$propNum}"] == 'simple'){
							echo "<br>$propNum<br>";
							var_dump($properties);
							echo "<br><br>";
						}
						else{
							$this->service->bindProperties(new core_kernel_classes_Resource(tao_helpers_Uri::decode($_POST['propertyUri'.$propNum])), $properties);
						}
					}
				}
			}
		}
		return $myForm;
	}
	
/*
 * Actions
 */

	/**
	 * Render the add property sub form.
	 * @return void
	 */
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
			$index,
			true
		);
		
		$this->setData('data', $myForm->renderElements());
		$this->setView('blank.tpl');
	}	
	
	/**
	 * Get the instances of a class
	 * Render a json response formated as an array with resource uri/label as key/value pair
	 * @return void
	 */
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