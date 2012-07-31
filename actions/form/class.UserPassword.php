<?php

error_reporting(E_ALL);

/**
 * TAO - tao/actions/form/class.UserPassword.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 31.07.2012, 16:40:18 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
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
// section 127-0-1-1-bd1e3ae:137ff81790c:-8000:0000000000003B23-includes begin
// section 127-0-1-1-bd1e3ae:137ff81790c:-8000:0000000000003B23-includes end

/* user defined constants */
// section 127-0-1-1-bd1e3ae:137ff81790c:-8000:0000000000003B23-constants begin
// section 127-0-1-1-bd1e3ae:137ff81790c:-8000:0000000000003B23-constants end

/**
 * Short description of class tao_actions_form_UserPassword
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_UserPassword
    extends tao_helpers_form_FormContainer
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method initForm
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function initForm()
    {
        // section 127-0-1-1-bd1e3ae:137ff81790c:-8000:0000000000003B25 begin
        $this->form = tao_helpers_form_FormFactory::getForm('password');
		
		$actions = tao_helpers_form_FormFactory::getCommonActions('top');
		$this->form->setActions($actions, 'top');
		$this->form->setActions($actions, 'bottom');
        // section 127-0-1-1-bd1e3ae:137ff81790c:-8000:0000000000003B25 end
    }

    /**
     * Short description of method initElements
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function initElements()
    {
        // section 127-0-1-1-bd1e3ae:137ff81790c:-8000:0000000000003B27 begin
				$pass1Element = tao_helpers_form_FormFactory::getElement('oldpassword', 'Hiddenbox');
				$pass1Element->setDescription(__('Old Password'));
				$pass1Element->addValidator(
					tao_helpers_form_FormFactory::getValidator('Callback', array(
						'message'	=> __('Passwords are not matching'), 
						'object'	=> core_kernel_users_Service::singleton(),
						'method'	=> 'isPasswordValid',
						'param'		=> tao_models_classes_UserService::singleton()->getCurrentUser()
				)));
				$this->form->addElement($pass1Element);
				
				$pass2Element = tao_helpers_form_FormFactory::getElement('newpassword', 'Hiddenbox');
				$pass2Element->setDescription(__('New password'));
				$pass2Element->addValidators(array(
					tao_helpers_form_FormFactory::getValidator('Length', array('min' => 3))
				));
				$this->form->addElement($pass2Element);
				
				$pass3Element = tao_helpers_form_FormFactory::getElement('newpassword2', 'Hiddenbox');
				$pass3Element->setDescription(__('Repeat new password'));
				$pass3Element->addValidators(array(
					tao_helpers_form_FormFactory::getValidator('Password', array('password2_ref' => $pass2Element)),
				));
				$this->form->addElement($pass3Element);
        // section 127-0-1-1-bd1e3ae:137ff81790c:-8000:0000000000003B27 end
    }

} /* end of class tao_actions_form_UserPassword */

?>