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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 * 
 */

namespace oat\tao\helpers\form\elements;

use oat\tao\model\TaoOntology;
use tao_helpers_form_elements_MultipleElement;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use tao_helpers_Uri;
use oat\tao\helpers\form\ValidationRuleRegistry;

/**
 * Implementation model selector
 * 
 * @abstract
 * @package tao
 */
abstract class Validators
    extends tao_helpers_form_elements_MultipleElement
{

    /**
     * @todo should be a constant
     * @var string
     */
    protected $widget = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ValidationSelector';
    
    /**
     * (non-PHPdoc)
     * @see tao_helpers_form_elements_MultipleElement::getOptions()
     */
    public function getOptions() {
        $options = parent::getOptions();
        
        $statusProperty = new core_kernel_classes_Property(TaoOntology::PROPERTY_ABSTRACT_MODEL_STATUS);
        $current = $this->getEvaluatedValue();
                    
        $options = array();
        $map = ValidationRuleRegistry::getRegistry()->getMap();
        foreach (ValidationRuleRegistry::getRegistry()->getMap() as $id => $validator) {
            $options[$id] = $id; 
        }
        
        return $options;
    }

    /**
     * (non-PHPdoc)
     * @see tao_helpers_form_elements_MultipleElement::setValue()
     */
    public function setValue($value)
    {
        $this->addValue($value);
    }

}