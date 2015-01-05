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
				$element->setName("property_{$index}_{$element->getName()}");
				$this->form->addElement($element);
				$elementNames[] = $element->getName();
                
                if ($propertyProperty->getUri() == TAO_GUIORDER_PROP){
                    $element->addValidator(tao_helpers_form_FormFactory::getValidator('Integer'));
                }
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
			
		$listElt = tao_helpers_form_FormFactory::getElement("property_{$index}_range", 'Combobox');
		$listElt->setDescription(__('List values'));
		$listElt->addAttribute('class', 'property-listvalues');
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
		
		//add an hidden element with the mode (simple)
		$modeElt = tao_helpers_form_FormFactory::getElement("propertyMode{$index}", 'Hidden');
		$modeElt->setValue('simple');
		$this->form->addElement($modeElt);
		$elementNames[] = $modeElt->getName();

        //index part
        $indexes = $property->getPropertyValues(new \core_kernel_classes_Property(INDEX_PROPERTY));
        foreach($indexes as $indexUri){
            $indexProperty = new \oat\tao\model\search\Index($indexUri);
            $indexUri = tao_helpers_Uri::encode($indexUri);

            //get and add Label (Text)
            $label = $indexProperty->getLabel();
            $propIndexElt = tao_helpers_form_FormFactory::getElement("index_{$index}_{$indexUri}_".tao_helpers_Uri::encode(RDFS_LABEL), 'Textbox');
            $propIndexElt->setDescription(__('Label'));
            $propIndexElt->addAttribute('class', 'index-label');
            $propIndexElt->setValue(tao_helpers_Uri::encode($label));
            $this->form->addElement($propIndexElt);
            $elementNames[] = $propIndexElt->getName();


            //get and add Fuzzy matching (Radiobox)
            $fuzzyMatching = ($indexProperty->isFuzzyMatching())?GENERIS_TRUE:GENERIS_FALSE;
            $options = array(
                tao_helpers_Uri::encode(GENERIS_TRUE)  => __('True'),
                tao_helpers_Uri::encode(GENERIS_FALSE) => __('False')
            );
            $propIndexElt = tao_helpers_form_FormFactory::getElement("index_{$index}_{$indexUri}_".tao_helpers_Uri::encode(INDEX_PROPERTY_FUZZY_MATCHING), 'Radiobox');
            $propIndexElt->setOptions($options);
            $propIndexElt->setDescription(__('Fuzzy Matching'));
            $propIndexElt->addAttribute('class', 'index-fuzzymatching');
            $propIndexElt->setValue(tao_helpers_Uri::encode($fuzzyMatching));
            $this->form->addElement($propIndexElt);
            $elementNames[] = $propIndexElt->getName();

            //get and add identifier (Text)
            $identifier = $indexProperty->getIdentifier();
            $propIndexElt = tao_helpers_form_FormFactory::getElement("index_{$index}_{$indexUri}_".tao_helpers_Uri::encode(INDEX_PROPERTY_IDENTIFIER), 'Textbox');
            $propIndexElt->setDescription(__('Identifier'));
            $propIndexElt->addAttribute('class', 'index-identifier');
            $propIndexElt->setValue(tao_helpers_Uri::encode($identifier));
            $propIndexElt->addValidator(new tao_helpers_form_validators_NotEmpty());
            $this->form->addElement($propIndexElt);
            $elementNames[] = $propIndexElt->getName();

            //get and add Default search
            $defaultSearch = ($indexProperty->isDefaultSearchable())?GENERIS_TRUE:GENERIS_FALSE;
            $options = array(
                tao_helpers_Uri::encode(GENERIS_TRUE)  => __('True'),
                tao_helpers_Uri::encode(GENERIS_FALSE) => __('False')
            );
            $propIndexElt = tao_helpers_form_FormFactory::getElement("index_{$index}_{$indexUri}_".tao_helpers_Uri::encode(INDEX_PROPERTY_DEFAULT_SEARCH), 'Radiobox');
            $propIndexElt->setOptions($options);
            $propIndexElt->setDescription(__('Default search'));
            $propIndexElt->addAttribute('class', 'index-defaultsearch');
            $propIndexElt->setValue(tao_helpers_Uri::encode($defaultSearch));
            $this->form->addElement($propIndexElt);
            $elementNames[] = $propIndexElt->getName();

            //get and add Tokenizer (Combobox)
            $tokenizerRange = new \core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAO.rdf#Tokenizer');
            $options = array();
            /** @var core_kernel_classes_Resource $value */
            foreach($tokenizerRange->getInstances() as $value){
                $options[tao_helpers_Uri::encode($value->getUri())] = $value->getLabel();
            }

            $tokenizer = $indexProperty->getOnePropertyValue(new \core_kernel_classes_Property(INDEX_PROPERTY_TOKENIZER));
            $tokenizer = (get_class($tokenizer) === 'core_kernel_classes_Resource')?$tokenizer->getUri():'';
            $propIndexElt = tao_helpers_form_FormFactory::getElement("index_{$index}_{$indexUri}_".tao_helpers_Uri::encode(INDEX_PROPERTY_TOKENIZER), 'Combobox');
            $propIndexElt->setDescription(__('Tokenizer'));
            $propIndexElt->addAttribute('class', 'index-tokenizer');
            $propIndexElt->setOptions($options);
            $propIndexElt->setValue(tao_helpers_Uri::encode($tokenizer));
            $propIndexElt->addValidator(new tao_helpers_form_validators_NotEmpty());
            $this->form->addElement($propIndexElt);
            $elementNames[] = $propIndexElt->getName();

            $removeIndexElt = tao_helpers_form_FormFactory::getElement("index_{$index}_{$indexUri}_remove", 'Free');
            $removeIndexElt->setValue(
                "<a href='#' id='{$indexUri}' class='btn-error index-remover small'><span class='icon-remove'></span> " . __(
                    'remove index'
                ) . "</a>"
            );
            $this->form->addElement($removeIndexElt);
            $elementNames[] = $removeIndexElt;

            $separatorIndexElt = tao_helpers_form_FormFactory::getElement("index_{$index}_{$indexUri}_separator", 'Free');
            $separatorIndexElt->setValue(
                "<hr id='index_{$index}_{$indexUri}_separator'>"
            );
            $this->form->addElement($separatorIndexElt);
            $elementNames[] = $separatorIndexElt;

        }

        //add this element only when the property is defined (type)
        if(!is_null($property->getRange())){
            $addIndexElt = tao_helpers_form_FormFactory::getElement("index_{$index}_add", 'Free');
            $addIndexElt->setValue(
                "<a href='#' id='index_{$index}_add' class='btn-info index-adder small'><span class='icon-add'></span> " . __(
                    'Add index'
                ) . "</a><div class='clearfix'></div>"
            );
            $this->form->addElement($addIndexElt);
            $elementNames[] = $addIndexElt;
        }
        else{
            $addIndexElt = tao_helpers_form_FormFactory::getElement("index_{$index}_p", 'Free');
            $addIndexElt->setValue(
                "<p id='index_{$index}_p' >" . __(
                    'Choose a type for your property first'
                ) . "</p>"
            );
            $this->form->addElement($addIndexElt);
            $elementNames[] = $addIndexElt;
        }


		if(count($elementNames) > 0){
			$groupTitle = $this->getGroupTitle($property);
			$this->form->createGroup("property_{$index}", $groupTitle, $elementNames);
		}
    	
		//add an hidden elt for the property uri
		$propUriElt = tao_helpers_form_FormFactory::getElement("propertyUri{$index}", 'Hidden');
		$propUriElt->addAttribute('class', 'property-uri');
		$propUriElt->setValue(tao_helpers_Uri::encode($property->getUri()));
		$this->form->addElement($propUriElt);

    }

}