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
     * @access protected
     * @var array
     */
    protected static $forms = array();

    /**
     * Short description of attribute MODE_SOFT
     *
     * @access public
     * @var int
     */
    const MODE_SOFT = 2;

    /**
     * Short description of attribute MODE_STANDALONE
     *
     * @access public
     * @var int
     */
    const MODE_STANDALONE = 3;

    /**
     * Short description of attribute mode
     *
     * @access protected
     * @var int
     */
    protected static $mode = 2;

    // --- OPERATIONS ---

    /**
     * Create a form from a class of your ontology, the form data comes from the
     * The default rendering is in xhtml
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class clazz
     * @param  Resource instance
     * @param  string name
     * @param  array options
     * @return tao_helpers_form_Form
     */
    public static function instanceEditor( core_kernel_classes_Class $clazz,  core_kernel_classes_Resource $instance = null, $name = '', $options = array())
    {
        $returnValue = null;

        // section 10-13-1-45--70bace43:123ffff90e9:-8000:00000000000018CD begin
		
		
		if(!is_null($clazz)){
			
			if(empty($name)){
				$name = 'form_'.(count(self::$forms)+1);
			}
			
			$myForm = tao_helpers_form_FormFactory::getForm($name, $options);
			
			//add translate action in toolbar
			$topActions = tao_helpers_form_FormFactory::getCommonActions('top');
			if(self::$mode == self::MODE_SOFT){
				$translateELt = tao_helpers_form_FormFactory::getElement('translate', 'Free');
				$translateELt->setValue(" | <a href='#' class='form-translator' ><img src='".TAOBASE_WWW."/img/translate.png'  /> ".__('Translate')."</a>");
				$topActions[] = $translateELt;
			}
			$myForm->setActions($topActions, 'top');
			
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
     * Create a translation form from an instance of your ontology.
     * Only text widgets are now supported
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class clazz
     * @param  Resource instance
     * @return tao_helpers_form_Form
     * @see tao_helpers_form_GenerisFormFactory::isntanceEditor
     */
    public static function translateInstanceEditor( core_kernel_classes_Class $clazz,  core_kernel_classes_Resource $instance)
    {
        $returnValue = null;

        // section 127-0-1-1--442e4448:1269d8ce833:-8000:0000000000001E7E begin
		
		$myForm = self::instanceEditor($clazz, $instance);
		$elements = $myForm->getElements();
		
		$myTranslatedForm = tao_helpers_form_FormFactory::getForm('translate_'.$myForm->getName());
		$myTranslatedForm->setActions(tao_helpers_form_FormFactory::getCommonActions('top'), 'top');
		
		$currentLangElt = tao_helpers_form_FormFactory::getElement('current_lang', 'Textbox');
		$currentLangElt->setDescription(__('Current language'));
		$currentLangElt->setAttributes(array('readonly' => 'true'));
		$currentLangElt->setValue(__(core_kernel_classes_Session::singleton()->getLg()));
		$myTranslatedForm->addElement($currentLangElt);
		
		$options = array();
		foreach($GLOBALS['available_langs'] as $langCode){
			$options[$langCode] = __($langCode);
		}
		$dataLangElement = tao_helpers_form_FormFactory::getElement('translate_lang', 'Combobox');
		$dataLangElement->setDescription(__('Translate to'));
		$dataLangElement->setOptions($options);
		$dataLangElement->setEmptyOption(__('Select a language'));
		$dataLangElement->addValidator( tao_helpers_form_FormFactory::getValidator('NotEmpty') );
		$myTranslatedForm->addElement($dataLangElement);
		
		$myTranslatedForm->createGroup('translation_info', __('Translation parameters'), array('current_lang', 'translate_lang'));
		
		$dataGroup = array();
		$level = 5;
		foreach($elements as $element){
			
			$element->setLevel($level);
			if( $element instanceof tao_helpers_form_elements_Hidden ||
				$element->getName() == 'uri' || $element->getName() == 'classUri'){
					
				$myTranslatedForm->addElement($element);
				
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
					
					$name = 'view_'.$element->getName();
					$element->setName($name);
					$element->setAttributes(array('readonly' => 'true'));
					$myTranslatedForm->addElement($element);
					
					$translatedElt->setDescription(' ');
					$translatedElt->setValue('');
					$translatedElt->setLevel($level + 1);
					$myTranslatedForm->addElement($translatedElt);
					
					$dataGroup[] = $name;
					$dataGroup[] = $translatedElt->getName();
				}
			}
			$level += 2;
		}
		
		$myTranslatedForm->createGroup('translation_form', __('Translate'), $dataGroup);
		
		//form data evaluation
		$myTranslatedForm->evaluate();
			
		$returnValue = $myTranslatedForm;
		
        // section 127-0-1-1--442e4448:1269d8ce833:-8000:0000000000001E7E end

        return $returnValue;
    }

    /**
     * Short description of method searchInstancesEditor
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class clazz
     * @param  boolean recursive
     * @return tao_helpers_form_Form
     */
    public static function searchInstancesEditor( core_kernel_classes_Class $clazz, $recursive = false)
    {
        $returnValue = null;

        // section 127-0-1-1-22aa168e:126ae36f293:-8000:0000000000001E8D begin
		
		if(empty($name)){
			$name = 'form_'.(count(self::$forms)+1);
		}
			
		$myForm = tao_helpers_form_FormFactory::getForm($name);
			
		//search action in toolbar
		$searchELt = tao_helpers_form_FormFactory::getElement('search', 'Free');
		$searchELt->setValue("<a href='#' class='form-submiter' ><img src='".TAOBASE_WWW."/img/search.png'  /> ".__('Search')."</a>");
		$myForm->setActions(array($searchELt), 'top');
		
		$searchBtnElt = tao_helpers_form_FormFactory::getElement('search-button', 'Submit');
		$searchBtnElt->setValue(__('Search'));
		$myForm->setActions(array($searchBtnElt), 'bottom');
		
		$chainingElt = tao_helpers_form_FormFactory::getElement('chaining', 'Radiobox');
		$chainingElt->setDescription(__('Filtering mode'));
		$chainingElt->setOptions(array('and' => __('Exclusive (AND)'), 'or' =>  __('Inclusive (OR)')));
		$chainingElt->setValue('and');
		$myForm->addElement($chainingElt);
		
		$options = array();
		foreach($GLOBALS['available_langs'] as $langCode){
			$options[$langCode] = __($langCode);
		}
		$langElt = tao_helpers_form_FormFactory::getElement('lang', 'Combobox');
		$langElt->setDescription(__('Language'));
		$langElt->setOptions($options);
		$myForm->addElement($langElt);
		
		$myForm->createGroup('params', __('Options'), array('chaining', 'lang'));
		
		
		$filters = array();
		
		$descElt = tao_helpers_form_FormFactory::getElement('desc', 'Label');
		$descElt->setValue(__('Use the * character to replace any string'));
		$myForm->addElement($descElt);
		$filters[] = 'desc';
		
		$defaultProperties 	= self::getDefaultProperties();
		$classProperties	= self::getClassProperties($clazz, new core_kernel_classes_Class(self::DEFAULT_TOP_LEVEL_CLASS));
		
		$properties = array_merge($defaultProperties, $classProperties);
		
		if($recursive){
			foreach($clazz->getSubClasses(true) as $subClass){
				$properties = array_merge(self::getClassProperties($subClass, $subClass), $properties);
			}
		}
		
		foreach($properties as $property){
	
			$element = self::elementMap($property);
			if(!is_null($element) && ! $element instanceof tao_helpers_form_elements_Authoring ){
				
				if($element instanceof tao_helpers_form_elements_Radiobox || $element instanceof tao_helpers_form_elements_Combobox){
					$newElement = tao_helpers_form_FormFactory::getElement($element->getName(), 'Checkbox');
					$newElement->setDescription($element->getDescription());
					$newElement->setOptions($element->getOptions());
					$element = $newElement;
				}
				
				$myForm->addElement($element);
				$filters[] = $element->getName();
			
			}
		}
		$myForm->createGroup('filters', __('Filters'), $filters);
		
		$myForm->evaluate();
		
		$returnValue = $myForm;
		
        // section 127-0-1-1-22aa168e:126ae36f293:-8000:0000000000001E8D end

        return $returnValue;
    }

    /**
     * create a Form to add a subclass to the rdfs:Class clazz
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
			
			//add property action in toolbar
			$topActions = tao_helpers_form_FormFactory::getCommonActions('top');
			$propertyElt = tao_helpers_form_FormFactory::getElement('property', 'Free');
			$propertyElt->setValue(" | <a href='#' class='property-adder'><img src='".TAOBASE_WWW."/img/prop_add.png'  /> ".__('Add property')."</a>");
			$topActions[] = $propertyElt;
			$myForm->setActions($topActions, 'top');
			
			//set bottom property actions
			$bottomActions = tao_helpers_form_FormFactory::getCommonActions('bottom');
			$addPropElement = tao_helpers_form_FormFactory::getElement('propertyAdder', 'Button');
			$addPropElement->addAttribute('class', 'property-adder');
			$addPropElement->setValue(__('Add a new property'));
			$bottomActions[] = $addPropElement;
			$myForm->setActions($bottomActions, 'bottom');
			
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
				$myForm->createGroup('class', "<img src='/tao/views/img/class.png' /> ".__('Class').": ".$clazz->getLabel(), $elementNames);
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
					
					$myForm->createGroup("parent_property_{$i}", "<img src='/tao/views/img/prop_orange.png' /> ".__('Property')." #".($i).": ".$classProperty->getLabel(), array('parentProperty'.$i));
				}
				else{
					$roElement = tao_helpers_form_FormFactory::getElement('roProperty'.$i, 'Free');
					$roElement->setValue(__("You cannot modify this property"));
					$myForm->addElement($roElement);
					
					$myForm->createGroup("ro_property_{$i}", "<img src='/tao/views/img/prop_red.png' /> ".__('Property')." #".($i).": ".$classProperty->getLabel(), array('roProperty'.$i));
				}
			}
			
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
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Property property
     * @param  Form form
     * @param  int index
     * @return tao_helpers_form_Form
     */
    protected static function simplePropertyEditor( core_kernel_classes_Property $property,  tao_helpers_form_Form $form, $index = 0)
    {
        $returnValue = null;

        // section 127-0-1-1-47336e64:124c90d0af6:-8000:0000000000001B39 begin
		
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
			TAO_TEST_CLASS,
			TAO_DELIVERY_CLASS,
			TAO_DELIVERY_CAMPAIGN_CLASS,
			TAO_DELIVERY_RESULTSERVER_CLASS,
			TAO_DELIVERY_HISTORY_CLASS
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
		$listElt->setOptions(array_merge($options, array('new' => ' + '.__('Add / Edit lists'))));
		$form->addElement($listElt);
		$elementNames[] = $listElt->getName();
		
		//add a delete button 
		$deleteElt = tao_helpers_form_FormFactory::getElement("propertyDeleter{$index}", 'Button');
		$deleteElt->addAttribute('class', 'property-deleter');
		$deleteElt->setValue(__('Delete property'));
		$form->addElement($deleteElt);
		$elementNames[] = $deleteElt->getName();
		
		//add an hidden element with the mode (simple)
		$modeElt = tao_helpers_form_FormFactory::getElement("propertyMode{$index}", 'Hidden');
		$modeElt->setValue('simple');
		$form->addElement($modeElt);
		$elementNames[] = $modeElt->getName();
		
		if(count($elementNames) > 0){
			$form->createGroup("property_{$index}", "<img src='/tao/views/img/prop_green.png' /> ".__('Property')." #".($index).": ".$property->getLabel(), $elementNames);
		}
			
		$returnValue = $form;
		
        // section 127-0-1-1-47336e64:124c90d0af6:-8000:0000000000001B39 end

        return $returnValue;
    }

    /**
     * Short description of method advancedPropertyEditor
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
				$form->addElement($element);
				$elementNames[] = $element->getName();
				$level++;
			}
		}
		
		//add a delete button 
		$deleteElt = tao_helpers_form_FormFactory::getElement("propertyDeleter{$index}", 'Button');
		$deleteElt->addAttribute('class', 'property-deleter');
		$deleteElt->setValue(__('Delete property'));
		$form->addElement($deleteElt);
		$elementNames[] = $deleteElt->getName();
		$level++;
		
		//add an hidden element with the mode (simple)
		$modeElt = tao_helpers_form_FormFactory::getElement("propertyMode{$index}", 'Hidden');
		$modeElt->setValue('advanced');
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
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Property property
     * @return tao_helpers_form_FormElement
     */
    public static function elementMap( core_kernel_classes_Property $property)
    {
        $returnValue = null;

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001937 begin
		
		//create the element from the right widget
		$widgetResource = $property->getWidget();
		if(is_null($widgetResource)){
			return null;
		}
		$widget = ucfirst(strtolower(substr($widgetResource->uriResource, strrpos($widgetResource->uriResource, '#') + 1 )));
		
		//authoring widget is not used in standalone mode
		if($widget == 'Authoring' && self::$mode == self::MODE_STANDALONE){
			return null;
		}
		
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
     * Short description of method getClassProperties
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
			$returnValue = $clazz->getProperties(true);
		}
		else{
			if($clazz->uriResource == $topLevelClazz->uriResource){
				return (array) $clazz->getProperties(false);
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
			
			$returnValue = array_merge($returnValue, $clazz->getProperties(false));
		}
		
        // section 127-0-1-1-2db84171:12476b7fa3b:-8000:0000000000001AAB end

        return (array) $returnValue;
    }

    /**
     * get the default properties to add to every forms
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
			),
			'calendar' => array(
				'title' 	=> __('A dynamic datepicker'),
				'widget'	=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Calendar',
				'range'		=> 'http://www.w3.org/2000/01/rdf-schema#Literal'
			)
		);
		
        // section 127-0-1-1-47336e64:124c90d0af6:-8000:0000000000001B31 end

        return (array) $returnValue;
    }

    /**
     * Short description of method setMode
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  int mode
     * @return mixed
     */
    public static function setMode($mode)
    {
        // section 127-0-1-1-4e0e5d33:126dbe320ca:-8000:0000000000001EAB begin
		
		if($mode != self::MODE_SOFT && $mode != self::MODE_STANDALONE){
			throw new Exception("Unknown mode");
		}
		
		self::$mode = $mode;
        
		// section 127-0-1-1-4e0e5d33:126dbe320ca:-8000:0000000000001EAB end
    }

} /* end of class tao_helpers_form_GenerisFormFactory */

?>