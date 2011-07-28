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
class tao_actions_form_CSVMapping
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
    	
    	$importElt = tao_helpers_form_FormFactory::getElement('import', 'Free');
		$importElt->setValue( "<a href='#' class='form-submiter' ><img src='".TAOBASE_WWW."/img/import.png' /> ".__('Import')."</a>");
		$this->form->setActions(array($importElt), 'bottom');
		$this->form->setActions(array($importElt), 'top');
    	
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
    		$propElt->setEmptyOption(' --- '.__('Select').' --- ');
    		
    		$this->form->addElement($propElt);
    		
    		$i++;
    	}
    	$this->form->createGroup('property_mapping', __('Map the properties to the CSV columns'), array_keys($this->options['class_properties']));
    	
    	$ranged = array();
    	foreach($this->options['ranged_properties'] as $propertyUri => $propertyLabel){
    		$property = new core_kernel_classes_Property(tao_helpers_Uri::decode($propertyUri));
    		$propElt = tao_helpers_form_GenerisFormFactory::elementMap($property);
    		if(!is_null($propElt)){
    			$this->form->addElement($propElt);
    			$ranged[] = $propElt->getName();
    		}
    	}
    	if(count($ranged) > 0){
    		$this->form->createGroup('ranged_property', __('Define the default values'), $ranged);
    	}
    	
        // section 127-0-1-1--250780b8:12843f3062f:-8000:00000000000023FF end
    }

} /* end of class tao_actions_form_CSVMapping */

?>