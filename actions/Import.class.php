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
	 * Constructor performs initializations actions
	 * @return void
	 */
	public function __construct(){		
		$this->defaultData();
	}
	
	/**
	 * Export the selected class instance in a flat CSV file
	 * download header sent
	 * @return void
	 */
	public function index(){
		
		$myFormContainer = new tao_actions_form_Import();
		$myForm = $myFormContainer->getForm();
		
		//if the form is submited and valid
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				//init import options
				$clazz = $this->getCurrentClass();
				$adapter = new tao_helpers_GenerisDataAdapterCsv();
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
		$this->setView('form/import.tpl', true);
	}
	
}
?>