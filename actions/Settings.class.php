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
		$myForm = new tao_helpers_form_xhtml_Form('settings');
		$myForm->setDecorator(new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div')));
		
		$defaultLangElement = new tao_helpers_form_elements_xhtml_Textbox();
		$defaultLangElement->setName('default_lang');
		$defaultLangElement->setDescription(__('Default language'));
		$defaultLangElement->setValue($GLOBALS['lang']);
		$myForm->addElement($defaultLangElement);
		
		$contentLangElement = new tao_helpers_form_elements_xhtml_Textbox();
		$contentLangElement->setName('content_lang');
		$contentLangElement->setDescription(__('Content language'));
		$contentLangElement->setValue($GLOBALS['lang']);
		$myForm->addElement($contentLangElement);
		
		$myForm->evaluate();
		
		return $myForm;
	}
}
?>