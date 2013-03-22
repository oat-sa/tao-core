<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php
/**
 * 
 * Enter description here ...
 * @author crp
 *
 */
class tao_update_form_DbUpdatorSettings extends tao_helpers_form_FormContainer{
	
	public function initForm()
	{
		
		$this->form = new tao_helpers_form_xhtml_Form('updateDb');
				
		$this->form->setDecorators(array(
			'element'			=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div')),
			'group'				=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-group')),
			'error'				=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-error ui-state-error ui-corner-all')),
			'help'				=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'span','cssClass' => 'form-help')),
			'actions-bottom'	=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-toolbar')),
			'actions-top'		=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-toolbar'))
		));
		
		$connectElt = tao_helpers_form_FormFactory::getElement('submit', 'Submit');
		$connectElt->setValue('Update');
		$this->form->setActions(array($connectElt), 'bottom');
	}
	
	/**
	 * Initialize the elements of the update form:
	 */
	public function initElements()
	{
		
		$moduleUpdate = tao_helpers_form_FormFactory::getElement('update', 'Hidden');
		$moduleUpdate->setValue(1);
		
		
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
		
		
		
		//Module URL
		$systemInfo = tao_install_utils_System::getInfos();
		$url = 'http://'.$systemInfo['host'];
		if(isset($systemInfo['folder']) && !empty($systemInfo['folder'])){
			$url .= '/'.$systemInfo['folder'];
		}
		
		$moduleUrlElt =  tao_helpers_form_FormFactory::getElement('module_url', 'Textbox');
		$moduleUrlElt->setDescription('URL *');
		$moduleUrlElt->setHelp("The URL to access your TAO instance with a Web Browser.");
		$moduleUrlElt->setValue($url);
		$moduleUrlElt->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('NotEmpty'),
				tao_helpers_form_FormFactory::getValidator('Url'),
				tao_helpers_form_FormFactory::getValidator('Length', array('min' => 8, 'max' => 254))
		));
		$this->form->addElement($moduleUrlElt);
		
		
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
		
		$this->form->createGroup('module', 'Module', array('module_name', 'module_host', 'module_url', 'module_mode'));
		
		
		
		$dbHostElt = tao_helpers_form_FormFactory::getElement('db_host', 'Textbox');
		$dbHostElt->setDescription('Host *');
		$dbHostElt->setValue('localhost');
		$dbHostElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$this->form->addElement($dbHostElt);
		
		$dbDriverElt =  tao_helpers_form_FormFactory::getElement('db_driver', 'Combobox');
		$dbDriverElt->setDescription('Driver');
		$dbDriverElt->setOptions(array(
				'mysql'	=> 'MySql',
				'postgres8'	=> 'PostgreSql 8.*'
		));
		$dbDriverElt->setValue('mysql');
		$this->form->addElement($dbDriverElt);
		
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
		$dbNameElt->addValidator(new tao_update_form_validators_DatabaseValidator(array(				'db_host' => $dbHostElt,
				'db_driver' => $dbDriverElt,
				'db_name' => $dbNameElt,
				'db_user' => $dbUserElt,
				'db_password' => $dbPassElt
				)));
		
		$this->form->createGroup('db', 'Database', array('db_driver', 'db_host', 'db_name_lbl', 'db_name', 'db_user', 'db_pass', 'db_test'));
		

		$this->form->addElement($moduleUpdate);
		
	}
	
}
?>
