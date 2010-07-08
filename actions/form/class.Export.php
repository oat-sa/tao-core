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
		
		$this->form = tao_helpers_form_FormFactory::getForm('login');
		
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
		
		$formatElt = tao_helpers_form_FormFactory::getElement('ontology', 'Radiobox');
		$formatElt->setDescription(__('Content'));
		$formatElt->setOptions(array(
			'all'			=> __('All (model and data)'),
			'current'		=> __('Current (current model, data and dependancies)'),
			'data'			=> __('Only Data')
		));
		$formatElt->setValue('current');
		$this->form->addElement($formatElt);
		
		$nameElt = tao_helpers_form_FormFactory::getElement('name', 'Textbox');
		$nameElt->setDescription(__('File name'));
		if(Session::hasAttribute('currentExtension')){
			$nameElt->setValue(Session::getAttribute('currentExtension'));
		}
		$this->form->addElement($nameElt);
		$this->form->createGroup('options', __('Export Options'), array('name', 'ontology'));
		
        // section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED7 end
    }

} /* end of class tao_actions_form_Export */

?>