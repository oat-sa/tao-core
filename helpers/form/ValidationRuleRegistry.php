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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA
 *               
 * 
 */

namespace oat\tao\helpers\form;

use oat\oatbox\AbstractRegistry;
use oat\generis\model\OntologyAwareTrait;

/**
 * The ValidationRuleRegistry allows you to register specific validation rules
 * 
 * @package tao
 */
class ValidationRuleRegistry extends AbstractRegistry
{
    use OntologyAwareTrait;
    
    const REGISTRY_ID = 'validationRules';
    
    const PROPERTY_VALIDATION_RULE = 'http://www.tao.lu/Ontologies/generis.rdf#validationRule';
    
    /**
     * (non-PHPdoc)
     * @see \oat\oatbox\AbstractRegistry::getExtension()
     */
    protected function getExtension()
    {
        return \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\oatbox\AbstractRegistry::getConfigId()
     */
    protected function getConfigId()
    {
        return self::REGISTRY_ID;
    }
    
    public function getValidators(\core_kernel_classes_Property $property)
    {
        $validationProp = $this->getProperty(self::PROPERTY_VALIDATION_RULE);
        $rules = [];
        foreach ($property->getPropertyValues($validationProp) as $ruleId) {
            $rule = $this->get($ruleId);
            if ($rule == '') {
                throw new \common_exception_NotFound('No validation rule found with id "'.$ruleId.'"');
            }
            $rules[] = $rule;
        }
        return $rules;
    }
}
