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
     * render mode constants
     *
     * @access public
     * @var string
     */
    const RENDER_MODE_XHTML = 'xhtml';

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
     * @param  string renderMode
     * @return tao_helpers_form_Form
     */
    public static function instanceEditor( core_kernel_classes_Class $clazz,  core_kernel_classes_Resource $instance = null, $renderMode = 'xhtml')
    {
        $returnValue = null;

        // section 10-13-1-45--70bace43:123ffff90e9:-8000:00000000000018CD begin
		
		if(!is_null($clazz)){
			
			$name = 'form_'.(count(self::$forms)+1);
			
			//use the right implementation (depending the render mode)
			//@todo refactor this and use a FormFactory/FormElementFactory
			switch($renderMode){
				case self::RENDER_MODE_XHTML:
					$myForm = new tao_helpers_form_xhtml_Form($name);
					$myForm->setDecorator(new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div')));
					$hiddenEltClass = 'tao_helpers_form_elements_xhtml_Hidden';
					break;
				default: 
					return null;
			}
			
			$defaultProperties 	= self::getDefaultProperties();
			$classProperties	= self::getClassProperties($clazz, new core_kernel_classes_Class(self::DEFAULT_TOP_LEVEL_CLASS));
			foreach(array_merge($defaultProperties, $classProperties) as $property){
				
				$property->feed();
				
				//map properties widgets to form elments 
				$element = self::elementMap($property, $renderMode);
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
						$element->setLevel(0);
					}
					else{
						$element->setLevel(1);
					}
					$myForm->addElement($element);
				}
			}
			
			//add an hidden elt for the class uri
			$classUriElt = new $hiddenEltClass('classUri');
			$classUriElt->setValue(tao_helpers_Uri::encode($clazz->uriResource));
			$classUriElt->setLevel(2);
			$myForm->addElement($classUriElt);
			
			if(!is_null($instance)){
				//add an hidden elt for the instance Uri
				$instanceUriElt = new $hiddenEltClass('uri');
				$instanceUriElt->setValue(tao_helpers_Uri::encode($instance->uriResource));
				$instanceUriElt->setLevel(2);
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
     * @param  string renderMode
     * @return tao_helpers_form_Form
     */
    public static function classEditor( core_kernel_classes_Class $clazz,  core_kernel_classes_Class $topClazz = null, $renderMode = 'xhtml')
    {
        $returnValue = null;

        // section 127-0-1-1-173d16:124524d2e59:-8000:0000000000001A5D begin
		
		if(!is_null($clazz)){
			
			$name = 'form_'.(count(self::$forms)+1);
			
			//use the right implementation (depending the render mode)
			//@todo refactor this and use a FormFactory/FormElementFactory
			switch($renderMode){
				case self::RENDER_MODE_XHTML:
					$myForm = new tao_helpers_form_xhtml_Form($name);
					$myForm->setDecorator(new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div')));
					$myForm->setGroupDecorator(new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-group')));
					$hiddenEltClass = 'tao_helpers_form_elements_xhtml_Hidden';
					$buttonEltClass = 'tao_helpers_form_elements_xhtml_Button';
					break;
				default: 
					return null;
			}
			
			
			//add a group form for the class edition 
			$elementNames = array();
			foreach(self::getDefaultProperties()  as $property){
				
				//map properties widgets to form elments 
				$element = self::elementMap($property, $renderMode);
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
					$element->setLevel(2);
					$element->setName('class_'.$element->getName());
					$myForm->addElement($element);
					$elementNames[] = $element->getName();
				}
			}
			if(count($elementNames) > 0){
				$myForm->createGroup('class', 'Class', $elementNames);
			}
			
			//add an hidden elt for the class uri
			$classUriElt = new $hiddenEltClass('classUri');
			$classUriElt->setValue(tao_helpers_Uri::encode($clazz->uriResource));
			$myForm->addElement($classUriElt);
			
			
			//class properties edition: add a grou pform for  each property
			if(is_null($topClazz)){
				$topClazz = new core_kernel_classes_Class(self::DEFAULT_TOP_LEVEL_CLASS);
			}
			$i = 0;
			foreach(self::getClassProperties($clazz, $topClazz) as $classProperty){
				self::propertyEditor($classProperty, $myForm, $i, $renderMode);
				$i++;
			}
			
			//add a button to add new properties
			$addPropElement = new $buttonEltClass("propertyAdder");
			$addPropElement->addAttribute('class', 'property-adder');
			$addPropElement->setValue(__('Add a new property'));
			$addPropElement->setLevel(3);
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
     * @param  string renderMode
     * @return tao_helpers_form_Form
     */
    public static function propertyEditor( core_kernel_classes_Property $property,  tao_helpers_form_Form $form, $index = 0, $renderMode = 'xhtml')
    {
        $returnValue = null;

        // section 127-0-1-1-397f707b:124b59ea33f:-8000:0000000000001B0E begin
		
		if(is_null($form)){
			throw new Exception("tao_helpers_form_Form parameter must be a valid form instance");
		}
		
		$level = ($index + 1) * 10;
		$elementNames = array();
		
		//use the right implementation (depending the render mode)
		//@todo refactor this and use a FormElementFactory
		switch($renderMode){
			case self::RENDER_MODE_XHTML:
				$hiddenEltClass = 'tao_helpers_form_elements_xhtml_Hidden';
				$selectEltClass = 'tao_helpers_form_elements_xhtml_Combobox';
				$buttonEltClass = 'tao_helpers_form_elements_xhtml_Button';
				break;
			default: 
				return null;
		}
		
		foreach(array_merge(self::getDefaultProperties(),self::getPropertyProperties()) as $propertyProperty){
			
			//map properties widgets to form elments 
			$element = self::elementMap($propertyProperty, $renderMode);
			
			//add range mannually because it's widget is not implemented
			if(is_null($element) && $propertyProperty->uriResource == 'http://www.w3.org/2000/01/rdf-schema#range'){
				$propertyProperty->feed();
				$element = new $selectEltClass();
				$element->setName(tao_helpers_Uri::encode($propertyProperty->uriResource));
				$element->setDescription('Range');
				
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
				$element->setLevel($level);
				$form->addElement($element);
				$elementNames[] = $element->getName();
				$level++;
				
			}
		}
		
		//add a delete button 
		$deleteElt = new $buttonEltClass("propertyDeleter{$index}");
		$deleteElt->addAttribute('class', 'property-deleter');
		$deleteElt->setValue(__('Delete property'));
		$deleteElt->setLevel($level);
		$form->addElement($deleteElt);
		$elementNames[] = $deleteElt->getName();
		$level++;
		
		//add an hidden elt for the property uri (IT MUST BE OUTSIDDE A GROUP FOR DELETION)
		$propUriElt = new $hiddenEltClass("propertyUri{$index}");
		$propUriElt->setValue(tao_helpers_Uri::encode($property->uriResource));
		$propUriElt->setLevel($level);
		$form->addElement($propUriElt);
		$level++;
		
		
		if(count($elementNames) > 0){
			$form->createGroup("property_{$index}", "Property #".($index+1), $elementNames);
		}
		
		$returnValue = $form;
		
        // section 127-0-1-1-397f707b:124b59ea33f:-8000:0000000000001B0E end

        return $returnValue;
    }

    /**
     * Enable you to map an rdf property to a form element using the Widget
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
		
		//create the element from the right widget
		$widgetResource = $property->getWidget();
		$widget = ucfirst(strtolower(substr($widgetResource->uriResource, strrpos($widgetResource->uriResource, '#') + 1 )));
		$elementClass = 'tao_helpers_form_elements_'.$renderMode.'_'.$widget;
		
		if(!class_exists($elementClass)){
		//	echo "unknown widget $elementClass for property {$property->uriResource}<br />";
			return null;
		}
		
		$element = new $elementClass(); 	//instanciate
		
		//security checks for dynamic instantiation
		if(!$element instanceof tao_helpers_form_FormElement){
			return null;
		}
		if($element->getWidget() != $widgetResource->uriResource){
			return null;
		}
		
		//use uri as element name					
		$element->setName( tao_helpers_Uri::encode($property->uriResource));

		//use the property label as element description
		(strlen(trim($property->getLabel())) > 0) ? $propDesc = $property->getLabel() : $propDesc = 'field '.(count($myForm->getElements())+1);	
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
     * @return array
     */
    public static function getPropertyProperties()
    {
        $returnValue = array();

        // section 127-0-1-1-696660da:12480a2774f:-8000:0000000000001AB5 begin
		
		$defaultUris = array(
			'http://www.w3.org/2000/01/rdf-schema#label',
			'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget',
			//'http://www.w3.org/2000/01/rdf-schema#domain',
			'http://www.w3.org/2000/01/rdf-schema#range',
			'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent'
		);
		
		$resourceClass = new core_kernel_classes_Class('http://www.w3.org/1999/02/22-rdf-syntax-ns#Property');
		
		foreach($resourceClass->getProperties(true) as $property){
			if(in_array($property->uriResource, $defaultUris)){
				array_push($returnValue, $property);
			}
		}
        // section 127-0-1-1-696660da:12480a2774f:-8000:0000000000001AB5 end

        return (array) $returnValue;
    }

} /* end of class tao_helpers_form_GenerisFormFactory */

?>