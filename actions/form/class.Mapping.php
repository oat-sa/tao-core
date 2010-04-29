<?php

error_reporting(E_ALL);

/**
 * This container initialize the form used to map class properties to data to be
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * This class provide a container for a specific form instance.
 * It's subclasses instanciate a form and it's elements to be used as a
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/class.FormContainer.php');

/* user defined includes */
// section 127-0-1-1--250780b8:12843f3062f:-8000:00000000000023FB-includes begin
// section 127-0-1-1--250780b8:12843f3062f:-8000:00000000000023FB-includes end

/* user defined constants */
// section 127-0-1-1--250780b8:12843f3062f:-8000:00000000000023FB-constants begin
// section 127-0-1-1--250780b8:12843f3062f:-8000:00000000000023FB-constants end

/**
 * This container initialize the form used to map class properties to data to be
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Mapping
    extends tao_helpers_form_FormContainer
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method initForm
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function initForm()
    {
        // section 127-0-1-1--250780b8:12843f3062f:-8000:00000000000023FD begin
        
    	$this->form = tao_helpers_form_FormFactory::getForm('mapping');
    	
    	$importElt = tao_helpers_form_FormFactory::getElement('import', 'Submit');
		$importElt->setValue(__('Import'));
		$this->form->setActions(array($importElt), 'bottom');
    	
        // section 127-0-1-1--250780b8:12843f3062f:-8000:00000000000023FD end
    }

    /**
     * Short description of method initElements
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function initElements()
    {
        // section 127-0-1-1--250780b8:12843f3062f:-8000:00000000000023FF begin
        
    	if(!isset($this->options['class_properties'])){
    		throw new Exception('No class properties found');
    	}
    	if(!isset($this->options['csv_column'])){
    		throw new Exception('No csv columns found');
    	}
    	
    	$columnsOptions =  array();
    	foreach($this->options['csv_column'] as $i => $column){
    		$columnsOptions[$column] = __('Column')." $i : ".$column;
    	}
    	$columnsOptions['empty'] = __('Empty');
    	$columnsOptions['null']  = __("Don't set");
    	
    	$i = 0;
    	foreach($this->options['class_properties'] as $propertyUri => $propertyLabel){
    		
    		$propElt = tao_helpers_form_FormFactory::getElement($propertyUri, 'Combobox');
    		$propElt->setDescription($propertyLabel);
    		$propElt->setOptions($columnsOptions);
    		if(array_key_exists($i, $columnsOptions)){
    			$propElt->setValue($i);
    		}
    		else{
    			$propElt->setValue($this->options['csv_column'][$i]);
    		}
    		
    		$this->form->addElement($propElt);
    		
    		$i++;
    	}
    	$this->form->createGroup('property_mapping', __('Map the properties to the CSV columns'), array_keys($this->options['class_properties']));
    	
        // section 127-0-1-1--250780b8:12843f3062f:-8000:00000000000023FF end
    }

} /* end of class tao_actions_form_Mapping */

?>