<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/actions/form/class.Search.php
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
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002494-includes begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002494-includes end

/* user defined constants */
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002494-constants begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002494-constants end

/**
 * Short description of class tao_actions_form_Search
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Search
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
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000249F begin
        
    	(isset($this->options['name'])) ? $name = $this->options['name'] : $name = ''; 
    	if(empty($name)){
			$name = 'form_'.(count(self::$forms)+1);
		}
		unset($this->options['name']);
			
		$this->form = tao_helpers_form_FormFactory::getForm($name, $this->options);
		
		//search action in toolbar
		$searchELt = tao_helpers_form_FormFactory::getElement('search', 'Free');
		$searchELt->setValue("<a href='#' class='form-submiter' ><img src='".TAOBASE_WWW."/img/search.png'  /> ".__('Search')."</a>");
		$this->form->setActions(array($searchELt), 'top');
		
		$searchBtnElt = tao_helpers_form_FormFactory::getElement('search-button', 'Submit');
		$searchBtnElt->setValue(__('Search'));
		$this->form->setActions(array($searchBtnElt), 'bottom');
    	
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000249F end
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
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:00000000000024A1 begin
        
    	$chainingElt = tao_helpers_form_FormFactory::getElement('chaining', 'Radiobox');
		$chainingElt->setDescription(__('Filtering mode'));
		$chainingElt->setOptions(array('and' => __('Exclusive (AND)'), 'or' =>  __('Inclusive (OR)')));
		$chainingElt->setValue('and');
		$this->form->addElement($chainingElt);
		
		$options = array();
		foreach($GLOBALS['available_langs'] as $langCode){
			$options[$langCode] = __($langCode);
		}
		$langElt = tao_helpers_form_FormFactory::getElement('lang', 'Combobox');
		$langElt->setDescription(__('Language'));
		$langElt->setOptions($options);
		$this->form->addElement($langElt);
		
		$this->form->createGroup('params', __('Options'), array('chaining', 'lang'));
		
		
		$filters = array();
		
		$descElt = tao_helpers_form_FormFactory::getElement('desc', 'Label');
		$descElt->setValue(__('Use the * character to replace any string'));
		$this->form->addElement($descElt);
		$filters[] = 'desc';
		
		$defaultProperties 	= $this->getDefaultProperties();
		$classProperties	= tao_helpers_form_GenerisFormFactory::getClassProperties($this->clazz, $this->getTopClazz());
		
		$properties = array_merge($defaultProperties, $classProperties);
		
		(isset($this->options['recursive'])) ? $recursive = $this->options['recursive'] : $recursive = false;
		if($recursive){
			foreach($this->clazz->getSubClasses(true) as $subClass){
				$properties = array_merge($subClass->getProperties(false), $properties);
			}
		}
		
		foreach($properties as $property){
	
			$element = tao_helpers_form_GenerisFormFactory::elementMap($property);
			if( ! is_null($element) && 
				! $element instanceof tao_helpers_form_elements_Authoring && 
				! $element instanceof tao_helpers_form_elements_Hiddenbox &&
				! $element instanceof tao_helpers_form_elements_Hidden ){
				
				if($element instanceof tao_helpers_form_elements_MultipleElement){
					$newElement = tao_helpers_form_FormFactory::getElement($element->getName(), 'Checkbox');
					$newElement->setDescription($element->getDescription());
					$newElement->setOptions($element->getOptions());
					$element = $newElement;
				}
				if($element instanceof tao_helpers_form_elements_Htmlarea){
					$newElement = tao_helpers_form_FormFactory::getElement($element->getName(), 'Textarea');
					$newElement->setDescription($element->getDescription());
					$element = $newElement;
				}
				
				$this->form->addElement($element);
				$filters[] = $element->getName();
			}
		}
		$this->form->createGroup('filters', __('Filters'), $filters);
    	
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:00000000000024A1 end
    }

} /* end of class tao_actions_form_Search */

?>