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
 * Enable you to edit a property
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 
 */
class tao_actions_form_SimpleProperty
    extends tao_actions_form_AbstractProperty
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---


    /**
     * Initialize the form elements
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initElements()
    {
        
        
    	$property = new core_kernel_classes_Property($this->instance->getUri());
    	
    	(isset($this->options['index'])) ? $index = $this->options['index'] : $index = 1;
    	
		$propertyProperties = array_merge(
			tao_helpers_form_GenerisFormFactory::getDefaultProperties(), 
			array(new core_kernel_classes_Property(PROPERTY_IS_LG_DEPENDENT),
				  new core_kernel_classes_Property(TAO_GUIORDER_PROP))
		);
    	
    	$elementNames = array();
		foreach($propertyProperties as $propertyProperty){
		
			//map properties widgets to form elements
			$element = tao_helpers_form_GenerisFormFactory::elementMap($propertyProperty);
			
			if(!is_null($element)){
				//take property values to populate the form
				$values = $property->getPropertyValuesCollection($propertyProperty);
				foreach($values->getIterator() as $value){
					if(!is_null($value)){
						if($value instanceof core_kernel_classes_Resource){
							$element->setValue($value->getUri());
						}
						if($value instanceof core_kernel_classes_Literal){
							$element->setValue((string)$value);
						}
					}
				}
				$element->setName("{$index}_{$element->getName()}");
                $element->addClass('property');

                if ($propertyProperty->getUri() == TAO_GUIORDER_PROP){
                    $element->addValidator(tao_helpers_form_FormFactory::getValidator('Integer'));
                }
                if ($propertyProperty->getUri() == RDFS_LABEL){
                    $element->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
                }
				$this->form->addElement($element);
				$elementNames[] = $element->getName();
			}
		}
		
		//build the type list from the "widget/range to type" map
		$typeElt = tao_helpers_form_FormFactory::getElement("{$index}_type", 'Combobox');
		$typeElt->setDescription(__('Type'));
		$typeElt->addAttribute('class', 'property-type property');
		$typeElt->setEmptyOption(' --- '.__('select').' --- ');
		$options = array();
		$checkRange = false;
		foreach(tao_helpers_form_GenerisFormFactory::getPropertyMap() as $typeKey => $map){
			$options[$typeKey] = $map['title'];
            $widget = $property->getWidget();
			if($widget instanceof core_kernel_classes_Resource) {
				if($widget->getUri() == $map['widget']){
					$typeElt->setValue($typeKey);
					$checkRange = is_null($map['range']);
				}
			}
		}
		$typeElt->setOptions($options);
		$this->form->addElement($typeElt);
		$elementNames[] = $typeElt->getName();
		
		//list drop down
		$listService = tao_models_classes_ListService::singleton();
			
		$listElt = tao_helpers_form_FormFactory::getElement("{$index}_range", 'Combobox');
		$listElt->setDescription(__('List values'));
		$listElt->addAttribute('class', 'property-listvalues property');
		$listElt->setEmptyOption(' --- '.__('select').' --- ');
		$listOptions = array();
		foreach($listService->getLists() as $list){
			$listOptions[tao_helpers_Uri::encode($list->getUri())] = $list->getLabel();
			$range = $property->getRange();
			if(!is_null($range)){
				if($range->getUri() == $list->getUri()){
					$listElt->setValue($list->getUri());
				}
			}
		}
		
		$listOptions['new'] = ' + '.__('Add / Edit lists');
		$listElt->setOptions($listOptions);
		if($checkRange){
			$listElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		}
		$this->form->addElement($listElt);
		$elementNames[] = $listElt->getName();

        //index part
        $indexes = $property->getPropertyValues(new \core_kernel_classes_Property(INDEX_PROPERTY));
        foreach($indexes as $i => $indexUri){
            $indexProperty = new \oat\tao\model\search\Index($indexUri);
            $indexFormContainer = new tao_actions_form_IndexProperty($this->getClazz(), $indexProperty,
                array('property' => $property->getUri(),
                    'propertyindex' => $index,
                    'index' => $i)
            );
            /** @var tao_helpers_form_Form $indexForm */
            $indexForm = $indexFormContainer->getForm();
            foreach($indexForm->getElements() as $element){
                $this->form->addElement($element);
                $elementNames[] = $element->getName();
            }
        }

        //add this element only when the property is defined (type)
        if(!is_null($property->getRange())){
            $addIndexElt = tao_helpers_form_FormFactory::getElement("index_{$index}_add", 'Free');
            $addIndexElt->setValue(
                "<a href='#' class='btn-info index-adder small index'><span class='icon-add'></span> " . __(
                    'Add index'
                ) . "</a><div class='clearfix'></div>"
            );
            $this->form->addElement($addIndexElt);
            $elementNames[] = $addIndexElt;
        }
        else{
            $addIndexElt = tao_helpers_form_FormFactory::getElement("index_{$index}_p", 'Free');
            $addIndexElt->setValue(
                "<p class='index' >" . __(
                    'Choose a type for your property first'
                ) . "</p>"
            );
            $this->form->addElement($addIndexElt);
            $elementNames[] = $addIndexElt;
        }

        //add an hidden elt for the property uri
        $encodedUri = tao_helpers_Uri::encode($property->getUri());
        $propUriElt = tao_helpers_form_FormFactory::getElement("{$index}_uri", 'Hidden');
        $propUriElt->addAttribute('class', 'property-uri property');
        $propUriElt->setValue($encodedUri);
        $this->form->addElement($propUriElt);
        $elementNames[] = $propUriElt;

		if(count($elementNames) > 0){
			$groupTitle = $this->getGroupTitle($property);
			$this->form->createGroup("property_{$encodedUri}", $groupTitle, $elementNames);
		}

    }

}