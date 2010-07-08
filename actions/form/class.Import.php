<?php

error_reporting(E_ALL);

/**
 * This container initialize the import form.
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
// section 127-0-1-1--5823ae53:12820f19957:-8000:00000000000023CE-includes begin
// section 127-0-1-1--5823ae53:12820f19957:-8000:00000000000023CE-includes end

/* user defined constants */
// section 127-0-1-1--5823ae53:12820f19957:-8000:00000000000023CE-constants begin
// section 127-0-1-1--5823ae53:12820f19957:-8000:00000000000023CE-constants end

/**
 * This container initialize the import form.
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Import
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
        // section 127-0-1-1--5823ae53:12820f19957:-8000:00000000000023CF begin
        
    	$this->form = tao_helpers_form_FormFactory::getForm('import');
    	
		$nextElt = tao_helpers_form_FormFactory::getElement('next', 'Free');
		$nextElt->setValue( "<a href='#' class='form-submiter' ><img src='".TAOBASE_WWW."/img/next.png' /> ".__('Next')."</a>");
		$this->form->setActions(array($nextElt), 'bottom');
		$this->form->setActions(array(), 'top');
    	
        // section 127-0-1-1--5823ae53:12820f19957:-8000:00000000000023CF end
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
        // section 127-0-1-1--5823ae53:12820f19957:-8000:00000000000023D1 begin
        
    	$adapter = new tao_helpers_data_GenerisAdapterCsv();
		$options = $adapter->getOptions();
		
		//create import options form
		foreach($options as $optName => $optValue){
			(is_bool($optValue))  ? $eltType = 'Checkbox' : $eltType = 'Textbox';
			$optElt = tao_helpers_form_FormFactory::getElement($optName, $eltType);
			$optElt->setDescription(tao_helpers_Display::textCleaner($optName, ' '));
			$optElt->setValue(addslashes($optValue));
			
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
			$this->form->addElement($optElt);
		}
		$this->form->createGroup('options', __('CSV Options'), array_keys($options));
		
		//create file upload form box
		$fileElt = tao_helpers_form_FormFactory::getElement('source', 'AsyncFile');
		$fileElt->setDescription(__("Add the source file"));
		$fileElt->addValidators(array(
			tao_helpers_form_FormFactory::getValidator('NotEmpty'),
			tao_helpers_form_FormFactory::getValidator('FileMimeType', array('mimetype' => array('text/plain', 'text/csv',  'application/csv-tab-delimited-table'), 'extension' => array('csv', 'txt'))),
			tao_helpers_form_FormFactory::getValidator('FileSize', array('max' => 2000000))
		));
		$this->form->addElement($fileElt);
		$this->form->createGroup('file', __('Upload CSV File'), array('source'));
    	
        // section 127-0-1-1--5823ae53:12820f19957:-8000:00000000000023D1 end
    }

} /* end of class tao_actions_form_Import */

?>