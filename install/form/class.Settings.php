<?php

/**
 * 
 * The Installation form of TAO.
 * @author crp
 *
 */
class tao_install_form_Settings extends tao_helpers_form_FormContainer{
	
	public function initForm()
	{ 
		
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
	public function initElements()
	{
		
		/*
		 * Module settings elements
		 */
		
		//Module Name
		$moduleNameElt =  tao_helpers_form_FormFactory::getElement('module_name', 'Textbox');
		$moduleNameElt->setDescription('Name *');
		$moduleNameElt->setHelp("The name of the module will be used to identify this instance of TAO from the others. " . 
								"The module name will be used as the database name and is the suffix of " .
								"the module namespace (http://host/MODULE NAME.rdf#).");
		$moduleNameElt->setValue('mytao');
		$moduleNameElt->addValidators(array(
			tao_helpers_form_FormFactory::getValidator('NotEmpty'),
			tao_helpers_form_FormFactory::getValidator('Length', array('min' => 1, 'max' => 63))
		));
		
		$this->form->addElement($moduleNameElt);
		
		//Module Host
		$moduleHostElt =  tao_helpers_form_FormFactory::getElement('module_host', 'Textbox');
		$moduleHostElt->setDescription('Host *');
		$moduleHostElt->setHelp("The host will be used in the module's namespace (http://HOST/module name.rdf#)." .  
													 "It must not be necessarily the host name of your web server.");
        $moduleHostElt->setValue($_SERVER['HTTP_HOST']);
		$moduleHostElt->addValidators(array(
			tao_helpers_form_FormFactory::getValidator('NotEmpty'),
			tao_helpers_form_FormFactory::getValidator('Url'),
			tao_helpers_form_FormFactory::getValidator('Length', array('min' => 1, 'max' => 254))
		));
		$this->form->addElement($moduleHostElt);
		
		//Module Namespace Label
		// ($moduleNsLblElt designed only to show information to user)
		$NSValue = 'http://'.$this->form->getValue('module_host').'/'.$this->form->getValue('module_name').'.rdf';
		$moduleNSLblElt =  tao_helpers_form_FormFactory::getElement('module_namespace_lbl', 'Label');
		$moduleNSLblElt->setDescription('Namespace');
		$moduleNSLblElt->setHelp("The module's namespace will be used to identify the data stored by your module. ".
								"Each data collected by TAO is identified uniquely by a URI (Uniform Resource Identifier) composed by ".
								"the module namespace followed by the resource identifier (NAMESPACE#resource-identifier) ");
		$moduleNSLblElt->setValue($NSValue);
		$moduleNSLblElt->setAttribute('id', 'module_namespace_lbl');
		$this->form->addElement($moduleNSLblElt);
		
		$moduleNSElt = tao_helpers_form_FormFactory::getElement('module_namespace', 'Hidden');
		$moduleNSElt->setValue($NSValue);
		$this->form->addElement($moduleNSElt);
		
		//Module URL
        $systemInfo = tao_install_utils_System::getInfos();
        $url = 'http://'.$systemInfo['host'];
        if(isset($systemInfo['folder']) && !empty($systemInfo['folder'])){
        	$url .= '/'.$systemInfo['folder'];
        }
                
		$moduleUrlElt =  tao_helpers_form_FormFactory::getElement('module_url', 'Textbox');
		$moduleUrlElt->setDescription('URL *');
		$moduleUrlElt->setHelp("The URL (Uniform Resource Locator) to access your TAO instance with a Web Browser.");
		$moduleUrlElt->setValue($url);
		$moduleUrlElt->addValidators(array(
			tao_helpers_form_FormFactory::getValidator('NotEmpty'),
			tao_helpers_form_FormFactory::getValidator('Url'),
			tao_helpers_form_FormFactory::getValidator('Length', array('min' => 8, 'max' => 254))
		));
		$this->form->addElement($moduleUrlElt);

		//Module default language
		$moduleLangElt =  tao_helpers_form_FormFactory::getElement('module_lang', 'Combobox');
		$moduleLangElt->setDescription('Default language');
		$moduleLangElt->setHelp('The default language will be used when the language parameters are not specified for the GUI (Graphical User Interface) and the data.');
		$moduleLangElt->setOptions(tao_install_utils_System::getAvailableLocales(dirname(__FILE__) . '/../../locales'));
		$moduleLangElt->setValue('EN');
		$this->form->addElement($moduleLangElt);
		
		//Module Deployment mode
		$moduleModeElt =  tao_helpers_form_FormFactory::getElement('module_mode', 'Combobox');
		$moduleModeElt->setDescription('Deployment mode');
		$moduleModeElt->setHelp("The deployment mode restricts access to TAO functionalities and settings that are not relevant in production context like for example debugging features. ".
									"The Test & Development mode will enable the debuging tools, unit tests, and a free access to every resources. ".
									"the Production mode is focused on the security and allows to run TAO in a production environment.");
		$moduleModeElt->setOptions(array(
			'debug' 		=> 'Test / Development',
			'production'	=> 'Production'
		));
		$moduleModeElt->setValue('debug');
		$this->form->addElement($moduleModeElt);
        
        // Sample data
        $sampleDataElt = tao_helpers_form_FormFactory::getElement('import_local', 'Checkbox');
        $sampleDataElt->setDescription('Sample data');
        $sampleDataElt->setOptions(array('on' => 'Import sample data such as demo items.'));
        $sampleDataElt->setValue('on');
        $this->form->addElement($sampleDataElt);
		
		$this->form->createGroup('module', 'Module', array('module_name', 'module_host', 'module_namespace_lbl', 'module_namespace', 'module_url', 'module_lang', 'module_mode', 'import_local'));
	
		
		/*
		 * Database settings elements
		 */
		
		//Databse Driver
		$dbDriverElt =  tao_helpers_form_FormFactory::getElement('db_driver', 'Combobox');
		$dbDriverElt->setDescription('Driver');
		$dbDriverElt->setOptions(array(
			'mysql'	=> 'MySql',
			'postgres8'	=> 'PostgreSql 8.*'
		));
		$dbDriverElt->setValue('mysql');
		$this->form->addElement($dbDriverElt);
		
		//Database Host
		$dbHostElt = tao_helpers_form_FormFactory::getElement('db_host', 'Textbox');
		$dbHostElt->setDescription('Host *');
		$dbHostElt->setValue('localhost');
		$dbHostElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$this->form->addElement($dbHostElt);
		
		//Database Name
		// ($dbNameLblElt designed only to show information to user)
		$dbNameLblElt = tao_helpers_form_FormFactory::getElement('db_name_lbl', 'Label');
		$dbNameLblElt->setDescription('Database name');
		$dbNameLblElt->setValue($this->form->getValue('module_name'));
		$dbNameLblElt->setHelp('The Database name corresponds to the Module name.');
		$dbNameLblElt->setAttribute('id', 'db_name_lbl');
		$this->form->addElement($dbNameLblElt);
		
		$dbNameElt = tao_helpers_form_FormFactory::getElement('db_name', 'Hidden');
		$dbNameElt->setValue($this->form->getValue('module_name'));
		$this->form->addElement($dbNameElt);
		
		//Delete database if it is existing
		$dbOverwriteDbElt = tao_helpers_form_FormFactory::getElement('db_override', 'Checkbox');
		$dbOverwriteDbElt->setDescription('Overwrite');
		$dbOverwriteDbElt->setOptions(array("on" => "Overwrite database if it exists."));
		$this->form->addElement($dbOverwriteDbElt);
		
		//Database User
		$dbUserElt =  tao_helpers_form_FormFactory::getElement('db_user', 'Textbox');
		$dbUserElt->setDescription('User *');
		$dbUserElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$this->form->addElement($dbUserElt);
		
		//Database Password
		$dbPassElt =  tao_helpers_form_FormFactory::getElement('db_pass', 'Hiddenbox');
		$dbPassElt->setDescription('Password ');
		$this->form->addElement($dbPassElt);
		
		$dbTestElt = tao_helpers_form_FormFactory::getElement('db_test', 'Button');
		$dbTestElt->setDescription(' ');
		$dbTestElt->setValue('Test connection ...');
		$this->form->addElement($dbTestElt);
		
		// Initialize db validator
		$dbNameLblElt->addValidator(new tao_install_form_validators_DatabaseValidator(array('db_host' => $dbHostElt,
																							'db_driver' => $dbDriverElt,
																							'db_name' => $dbNameElt,
																							'db_user' => $dbUserElt,
																							'db_password' => $dbPassElt,
																							'db_override' => $dbOverwriteDbElt)));
				
		$this->form->createGroup('db', 'Database', array('db_driver', 'db_host', 'db_name_lbl', 'db_override', 'db_name', 'db_user', 'db_pass', 'db_test'));
	
		
		/*
		 * Super User settings element
		 */
		
		//Super User LastName
		$userLNameElt	= tao_helpers_form_FormFactory::getElement('user_lastname', 'Textbox');
		$userLNameElt->setDescription('Last name');
		$this->form->addElement($userLNameElt);
		
		//Super User FirstName
		$userFNameElt	= tao_helpers_form_FormFactory::getElement('user_firstname', 'Textbox');
		$userFNameElt->setDescription('First name');
		$this->form->addElement($userFNameElt);
		
		//Super User Login
		$userLoginElt	= tao_helpers_form_FormFactory::getElement('user_login', 'Textbox');
		$userLoginElt->setDescription('Login *');
		$userLoginElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$userLoginElt->addValidator(tao_helpers_form_FormFactory::getValidator('Length', array('min' => 3)));
		$this->form->addElement($userLoginElt);
		
		//Super User password
		$userPass0Elt	= tao_helpers_form_FormFactory::getElement('user_pass0', 'Hidden');
		$this->form->addElement($userPass0Elt);
		
		$userPass1Elt	= tao_helpers_form_FormFactory::getElement('user_pass1', 'Hiddenbox');
		$userPass1Elt->setDescription('Password *');
		$userPass1Elt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$userPass1Elt->addValidator(tao_helpers_form_FormFactory::getValidator('Length', array('min' => 3)));
		$this->form->addElement($userPass1Elt);
		
		$userPass2Elt	= tao_helpers_form_FormFactory::getElement('user_pass2', 'Hiddenbox');
		$userPass2Elt->setDescription('Confirm password *');
		$userPass2Elt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$userPass2Elt->addValidator(tao_helpers_form_FormFactory::getValidator('Password', array('password2_ref' => $userPass1Elt)));
		$this->form->addElement($userPass2Elt);
		
		//Super User Email
		$userEmailElt	= tao_helpers_form_FormFactory::getElement('user_email', 'Textbox');
		$userEmailElt->setDescription('Email');
		$this->form->addElement($userEmailElt);
		
		$this->form->createGroup('user', 'Super User', array('user_lastname', 'user_firstname', 'user_login', 'user_pass1', 'user_pass2', 'user_email'));
	}
	
}
?>
