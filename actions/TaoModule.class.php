<?php
/**
 * The TaoModule is an abstract controller, 
 * the tao children extensions Modules should extends the TaoModule to beneficiate the shared methods.
 * It regroups the methods that can be applied on any extension (the rdf:Class managment for example)
 */
abstract class TaoModule extends CommonModule {

	public function __construct(){
		
		$errorMessage = __('Access denied. Please renew your authentication!');
		
		if(!$this->_isAllowed()){
			if(tao_helpers_Request::isAjax()){
				header("HTTP/1.0 403 Forbidden");
				echo $errorMessage;
				return;
			}
			throw new Exception($errorMessage);
		}
	}
	
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
	 * Import module data Action
	 * @return void
	 */
	public function import(){
		
		//CSV Adapter
		$adapter = new tao_helpers_GenerisDataAdapterCsv();
		$options = $adapter->getOptions();
		
		//option form
		$myForm = tao_helpers_form_FormFactory::getForm('import', array('noSubmit' => false, 'noRevert' => true));
		$level = 1;
		
		//create import options form
		foreach($options as $optName => $optValue){
			(is_bool($optValue))  ? $eltType = 'Checkbox' : $eltType = 'Textbox';
			$optElt = tao_helpers_form_FormFactory::getElement($optName, $eltType);
			$optElt->setDescription(tao_helpers_Display::textCleaner($optName, ' '));
			$optElt->setLevel($level);
			$optElt->addAttribute("size", ($optName == 'column_order') ? 40 : 6);
			if(is_null($optValue) || $optName == 'line_break'){
				$optElt->addAttribute("disabled", "true");
			}
			$optElt->setValue($optValue);
			if($eltType == 'Checkbox'){
				$optElt->setOptions(array($optName => ''));
				$optElt->setValue($optName);
			}
			if(!preg_match("/column/", strtolower($optName))){
				$optElt->addValidator(
					tao_helpers_form_FormFactory::getValidator('NotEmpty')
				);
			}
			$myForm->addElement($optElt);
			$level++;
		}
		$myForm->createGroup('options', __('CSV Options'), array_keys($options));
		
		//create file upload form box
		$fileElt = tao_helpers_form_FormFactory::getElement('source', 'File');
		$fileElt->setLevel($level);
		$fileElt->setDescription("Add the source file");
		$fileElt->addValidators(array(
			tao_helpers_form_FormFactory::getValidator('NotEmpty'),
			tao_helpers_form_FormFactory::getValidator('FileMimeType', array('mimetype' => array('text/plain', 'text/csv', 'application/csv-tab-delimited-table'))),
			tao_helpers_form_FormFactory::getValidator('FileSize', array('max' => 2000000))
		));
		$myForm->addElement($fileElt);
		$myForm->createGroup('file', __('Upload CSV File'), array('source'));
		
		$myForm->evaluate();

		//if the form is submited and valid
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				//init import options
				$clazz = $this->getCurrentClass();
				
				$adapter->setOptions(array(
					'field_delimiter' 		=> $myForm->getValue('field_delimiter'),
					'field_encloser' 		=> $myForm->getValue('field_encloser'),
					'line_break' 			=> $myForm->getValue('line_break'),
					'multi_values_delimiter' => $myForm->getValue('multi_values_delimiter'),
					'first_row_column_names' => $myForm->getValue('first_row_column_names'),
					'column_order' 			=> $myForm->getValue('column_order')
				));
				
				//import data from the file
				$fileData = $myForm->getValue('source');
				if($adapter->import(file_get_contents($fileData['tmp_name']), $clazz)){
					unlink($fileData['tmp_name']);
					
					$this->setSessionAttribute("showNodeUri", tao_helpers_Uri::encode($clazz->uriResource));
					$this->setData('message', __('Data imported'));
					$this->setData('reload', true);
					$this->forward(get_class($this), 'index');
				}
			}
		}
		
		$this->setData('myForm', $myForm->render());
		$this->setData('formTitle', __('Import data'));
		$this->setView('importform.tpl');
	}
	
	
	/**
	 * Export the selected class instance in a flat CSV file
	 * download header sent
	 * @return void
	 */
	public function export(){
		
		if($this->hasSessionAttribute('currentExtension')){
			
			$extension = str_replace('tao', '', $this->getSessionAttribute('currentExtension'));
			$clazz = $this->getCurrentClass();
			
			if(!is_null($clazz)){
				$fileName = strtolower($extension."_".tao_helpers_Display::textCleaner($clazz->getLabel())."_".time().".csv");
				$adapter = new tao_helpers_GenerisDataAdapterCsv();
				$data = $adapter->export($clazz);
				
				if($data){
					header('Content-type: application/csv-tab-delimited-table');
					header('Content-Disposition: attachment; filename="'.$fileName.'"');
					echo $data;
					return;
				}
			}
		}
		throw new Exception("Unable to export data");
	}
	
	/**
	 * Get the lists of a module which are the first child of TAO Object
	 * Render a json response
	 * @return void
	 */
	abstract public function getLists();
	
/*
 * Shared Methods
 */
	
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
				$propertyMap = tao_helpers_form_GenerisFormFactory::getPropertyMap();
				
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
							
							$type = $properties['type'];
							$range = $properties['range'];
							unset($properties['type']);
							unset($properties['range']);
							
							if(isset($propertyMap[$type])){
								$properties['http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget'] = $propertyMap[$type]['widget'];
								if(isset($propertyMap[$type]['range']) &&  is_null($propertyMap[$type]['range'])){
									$properties['http://www.w3.org/2000/01/rdf-schema#range'] = $range;
								}
								else{
									$properties['http://www.w3.org/2000/01/rdf-schema#range'] = $propertyMap[$type]['range'];
								}
								
							}
						}
						
						$this->service->bindProperties(new core_kernel_classes_Resource(tao_helpers_Uri::decode($_POST['propertyUri'.$propNum])), $properties);
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

	/**
	 * Get the data of the flat lists which are the first level children of TAO Object
	 * @param array $exclude [optional]
	 * @return array $data the lists data
	 */
	protected function getListData($exclude = array()){ 
	
		$data = array();
		
		//generis boolean is always in the list
		array_push(
			$data, 
			$this->service->toTree(new core_kernel_classes_Class(GENERIS_BOOLEAN), false, true)
		);
	
		$taoObjectClass = new core_kernel_classes_Class(TAO_OBJECT_CLASS);
		foreach($taoObjectClass->getSubClasses(false)  as $subClass){
			if(in_array($subClass->uriResource, $exclude)){
				continue;
			}
			array_push(
				$data, 
				$this->service->toTree($subClass, false, true)
			);
		}
		return array(
			'data' 		=> __('Lists'),
			'attributes' => array('class' => 'node-root'),
			'children' 	=> $data,
			'state'		=> 'open'
		);
	}
	
	/**
	 * Create a list node (a class as the list and an instance as the list item)
	 * Render the json response with the label and uri of the created resource 
	 * @return void
	 */
	public function createList(){
		
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		
		$response = array();
		
		if($this->getRequestParameter('classUri')){
			
			$taoObjectClass = new core_kernel_classes_Class(TAO_OBJECT_CLASS);
			
			if($this->getRequestParameter('type') == 'class' && $this->getRequestParameter('classUri') == 'root'){
				
				$label = __('List ').(count($taoObjectClass->getSubClasses(false)) + 1);
				$clazz = $this->service->createSubClass($taoObjectClass, $label);
				if(!is_null($clazz)){
					$response['label']	= $clazz->getLabel();
					$response['uri'] 	= tao_helpers_Uri::encode($clazz->uriResource);
				}
			}
			if($this->getRequestParameter('type') == 'instance'){
				
				$clazz = new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
				if(!is_null($clazz)){
					if($clazz->isSubClassOf($taoObjectClass)){
						
						$label = __('List item ').(count($clazz->getInstances(false)) + 1);
						$instance = $this->service->createInstance($clazz, $label);
						if(!is_null($instance)){
							$response['label']	= $instance->getLabel();
							$response['uri'] 	= tao_helpers_Uri::encode($instance->uriResource);
						}
					}
				}
				
			}
		}
		echo json_encode($response);
	}
	
	/**
	 * Remove a list node: either a class (the list) or an instance (the list item)
	 *  Render the json response with the deletion status
	 * @return void
	 */
	public function removeList(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		
		$taoObjectClass = new core_kernel_classes_Class(TAO_OBJECT_CLASS);
		
		$deleted = false;
		if($this->getRequestParameter('uri') && $this->getRequestParameter('classUri')){
			$instance = $this->service->getOneInstanceBy(
				new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getRequestParameter('classUri'))),
				tao_helpers_Uri::decode($this->getRequestParameter('uri')),
				'uri'
			);
			if(!is_null($instance)){
				$deleted = $instance->delete();
			}
		}
		if($this->getRequestParameter('classUri')){
			$clazz = new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
			if(!is_null($clazz)){
				if($clazz->setSubClassOf($taoObjectClass)){
					$deleted = $clazz->delete();
				}
			}
		}
		
		echo json_encode(array('deleted' => $deleted));
	}
	
	/**
	 * Rename a list node: change the label of a resource
	 * Render the json response with the renamed status
	 * @return void
	 */
	public function renameList(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		
		$data = array('renamed'	=> false);
		
		$resource = null;
		if($this->getRequestParameter('uri')){
			$resource = new core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
		}
		if(!is_null($resource)){
			$data['oldName'] = (string)$resource->getUniquePropertyValue(new core_kernel_classes_Property(RDFS_LABEL));
		}
		if($this->getRequestParameter('newName')){
			$resource = $this->service->bindProperties($resource, array(RDFS_LABEL => $this->getRequestParameter('newName')));
			if($resource->getUniquePropertyValue(new core_kernel_classes_Property(RDFS_LABEL)).'' == $this->getRequestParameter('newName') && $this->getRequestParameter('newName') != ''){
				$data['renamed'] = true;
			}
		}
		echo json_encode($data);
	}
	
}
?>