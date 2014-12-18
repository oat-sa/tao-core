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
class tao_actions_form_IndexProperty
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
    	
    	$elementNames = array();

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
            $propIndexElt = tao_helpers_form_FormFactory::getElement("index_{$index}_{$indexUri}_".tao_helpers_Uri::encode(INDEX_PROPERTY_TOKENIZER), 'Combobox');
            $propIndexElt->setDescription(__('Tokenizer'));
            $propIndexElt->addAttribute('class', 'index-tokenizer');
            $propIndexElt->setOptions($options);
            $propIndexElt->setEmptyOption(' --- '.__('select').' --- ');
            $propIndexElt->setValue(tao_helpers_Uri::encode($tokenizer->uriResource));
            $this->form->addElement($propIndexElt);
            $elementNames[] = $propIndexElt->getName();

        }

    }

}