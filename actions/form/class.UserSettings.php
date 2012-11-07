<?php

error_reporting(E_ALL);

/**
 * This container initialize the settings form.
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
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
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/helpers/form/class.FormContainer.php');

/* user defined includes */
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF1-includes begin
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF1-includes end

/* user defined constants */
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF1-constants begin
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF1-constants end

/**
 * This container initialize the settings form.
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_UserSettings
    extends tao_helpers_form_FormContainer
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method initForm
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    protected function initForm()
    {
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF3 begin
		
		$this->form = tao_helpers_form_FormFactory::getForm('settings');
		
		$actions = tao_helpers_form_FormFactory::getCommonActions('top');
		$this->form->setActions($actions, 'top');
		$this->form->setActions($actions, 'bottom');
		
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF3 end
    }

    /**
     * Short description of method initElements
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    protected function initElements()
    {
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF5 begin
		
    	// Retrieve languages available for a GUI usage.
    	$guiUsage = new core_kernel_classes_Resource(INSTANCE_LANGUAGE_USAGE_GUI);
        $guiOptions = tao_helpers_I18n::getAvailableLangsByUsage($guiUsage);
        
        // Retrieve languages available for a Data usage.
        $guiData = new core_kernel_classes_Resource(INSTANCE_LANGUAGE_USAGE_DATA);
        $dataOptions = tao_helpers_I18n::getAvailableLangsByUsage($guiUsage);

        $uiLangElement = tao_helpers_form_FormFactory::getElement('ui_lang', 'Combobox');
        $uiLangElement->setDescription(__('Interface language'));
        $uiLangElement->setOptions($guiOptions);

        $this->form->addElement($uiLangElement);

        $dataLangElement = tao_helpers_form_FormFactory::getElement('data_lang', 'Combobox');
        $dataLangElement->setDescription(__('Data language'));
        $dataLangElement->setOptions($dataOptions);

        $this->form->addElement($dataLangElement);
		
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF5 end
    }

} /* end of class tao_actions_form_UserSettings */

?>