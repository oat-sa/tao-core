<?php

error_reporting(E_ALL);

/**
 * The GenerisFormFactory enables you to create Forms using rdf data and the
 * api to provide it. You can give any node of your ontology and the factory
 * create the appriopriate form. The Generis ontology (with the Widget Property)
 * required to use it.
 * Now only the xhtml rendering mode is implemented
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @see core_kernel_classes_* packages
 * @subpackage helpers_form
 */
class tao_helpers_form_GenerisFormFactory
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Enable you to map an rdf property to a form element using the Widget
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Property property
     * @return tao_helpers_form_FormElement
     */
    public static function elementMap( core_kernel_classes_Property $property)
    {
        $returnValue = null;

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001937 begin
		
		//create the element from the right widget
		$property->feed();
		
        $widgetResource = $property->getWidget();
		if(is_null($widgetResource)){
			return null;
		}
		$widget = ucfirst(strtolower(substr($widgetResource->uriResource, strrpos($widgetResource->uriResource, '#') + 1 )));
		
		//authoring widget is not used in standalone mode
		if($widget == 'Authoring' && tao_helpers_Context::check('STANDALONE_MODE')){
			return null;
		}
		
		$element = tao_helpers_form_FormFactory::getElement(tao_helpers_Uri::encode($property->uriResource), $widget);
		if(!is_null($element)){
			if($element->getWidget() != $widgetResource->uriResource){
				return null;
			}
	
			//use the property label as element description
			(strlen(trim($property->getLabel())) > 0) ? $propDesc = htmlentities($property->getLabel()) : $propDesc = 'field '.(count($myForm->getElements())+1);	
			$element->setDescription($propDesc);
			
			//multi elements use the property range as options
			if(method_exists($element, 'setOptions')){
				$range = $property->getRange();
				if($range != null){
					$options = array();
					foreach($range->getInstances(true) as $rangeInstance){
						$options[ tao_helpers_Uri::encode($rangeInstance->uriResource) ] = $rangeInstance->getLabel();
					}
					
					//set the default value to an empty space
					if(method_exists($element, 'setEmptyOption')){
						$element->setEmptyOption(' ');
					}
					
					//complete the options listing
					$element->setOptions($options);
				}
			}
			$returnValue = $element;
		}
		
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001937 end

        return $returnValue;
    }

    /**
     * Enable you to get the properties of a class. 
     * The advantage of this method is to limit the level of recusrivity in the
     * It get the properties up to the defined top class
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class clazz
     * @param  Class topLevelClazz
     * @return array
     */
    public static function getClassProperties( core_kernel_classes_Class $clazz,  core_kernel_classes_Class $topLevelClazz = null)
    {
        $returnValue = array();

        // section 127-0-1-1-2db84171:12476b7fa3b:-8000:0000000000001AAB begin
		
		 
        if(is_null($topLevelClazz)){
			$topLevelClazz = new core_kernel_classes_Class(TAO_OBJECT_CLASS);
		}
		
		if($clazz->uriResource == $topLevelClazz->uriResource){
			$returnValue = $clazz->getProperties(false);
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
			
			foreach($parents as $aParent){
				
				
				if( !($aParent instanceof core_kernel_classes_Class) || is_null($aParent)){
					$top = true; 
					break;
				}
				if($aParent->uriResource == RDF_CLASS){
					//$top = true; 
					continue;
				}
				
				$returnValue = array_merge($returnValue, $aParent->getProperties(false));
				
				if($aParent->uriResource == $topLevelClazz->uriResource){
					$top = true; 
				}
				
				$parent = $aParent;
			}
		} while ($top === false);
		
		$returnValue = array_merge($returnValue, $clazz->getProperties(false));
		
        // section 127-0-1-1-2db84171:12476b7fa3b:-8000:0000000000001AAB end

        return (array) $returnValue;
    }

    /**
     * get the default properties to add to every forms
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return array
     */
    public static function getDefaultProperties()
    {
        $returnValue = array();

        // section 127-0-1-1--5ce810e0:1244ce713f8:-8000:0000000000001A43 begin

		 $returnValue = array(
			new core_kernel_classes_Property(RDFS_LABEL)
		);
		
        // section 127-0-1-1--5ce810e0:1244ce713f8:-8000:0000000000001A43 end

        return (array) $returnValue;
    }

    /**
     * Get the properties of the rdfs Property class
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string mode
     * @return array
     */
    public static function getPropertyProperties($mode = 'simple')
    {
        $returnValue = array();

        // section 127-0-1-1-696660da:12480a2774f:-8000:0000000000001AB5 begin
		
		switch($mode){
			case 'simple':
				$defaultUris = array(PROPERTY_IS_LG_DEPENDENT);
				break;
			case 'advanced':
			default:	
				$defaultUris = array(
					RDFS_LABEL,
					PROPERTY_WIDGET,
					RDFS_RANGE,
					PROPERTY_IS_LG_DEPENDENT
				);
				break;
		} 
		$resourceClass = new core_kernel_classes_Class(RDF_PROPERTY);
		foreach($resourceClass->getProperties() as $property){
			if(in_array($property->uriResource, $defaultUris)){
				array_push($returnValue, $property);
			}
		}
		
        // section 127-0-1-1-696660da:12480a2774f:-8000:0000000000001AB5 end

        return (array) $returnValue;
    }

    /**
     * Returnn the map between the Property properties: range, widget, etc. to
     * shorcuts for the simplePropertyEditor
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return array
     */
    public static function getPropertyMap()
    {
        $returnValue = array();

        // section 127-0-1-1-47336e64:124c90d0af6:-8000:0000000000001B31 begin
		
		$returnValue = array(
			'text' => array(
				'title' 	=> __('A short text'),
				'widget'	=> PROPERTY_WIDGET_TEXTBOX,
				'range'		=> RDFS_LITERAL
			),
			'longtext' => array(
				'title' 	=> __('A long text'),
				'widget'	=> PROPERTY_WIDGET_TEXTAREA,
				'range'		=> RDFS_LITERAL
			),
			'html' => array(
				'title' 	=> __('A formated text'),
				'widget'	=> PROPERTY_WIDGET_HTMLAREA,
				'range'		=> RDFS_LITERAL
			),
			'password' => array(
				'title' 	=> __('A password'),
				'widget'	=> PROPERTY_WIDGET_HIDDENBOX,
				'range'		=> RDFS_LITERAL
			),
			'calendar' => array(
				'title' 	=> __('Dynamic date picker'),
				'widget'	=> PROPERTY_WIDGET_CALENDAR,
				'range'		=> RDFS_LITERAL
			),
			'list' => array(
				'title' 	=> __('A single choice list'),
				'widget'	=> PROPERTY_WIDGET_RADIOBOX,
				'range'		=> null
			),
			'longlist' => array(
				'title' 	=> __('A single choice long list'),
				'widget'	=> PROPERTY_WIDGET_COMBOBOX,
				'range'		=> null
			),
			'multilist' => array(
				'title' 	=> __('A multiple choice list'),
				'widget'	=> PROPERTY_WIDGET_CHECKBOX,
				'range'		=> null
			)
		);
		
        // section 127-0-1-1-47336e64:124c90d0af6:-8000:0000000000001B31 end

        return (array) $returnValue;
    }

} /* end of class tao_helpers_form_GenerisFormFactory */

?>