<?php
/**
 * The TaoModule is an abstract controller, 
 * the tao children extensions Modules should extends the TaoModule to beneficiate the shared methods.
 * It regroups the methods that can be applied on any extension (the rdf:Class managment for example)
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 */
abstract class TaoModule extends CommonModule {

	/**
	 * Check the authentication
	 */
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
	 * @todo 
     * @see Module::setView()
     * @param string $identifier view identifier
     * @param boolean set to true if you want to use the views in the tao extension instead of the current extension 
     */
    public function setView($identifier, $useMetaExtensionView = false) {
        parent::setView($identifier);
		if($useMetaExtensionView){
			Renderer::setViewsBasePath(TAOVIEW_PATH);
		}
	}
	
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
	 *  ! Please override me !
	 * get the current instance regarding the uri and classUri in parameter
	 * @return core_kernel_classes_Resource
	 */
	protected function getCurrentInstance(){
		$uri = tao_helpers_Uri::decode($this->getRequestParameter('uri'));
		if(is_null($uri) || empty($uri)){
			throw new Exception("No valid uri found");
		}
		
		$clazz = $this->getCurrentClass();
		$instance = $this->service->getOneInstanceBy( $clazz, $uri, 'uri');
		if(is_null($instance)){
			throw new Exception("No instance found for the uri {$uri}");
		}
		
		return $instance;
	}

	/**
	 * get the main class
	 * @return core_kernel_classes_Classes
	 */
	protected abstract function getRootClass();

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
				
				//in case of deletion of just added properties
				foreach($_POST as $key => $value){
					if(preg_match("/^propertyUri/", $key)){
						$propNum = str_replace('propertyUri', '', $key);
						if(!isset($propertyValues[$propNum])){
							$propertyValues[$propNum] = array();
						}
					}
				}
				
				//create a table of property models
				foreach($myForm->getValues() as $key => $value){
					if(preg_match("/^class_/", $key)){
						$classKey =  tao_helpers_Uri::decode(str_replace('class_', '', $key));
						$classValues[$classKey] =  tao_helpers_Uri::decode($value);
					}
					if(preg_match("/^property_/", $key)){
						if(isset($_POST[$key])){
							$pkey = str_replace('property_', '', $key);
							$propNum = substr($pkey, 0, strpos($pkey, '_') );
							$propKey = tao_helpers_Uri::decode(str_replace($propNum.'_', '', $pkey));
							$propertyValues[$propNum][$propKey] = tao_helpers_Uri::decode($value);
						}
						else{
							$pkey = str_replace('property_', '', $key);
							$propNum = substr($pkey, 0, strpos($pkey, '_') );
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
								if($classProperty->delete()){
									$myForm->removeGroup("property_".$propNum);
									break;
								}
							}
						}
					}
					else{
						$simpleMode = false;
						if($_POST["propertyMode{$propNum}"] == 'simple'){
							$simpleMode = true;
						}
						if($simpleMode){
							$type = $properties['type'];
							$range = $properties['range'];
							unset($properties['type']);
							unset($properties['range']);
							
							if(isset($propertyMap[$type])){
								$properties['http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget'] = $propertyMap[$type]['widget'];
								if(is_null($propertyMap[$type]['range'])){
									$properties['http://www.w3.org/2000/01/rdf-schema#range'] = $range;
								}
								else{
									$properties['http://www.w3.org/2000/01/rdf-schema#range'] = $propertyMap[$type]['range'];
								}
							}
						}
						$property = new core_kernel_classes_Property(tao_helpers_Uri::decode($_POST['propertyUri'.$propNum]));
						$this->service->bindProperties($property, $properties);
						
						$myForm->removeGroup("property_".$propNum);
						tao_helpers_form_GenerisFormFactory::propertyEditor($property, $myForm, $propNum, $simpleMode );
					}
					//reload form
					//$myForm = tao_helpers_form_GenerisFormFactory::classEditor($clazz, $resource);
				}
			}
		}
		return $myForm;
	}
	
	
	
/*
 * Actions
 */
 
	
	/**
	 * Main action
	 * @return void
	 */
	public function index(){
		
		if($this->getData('reload') == true){
			unset($_SESSION[SESSION_NAMESPACE]['uri']);
			unset($_SESSION[SESSION_NAMESPACE]['classUri']);
		}
		$this->setView('index.tpl', false);
	}
	
	
	/**
	 * Render json data from the current ontology root class
	 * @return void
	 */
	public function getOntologyData(){
		
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$filter = '';
		if($this->hasRequestParameter('filter')){
			$filter = $this->getRequestParameter('filter');
		}
		$highlightUri = '';
		if($this->hasSessionAttribute("showNodeUri")){
			$highlightUri = $this->getSessionAttribute("showNodeUri");
			unset($_SESSION[SESSION_NAMESPACE]["showNodeUri"]);
		}
		$instances = true;
		if($this->hasRequestParameter('type')){
			$type = $this->getRequestParameter('type');
			if(preg_match("/^tmp\-moving\-tree$/", $type)){
				$instances = false;
			}
		}
		echo json_encode( $this->service->toTree( $this->getRootClass(), true, $instances, $highlightUri, $filter));
	}
	
	/**
	 * Duplicate the current instance
	 * render a JSON response
	 * @return void
	 */
	public function cloneInstance(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		
		$clone = $this->service->cloneInstance($this->getCurrentInstance(), $this->getCurrentClass());
		if(!is_null($clone)){
			echo json_encode(array(
				'label'	=> $clone->getLabel(),
				'uri' 	=> tao_helpers_Uri::encode($clone->uriResource)
			));
		}
	}
	
	/**
	 * Add an instance of the selected class
	 * @return void
	 */
	public function addInstance(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$clazz = $this->getCurrentClass();
		$instance = $this->service->createInstance($clazz);
		if(!is_null($instance) && $instance instanceof core_kernel_classes_Resource){
			echo json_encode(array(
				'label'	=> $instance->getLabel(),
				'uri' 	=> tao_helpers_Uri::encode($instance->uriResource)
			));
		}
	}
	
	public function moveInstance(){
		
		if($this->hasRequestParameter('destinationClassUri')){
			
			if(!$this->hasRequestParameter('classUri') && $this->hasRequestParameter('uri')){
				$instance = new core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
				$clazz = $this->service->getClass($instance);
			}
			else{
				$clazz = $this->getCurrentClass();
				$instance = $this->getCurrentInstance();
			}	
			
			
			$destinationUri = $this->getRequestParameter('destinationClassUri');
			if(!empty($destinationUri) && $destinationUri != $clazz->uriResource){
				$destinationClass = new core_kernel_classes_Class(tao_helpers_Uri::decode($destinationUri));
				
				if(!$this->hasRequestParameter('confirmed')){
					
					$diff = $this->service->getPropertyDiff($clazz, $destinationClass);
				
					if(count($diff) > 0){
						echo json_encode(array(
							'status'	=> 'diff',
							'data'		=> $diff
						));
						return true;
					}
				}
				
				$status = $this->service->changeClass($instance, $destinationClass);
				echo json_encode(array('status'	=> $status));
			}
		}
	}
	
	/**
	 * 
	 * @return 
	 */
	public function sasAddInstance(){
		$clazz = $this->getCurrentClass();
		$instance = $this->service->createInstance($clazz);
		if(!is_null($instance) && $instance instanceof core_kernel_classes_Resource){
			$this->redirect('sasEditInstance?uri='.tao_helpers_Uri::encode($instance->uriResource).'&classUri='.tao_helpers_Uri::encode($clazz->uriResource));
		}
	}
	
	/**
	 * 
	 * @return 
	 */
	public function sasEditInstance(){
		$clazz = $this->getCurrentClass();
		$instance = $this->getCurrentInstance();
		
		$myForm = tao_helpers_form_GenerisFormFactory::instanceEditor($clazz, $instance);
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				$instance = $this->service->bindProperties($instance, $myForm->getValues());
				
				$this->setSessionAttribute("showNodeUri", tao_helpers_Uri::encode($instance->uriResource));
				$this->setData('message', __('Resource saved'));
			}
		}
		
		$this->setData('uri', tao_helpers_Uri::encode($instance->uriResource));
		$this->setData('classUri', tao_helpers_Uri::encode($clazz->uriResource));
		$this->setData('formTitle', __('Edit'));
		$this->setData('myForm', $myForm->render());
		$this->setView('form.tpl', true);
	}
	
	/**
	 * 
	 * @return 
	 */
	public function sasDeleteInstance(){
		$clazz = $this->getCurrentClass();
		$instance = $this->getCurrentInstance();
		
		$this->setData('label', $instance->getLabel());
		
		$this->setData('uri', tao_helpers_Uri::encode($instance->uriResource));
		$this->setData('classUri', tao_helpers_Uri::encode($clazz->uriResource));
		$this->setView('delete.tpl', true);
	}
	
	/**
	 * Import module data Action
	 * @return void
	 */
	public function import(){
		
		//CSV Adapter
		$adapter = new tao_helpers_GenerisDataAdapterCsv();
		$options = $adapter->getOptions();
		
		//option form
		$myForm = tao_helpers_form_FormFactory::getForm('import');
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
		$fileElt->setDescription(__("Add the source file"));
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
		$this->setView('importform.tpl', true);
	}
	
	
	/**
	 * Export the selected class instance in a flat CSV file
	 * download header sent
	 * @return void
	 */
	public function export(){


		if(!file_exists(EXPORT_PATH)){
			if(!mkdir(EXPORT_PATH)){
				throw new Exception("Unable to create  " .EXPORT_PATH.". Check your filesystem!");
			}
		}
		
		$myLoginFormContainer = new tao_actions_form_Export();
		$myForm = $myLoginFormContainer->getForm();
		if($myForm->isSubmited()){
			if($myForm->isValid()){
			
				$rdf = '';
				$adapter = new tao_helpers_GenerisDataAdapterRdf();
				if($myForm->getValue('ontology') == 'current'){
					$rdf =  $adapter->export($this->getRootClass());
				}
				else{
					$rdf =  $adapter->export();
				}
				
				if(!empty($rdf)){
					
					$path = EXPORT_PATH."/".$myForm->getValue('name').'_'.time().'.rdf';
					file_put_contents($path, $rdf);
				}
			}
		}
		
		
		
		$this->setData('formTitle', __('Export data to RDF'));
		$this->setData('myForm', $myForm->render());
		$this->setView('export_form.tpl', true);
		
		
	}
	
	public function getExportedFiles(){
		$exportedFiles = array();
		foreach(scandir(EXPORT_PATH) as $file){
			$path = EXPORT_PATH.'/'.$file;
			if(preg_match("/\.rdf$/", $file) && !is_dir($path)){
				$exportedFiles[] = array(
					'path'		=> $path,
					'url'		=> EXPORT_URL.'/'.$file,
					'name'		=> substr($file, 0, strrpos($file, '_')),
					'date'		=> date('Y-m-d H:i:s', ((int)substr(str_replace('.rdf', '', $file), strrpos($file, '_') + 1)))
				);
			}
		}
		
		$page = $this->getRequestParameter('page'); 
		$limit = $this->getRequestParameter('rows'); 
		$sidx = $this->getRequestParameter('sidx');  
		$sord = $this->getRequestParameter('sord'); 
		$start = $limit * $page - $limit; 
		
		if(!$sidx) $sidx =1; 
		
		//slice from start to limit
		$files = array_slice($exportedFiles, $start, $limit);
		
		$col = array();
		foreach($files as $key => $val){
			$col[$key] = $val[$sidx];
		}
		array_multisort($col, ($sord == 'asc') ? SORT_ASC: SORT_DESC, $files);
		
		$count = count($exportedFiles); 
		if( $count >0 ) { 
			$total_pages = ceil($count/$limit); 
		} 
		else { 
			$total_pages = 0; 
		} 
		if ($page > $total_pages){
			$page = $total_pages; 
		}
		
		$response = new stdClass();
		$response->page = $page; 
		$response->total = $total_pages; 
		$response->records = $count; 
		foreach($files as $i => $file) { 
			$response->rows[$i]['id']= $i; 
			$response->rows[$i]['cell']= array(
				$file['name'],
				basename($file['path']),
				$file['date'],
				"<a href='{$file['url']}' class='nd' target='_blank' ><img src='".TAOBASE_WWW."img/search.png'  title='".__('view')."' />".__('View')."</a>&nbsp;|&nbsp;" .
				"<a href='".tao_helpers_Uri::url('downloadExportedFiles')."?filePath=".urlencode($file['path'])."' class='nd'  ><img src='".TAOBASE_WWW."img/bullet_go.png'  title='".__('download')."' />".__('Download')."</a>&nbsp;|&nbsp;" .
				"<a href='".tao_helpers_Uri::url('deleteExportedFiles')."?filePath=".urlencode($file['path'])."' class='nav nd' ><img src='".TAOBASE_WWW."img/delete.png' title='".__('delete')."' />".__('Delete')."</a>"
			);
		} 
		echo json_encode($response); 
	}
	
	public function deleteExportedFiles(){
		if($this->hasRequestParameter('filePath')){
			$path = urldecode($this->getRequestParameter('filePath'));
			if(preg_match("/^".preg_quote(EXPORT_PATH, '/')."/", $path)){
				unlink($path);
			}
		}
		$this->redirect(tao_helpers_Uri::url('export'));
	}
	
	public function downloadExportedFiles(){
		if($this->hasRequestParameter('filePath')){
			$path = urldecode($this->getRequestParameter('filePath'));
			if(preg_match("/^".preg_quote(EXPORT_PATH, '/')."/", $path) && file_exists($path)){
				
				header('Content-Type: text/xml');
				header('Content-Disposition: attachment; fileName="'.basename($path).'"');
				echo file_get_contents($path);
				return;
			}
		}
		return;
	}
	
	
	
	/**
	 * Render the  form to translate a Resource instance
	 * @return void
	 */
	public function translateInstance(){
		
		$instance = $this->getCurrentInstance();
		$myForm = tao_helpers_form_GenerisFormFactory::translateInstanceEditor($this->getCurrentClass(), $instance);
		
		if($this->hasRequestParameter('target_lang')){
			
			$targetLang = $this->getRequestParameter('target_lang');
		
			if(in_array($targetLang, $GLOBALS['available_langs'])){
				$langElt = $myForm->getElement('translate_lang');
				$langElt->setValue($targetLang);
				
				$trData = $this->service->getTranslatedProperties($instance, $targetLang);
				foreach($trData as $key => $value){
					$element = $myForm->getElement(tao_helpers_Uri::encode($key));
					if(!is_null($element)){
						$element->setValue($value);
					}
				}
			}
		}
		
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				$values = $myForm->getValues();
				if(isset($values['translate_lang'])){
					$lang = $values['translate_lang'];
					
					$translated = 0;
					foreach($values as $key => $value){
						if(preg_match("/^http/", $key) && !empty($value)){
							if($instance->editPropertyValueByLg(new core_kernel_classes_Property($key), $value, $lang)){
								$translated++;
							}
						}
					}
					if($translated > 0){
						$this->setData('message', __('Translation saved'));
					}
				}
			}
		}
		
		$this->setData('myForm', $myForm->render());
		$this->setData('formTitle', __('Translate'));
		$this->setView('form.tpl', true);
	}
	
	/**
	 * load the translated data of an instance regarding the given lang 
	 * @return void
	 */
	public function getTranslatedData(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$data = array();
		if($this->hasRequestParameter('lang')){
			
			$trData = $this->service->getTranslatedProperties(
					$this->getCurrentInstance(),
					$this->getRequestParameter('lang') 
				);
			foreach($trData as $key => $value){
				$data[tao_helpers_Uri::encode($key)] = $value;
			}
		}
		echo json_encode($data);
	}
	
	/**
	 * search the instances of an ontology
	 * @return 
	 */
	public function search(){
		$found = false;
		$clazz = $this->getRootClass();
		$myForm = tao_helpers_form_GenerisFormFactory::searchInstancesEditor($clazz, true);
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				$filters = $myForm->getValues('filters');
				$properties = array();
				foreach($filters as $propUri => $filter){
					if(preg_match("/^http/", $propUri)){
						$properties[] = new core_kernel_classes_Property($propUri);
					}
					else{
						unset($filters[$propUri]);
					}
				}
				$this->setData('properties', $properties);
				$instances = $this->service->searchInstances($filters, $clazz, $myForm->getValues('params'));
				if(count($instances) > 0 ){
					$found = array();
					$index = 1;
					foreach($instances as $instance){
						
						$instanceProperties = array();
						foreach($properties as $i => $property){
							$value = '';
							$propertyValues = $instance->getPropertyValuesCollection($property);
							foreach($propertyValues->getIterator() as $j => $propertyValue){
								if($propertyValue instanceof core_kernel_classes_Literal){
									$value .= (string)$propertyValue;
								}
								if($propertyValue instanceof core_kernel_classes_Resource){
									$value .= $propertyValue->getLabel();
								}
								if($j < $propertyValues->count()){
									$value .= "<br />";
								}
							}
							$instanceProperties[$i] = $value;
						}
						$found[$index]['uri'] = tao_helpers_Uri::encode($instance->uriResource);
						$found[$index]['properties'] = $instanceProperties;
						$index++;
					}
				}
			}
		}
		
		$this->setData('openAction', 'GenerisAction.select');
		if(preg_match("/^SaS/", get_class($this))){
			$this->setData('openAction', 'alert');
		}
		
		$this->setData('found', $found);
		$this->setData('myForm', $myForm->render());
		$this->setData('formTitle', __('Search'));
		$this->setView('search_form.tpl', true);
	}

	/**
	 * Render the add property sub form.
	 * @return void
	 */
	public function addClassProperty(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		
		$clazz = $this->getCurrentClass();
		
		if($this->hasRequestParameter('index')){
			$index = $this->getRequestParameter('index');
		}
		else{
			$index = count($clazz->getProperties(false)) + 1;
		}
		
		$myForm = tao_helpers_form_GenerisFormFactory::propertyEditor(
			$clazz->createProperty('Property_'.$index),
			tao_helpers_form_FormFactory::getForm('property_'.$index),
			$index,
			true
		);
		
		$this->setData('data', $myForm->renderElements());
		$this->setView('blank.tpl', true);
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
	 * Get the lists of a module which are the first child of TAO Object
	 * Render a json response
	 * @return void
	 */
	public function getLists(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		
		echo  json_encode(
			$this->getListData(array(
				TAO_GROUP_CLASS,
				TAO_ITEM_CLASS,
				TAO_ITEM_MODEL_CLASS,
				TAO_RESULT_CLASS,
				TAO_SUBJECT_CLASS,
				TAO_TEST_CLASS,
				TAO_DELIVERY_CLASS,
				TAO_DELIVERY_CAMPAIGN_CLASS,
				TAO_DELIVERY_RESULTSERVER_CLASS,
				TAO_DELIVERY_HISTORY_CLASS
			))
		);
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
	
	/**
	 * get the meta data of the selected resource
	 * Display the metadata. 
	 * @return void
	 */
	public function getMetaData(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		
		$this->setData('metadata', false); 
		try{
			$instance = $this->getCurrentInstance();
			
			$date = $instance->getLastModificationDate();
			$this->setData('date', $date->format('d/m/Y H:i:s'));
			$this->setData('user', $instance->getLastModificationUser());
			$this->setData('comment', $instance->comment);
			
			$this->setData('uri', $this->getRequestParameter('uri'));
			$this->setData('classUri', $this->getRequestParameter('classUri'));
			$this->setData('metadata', true); 
		}
		catch(Exception $e){}
		
		$this->setView('metadata.tpl', true);
	}
	
	/**
	 * save the comment field of the selected resource
	 * @return json response {saved: true, comment: text of the comment to refresh it}
	 */
	public function saveComment(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$response = array(
			'saved' 	=> false,
			'comment' 	=> ''
		);
		try{
			if($this->getRequestParameter('comment')){
				$instance = $this->getCurrentInstance();
				$instance->setComment($this->getRequestParameter('comment'));
				if($instance->comment == $this->getRequestParameter('comment')){
					$response['saved'] = true;
					$response['comment'] = $instance->comment;
				}
			}
		}
		catch(Exception $e){}
		echo json_encode($response);
	}
	
}
?>