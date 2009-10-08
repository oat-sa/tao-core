<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/class.GenerisFormFactory.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 07.10.2009, 14:26:27 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 10-13-1-45--70bace43:123ffff90e9:-8000:00000000000018CC-includes begin
// section 10-13-1-45--70bace43:123ffff90e9:-8000:00000000000018CC-includes end

/* user defined constants */
// section 10-13-1-45--70bace43:123ffff90e9:-8000:00000000000018CC-constants begin
// section 10-13-1-45--70bace43:123ffff90e9:-8000:00000000000018CC-constants end

/**
 * Short description of class tao_helpers_form_GenerisFormFactory
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form
 */
class tao_helpers_form_GenerisFormFactory
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute RENDER_MODE_XHTML
     *
     * @access public
     * @var string
     */
    const RENDER_MODE_XHTML = 'xhtml';

    /**
     * Short description of attribute TOP_LEVEL_CLASS_URI
     *
     * @access public
     * @var string
     */
    const TOP_LEVEL_CLASS_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject';

    /**
     * Short description of attribute forms
     *
     * @access private
     * @var array
     */
    private static $forms = array();

    // --- OPERATIONS ---

    /**
     * Short description of method createFromClass
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Class clazz
     * @param  Resource instance
     * @param  string renderMode
     * @return tao_helpers_form_Form
     */
    public static function createFromClass( core_kernel_classes_Class $clazz,  core_kernel_classes_Resource $instance = null, $renderMode = 'xhtml')
    {
        $returnValue = null;

        // section 10-13-1-45--70bace43:123ffff90e9:-8000:00000000000018CD begin
		
		if(!is_null($clazz)){
			
			(strlen(trim($clazz->getLabel())) > 0) ? $name = strtolower($clazz->getLabel()) : $name = 'form_'.(count(self::$forms)+1);
			
			//use the right implementation (depending the render mode)
			switch($renderMode){
				case self::RENDER_MODE_XHTML:
					$myForm = new tao_helpers_form_xhtml_Form($name);
					$myForm->setDecorator(new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div')));
					$hiddenEltClass = 'tao_helpers_form_elements_xhtml_Hiddenbox';
					break;
				default: 
					return null;
			}
			
			//@todo take the properties ahead the class recursivly till the top level class  
			foreach($clazz->getProperties()->getIterator() as $property){
				
				//map properties widgets to form elments 
				$element = self::elementMap($property, $renderMode);
				if(!is_null($element)){
					
					//use uri as element name					
					$element->setName( tao_helpers_Uri::encode($property->uriResource));

					//use the property label as element description
					(strlen(trim($property->getLabel())) > 0) ? $propDesc = strtolower($property->getLabel()) : $propDesc = 'element_'.(count($myForm->getElements())+1);	
					$element->setDescription($propDesc);
					
					//multi elements use the property range as options
					if(method_exists($element, 'setOptions')){
						$range = $property->getRange();
						if($range != null){
							$range = new core_kernel_classes_Class($range);
							$options = array();
							foreach($range->getInstances()->getIterator() as $rangeInstance){
								$options[ tao_helpers_Uri::encode($rangeInstance->uriResource) ] = $rangeInstance->getLabel();
							}
							$element->setOptions($options);
						}
					}
					
					//take instance values to populate the form
					if(!is_null($instance)){
						$values = $instance->getPropertyValuesCollection($property);
						foreach($values->getIterator() as $value){
							if(!is_null($value)){
								if($value instanceof core_kernel_classes_Resource){
									$element->setValue($value->uriResource);
								}
								if($value instanceof core_kernel_classes_Literal){
									$element->setValue((string)$value);
								}
							}
						}
					}
					$myForm->addElement($element);
				}
			}
			
			//add an hidden elt for the class uri
			$classUriElt = new $hiddenEltClass('classUri');
			$classUriElt->setValue(tao_helpers_Uri::encode($clazz->uriResource));
			$myForm->addElement($classUriElt);
			
			if(!is_null($instance)){
				//add an hidden elt for the instance Uri
				$instanceUriElt = new $hiddenEltClass('uri');
				$instanceUriElt->setValue(tao_helpers_Uri::encode($instance->uriResource));
				$myForm->addElement($instanceUriElt);
			}
			
			//form data evaluation
			$myForm->evaluate();		
				
			self::$forms[$name] = $myForm;
			$returnValue = self::$forms[$name];
		}
		
        // section 10-13-1-45--70bace43:123ffff90e9:-8000:00000000000018CD end

        return $returnValue;
    }

    /**
     * Short description of method elementMap
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Property property
     * @param  string renderMode
     * @return tao_helpers_form_FormElement
     */
    public static function elementMap( core_kernel_classes_Property $property, $renderMode = 'xhtml')
    {
        $returnValue = null;

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001937 begin
		
		$widget = ucfirst(strtolower(substr($property->getWidget(), strrpos($property->getWidget(), '#') + 1 )));
		$elementClass = 'tao_helpers_form_elements_'.$renderMode.'_'.$widget;
		if(!class_exists($elementClass)){
			return null;
		}
		$returnValue = new $elementClass();
		
		if($returnValue->getWidget() != $property->getWidget()){
			return null;
		}
		
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001937 end

        return $returnValue;
    }

} /* end of class tao_helpers_form_GenerisFormFactory */

?>