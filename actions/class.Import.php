<?php
/**
 * This controller provide the actions to export and manage exported data
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class tao_actions_Import extends tao_actions_CommonModule {

	
	/**
	 * to be overriden if needed
	 * @var tao_actions_form_Import
	 */
	protected $formContainer;
	
	/**
	 * to set static data that will be used during the import
	 * @var array
	 */
	protected $staticData = array();
	
	/**
	 * initialize the formContainer
	 */
	public function __construct(){
		parent::__construct();
		$this->formContainer = new tao_actions_form_Import();
	}
	
	/**
	 * initialize the classUri and execute the upload action
	 * @return void
	 */
	public function index(){
		if($this->hasRequestParameter('classUri')){
			$this->setSessionAttribute('classUri', $this->getRequestParameter('classUri'));
		}
		$this->upload();
	}
	
	/**
	 * Main method, select the format and display the right form
	 * @return void
	 */
	public function upload(){
		
		$this->removeSessionAttribute('import');
		
		$myForm = $this->formContainer->getForm();
		
		//if the form is submited and valid
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				//import method for the given format
				if(!is_null($myForm->getValue('format'))){
					
					$importMethod = 'import'.strtoupper($myForm->getValue('format')).'File';
					if(method_exists($this, $importMethod)){
						
						//apply the matching method
						$this->$importMethod($myForm->getValues());
						
					}
				}
			}
		}
		$this->setData('myForm', $myForm->render());
		$this->setData('formTitle', __('Import'));
		$this->setView('form/import.tpl', true);
	}
	
	/**
	 * action to perform on a posted RDF file
	 * @param array $formValues the posted data
	 */
	protected function importRDFFile($formValues){
		if(isset($formValues['source'])){
			
			//get the item parent class
			$uploadedFile = $formValues['source']['uploaded_file'];
			
			//validate the file to import
			$parser = new tao_models_classes_Parser($uploadedFile, array('extension' => 'rdf'));
			
			$parser->validate();
			if(!$parser->isValid()){
				echo "not valid";
				$this->setData('importErrorTitle', __('Validation of the imported file has failed'));
				$this->setData('importErrors', $parser->getErrors());
			}
			else{
			
				//initialize the adapter
				$adapter = new tao_helpers_data_GenerisAdapterRdf();
				if($adapter->import($uploadedFile, null)){
					
					$this->removeSessionAttribute('classUri');
					$this->setData('message', __('Data imported successfully'));
					$this->setData('reload', true);
					
					@unlink($uploadedFile);
								
					return true;
				}		
				else{
					$this->setData('message', __('Nothing imported'));
				}	
			}
			
		}
	}
	
	/**
	 * action to perform on a posted CSV file
	 * @param array $formValues the posted data
	 */
	protected function importCSVFile($formValues){
		
		//import for CSV
		$importData = array();
		$importData['options'] = array(
			'field_delimiter' 			=> $formValues['field_delimiter'],
			'field_encloser' 			=> $formValues['field_encloser'],
			'line_break' 				=> $formValues['line_break'],
			'multi_values_delimiter' 	=> $formValues['multi_values_delimiter'],
			'first_row_column_names' 	=> isset($formValues['first_row_column_names'][0])
		);
		if(!empty($formValues['column_order'])){
			$importData['options']['column_order'] = $formValues['column_order'];
		}
		$fileData = $formValues['source'];
		$importData['file'] = $fileData['uploaded_file'];
		
		$this->setSessionAttribute('import', $importData);
		$this->redirect(_url('mapping'));
	}
	
	
	/**
	 * display the mapping form, after a CSV file import
	 * @return void
	 */
	public function mapping(){
		if(!$this->hasSessionAttribute('import')){
			$this->redirect(_url('upload'));
		}
		
		if($this->hasSessionAttribute('classUri')){
			
			//get the import options in the session (from the upload form)
			$importData = $this->getSessionAttribute('import');
			
			//initialize the adapter
			$adapter = new tao_helpers_data_GenerisAdapterCsv($importData['options']);
			
			$currentExtention = $this->getSessionAttribute('currentExtension');
			$service = tao_models_classes_ServiceFactory::get(str_replace('tao', '',$currentExtention));
			
			//get the current class of properties
			$clazz = new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getSessionAttribute('classUri')));
			$properties = array(tao_helpers_Uri::encode(RDFS_LABEL) => __('Label'));
			$rangedProperties = array();
			
			if($currentExtention == 'taoSubjects'){
				$topLevelClass = new core_kernel_classes_Class(CLASS_GENERIS_USER);
			}
			else{
				$topLevelClass = new core_kernel_classes_Class(TAO_OBJECT_CLASS);
			}
			
			foreach($service->getClazzProperties($clazz, $topLevelClass) as $property){
				
				//@todo manage the properties with range
				$range = $property->getRange();
				if($range->uriResource == RDFS_LITERAL){	
					$properties[tao_helpers_Uri::encode($property->uriResource)] = $property->getLabel();
				}
				else{
					$rangedProperties[tao_helpers_Uri::encode($property->uriResource)] = $property->getLabel();
				}
				
			}
			
			//load the csv data from the file (uploaded in the upload form) to get the columns
			$csv_data = $adapter->load($importData['file']);
			
			//build the mapping form 
			$myFormContainer = new tao_actions_form_CSVMapping(array(), array(
				'class_properties'  => $properties,
				'ranged_properties'	=> $rangedProperties,
				'csv_column'		=> array_keys($csv_data[0])
			));
			$myForm = $myFormContainer->getForm();
			if($myForm->isSubmited()){
				if($myForm->isValid()){
					
					
					//set the mapping to the adapter
					$adapter->addOption('map', $myForm->getValues('property_mapping'));
					$adapter->addOption('staticMap', array_merge($myForm->getValues('ranged_property'), $this->staticData));
					
					//import it!
					if($adapter->import($importData['file'], $clazz)){
						$this->setData('message', __('Data imported successfully'));
						$this->setSessionAttribute("showNodeUri", tao_helpers_Uri::encode($clazz->uriResource));
						$this->removeSessionAttribute('classUri');
						$this->setData('reload', true);
						
						@unlink($importData['file']);
					}
				}
			}
			
			$this->setData('myForm', $myForm->render());
			$this->setData('formTitle', __('Import into ').$clazz->getLabel());
			$this->setView('form.tpl', true);
		}
	}
}
?>
