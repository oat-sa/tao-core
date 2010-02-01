<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/actions/form/class.Users.php
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
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF8-includes begin
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF8-includes end

/* user defined constants */
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF8-constants begin
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF8-constants end

/**
 * Short description of class tao_actions_form_Users
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Users
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
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DFA begin
		
		$this->form = tao_helpers_form_FormFactory::getForm('users', array('topAction' => true));
		
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DFA end
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
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DFC begin
		
		if(!isset($this->options['mode'])){
			throw new Exception("Please set a mode into container options ");
		}
		
		//login field
		$loginElement = tao_helpers_form_FormFactory::getElement('login', 'Textbox');
		$loginElement->setDescription(__('Login *'));
		if($this->options['mode'] == 'add'){
			$loginElement->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('NotEmpty'),
				tao_helpers_form_FormFactory::getValidator('Callback', array(
					'class' => 'tao_models_classes_UserService', 
					'method' => 'loginAvailable', 
					'message' => __('login already exist') 
				))
			));
		}
		else{
			$loginElement->setAttributes(array('readonly' => 'true'));
		}
		$this->form->addElement($loginElement);
		
		//password field
		if($this->options['mode'] == 'add'){
			$pass1Element = tao_helpers_form_FormFactory::getElement('password1', 'Hiddenbox');
			$pass1Element->setDescription(__('Password *'));
			$pass1Element->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('NotEmpty'),
				tao_helpers_form_FormFactory::getValidator('Length', array('min' => 4))
			));
			$this->form->addElement($pass1Element);
			
			$pass2Element = tao_helpers_form_FormFactory::getElement('password2', 'Hiddenbox');
			$pass2Element->setDescription(__('Repeat password *'));
			$pass2Element->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('NotEmpty'),
				tao_helpers_form_FormFactory::getValidator('Password', array('password2_ref' => $pass1Element)),
			));
			$this->form->addElement($pass2Element);
		}
		else{
			
			$validatePasswords = true;
			if(isset($_POST['users_sent']) && isset($_POST['password1'])){
				if(empty($_POST['password1'])) {
					$validatePasswords = false;
				}
			}
			
			$pass0Element = tao_helpers_form_FormFactory::getElement('password0', 'Hidden');
			if(isset($this->data['password'])){
				$pass0Element->setValue($this->data['password']);
			}
			$this->form->addElement($pass0Element);
			
			$pass1Element = tao_helpers_form_FormFactory::getElement('password1', 'Hiddenbox');
			$pass1Element->setDescription(__('Old Password'));
			$pass1Element->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('Md5Password', array('password2_ref' => $pass0Element)),
			));
			if(!$validatePasswords){
				$pass1Element->setForcedValid();
			}
			$this->form->addElement($pass1Element);
			
			$pass2Element = tao_helpers_form_FormFactory::getElement('password2', 'Hiddenbox');
			$pass2Element->setDescription(__('New password'));
			$pass2Element->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('Length', array('min' => 4))
			));
			if(!$validatePasswords){
				$pass2Element->setForcedValid();
			}
			$this->form->addElement($pass2Element);
			
			$pass3Element = tao_helpers_form_FormFactory::getElement('password3', 'Hiddenbox');
			$pass3Element->setDescription(__('Repeat new password'));
			$pass3Element->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('Password', array('password2_ref' => $pass2Element)),
			));
			if(!$validatePasswords){
				$pass3Element->setForcedValid();
			}
			$this->form->addElement($pass3Element);
			
			$this->form->createGroup("pass_group", "Change your password", array('password0', 'password1', 'password2', 'password3'));
		}
		
		//firstname field
		$fNameElement = tao_helpers_form_FormFactory::getElement('FirstName', 'Textbox');
		$fNameElement->setDescription(__('FirstName'));
		$this->form->addElement($fNameElement);
		
		//lastname field
		$lNameElement = tao_helpers_form_FormFactory::getElement('LastName', 'Textbox');
		$lNameElement->setDescription(__('LastName'));
		$this->form->addElement($lNameElement);
		
		//email field 
		$emailElement = tao_helpers_form_FormFactory::getElement('E_Mail', 'Textbox');
		$emailElement->setDescription(__('Email'));
		$this->form->addElement($emailElement);
		
		//company field
		$companyElement = tao_helpers_form_FormFactory::getElement('Company', 'Textbox');
		$companyElement->setDescription(__('Company'));
		$this->form->addElement($companyElement);
		
		//language field
		$lgElement = tao_helpers_form_FormFactory::getElement('Deflg', 'Textbox');
		$lgElement->setDescription(__('Language *'));
		$lgElement->setAttributes(array('size' => 6));
		$lgElement->addValidators(array(
			tao_helpers_form_FormFactory::getValidator('NotEmpty'),
			tao_helpers_form_FormFactory::getValidator('Regex', array('format' => "/^[A-Z]{2,3}$/"))
		));
		if($this->options['mode'] == 'add'){
			$lgElement->setValue($GLOBALS['lang']);
		}
		$this->form->addElement($lgElement);
		
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DFC end
    }

} /* end of class tao_actions_form_Users */

?>