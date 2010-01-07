<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/actions/form/class.Settings.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 07.01.2010, 16:02:27 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_FormContainer
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/class.FormContainer.php');

/* user defined includes */
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF1-includes begin
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF1-includes end

/* user defined constants */
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF1-constants begin
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF1-constants end

/**
 * Short description of class tao_actions_form_Settings
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Settings
    extends tao_helpers_form_FormContainer
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method initForm
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initForm()
    {
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF3 begin
		
		$this->form = tao_helpers_form_FormFactory::getForm('settings', array('noRevert' => true));
		
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF3 end
    }

    /**
     * Short description of method initElements
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initElements()
    {
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF5 begin
		
		//@todo manage ui language with .po files
		$uiLangElement = tao_helpers_form_FormFactory::getElement('ui_lang', 'Textbox');
		$uiLangElement->setDescription(__('Interface language'));
		$uiLangElement->setValue('EN');
		$uiLangElement->setAttributes(array("readonly" => "true"));
		$this->form->addElement($uiLangElement);
		
		$dataLangElement = tao_helpers_form_FormFactory::getElement('data_lang', 'Textbox');
		$dataLangElement->setDescription(__('Data language'));
		if(isset($this->data['data_lang'])){
			$dataLangElement->setValue($this->data['data_lang']);
		}
		$dataLangElement->addValidators(array(
			tao_helpers_form_FormFactory::getValidator('NotEmpty'),
			tao_helpers_form_FormFactory::getValidator('Regex', array('format' => "/^[A-Z]{2,3}$/"))
		));
		$this->form->addElement($dataLangElement);
		
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF5 end
    }

} /* end of class tao_actions_form_Settings */

?>