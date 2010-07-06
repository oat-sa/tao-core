<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/actions/form/class.Property.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 06.07.2010, 14:18:42 with ArgoUML PHP module 
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
 * include tao_actions_form_Generis
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/actions/form/class.Generis.php');

/* user defined includes */
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002492-includes begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002492-includes end

/* user defined constants */
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002492-constants begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002492-constants end

/**
 * Short description of class tao_actions_form_Property
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Property
    extends tao_actions_form_Generis
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
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:00000000000024A3 begin
        
    	(isset($this->options['name'])) ? $name = $this->options['name'] : $name = ''; 
    	if(empty($name)){
			$name = 'form_'.(count(self::$forms)+1);
		}
		unset($this->options['name']);
			
		$this->form = tao_helpers_form_FormFactory::getForm($name, $this->options);
    	
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:00000000000024A3 end
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
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:00000000000024A5 begin
        
    	$property = new core_kernel_classes_Property($this->instance->uriResource);
    	
    	(isset($this->options['index'])) ? $index = $this->options['index'] : $index = 1;
    	
		$propertyProperties = array_merge(
			$this->getDefaultProperties(), 
			tao_helpers_form_GenerisFormFactory::getPropertyProperties('simple')
		);
    	
    	$elementNames = array();
		foreach($propertyProperties as $propertyProperty){
		
			//map properties widgets to form elments 
			$element = tao_helpers_form_GenerisFormFactory::elementMap($propertyProperty);
			
			if(!is_null($element)){
				//take property values to populate the form
				$values = $property->getPropertyValuesCollection($propertyProperty);
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
				$element->setName("property_{$index}_{$element->getName()}");
				$this->form->addElement($element);
				$elementNames[] = $element->getName();
			}
		}
		
		//build the type list from the "widget/range to type" map
		$typeElt = tao_helpers_form_FormFactory::getElement("property_{$index}_type", 'Combobox');
		$typeElt->setDescription(__('Type'));
		$typeElt->addAttribute('class', 'property-type');
		$typeElt->setEmptyOption(' --- '.__('select').' --- ');
		$options = array();
		$checkRange = false;
		foreach(tao_helpers_form_GenerisFormFactory::getPropertyMap() as $typeKey => $map){
			$options[$typeKey] = $map['title'];
			if($property->getWidget()){
				if($property->getWidget()->uriResource == $map['widget']){
					$typeElt->setValue($typeKey);
					$checkRange = is_null($map['range']);
				}
			}
		}
		$typeElt->setOptions($options);
		$this->form->addElement($typeElt);
		$elementNames[] = $typeElt->getName();
		
		//list drop down
		$listService = tao_models_classes_ServiceFactory::get("tao_models_classes_ListService");
			
		$listElt = tao_helpers_form_FormFactory::getElement("property_{$index}_range", 'Combobox');
		$listElt->setDescription(__('List values'));
		$listElt->addAttribute('class', 'property-listvalues');
		$listElt->setEmptyOption(' --- '.__('select').' --- ');
		$listOptions = array();
		foreach($listService->getLists() as $list){
			$listOptions[tao_helpers_Uri::encode($list->uriResource)] = $list->getLabel();
			if($property->getRange()->uriResource == $list->uriResource){
				$listElt->setValue($list->uriResource);
			}
		}	
		$listOptions['new'] = ' + '.__('Add / Edit lists');
		$listElt->setOptions($listOptions);
		if($checkRange){
			$listElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		}
		$this->form->addElement($listElt);
		$elementNames[] = $listElt->getName();
		
		//add a delete button 
		$deleteElt = tao_helpers_form_FormFactory::getElement("propertyDeleter{$index}", 'Button');
		$deleteElt->addAttribute('class', 'property-deleter');
		$deleteElt->setValue(__('Delete property'));
		$this->form->addElement($deleteElt);
		$elementNames[] = $deleteElt->getName();
		
		//add an hidden element with the mode (simple)
		$modeElt = tao_helpers_form_FormFactory::getElement("propertyMode{$index}", 'Hidden');
		$modeElt->setValue('simple');
		$this->form->addElement($modeElt);
		$elementNames[] = $modeElt->getName();
		
		if(count($elementNames) > 0){
			$groupTitle = "<img src='".TAOBASE_WWW."img/prop_green.png' /> ".__('Property')." #".($index).": ".$property->getLabel();
			$this->form->createGroup("property_{$index}", $groupTitle, $elementNames, array('class' => 'form-group-opened'));
		}
    	
		//add an hidden elt for the property uri
		$propUriElt = tao_helpers_form_FormFactory::getElement("propertyUri{$index}", 'Hidden');
		$propUriElt->addAttribute('class', 'property-uri');
		$propUriElt->setValue(tao_helpers_Uri::encode($property->uriResource));
		$this->form->addElement($propUriElt);
				
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:00000000000024A5 end
    }

} /* end of class tao_actions_form_Property */

?>