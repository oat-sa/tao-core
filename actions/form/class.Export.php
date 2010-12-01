<?php

error_reporting(E_ALL);

/**
 * This container initialize the export form.
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
// section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED4-includes begin
// section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED4-includes end

/* user defined constants */
// section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED4-constants begin
// section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED4-constants end

/**
 * This container initialize the export form.
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Export
    extends tao_helpers_form_FormContainer
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute formats
     *
     * @access protected
     * @var array
     */
    protected $formats = array('rdf' => 'RDF');

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
        // section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED5 begin
		
		$this->form = tao_helpers_form_FormFactory::getForm('export');
		
    	$exportElt = tao_helpers_form_FormFactory::getElement('export', 'Free');
		$exportElt->setValue( "<a href='#' class='form-submiter' ><img src='".TAOBASE_WWW."/img/export.png' /> ".__('Export')."</a>");
		
		$this->form->setActions(array($exportElt), 'bottom');
		$this->form->setActions(array(), 'top');
		
        // section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED5 end
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
        // section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED7 begin
		
    	//create the element to select the import format
    	$formatElt = tao_helpers_form_FormFactory::getElement('format', 'Radiobox');
    	$formatElt->setDescription(__('Please select the way to export the data'));
    	
    	//mandatory field
    	$formatElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
    	$formatElt->setOptions($this->formats);

    	//shortcut: add the default value here to load the first time the form is defined
		if(count($this->formats) == 1){
			foreach($this->formats as $formatKey => $format){
				$formatElt->setValue($formatKey);
			}
		}
		if(isset($_POST['format'])){
			if(array_key_exists($_POST['format'], $this->formats)){
				$formatElt->setValue($_POST['format']);
			}
		}
		
    	$this->form->addElement($formatElt);
    	$this->form->createGroup('formats', __('Supported export formats'), array('format'));
    	
    	//load dynamically the method regarding the selected format 
    	if(!is_null($formatElt->getValue())){
    		$method = "init".strtoupper($formatElt->getValue())."Elements";
    		
    		if(method_exists($this, $method)){
    			$this->$method();
    		}
    	}
		
        // section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED7 end
    }

    /**
     * Short description of method initRDFElements
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initRDFElements()
    {
        // section 127-0-1-1-70b2308e:12ca2398ae8:-8000:000000000000291A begin
        
    	$descElt = tao_helpers_form_FormFactory::getElement('rdf_desc', 'Label');
		$descElt->setValue(__('Enable your to export an RDF file containing the selected namespaces'));
		$this->form->addElement($descElt);
    	
    	$formatElt = tao_helpers_form_FormFactory::getElement('ontology', 'Radiobox');
		$formatElt->setDescription(__('Namespaces'));
		$formatElt->setOptions(array(
			'all'			=> __('All (the complete TAO Module)'),
			'current'		=> __('Current (the current extension, the local data and their dependancies)'),
			'data'			=> __('Local Data (the local namespace containing only the data inserted by the users)')
		));
		$formatElt->setValue('current');
		$this->form->addElement($formatElt);
		
		$nameElt = tao_helpers_form_FormFactory::getElement('name', 'Textbox');
		$nameElt->setDescription(__('File name'));
		$nameElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		if(Session::hasAttribute('currentExtension')){
			$nameElt->setValue(Session::getAttribute('currentExtension'));
		}
		$nameElt->setUnit(".rdf");
		$this->form->addElement($nameElt);
		$this->form->createGroup('options', __('Export Options'), array('rdf_desc',  'ontology', 'name'));
    	
        // section 127-0-1-1-70b2308e:12ca2398ae8:-8000:000000000000291A end
    }

} /* end of class tao_actions_form_Export */

?>