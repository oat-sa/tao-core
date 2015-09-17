<?php
/**  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

/**
 * Create a form from a  resource of your ontology. 
 * Each property will be a field, regarding it's widget.
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 
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
        
        
    	$name = isset($this->options['name']) ? $this->options['name'] : ''; 
    	if(empty($name)){
			$name = 'form_'.(count(self::$forms)+1);
		}
		unset($this->options['name']);
			
		$this->form = tao_helpers_form_FormFactory::getForm($name, $this->options);
    	
		//add translate action in toolbar
		$actions = tao_helpers_form_FormFactory::getCommonActions();
		$this->form->setActions($actions, 'top');
		$this->form->setActions($actions, 'bottom');
		
        
    }
    
    protected function getExcludedProperties()
    {
        return (isset($this->options['excludedProperties']) && is_array($this->options['excludedProperties']))
            ? $this->options['excludedProperties']
            : array();
        
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
        
    	$instance = $this->getInstance();
    	$guiOrderProperty = new core_kernel_classes_Property(TAO_GUIORDER_PROP);
    	
    	//get the list of properties to set in the form
		$properties = array();
		foreach (tao_helpers_form_GenerisFormFactory::getDefaultProperties() as $property) {
		    $properties[$property->getUri()] = $property;
		}
		
		foreach ($instance->getTypes() as $type) {
		    $addProp = tao_helpers_form_GenerisFormFactory::getClassProperties($type, $this->getTopClazz());
		    foreach ($addProp as $property) {
		        $properties[$property->getUri()] = $property;
		    }
		}

		$additionalProperties = (isset($this->options['additionalProperties']) && is_array($this->options['additionalProperties']))
            ? $this->options['additionalProperties']
            : array();
		foreach ($additionalProperties as $property) {
		    $properties[$property->getUri()] = $property;
		}
		
		
		foreach ($this->getExcludedProperties() as $property) {
		    if (isset($properties[$property->getUri()])) {
		        unset($properties[$property->getUri()]);
		    }
		}
			
		$finalElements = array();
		
		$instanceValues = is_null($instance) ? array() : $instance->getPropertiesValues($properties);
		
    	foreach($properties as $property){

			$property->feed();
			
			//map properties widgets to form elments 
			$element = tao_helpers_form_GenerisFormFactory::elementMap($property);
			
			if(!is_null($element)){
				
				//take instance values to populate the form
				if(isset($instanceValues[$property->getUri()])) {
					
					foreach($instanceValues[$property->getUri()] as $value) {
						if(!is_null($value)){
							if($value instanceof core_kernel_classes_Resource){
								if($element instanceof tao_helpers_form_elements_Readonly){
									$element->setValue($value->getLabel());
								}else{
									$element->setValue($value->getUri());
								}
							}
							if($value instanceof core_kernel_classes_Literal){
								$element->setValue((string)$value);
							}
						}
					}
				}
					
				//set label validator
				if($property->getUri() == RDFS_LABEL){
					$element->setDescription(__('Label *'));
					$element->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
				}

				// don't show empty labels
				if($element instanceof tao_helpers_form_elements_Label && strlen($element->getRawValue()) == 0) {
					continue;
				}
				
				//set file element validator:
				if($element instanceof tao_helpers_form_elements_AsyncFile){
					
				}
				
				if ($property->getUri() == RDFS_LABEL){
					// Label will not be a TAO Property. However, it should
					// be always first.
					array_splice($finalElements, 0, 0, array(array($element, 1)));
				}
				else if (count($guiOrderPropertyValues = $property->getPropertyValues($guiOrderProperty))){
					
					// get position of this property if it has one.
					$position = intval($guiOrderPropertyValues[0]);
					
					// insert the element at the right place.
					$i = 0;
					while ($i < count($finalElements) && ($position >= $finalElements[$i][1] && $finalElements[$i][1] !== null)){
						$i++;
					}
					
					array_splice($finalElements, $i, 0, array(array($element, $position)));
				}
				else{
					// Unordered properties will go at the end of the form.
					$finalElements[] = array($element, null);
				}
			}
		}
		
		// Add elements related to class properties to the form.
		foreach ($finalElements as $element){
			$this->form->addElement($element[0]);
		}
			
		if(!is_null($instance)){
			//add an hidden elt for the instance Uri
			$instanceUriElt = tao_helpers_form_FormFactory::getElement('uri', 'Hidden');
			$instanceUriElt->setValue(tao_helpers_Uri::encode($instance->getUri()));
			$this->form->addElement($instanceUriElt);
			
			$hiddenId = tao_helpers_form_FormFactory::getElement('id', 'Hidden');
			$hiddenId->setValue($instance->getUri());
			$this->form->addElement($hiddenId);
		}
        
        
    }

}
