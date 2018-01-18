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

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\search\Index;
use oat\tao\model\TaoOntology;

/**
 * Enable you to edit a property
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 */
class tao_actions_form_IndexProperty
    extends tao_helpers_form_FormContainer
{
    /**
     * @var Index
     */
    protected $index;

    protected $prefix;

    public function __construct(Index $index, $prefix)
    {
        $this->index = $index;
        $this->prefix = $prefix;
        return parent::__construct();
    }

    /**
     * @return Index
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Initialize the form
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initForm()
    {
        $this->form = tao_helpers_form_FormFactory::getForm('indexform', array());
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

    	$elementNames = array();

        //index part
        $indexProperty = $this->getIndex();
        $indexUri = tao_helpers_Uri::encode($indexProperty->getUri());

        //get and add Label (Text)
        $label = (!is_null($indexProperty))?$indexProperty->getLabel():'';
        $propIndexElt = tao_helpers_form_FormFactory::getElement("index_".$this->prefix."_".tao_helpers_Uri::encode(OntologyRdfs::RDFS_LABEL), 'Textbox');
        $propIndexElt->setDescription(__('Label'));
        $propIndexElt->addAttribute('class', 'index');
        $propIndexElt->addAttribute('data-related-index', $indexUri);
        $propIndexElt->setValue($label);
        $this->form->addElement($propIndexElt);
        $elementNames[] = $propIndexElt->getName();


        //get and add Fuzzy matching (Radiobox)
        $fuzzyMatching = (!is_null($indexProperty))?($indexProperty->isFuzzyMatching())?GenerisRdf::GENERIS_TRUE:GenerisRdf::GENERIS_FALSE:'';
        $options = array(
            tao_helpers_Uri::encode(GenerisRdf::GENERIS_TRUE)  => __('True'),
            tao_helpers_Uri::encode(GenerisRdf::GENERIS_FALSE) => __('False')
        );
        $propIndexElt = tao_helpers_form_FormFactory::getElement($this->prefix."_".tao_helpers_Uri::encode(Index::PROPERTY_INDEX_FUZZY_MATCHING), 'Radiobox');
        $propIndexElt->setOptions($options);
        $propIndexElt->setDescription(__('Fuzzy Matching'));
        $propIndexElt->addAttribute('class', 'index');
        $propIndexElt->addAttribute('data-related-index', $indexUri);
        $propIndexElt->setValue(tao_helpers_Uri::encode($fuzzyMatching));
        $propIndexElt->addValidator(new tao_helpers_form_validators_NotEmpty());
        $this->form->addElement($propIndexElt);
        $elementNames[] = $propIndexElt->getName();

        //get and add identifier (Text)
        $identifier = (!is_null($indexProperty))?$indexProperty->getIdentifier():'';
        $propIndexElt = tao_helpers_form_FormFactory::getElement($this->prefix."_".tao_helpers_Uri::encode(Index::PROPERTY_INDEX_IDENTIFIER), 'Textbox');
        $propIndexElt->setDescription(__('Identifier'));
        $propIndexElt->addAttribute('class', 'index');
        $propIndexElt->addAttribute('data-related-index', $indexUri);
        $propIndexElt->setValue($identifier);
        $propIndexElt->addValidator(new tao_helpers_form_validators_IndexIdentifier());
        $this->form->addElement($propIndexElt);
        $elementNames[] = $propIndexElt->getName();


        //get and add Default search
        $defaultSearch = (!is_null($indexProperty))?($indexProperty->isDefaultSearchable())?GenerisRdf::GENERIS_TRUE:GenerisRdf::GENERIS_FALSE:'';
        $options = array(
            tao_helpers_Uri::encode(GenerisRdf::GENERIS_TRUE)  => __('True'),
            tao_helpers_Uri::encode(GenerisRdf::GENERIS_FALSE) => __('False')
        );
        $propIndexElt = tao_helpers_form_FormFactory::getElement($this->prefix."_".tao_helpers_Uri::encode(Index::PROPERTY_DEFAULT_SEARCH), 'Radiobox');
        $propIndexElt->setOptions($options);
        $propIndexElt->setDescription(__('Default search'));
        $propIndexElt->addAttribute('class', 'index');
        $propIndexElt->addAttribute('data-related-index', $indexUri);
        $propIndexElt->setValue(tao_helpers_Uri::encode($defaultSearch));
        $propIndexElt->addValidator(new tao_helpers_form_validators_NotEmpty());
        $this->form->addElement($propIndexElt);
        $elementNames[] = $propIndexElt->getName();

        //get and add Tokenizer (Combobox)
        $tokenizerRange = new \core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAO.rdf#Tokenizer');
        $options = array();
        /** @var core_kernel_classes_Resource $value */
        foreach($tokenizerRange->getInstances() as $value){
            $options[tao_helpers_Uri::encode($value->getUri())] = $value->getLabel();
        }
        $tokenizer = (!is_null($indexProperty))?$indexProperty->getOnePropertyValue(new \core_kernel_classes_Property(Index::PROPERTY_INDEX_TOKENIZER)):null;
        $tokenizer = (get_class($tokenizer) === 'core_kernel_classes_Resource')?$tokenizer->getUri():'';
        $propIndexElt = tao_helpers_form_FormFactory::getElement($this->prefix."_".tao_helpers_Uri::encode(Index::PROPERTY_INDEX_TOKENIZER), 'Combobox');
        $propIndexElt->setDescription(__('Tokenizer'));
        $propIndexElt->addAttribute('class', 'index');
        $propIndexElt->addAttribute('data-related-index', $indexUri);
        $propIndexElt->setOptions($options);
        $propIndexElt->setValue(tao_helpers_Uri::encode($tokenizer));
        $propIndexElt->addValidator(new tao_helpers_form_validators_NotEmpty());
        $this->form->addElement($propIndexElt);
        $elementNames[] = $propIndexElt->getName();

        $removeIndexElt = tao_helpers_form_FormFactory::getElement("index_{$indexUri}_remove", 'Free');
        $removeIndexElt->setValue(
            "<a href='#' id='{$indexUri}' class='btn-error index-remover small' data-index='".$indexProperty->getUri()."'><span class='icon-remove'></span> " . __(
                'remove index'
            ) . "</a>"
        );
        $this->form->addElement($removeIndexElt);
        $elementNames[] = $removeIndexElt;

        $separatorIndexElt = tao_helpers_form_FormFactory::getElement("index_".$this->prefix."_separator", 'Free');
        $separatorIndexElt->setValue(
            "<hr class='index' data-related-index='{$indexUri}'>"
        );
        $this->form->addElement($separatorIndexElt);
        $elementNames[] = $separatorIndexElt;

    }

}
