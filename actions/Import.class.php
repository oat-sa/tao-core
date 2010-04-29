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
class Import extends CommonModule {

	
	/**
	 * @return void
	 */
	public function index(){
		$this->forward('Import', 'upload');
	}
	
	/**
	 * display the import form: csv options and file upload
	 * @return void
	 */
	public function upload(){
		
		$this->removeSessionAttribute('import');
		
		$myFormContainer = new tao_actions_form_Import();
		$myForm = $myFormContainer->getForm();
		
		//if the form is submited and valid
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				//init import options
				
				$importData = array();
				$importData['options'] = array(
					'field_delimiter' 			=> $myForm->getValue('field_delimiter'),
					'field_encloser' 			=> $myForm->getValue('field_encloser'),
					'line_break' 				=> $myForm->getValue('line_break'),
					'multi_values_delimiter' 	=> $myForm->getValue('multi_values_delimiter'),
					'first_row_column_names' 	=> $myForm->getValue('first_row_column_names'),
					'column_order' 				=> $myForm->getValue('column_order')
				);
				$fileData = $myForm->getValue('source');
				$importData['file'] = $fileData['uploaded_file'];
				
				$this->setSessionAttribute('import', $importData);
				$this->redirect('mapping');
			}
		}
		$this->setData('myForm', $myForm->render());
		$this->setData('formTitle', __('Import data'));
		$this->setView('form/import.tpl', true);
	}
	
	
	/**
	 * display the mapping form
	 * @return void
	 */
	public function mapping(){
		if(!$this->hasSessionAttribute('import')){
			$this->redirect('upload');
		}
		
		if($this->hasSessionAttribute('classUri')){
			
			//get the import options in the session (from the upload form)
			$importData = $this->getSessionAttribute('import');
			
			//initialize the adapter
			$adapter = new tao_helpers_data_GenerisAdapterCsv($importData['options']);
			
			
			$service = tao_models_classes_ServiceFactory::get(str_replace('tao', '',$this->getSessionAttribute('currentExtension')));
			
			//get the current class of properties
			$clazz = new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getSessionAttribute('classUri')));
			$properties = array(RDFS_LABEL => __('Label'));
			foreach($service->getClazzProperties($clazz) as $property){
				
				//@todo manage the properties with range
				$range = $property->getRange();
				if($range->uriResource == RDFS_LITERAL){	
					$properties[tao_helpers_Uri::encode($property->uriResource)] = $property->getLabel();
				}
				
			}
			
			//load the csv data from the file (uploaded in the upload form) to get the columns
			$csv_data = $adapter->load($importData['file']);
			
			//build the mapping form 
			$myFormContainer = new tao_actions_form_Mapping(array(), array(
				'class_properties'  => $properties,
				'csv_column'		=> array_keys($csv_data[0])
			));
			$myForm = $myFormContainer->getForm();
			if($myForm->isSubmited()){
				if($myForm->isValid()){
					
					
					//set the mapping to the adapter
					$adapter->addOption('map', $myForm->getValues());
					
					//import it!
					if($adapter->import($importData['file'], $clazz)){
						$this->setData('message', __('Data imported successfully'));
						$this->setData('reload', true);
						$this->forward('Import', 'upload');
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