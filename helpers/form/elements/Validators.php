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
 * Copyright (c) 2015-2022 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\helpers\form\elements;

use tao_helpers_form_elements_MultipleElement;
use oat\tao\helpers\form\ValidationRuleRegistry;
use tao_helpers_form_Validator;

/**
 * Implementation model selector
 *
 * @abstract
 * @package tao
 */
abstract class Validators extends tao_helpers_form_elements_MultipleElement
{
    public const WIDGET_ID = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ValidationSelector';

    /**
     * (non-PHPdoc)
     * @see tao_helpers_form_elements_MultipleElement::getOptions()
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->getValidationRuleRegistry()->getMap() as $id => $validator) {
            $options[$id] = $id;
        }

        return $options;
    }

    public function getOptionValidatorName(string $id): ?string
    {
        $validator = $this->getValidationRuleRegistry()->get($id);

        if ($validator instanceof tao_helpers_form_Validator) {
            return $validator->getName();
        }

        return null;
    }

    /**
     * (non-PHPdoc)
     * @see tao_helpers_form_elements_MultipleElement::setValue()
     */
    public function setValue($value)
    {
        $this->addValue($value);
    }

    private function getValidationRuleRegistry(): ValidationRuleRegistry
    {
        return ValidationRuleRegistry::getRegistry();
    }
}
