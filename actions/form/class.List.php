<?php

error_reporting(E_ALL);

/**
 * This container initialize the list form.
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
// section 127-0-1-1--289f70ef:127af0e99db:-8000:000000000000237A-includes begin
// section 127-0-1-1--289f70ef:127af0e99db:-8000:000000000000237A-includes end

/* user defined constants */
// section 127-0-1-1--289f70ef:127af0e99db:-8000:000000000000237A-constants begin
// section 127-0-1-1--289f70ef:127af0e99db:-8000:000000000000237A-constants end

/**
 * This container initialize the list form.
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_List
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
        // section 127-0-1-1--289f70ef:127af0e99db:-8000:000000000000237C begin
        
        $this->form = tao_helpers_form_FormFactory::getForm('list');

        $addElt = tao_helpers_form_FormFactory::getElement('add', 'Free');
		$addElt->setValue("<button class='form-submiter'><img src='".TAOBASE_WWW."img/add.png' class='icon' />".__('Add')."</button>");
		$this->form->setActions(array($addElt), 'bottom');
		
        // section 127-0-1-1--289f70ef:127af0e99db:-8000:000000000000237C end
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
        // section 127-0-1-1--289f70ef:127af0e99db:-8000:000000000000237E begin
        
    	$labelElt = tao_helpers_form_FormFactory::getElement('label', 'Textbox');
		$labelElt->setDescription(__('Name'));
		$labelElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$this->form->addElement($labelElt);
		
		$sizeElt = tao_helpers_form_FormFactory::getElement('size', 'Textbox');
		$sizeElt->setDescription(__('Number of elements'));
		$sizeElt->setAttribute('size', '4');
		$sizeElt->setValue(0);
		$sizeElt->addValidator(
			tao_helpers_form_FormFactory::getValidator('Integer', array('min' => 1))
		);
		$this->form->addElement($sizeElt);
    	
        // section 127-0-1-1--289f70ef:127af0e99db:-8000:000000000000237E end
    }

} /* end of class tao_actions_form_List */

?>