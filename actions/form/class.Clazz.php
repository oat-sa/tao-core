<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/actions/form/class.Clazz.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 06.07.2010, 15:23:17 with ArgoUML PHP module 
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
 * This container enables gives you tools to create a form from ontology
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/actions/form/class.Generis.php');

/* user defined includes */
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002490-includes begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002490-includes end

/* user defined constants */
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002490-constants begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002490-constants end

/**
 * Short description of class tao_actions_form_Clazz
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Clazz
    extends tao_actions_form_Generis
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Initialize the form
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initForm()
    {
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:00000000000024A7 begin
        
    	(isset($this->options['name'])) ? $name = $this->options['name'] : $name = ''; 
    	if(empty($name)){
			$name = 'form_'.(count(self::$forms)+1);
		}
		unset($this->options['name']);
			
		$this->form = tao_helpers_form_FormFactory::getForm($name, $this->options);
			
		//add property action in toolbar
		$topActions = tao_helpers_form_FormFactory::getCommonActions('top');
		$propertyElt = tao_helpers_form_FormFactory::getElement('property', 'Free');
		$propertyElt->setValue(" | <a href='#' class='property-adder'><img src='".TAOBASE_WWW."/img/prop_add.png'  /> ".__('Add property')."</a>");
		$topActions[] = $propertyElt;
		$this->form->setActions($topActions, 'top');
		
		//set bottom property actions
		$bottomActions = tao_helpers_form_FormFactory::getCommonActions('bottom');
		$addPropElement = tao_helpers_form_FormFactory::getElement('propertyAdder', 'Button');
		$addPropElement->addAttribute('class', 'property-adder');
		$addPropElement->setValue(__('Add a new property'));
		$bottomActions[] = $addPropElement;
		$this->form->setActions($bottomActions, 'bottom');
    	
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:00000000000024A7 end
    }

    /**
     * Initialize the form elements
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initElements()
    {
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:00000000000024A9 begin
        
    	$clazz = $this->getClazz();

    	//add a group form for the class edition 
		$elementNames = array();
		foreach(tao_helpers_form_GenerisFormFactory::getDefaultProperties()  as $property){
			
			//map properties widgets to form elments 
			$element = tao_helpers_form_GenerisFormFactory::elementMap($property);
			if(!is_null($element)){
				
				//take property values to populate the form
				$values = $clazz->getPropertyValuesCollection($property);
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
				$element->setName('class_'.$element->getName());
				$this->form->addElement($element);
				$elementNames[] = $element->getName();
			}
		}
		if(count($elementNames) > 0){
			$this->form->createGroup('class', "<img src='".TAOBASE_WWW."img/class.png' /> ".__('Class').": ".$clazz->getLabel(), $elementNames);
		}
		
		//add an hidden elt for the class uri
		$classUriElt = tao_helpers_form_FormFactory::getElement('classUri', 'Hidden');
		$classUriElt->setValue(tao_helpers_Uri::encode($clazz->uriResource));
		$this->form->addElement($classUriElt);
		
		$session = core_kernel_classes_Session::singleton();
		$localNamespace = $session->getNameSpace();
		
		//class properties edition: add a group form for each property
		$classProperties = tao_helpers_form_GenerisFormFactory::getClassProperties($clazz, $this->getTopClazz());
		$i = 0;
		foreach($classProperties as $classProperty){
			$i++;
			$useEditor = false;
			$parentProp = true;
			$domains = $classProperty->getDomain();
			foreach($domains->getIterator() as $domain){
				if($domain->uriResource == $clazz->uriResource){
					$parentProp = false;
					
					//@todo use the getPrivileges method once implemented
					if(preg_match("/^".preg_quote($localNamespace, '/')."/", $classProperty->uriResource)){
						$useEditor = true;
					}
					break;
				}
			}
			
			if($useEditor){

				//instanciate a property form
				$propFormContainer = new tao_actions_form_Property($clazz, $classProperty, array('index' => $i));
				$propForm = $propFormContainer->getForm();
				
				//and get its elements and groups
				$this->form->setElements(array_merge($this->form->getElements(), $propForm->getElements()));
				$this->form->setGroups(array_merge($this->form->getGroups(), $propForm->getGroups()));
				
				unset($propForm);
				unset($propFormContainer);
			}
			else if($parentProp){
				$domainElement = tao_helpers_form_FormFactory::getElement('parentProperty'.$i, 'Free');
				$value = __("Edit property into parent class ");
				foreach($domains->getIterator() as $domain){
					$value .= "<a  href='#' onclick='GenerisTreeClass.selectTreeNode(\"".tao_helpers_Uri::encode($domain->uriResource)."\");' >".$domain->getLabel()."</a> ";
				}
				$domainElement->setValue($value);
				$this->form->addElement($domainElement);
				
				$this->form->createGroup("parent_property_{$i}", "<img src='".TAOBASE_WWW."img/prop_orange.png' /> ".__('Property')." #".($i).": ".$classProperty->getLabel(), array('parentProperty'.$i));
			}
			else{
				$roElement = tao_helpers_form_FormFactory::getElement('roProperty'.$i, 'Free');
				$roElement->setValue(__("You cannot modify this property"));
				$this->form->addElement($roElement);
				
				$this->form->createGroup("ro_property_{$i}", "<img src='".TAOBASE_WWW."img/prop_red.png' /> ".__('Property')." #".($i).": ".$classProperty->getLabel(), array('roProperty'.$i));
			}
			
		}
    	
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:00000000000024A9 end
    }

} /* end of class tao_actions_form_Clazz */

?>