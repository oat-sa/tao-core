<?php

error_reporting(E_ALL);

/**
 * The GenerisFormFactory enables you to create Forms using rdf data and the
 * api to provide it. You can give any node of your ontology and the factory
 * create the appriopriate form. The Generis ontology (with the Widget Property)
 * required to use it.
 * Now only the xhtml rendering mode is implemented
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @see core_kernel_classes_* packages
 * @subpackage helpers_form
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
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
 * The GenerisFormFactory enables you to create Forms using rdf data and the
 * api to provide it. You can give any node of your ontology and the factory
 * create the appriopriate form. The Generis ontology (with the Widget Property)
 * required to use it.
 * Now only the xhtml rendering mode is implemented
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @see core_kernel_classes_* packages
 * @subpackage helpers_form
 */
class tao_helpers_form_GenerisFormFactory
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * the default top level (to stop the recursivity look up) class commly used
     *
     * @access public
     * @var string
     */
    const DEFAULT_TOP_LEVEL_CLASS = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject';

    /**
     * a list of forms currently instanciated with the factory (can be used to
     * multiple forms)
     *
     * @access private
     * @var array
     */
    private static $forms = array();

    // --- OPERATIONS ---

    /**
     * Create a form from a class of your ontology, the form data comes from the
     * The default rendering is in xhtml
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Class clazz
     * @param  Resource instance
     * @return tao_helpers_form_Form
     */
    public static function instanceEditor( core_kernel_classes_Class $clazz,  core_kernel_classes_Resource $instance = null)
    {
        $returnValue = null;

        // section 10-13-1-45--70bace43:123ffff90e9:-8000:00000000000018CD begin
		
		
		if(!is_null($clazz)){
			
			$name = 'form_'.(count(self::$forms)+1);
			$myForm = tao_helpers_form_FormFactory::getForm($name);
			
			$level = 2;
			$defaultProperties 	= self::getDefaultProperties();
			$classProperties	= self::getClassProperties($clazz, new core_kernel_classes_Class(self::DEFAULT_TOP_LEVEL_CLASS));
			$maxLevel = count(array_merge($defaultProperties, $classProperties));
			foreach(array_merge($defaultProperties, $classProperties) as $property){
				
				$property->feed();
				
				//map properties widgets to form elments 
				$element = self::elementMap($property);
				
				if(!is_null($element)){
					
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
					if(in_array($property, $defaultProperties)){
						$element->setLevel($level);
						$level++;
					}
					else{
						$element->setLevel($maxLevel + $level);
						$maxLevel++;
					}
					$myForm->addElement($element);
				}
			}
			
			//add an hidden elt for the class uri
			$classUriElt = tao_helpers_form_FormFactory::getElement('classUri', 'Hidden');
			$classUriElt->setValue(tao_helpers_Uri::encode($clazz->uriResource));
			$classUriElt->setLevel($level);
			$myForm->addElement($classUriElt);
			
			if(!is_null($instance)){
				//add an hidden elt for the instance Uri
				$instanceUriElt = tao_helpers_form_FormFactory::getElement('uri', 'Hidden');
				$instanceUriElt->setValue(tao_helpers_Uri::encode($instance->uriResource));
				$instanceUriElt->setLevel($level+1);
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
     * create a Form to add a subclass to the rdfs:Class clazz
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Class clazz
     * @param  Class topClazz
     * @return tao_helpers_form_xhtml_Form
     */
    public static function classEditor( core_kernel_classes_Class $clazz,  core_kernel_classes_Class $topClazz = null)
    {
        $returnValue = null;

        // section 127-0-1-1-173d16:124524d2e59:-8000:0000000000001A5D begin
		
		if(!is_null($clazz)){
			
			$name = 'form_'.(count(self::$forms)+1);
			$myForm = tao_helpers_form_FormFactory::getForm($name);
			
			//add a group form for the class edition 
			$elementNames = array();
			foreach(self::getDefaultProperties()  as $property){
				
				//map properties widgets to form elments 
				$element = self::elementMap($property);
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
					$myForm->addElement($element);
					$elementNames[] = $element->getName();
				}
			}
			if(count($elementNames) > 0){
				$myForm->createGroup('class', "<img src='/tao/views/img/class.png' /> Class: ".$clazz->getLabel(), $elementNames);
			}
			
			//add an hidden elt for the class uri
			$classUriElt = tao_helpers_form_FormFactory::getElement('classUri', 'Hidden');
			$classUriElt->setValue(tao_helpers_Uri::encode($clazz->uriResource));
			$myForm->addElement($classUriElt);
			
			//class properties edition: add a group form for each property
			if(is_null($topClazz)){
				$topClazz = new core_kernel_classes_Class(self::DEFAULT_TOP_LEVEL_CLASS);
			}
			$i = 0;
			foreach(self::getClassProperties($clazz, $topClazz) as $classProperty){
				$i++;
				$useEditor = false;
				$parentProp = true;
				$domains = $classProperty->getDomain();
				foreach($domains->getIterator() as $domain){
					if($domain->uriResource == $clazz->uriResource){
						$parentProp = false;
						
						//@todo use the getPrivileges method once implemented
						if($classProperty->getLastModificationUser() != 'generis'){
							$useEditor = true;
						}
						break;
					}
				}
				
				if($useEditor){
					self::propertyEditor($classProperty, $myForm, $i, true);
				}
				else if($parentProp){
					$domainElement = tao_helpers_form_FormFactory::getElement('parentProperty'.$i, 'Free');
					$value = __("Edit property into parent class ");
					foreach($domains->getIterator() as $domain){
						$value .= "<a  href='#' onclick='GenerisTreeClass.selectTreeNode(\"".tao_helpers_Uri::encode($domain->uriResource)."\");' >".$domain->getLabel()."</a> ";
					}
					$domainElement->setValue($value);
					$myForm->addElement($domainElement);
					
					$myForm->createGroup("parent_property_{$i}", "<img src='/tao/views/img/prop_orange.png' /> Property #".($i).": ".$classProperty->getLabel(), array('parentProperty'.$i));
				}
				else{
					$roElement = tao_helpers_form_FormFactory::getElement('roProperty'.$i, 'Free');
					$roElement->setValue(__("You cannot modify this property"));
					$myForm->addElement($roElement);
					
					$myForm->createGroup("ro_property_{$i}", "<img src='/tao/views/img/prop_red.png' /> Property #".($i).": ".$classProperty->getLabel(), array('roProperty'.$i));
				}
			}
			
			//add a button to add new properties
			$addPropElement = tao_helpers_form_FormFactory::getElement('propertyAdder', 'Button');
			$addPropElement->addAttribute('class', 'property-adder');
			$addPropElement->setValue(__('Add a new property'));
			$myForm->addElement($addPropElement);
			$myForm->createGroup('property-actions', 'Actions', array($addPropElement->getName()));
			
			//form data evaluation
			$myForm->evaluate();		
				
			self::$forms[$name] = $myForm;
			$returnValue = self::$forms[$name];
		}
		
        // section 127-0-1-1-173d16:124524d2e59:-8000:0000000000001A5D end

        return $returnValue;
    }

    /**
     * Short description of method propertyEditor
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Property property
     * @param  Form form
     * @param  int index
     * @param  boolean simpleMode
     * @return tao_helpers_form_xhtml_Form
     */
    public static function propertyEditor( core_kernel_classes_Property $property,  tao_helpers_form_xhtml_Form $form, $index = 0, $simpleMode = false)
    {
        $returnValue = null;

        // section 127-0-1-1-397f707b:124b59ea33f:-8000:0000000000001B0E begin
		
		if(is_null($form)){
			throw new Exception("tao_helpers_form_Form parameter must be a valid form instance");
		}
		
		if($simpleMode){
			$returnValue = self::simplePropertyEditor($property, $form, $index);
		}
		else{
			$returnValue = self::advancedPropertyEditor($property, $form, $index);
		}
		
		//add an hidden elt for the property uri
		$propUriElt = tao_helpers_form_FormFactory::getElement("propertyUri{$index}", 'Hidden');
		$propUriElt->addAttribute('class', 'property-uri');
		$propUriElt->setValue(tao_helpers_Uri::encode($property->uriResource));
		$returnValue->addElement($propUriElt);
		
        // section 127-0-1-1-397f707b:124b59ea33f:-8000:0000000000001B0E end

        return $returnValue;
    }

    /**
     * Short description of method simplePropertyEditor
     *
     * @access protected
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Property property
     * @param  Form form
     * @param  int index
     * @return tao_helpers_form_Form
     */
    protected static function simplePropertyEditor( core_kernel_classes_Property $property,  tao_helpers_form_Form $form, $index = 0)
    {
        $returnValue = null;

        // section 127-0-1-1-47336e64:124c90d0af6:-8000:0000000000001B39 begin
		
		$level = ($index * 10) + 1;
		$elementNames = array();
		
		foreach(array_merge(self::getDefaultProperties(), self::getPropertyProperties('simple')) as $propertyProperty){
		
			//map properties widgets to form elments 
			$element = self::elementMap($propertyProperty);
			
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
				$form->addElement($element);
				$elementNames[] = $element->getName();
				$level++;
			}
		}
		
		//build the type list from the "widget/range to type" map
		$typeElt = tao_helpers_form_FormFactory::getElement("property_{$index}_type", 'Combobox');
		$typeElt->setDescription(__('Type'));
		$typeElt->addAttribute('class', 'property-type');
		$typeElt->setEmptyOption(' --- '.__('select').' --- ');
		$options = array();
		foreach(self::getPropertyMap() as $typeKey => $map){
			$options[$typeKey] = $map['title'];
			if($property->getWidget()){
				if($property->getWidget()->uriResource == $map['widget']){
					$typeElt->setValue($typeKey);
				}
			}
			
		}
		$typeElt->setOptions($options);
		$form->addElement($typeElt);
		$elementNames[] = $typeElt->getName();
		$level++;
		
		$listElt = tao_helpers_form_FormFactory::getElement("property_{$index}_range", 'Combobox');
		$listElt->setDescription(__('List values'));
		$listElt->addAttribute('class', 'property-listvalues');
		$listElt->setEmptyOption(' --- '.__('select').' --- ');
		
		$exclude = array(
			TAO_GROUP_CLASS,
			TAO_ITEM_CLASS,
			TAO_ITEM_MODEL_CLASS,
			TAO_RESULT_CLASS,
			TAO_SUBJECT_CLASS,
			TAO_TEST_CLASS
		);
		$topLevelClazz = new core_kernel_classes_Class(self::DEFAULT_TOP_LEVEL_CLASS);
		$domains = $property->getDomain();
		$options = array();
		foreach(
			array_merge(
				array(new core_kernel_classes_Class(GENERIS_BOOLEAN)),
				$topLevelClazz->getSubClasses(false)
			) 
			as $subClass){
			
			if(in_array($subClass->uriResource, $exclude)){
				continue;
			}
			
			$isDomain = false;
			foreach($domains->getIterator() as $domain){
				if($subClass->uriResource == $domain->uriResource){
					$isDomain = true;
					break;
				} 
				foreach($domain->getParentClasses(true) as $domainParent){
					if($subClass->uriResource == $domainParent->uriResource){
						$isDomain = true;
						break;
					} 
				}
			}
			if(!$isDomain){
				$options[tao_helpers_Uri::encode($subClass->uriResource)] = $subClass->getLabel();
				if($property->getRange()->uriResource == $subClass->uriResource){
					$listElt->setValue($subClass->uriResource);
				}
			}
		}
		$listElt->setOptions(array_merge($options, array('new' => '+ '.__('Add / Edit lists'))));
		$form->addElement($listElt);
		$elementNames[] = $listElt->getName();
		$level++;
		
		
		//add a delete button 
		$deleteElt = tao_helpers_form_FormFactory::getElement("propertyDeleter{$index}", 'Button');
		$deleteElt->addAttribute('class', 'property-deleter');
		$deleteElt->setValue(__('Delete property'));
		$form->addElement($deleteElt);
		$elementNames[] = $deleteElt->getName();
		$level++;
		
		//add an hidden element with the mode (simple)
		$modeElt = tao_helpers_form_FormFactory::getElement("propertyMode{$index}", 'Hidden');
		$modeElt->setValue('simple');
		$form->addElement($modeElt);
		$elementNames[] = $modeElt->getName();
		$level++;
		
		if(count($elementNames) > 0){
			$form->createGroup("property_{$index}", "<img src='/tao/views/img/prop_green.png' />Property #".($index).": ".$property->getLabel(), $elementNames);
		}
			
		$returnValue = $form;
		
        // section 127-0-1-1-47336e64:124c90d0af6:-8000:0000000000001B39 end

        return $returnValue;
    }

    /**
     * Short description of method advancedPropertyEditor
     *
     * @access protected
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Property property
     * @param  Form form
     * @param  int index
     * @return tao_helpers_form_Form
     */
    protected static function advancedPropertyEditor( core_kernel_classes_Property $property,  tao_helpers_form_Form $form, $index = 0)
    {
        $returnValue = null;

        // section 127-0-1-1-47336e64:124c90d0af6:-8000:0000000000001B40 begin
		
		$level = ($index * 10) + 1;
		$elementNames = array();
		
		foreach(array_merge(self::getDefaultProperties(), self::getPropertyProperties('advanced')) as $propertyProperty){
			
			//map properties widgets to form elments 
			$element = self::elementMap($propertyProperty);
			
			//add range mannually because it's widget is not implemented
			if(is_null($element) && $propertyProperty->uriResource == 'http://www.w3.org/2000/01/rdf-schema#range'){
				$propertyProperty->feed();
				$element = tao_helpers_form_FormFactory::getElement(tao_helpers_Uri::encode($propertyProperty->uriResource), 'Combobox');
				$element->setDescription(__('Range'));
				
				$range = $propertyProperty->getRange();
				if($range != null){
					$options = array();
					foreach($range->getInstances(true) as $rangeInstance){
						$options[ tao_helpers_Uri::encode($rangeInstance->uriResource) ] = $rangeInstance->getLabel();
					}
					$element->setOptions($options);
				}
			}
			
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
			//	$element->setLevel($level);
				$form->addElement($element);
				$elementNames[] = $element->getName();
				$level++;
			}
		}
		
		//add a delete button 
		$deleteElt = tao_helpers_form_FormFactory::getElement("propertyDeleter{$index}", 'Button');
		$deleteElt->addAttribute('class', 'property-deleter');
		$deleteElt->setValue(__('Delete property'));
	//	$deleteElt->setLevel($level);
		$form->addElement($deleteElt);
		$elementNames[] = $deleteElt->getName();
		$level++;
		
		//add an hidden element with the mode (simple)
		$modeElt = tao_helpers_form_FormFactory::getElement("propertyMode{$index}", 'Hidden');
		$modeElt->setValue('advanced');
	//	$modeElt->setLevel($level);
		$form->addElement($modeElt);
		$elementNames[] = $modeElt->getName();
		$level++;
		
		if(count($elementNames) > 0){
			$form->createGroup("property_{$index}", "Property #".($index+1).": ".$property->getLabel(), $elementNames);
		}
		
		$returnValue = $form;
		
        // section 127-0-1-1-47336e64:124c90d0af6:-8000:0000000000001B40 end

        return $returnValue;
    }

    /**
     * Enable you to map an rdf property to a form element using the Widget
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Property property
     * @return tao_helpers_form_FormElement
     */
    public static function elementMap( core_kernel_classes_Property $property)
    {
        $returnValue = null;

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001937 begin
		
		//create the element from the right widget
		$widgetResource = $property->getWidget();
		$widget = ucfirst(strtolower(substr($widgetResource->uriResource, strrpos($widgetResource->uriResource, '#') + 1 )));
		$element = tao_helpers_form_FormFactory::getElement(tao_helpers_Uri::encode($property->uriResource), $widget);
		if(!is_null($element)){
			if($element->getWidget() != $widgetResource->uriResource){
				return null;
			}
	
			//use the property label as element description
			(strlen(trim($property->getLabel())) > 0) ? $propDesc = tao_helpers_Display::textCleaner($property->getLabel(), ' ') : $propDesc = 'field '.(count($myForm->getElements())+1);	
			$element->setDescription($propDesc);
			
			//multi elements use the property range as options
			if(method_exists($element, 'setOptions')){
				$range = $property->getRange();
				if($range != null){
					$options = array();
					foreach($range->getInstances() as $rangeInstance){
						$options[ tao_helpers_Uri::encode($rangeInstance->uriResource) ] = $rangeInstance->getLabel();
					}
					$element->setOptions($options);
				}
			}
			$returnValue = $element;
		}
		
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001937 end

        return $returnValue;
    }

    /**
     * Short description of method getClassProperties
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Class clazz
     * @param  Class topLevelClazz
     * @return array
     */
    public static function getClassProperties( core_kernel_classes_Class $clazz,  core_kernel_classes_Class $topLevelClazz = null)
    {
        $returnValue = array();

        // section 127-0-1-1-2db84171:12476b7fa3b:-8000:0000000000001AAB begin
		
		if(is_null($topLevelClazz)){
			$returnValue = $clazz->getProperties(true);
		}
		else{
			$returnValue = $clazz->getProperties(false);
			if($clazz->uriResource == $topLevelClazz->uriResource){
				return (array) $returnValue;
			}
			$top = false;
			$parent = null;
			do{
				if(is_null($parent)){
					$parents = $clazz->getParentClasses(false);
				}
				else{
					$parents = $parent->getParentClasses(false);
				}
				if(count($parents) == 0){
					break;
				}
				
				foreach($parents as $parent){
					if( !($parent instanceof core_kernel_classes_Class) || is_null($parent)){
						$top = true; 
						break;
					}
					if($parent->uriResource == 'http://www.w3.org/2000/01/rdf-schema#Class'){
						continue;
					}
					
					$returnValue = array_merge($returnValue, $parent->getProperties(false));
					if($parent->uriResource == $topLevelClazz->uriResource){
						$top = true; 
						break;
					}
					
				}
			}while($top === false);
		}
		
        // section 127-0-1-1-2db84171:12476b7fa3b:-8000:0000000000001AAB end

        return (array) $returnValue;
    }

    /**
     * get the default properties to add to every forms
     *
     * @access protected
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return array
     */
    protected static function getDefaultProperties()
    {
        $returnValue = array();

        // section 127-0-1-1--5ce810e0:1244ce713f8:-8000:0000000000001A43 begin

		$defaultUris = array(
			'http://www.w3.org/2000/01/rdf-schema#label'
		);
		
		$resourceClass = new core_kernel_classes_Class('http://www.w3.org/2000/01/rdf-schema#Resource');
		foreach($resourceClass->getProperties() as $property){
			if(in_array($property->uriResource, $defaultUris)){
				array_push($returnValue, $property);
			}
		}
		
        // section 127-0-1-1--5ce810e0:1244ce713f8:-8000:0000000000001A43 end

        return (array) $returnValue;
    }

    /**
     * Short description of method getPropertyProperties
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string mode
     * @return array
     */
    public static function getPropertyProperties($mode = 'simple')
    {
        $returnValue = array();

        // section 127-0-1-1-696660da:12480a2774f:-8000:0000000000001AB5 begin
		
		switch($mode){
			case 'simple':
				$defaultUris = array(
					'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent'
				);
				break;
			case 'advanced':
			default:	
				$defaultUris = array(
					'http://www.w3.org/2000/01/rdf-schema#label',
					'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget',
					'http://www.w3.org/2000/01/rdf-schema#range',
					'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent'
				);
				break;
		} 
		$resourceClass = new core_kernel_classes_Class('http://www.w3.org/1999/02/22-rdf-syntax-ns#Property');
		foreach($resourceClass->getProperties() as $property){
			if(in_array($property->uriResource, $defaultUris)){
				array_push($returnValue, $property);
			}
		}
		
        // section 127-0-1-1-696660da:12480a2774f:-8000:0000000000001AB5 end

        return (array) $returnValue;
    }

    /**
     * Short description of method getPropertyMap
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return array
     */
    public static function getPropertyMap()
    {
        $returnValue = array();

        // section 127-0-1-1-47336e64:124c90d0af6:-8000:0000000000001B31 begin
		
		$returnValue = array(
			'text' => array(
				'title' 	=> __('A short text'),
				'widget'	=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
				'range'		=> 'http://www.w3.org/2000/01/rdf-schema#Literal'
			),
			'longtext' => array(
				'title' 	=> __('A long text'),
				'widget'	=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea',
				'range'		=> 'http://www.w3.org/2000/01/rdf-schema#Literal'
			),
			'html' => array(
				'title' 	=> __('A formated text'),
				'widget'	=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea',
				'range'		=> 'http://www.w3.org/2000/01/rdf-schema#Literal'
			),
			'password' => array(
				'title' 	=> __('A password'),
				'widget'	=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox',
				'range'		=> 'http://www.w3.org/2000/01/rdf-schema#Literal'
			),
			'list' => array(
				'title' 	=> __('A simple choice list'),
				'widget'	=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox',
				'range'		=> null
			),
			'longlist' => array(
				'title' 	=> __('A simple choice long list'),
				'widget'	=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox',
				'range'		=> null
			),
			'multilist' => array(
				'title' 	=> __('A multiple choice list'),
				'widget'	=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox',
				'range'		=> null
			)
		);
		
        // section 127-0-1-1-47336e64:124c90d0af6:-8000:0000000000001B31 end

        return (array) $returnValue;
    }

} /* end of class tao_helpers_form_GenerisFormFactory */

?>