<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/actions/form/class.Export.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 01.03.2010, 18:00:34 with ArgoUML PHP module 
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
// section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED4-includes begin
// section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED4-includes end

/* user defined constants */
// section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED4-constants begin
// section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED4-constants end

/**
 * Short description of class tao_actions_form_Export
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
		
		$exportElt = tao_helpers_form_FormFactory::getElement('export', 'Submit');
		$exportElt->setValue(__('Export'));
		$this->form->setActions(array($exportElt), 'bottom');
		
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
		$formatElt->setDescription(__('Ontology'));
		$formatElt->setOptions(array(
			'all'			=> __('All'),
			'current'		=> __('Current')
		));
		$formatElt->setValue('current');
		$this->form->addElement($formatElt);
		
		$nameElt = tao_helpers_form_FormFactory::getElement('name', 'Textbox');
		$nameElt->setDescription(__('File name'));
		if(Session::hasAttribute('currentExtension')){
			$nameElt->setValue(Session::getAttribute('currentExtension'));
		}
		$this->form->addElement($nameElt);
		
        // section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED7 end
    }

} /* end of class tao_actions_form_Export */

?>