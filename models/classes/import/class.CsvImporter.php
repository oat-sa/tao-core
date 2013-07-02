<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2013 (original work) Open Assessment Techonologies SA (under the project TAO-PRODUCT);
 *               
 * 
 */

/**
 * Short description of class taoQTI_models_classes_ItemModel
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes_Export
 */
class tao_models_classes_import_CsvImporter implements tao_models_classes_import_ImportHandler
{

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_import_ImportHandler::getLabel()
     */
    public function getLabel() {
    	return __('CSV');
    }
    
    /**
     * (non-PHPdoc)
     * @see tao_models_classes_export_ExportHandler::getForm()
     */
    public function getForm() {
    	$form = new tao_models_classes_import_CsvImportForm();
    	return $form->getForm();
    }

    public function import($class, $formValues) {
				//import for CSV
		$importData = array();
		$importData['options'] = array(
			'field_delimiter' 			=> $formValues['field_delimiter'],
			'field_encloser' 			=> $formValues['field_encloser'],
			'line_break' 				=> "\n",
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
	 * display the mapping form, after a CSV file import. This is the second (and last) form
	 * the users see to import a CSV file.
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
			$adapterOptions = array_merge($this->additionalAdapterOptions, $importData['options']);
			$adapter = new tao_helpers_data_GenerisAdapterCsv($adapterOptions);
			
			$service = tao_models_classes_Service::getServiceByName(str_replace('tao', '',Context::getInstance()->getExtensionName()));
			
			//get the current class of properties
			$clazz = new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getSessionAttribute('classUri')));
			$properties = array(tao_helpers_Uri::encode(RDFS_LABEL) => __('Label'));
			$rangedProperties = array();
			
			$topLevelClass = new core_kernel_classes_Class(CLASS_GENERIS_RESOURCE);
			$classProperties = $service->getClazzProperties($clazz, $topLevelClass);

			foreach($classProperties as $property){
				common_Logger::i($property->getLabel());
				if(!in_array($property->getUri(), $this->excludedProperties)){
					//@todo manage the properties with range
					$range = $property->getRange();
					$properties[tao_helpers_Uri::encode($property->getUri())] = $property->getLabel();
					
					if($range->getUri() != RDFS_LITERAL){
						$rangedProperties[tao_helpers_Uri::encode($property->getUri())] = $property->getLabel();
					}
				}
			}
			
			//load the csv data from the file (uploaded in the upload form) to get the columns
			$csv_data = $adapter->load($importData['file']);
			
			//build the mapping form 
			if ($csv_data->count()) {
				
				// 'class properties' contains an associative array(str:'propertyUri' => 'str:propertyLabel') describing properties belonging to the target class.
				// 'ranged properties' contains an associative array(str:'propertyUri' => 'str:propertyLabel')  describing properties belonging to the target class and that have a range.
				// 'csv_column' contains an array(int:columnIndex => 'str:columnLabel') that will be used to create the selection of possible CSV column to map in views.
				// 'csv_column' might have NULL values for 'str:columnLabel' meaning that there was no header row with column names in the CSV file. 
				
				// Format the column mapping option for the form.
				$csvColMapping = array();
				if (true == $importData['options']['first_row_column_names'] && null != $csv_data->getColumnMapping()){
					// set the column label for each entry.
					// $csvColMapping = array('label', 'comment', ...)
					$csvColMapping = $csv_data->getColumnMapping();
				}
				else{
					// set an empty value for each entry of the array
					// to describe that column names are unknown.
					// $csvColMapping = array(null, null, ...)
					for ($i = 0; $i < $csv_data->getColumnCount(); $i++) {
						$csvColMapping[$i] = null;
					}
				}
				
				$myFormContainer = new tao_actions_form_CSVMapping(array(), array(
					'class_properties'  		=> $properties,
					'ranged_properties'			=> $rangedProperties,
					'csv_column'				=> $csvColMapping,
					'first_row_column_names'	=> $importData['options']['first_row_column_names']
				));
				
				$myForm = $myFormContainer->getForm();
				
				if($myForm->isSubmited()){
					
					if($myForm->isValid()){
						
						// set the mapping to the adapter
						// Clean "csv_select" values from form view.
						// Transform any "csv_select" in "csv_null" in order to
						// have the same importation behaviour for both because
						// semantics are the same.
						$map = $myForm->getValues('property_mapping');
						$newMap = array();
						
						foreach($map as $k => $m) {
							if ($m !== 'csv_select') {
								$newMap[$k] = $map[$k];
							}
							else {
								$newMap[$k] = 'csv_null';
							}
						}
						
						$adapter->addOption('map', $newMap);
						$adapter->addOption('staticMap', array_merge($myForm->getValues('ranged_property'), $this->staticData));
						
						//import it!
						if($adapter->import($importData['file'], $clazz)){
							$this->setData('message', __('Data imported successfully'));
							$this->setSessionAttribute("showNodeUri", tao_helpers_Uri::encode($clazz->getUri()));
							$this->removeSessionAttribute('classUri');
							$this->setData('reload', true);
							
							@unlink($importData['file']);
						}
					}
				}
				
				$this->setData('myForm', $myForm->render());
				$this->setData('formTitle', __('Import into ').$clazz->getLabel());
				$this->setView('form.tpl', 'tao');
			}
			else {
				// Nothing was retrieved.
				$this->redirect('index');
			}
		}
	}

}

?>