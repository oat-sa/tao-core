<?php

error_reporting(E_ALL);

/**
 * TAO - tao/actions/form/class.Versionning.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 28.06.2012, 13:37:39 with ArgoUML PHP module
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
 * @author Jehan Bihin, <jehan.bihin@tudor.lu>
 */
require_once('tao/helpers/form/class.FormContainer.php');

/* user defined includes */
// section 127-0-1-1-35689176:13832da9f32:-8000:0000000000003B3A-includes begin
// section 127-0-1-1-35689176:13832da9f32:-8000:0000000000003B3A-includes end

/* user defined constants */
// section 127-0-1-1-35689176:13832da9f32:-8000:0000000000003B3A-constants begin
// section 127-0-1-1-35689176:13832da9f32:-8000:0000000000003B3A-constants end

/**
 * Short description of class tao_actions_form_Versionning
 *
 * @access public
 * @author Jehan Bihin, <jehan.bihin@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Versionning
    extends tao_helpers_form_FormContainer
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method initForm
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @return mixed
     */
    public function initForm()
    {
        // section 127-0-1-1-35689176:13832da9f32:-8000:0000000000003B3C begin
		$this->form = tao_helpers_form_FormFactory::getForm('settings');
		$actions = tao_helpers_form_FormFactory::getCommonActions('top');
		$this->form->setActions($actions, 'top');
        // section 127-0-1-1-35689176:13832da9f32:-8000:0000000000003B3C end
    }

    /**
     * Short description of method initElements
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @return mixed
     */
    public function initElements()
    {
        // section 127-0-1-1-35689176:13832da9f32:-8000:0000000000003B3E begin
		$loginElt = tao_helpers_form_FormFactory::getElement('login', 'Textbox');
		$loginElt->setDescription(__('Login'));
		$loginElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$this->form->addElement($loginElt);

		$passElt = tao_helpers_form_FormFactory::getElement('password', 'Hiddenbox');
		$passElt->setDescription(__('Password'));
		$passElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$this->form->addElement($passElt);

		$elt = tao_helpers_form_FormFactory::getElement('type', 'Hidden');
		$elt->setDescription(__('Type'));
		$elt->setValue('svn');
		$elt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$this->form->addElement($elt);
		$this->form->addElement($passElt);

		$elt = tao_helpers_form_FormFactory::getElement('url', 'Textbox');
		$elt->setDescription(__('URL'));
		$elt->setHelp('http://mydomain/svn');
		$elt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$this->form->addElement($elt);
		$this->form->addElement($passElt);

		$elt = tao_helpers_form_FormFactory::getElement('path', 'Textbox');
		$elt->setDescription(__('Path'));
		$elt->setHelp(GENERIS_FILES_PATH.'versionning/DEFAULT');
		$elt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$this->form->addElement($elt);
        // section 127-0-1-1-35689176:13832da9f32:-8000:0000000000003B3E end
    }

} /* end of class tao_actions_form_Versionning */

?>