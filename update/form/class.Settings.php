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
