<?php
/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class Settings extends CommonModule {

	public function index(){
		
		$myForm = $this->initSettingsForm();
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
			}
		}
		$this->setData('myForm', $myForm->render());
		$this->setView('settings.tpl');
	}
	
	private function initSettingsForm(){
		
		
		$myForm = tao_helpers_form_FormFactory::getForm('users', array('noRevert' => true));
		
		$uiLangElement = tao_helpers_form_FormFactory::getElement('ui_lang', 'Textbox');
		$uiLangElement->setDescription(__('Interface language'));
		$myForm->addElement($uiLangElement);
		
		$dataLangElement = tao_helpers_form_FormFactory::getElement('data_lang', 'Textbox');
		$dataLangElement->setDescription(__('Data language'));
		$myForm->addElement($dataLangElement);
		
		$myForm->evaluate();
		
		return $myForm;
	}
}
?>