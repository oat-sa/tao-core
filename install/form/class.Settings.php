<?php
/**
 * 
 * Enter description here ...
 * @author crp
 *
 */
class tao_install_form_Settings extends tao_helpers_form_FormContainer{
	
	public function initForm(){
		
		$this->form = new tao_helpers_form_xhtml_Form('install');
				
		$this->form->setDecorators(array(
			'element'			=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div')),
			'group'				=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-group')),
			'error'				=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-error ui-state-error ui-corner-all')),
			'help'				=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'span','cssClass' => 'form-help')),
			'actions-bottom'	=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-toolbar')),
			'actions-top'		=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-toolbar'))
		));
		
		$connectElt = tao_helpers_form_FormFactory::getElement('submit', 'Submit');
		$connectElt->setValue('Install');
		$this->form->setActions(array($connectElt), 'bottom');
	}
	
	/**
	 * Initialize the elements of the install form:
	 *  - Module
	 *  - Database
	 *  - Super User
	 *  - Extra
	 */
	public function initElements(){
		
		/*
		 * Module settings elements
		 */
		
		//Module Name
		$moduleNameElt =  tao_helpers_form_FormFactory::getElement('module_name', 'Textbox');
		$moduleNameElt->setDescription('Name *');
		$moduleNameElt->setHelp("The name of the module will be used to identifiate this instance of TAO from the others. " . 
								"The module name will be used as the database name and is usually the suffix of " .
								"the module's namespace (http://host/MODULE NAME.rdf#).");
		$moduleNameElt->setValue('mytao');
		$moduleNameElt->addValidators(array(
			tao_helpers_form_FormFactory::getValidator('NotEmpty'),
		//	tao_helpers_form_FormFactory::getValidator('AlphaNum', array('allow_punctuation' => true)),
			tao_helpers_form_FormFactory::getValidator('Length', array('min' => 4, 'max' => 15))
		));
		
		$this->form->addElement($moduleNameElt);
		
		//Module Host
		$moduleHostElt =  tao_helpers_form_FormFactory::getElement('module_host', 'Textbox');
		$moduleHostElt->setDescription('Host');
		$moduleHostElt->setHelp("The host will be used in the module's namespace http://HOST/module name.rdf#)");
		$moduleHostElt->setValue($_SERVER['HTTP_HOST']);
		$moduleHostElt->addValidators(array(
			tao_helpers_form_FormFactory::getValidator('NotEmpty'),
			tao_helpers_form_FormFactory::getValidator('Url'),
			tao_helpers_form_FormFactory::getValidator('Length', array('min' => 4, 'max' => 254))
		));
		$this->form->addElement($moduleHostElt);
		
		//Module Namespace
		$moduleNSElt =  tao_helpers_form_FormFactory::getElement('module_namespace', 'Label');
		$moduleNSElt->setDescription('Namespace');
		$moduleNSElt->setHelp("The module's namespace will be used to identify the data of your module. ".
								"Each data collected by tao is identified uniquely by an URI composed by ".
								"the namespace followed by the resource identifier (NAMESPACE#resource) ");
		$moduleNSElt->setValue('http://'.$moduleHostElt->getValue().'/'.$moduleNameElt->getValue().'.rdf');
		$this->form->addElement($moduleNSElt);
		
		//Module URL
		$moduleUrlElt =  tao_helpers_form_FormFactory::getElement('module_url', 'Textbox');
		$moduleUrlElt->setDescription('Url');
		$moduleUrlElt->setHelp("The url to access to the module.");
		$moduleUrlElt->setValue($_SERVER['HTTP_HOST']);
		$moduleUrlElt->addValidators(array(
			tao_helpers_form_FormFactory::getValidator('NotEmpty'),
			tao_helpers_form_FormFactory::getValidator('Url'),
			tao_helpers_form_FormFactory::getValidator('Length', array('min' => 12, 'max' => 254))
		));
		$this->form->addElement($moduleUrlElt);

		//Module default language
		$moduleLangElt =  tao_helpers_form_FormFactory::getElement('module_lang', 'Combobox');
		$moduleLangElt->setDescription('Default language');
		$moduleLangElt->setHelp('The default language will be used when the language parameters are not specified for the graphical interface and the data.');
		$moduleLangElt->setOptions(array(
			'EN'	=> 'English',
			'FR'	=> 'French',
			'DE'	=> 'German',
			'LU'	=> 'Luxemburgish'
		));
		$moduleLangElt->setValue('EN');
		$this->form->addElement($moduleLangElt);
		
		//Module Deployment mode
		$moduleModeElt =  tao_helpers_form_FormFactory::getElement('module_mode', 'Combobox');
		$moduleModeElt->setDescription('Deployment mode');
		$moduleModeElt->setHelp("The deployment mode allow and deny access to resources regarding the needs of the pltaform.".
									"The test & development mode will enables the debugs tools, the unit tests, and the access to all the resources.".
									"the production mode is focused on the security and allow only the required resources to run TAO.");
		$moduleModeElt->setOptions(array(
			'debug' 		=> 'Test / Development',
			'production'	=> 'Production'
		));
		$moduleModeElt->setValue('production');
		$this->form->addElement($moduleModeElt);
		
		$this->form->createGroup('module', 'Module', array('module_name', 'module_host', 'module_namespace', 'module_url', 'module_lang', 'module_mode'));
	
		
		/*
		 * Database settings elements
		 */
		
		//Databse Driver
		$dbDriverElt =  tao_helpers_form_FormFactory::getElement('db_driver', 'Combobox');
		$dbDriverElt->setDescription('Driver');
		$dbDriverElt->setOptions(array(
			'mysql'	=> 'MySql'
		));
		$dbDriverElt->setValue('mysql');
		$this->form->addElement($dbDriverElt);
		
		//Database Host
		$dbHostElt =  tao_helpers_form_FormFactory::getElement('db_host', 'Textbox');
		$dbHostElt->setDescription('Host');
		$dbHostElt->setValue('localhost');
		$this->form->addElement($dbHostElt);
		
		//Database Name
	//	$dbNameElt =  tao_helpers_form_FormFactory::getElement('db_name', 'Label');
		$dbNameElt =  tao_helpers_form_FormFactory::getElement('db_name', 'Textbox');
		$dbNameElt->setDescription('Name');
		$dbNameElt->setValue($moduleNameElt->getValue());
		$this->form->addElement($dbNameElt);
		
		//Database User
		$dbUserElt =  tao_helpers_form_FormFactory::getElement('db_user', 'Textbox');
		$dbUserElt->setDescription('User');
		$this->form->addElement($dbUserElt);
		
		//Database Password
		$dbPassElt =  tao_helpers_form_FormFactory::getElement('db_pass', 'Hiddenbox');
		$dbPassElt->setDescription('Password');
		$this->form->addElement($dbPassElt);
		
		$this->form->createGroup('db', 'Database', array('db_driver', 'db_host', 'db_name', 'db_user', 'db_pass'));
	
		
		/*
		 * Super User settings element
		 */
		
		//Super User LastName
		$userLNameElt	= tao_helpers_form_FormFactory::getElement('user_lastname', 'Textbox');
		$userLNameElt->setDescription('LastName');
		$this->form->addElement($userLNameElt);
		
		//Super User FirstName
		$userFNameElt	= tao_helpers_form_FormFactory::getElement('user_firstname', 'Textbox');
		$userFNameElt->setDescription('FirstName');
		$this->form->addElement($userFNameElt);
		
		//Super User Login
		$userLoginElt	= tao_helpers_form_FormFactory::getElement('user_login', 'Textbox');
		$userLoginElt->setDescription('Login');
		$this->form->addElement($userLoginElt);
		
		//Super User password
		$userPass0Elt	= tao_helpers_form_FormFactory::getElement('user_pass0', 'Hidden');
		$this->form->addElement($userPass0Elt);
		
		$userPass1Elt	= tao_helpers_form_FormFactory::getElement('user_pass1', 'Hiddenbox');
		$userPass1Elt->setDescription('Password');
		$this->form->addElement($userPass1Elt);
		
		$userPass2Elt	= tao_helpers_form_FormFactory::getElement('user_pass2', 'Hiddenbox');
		$userPass2Elt->setDescription('Confirm password');
		$this->form->addElement($userPass2Elt);
		
		//Super User Email
		$userEmailElt	= tao_helpers_form_FormFactory::getElement('user_email', 'Textbox');
		$userPass0Elt->setDescription('Email');
		$this->form->addElement($userEmailElt);
		
		$this->form->createGroup('user', 'Super User', array('user_lastname', 'user_firstname', 'user_login', 'user_pass1', 'user_pass2', 'user_email'));
	}
	
}
?>