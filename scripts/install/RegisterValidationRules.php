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
 */
namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\tao\helpers\form\ValidationRuleRegistry;
use oat\generis\model\OntologyAwareTrait;

/**
 * This post-installation script creates a new local file source for services
 */
class RegisterValidationRules extends InstallAction
{
    use OntologyAwareTrait;
    
    public function __invoke($params)
    {
        ValidationRuleRegistry::getRegistry()->set('notEmpty', new \tao_helpers_form_validators_NotEmpty());

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'validator registered');
    }
    
    protected function addValidator($propertyUri, $validationRuleId)
    {
        $labelProperty = $this->getProperty($propertyUri);
        return $labelProperty->setPropertyValue($this->getProperty(ValidationRuleRegistry::PROPERTY_VALIDATION_RULE), $validationRuleId);
    }
}
