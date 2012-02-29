<?php

error_reporting(E_ALL);

/**
 * Create a form from a  resource of your ontology. 
 * Each property will be a field, regarding it's widget.
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
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248C-includes begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248C-includes end

/* user defined constants */
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248C-constants begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248C-constants end

/**
 * Create a form from a  resource of your ontology. 
 * Each property will be a field, regarding it's widget.
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Instance
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
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002496 begin
        
    	(isset($this->options['name'])) ? $name = $this->options['name'] : $name = ''; 
    	if(empty($name)){
			$name = 'form_'.(count(self::$forms)+1);
		}
		unset($this->options['name']);
			
		$this->form = tao_helpers_form_FormFactory::getForm($name, $this->options);
    	
		//add translate action in toolbar
		$actions = tao_helpers_form_FormFactory::getCommonActions();
		
		if(!tao_helpers_Context::check('STANDALONE_MODE')){
			$translateELt = tao_helpers_form_FormFactory::getElement('translate', 'Free');
			$translateELt->setValue("<a href='#' class='form-translator' ><img src='".TAOBASE_WWW."/img/translate.png'  /> ".__('Translate')."</a>");
			$actions[] = $translateELt;
		}
		
		$this->form->setActions($actions, 'top');
		$this->form->setActions($actions, 'bottom');
		
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002496 end
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
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002498 begin
        
    	
    	$clazz = $this->getClazz();
    	$instance = $this->getInstance();
    	$guiOrderProperty = new core_kernel_classes_Property(TAO_GUIORDER_PROP);
    	
    	//get the list of properties to set in the form
    	$defaultProperties 	= tao_helpers_form_GenerisFormFactory::getDefaultProperties();
		$editedProperties = $defaultProperties;
		$excludedProperties = (isset($this->options['excludedProperties']) && is_array($this->options['excludedProperties']))?$this->options['excludedProperties']:array();
		$additionalProperties = (isset($this->options['additionalProperties']) && is_array($this->options['additionalProperties']))?$this->options['additionalProperties']:array();
    	$uniqueLabel = isset($this->options['uniqueLabel'])?$this->options['uniqueLabel']:true;
		$finalElements = array();
    	
		$classProperties = array();
		/**
		 * @todo override it in the taoSubject module instead of having this crapy IF here
		 */
		if(Session::getAttribute('currentExtension') == 'taoSubjects'){
			$classProperties = tao_helpers_form_GenerisFormFactory::getClassProperties($clazz);
			$classProperties = array_merge($classProperties, tao_helpers_form_GenerisFormFactory::getClassProperties(new core_kernel_classes_Class(CLASS_ROLE_SUBJECT), new core_kernel_classes_Class(CLASS_GENERIS_USER)));
		}
		else{
			$classProperties = tao_helpers_form_GenerisFormFactory::getClassProperties($clazz, $this->getTopClazz());
			if(!empty($additionalProperties)){
				$classProperties = array_merge($classProperties, $additionalProperties);
			}
		}
		
		foreach($classProperties as $property){
			if(!isset($editedProperties[$property->uriResource]) && !in_array($property->uriResource, $excludedProperties)){
				$editedProperties[$property->uriResource] = $property;
			}
		}
			
		foreach($editedProperties as $property){

			$property->feed();
			$widget = $property->getWidget();
			if($widget == null || $widget instanceof core_kernel_classes_Literal) {
				continue;
			}
			else if ($widget instanceof core_kernel_classes_Resource &&	$widget->uriResource == WIDGET_TREEVIEW){
			    continue;
			}
			
			//map properties widgets to form elments 
			$element = tao_helpers_form_GenerisFormFactory::elementMap($property);
			
			if(!is_null($element)){
				
				//take instance values to populate the form
				if(!is_null($instance)){
					
					$values = $instance->getPropertyValuesCollection($property);
					foreach($values->getIterator() as $value){
						if(!is_null($value)){
							if($value instanceof core_kernel_classes_Resource){
								if($element instanceof tao_helpers_form_elements_Readonly){
									$element->setValue($value->getLabel());
								}else{
									$element->setValue($value->uriResource);
								}
							}
							if($value instanceof core_kernel_classes_Literal){
								$element->setValue((string)$value);
							}
						}
					}
				}
					
				//set label validator
				if($property->uriResource == RDFS_LABEL){
					if($uniqueLabel){
						$element->addValidator(tao_helpers_form_FormFactory::getValidator('Label', array('class' => $clazz, 'uri' => $instance->uriResource)));
					}
					$element->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
				}
				//set file element validator:
				if($element instanceof tao_helpers_form_elements_AsyncFile){
					
				}
				
				if ($property->uriResource == RDFS_LABEL){
					// Label will not be a TAO Property. However, it should
					// be always first.
					array_splice($finalElements, 0, 0, array(array($element, 1)));
				}
				else if (!array_key_exists(TAO_PROP, $property->getType())){
					// Unordered properties will go at the end of the form.
					$finalElements[] = array($element, null);
				}
				else{
					// get position of this property if it has one.
					$guiOrderPropertyValues = $property->getPropertyValues($guiOrderProperty);
					if (count($guiOrderPropertyValues)){
						$position = intval($guiOrderPropertyValues[0]);
						
						// insert the element at the right place.
						$i = 0;
						while ($i < count($finalElements) && ($position >= $finalElements[$i][1] && $finalElements[$i][1] !== null)){
							$i++;
						}
						
						array_splice($finalElements, $i, 0, array(array($element, $position)));
					}
					else{
						// Unknown position. It will go at the end of the form.
						$finalElements[] = array($element, null);
					}
				}
			}
		}

		// Add elements related to class properties to the form.
		foreach ($finalElements as $element){
			$this->form->addElement($element[0]);
		}
		
		//add an hidden elt for the class uri
		$classUriElt = tao_helpers_form_FormFactory::getElement('classUri', 'Hidden');
		$classUriElt->setValue(tao_helpers_Uri::encode($clazz->uriResource));
		$this->form->addElement($classUriElt);
			
		if(!is_null($instance)){
			//add an hidden elt for the instance Uri
			$instanceUriElt = tao_helpers_form_FormFactory::getElement('uri', 'Hidden');
			$instanceUriElt->setValue(tao_helpers_Uri::encode($instance->uriResource));
			$this->form->addElement($instanceUriElt);
		}
        
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:0000000000002498 end
    }

} /* end of class tao_actions_form_Instance */

?>