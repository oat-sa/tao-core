<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/actions/form/class.Translate.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 06.07.2010, 11:24:26 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_actions_form_Instance
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/actions/form/class.Instance.php');

/* user defined includes */
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248E-includes begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248E-includes end

/* user defined constants */
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248E-constants begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248E-constants end

/**
 * Short description of class tao_actions_form_Translate
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Translate
    extends tao_actions_form_Instance
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
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000249A begin
        
    	parent::initForm();
    	$this->form->setName('translate_'.$this->form->getName());
		$this->form->setActions(tao_helpers_form_FormFactory::getCommonActions('top'), 'top');
    	
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000249A end
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
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000249D begin
        
    	parent::initElements();
    	
    	$elements = $this->form->getElements();
    	$this->form->setElements(array());
    	
    	
    	$currentLangElt = tao_helpers_form_FormFactory::getElement('current_lang', 'Textbox');
		$currentLangElt->setDescription(__('Current language'));
		$currentLangElt->setAttributes(array('readonly' => 'true'));
		$currentLangElt->setValue(__(core_kernel_classes_Session::singleton()->getLg()));	//API lang /data lang
		$this->form->addElement($currentLangElt);
		
		$options = array();
		foreach($GLOBALS['available_langs'] as $langCode){
			$options[$langCode] = __($langCode);
		}
		$dataLangElement = tao_helpers_form_FormFactory::getElement('translate_lang', 'Combobox');
		$dataLangElement->setDescription(__('Translate to'));
		$dataLangElement->setOptions($options);
		$dataLangElement->setEmptyOption(__('Select a language'));
		$dataLangElement->addValidator( tao_helpers_form_FormFactory::getValidator('NotEmpty') );
		$this->form->addElement($dataLangElement);
		
		$this->form->createGroup('translation_info', __('Translation parameters'), array('current_lang', 'translate_lang'));
    	
		$dataGroup = array();
		foreach($elements as $element){
			
			if( $element instanceof tao_helpers_form_elements_Hidden ||
				$element->getName() == 'uri' || $element->getName() == 'classUri'){
					
				$this->form->addElement($element);
				
			}
			else{
				
				$propertyUri = tao_helpers_Uri::decode($element->getName());
				$property = new core_kernel_classes_Property($propertyUri);
				
				//translate only language dependent properties or Labels
				//supported widget are: Textbox, TextArea, HtmlArea
				//@todo support other widgets
				if(	( $property->isLgDependent() && 
					  ($element instanceof tao_helpers_form_elements_Textbox ||
					   $element instanceof tao_helpers_form_elements_TextArea ||
					   $element instanceof tao_helpers_form_elements_HtmlArea
					  ) ) ||
					$propertyUri == RDFS_LABEL){	
				
					$translatedElt = clone $element;
					
					$element->setName('view_'.$element->getName());
					$element->setAttributes(array('readonly' => 'true'));
					$this->form->addElement($element);
					
					$translatedElt->setDescription(' ');
					$translatedElt->setValue('');
					$this->form->addElement($translatedElt);
					
					$dataGroup[] = $element->getName();
					$dataGroup[] = $translatedElt->getName();
				}
			}
		}
		
		$this->form->createGroup('translation_form', __('Translate'), $dataGroup);
		
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000249D end
    }

} /* end of class tao_actions_form_Translate */

?>