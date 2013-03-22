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
class tao_update_form_Settings extends tao_helpers_form_FormContainer{
	
	public function initForm()
	{
		
		$this->form = new tao_helpers_form_xhtml_Form('update');
				
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
		$this->form->addElement($moduleUpdate);
		
	}
	
}
?>
