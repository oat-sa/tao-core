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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\helpers\form\elements;

use tao_helpers_form_FormElement;
use tao_helpers_Uri;

abstract class AbstractSearchElement extends tao_helpers_form_FormElement
{
    protected const VALUE_DELIMITER = ',';

    /** @var string[] */
    protected $values = [];

    /**
     * @inheritDoc
     */
    public function feed(): void
    {
        $newValuesMap = array_flip(explode(static::VALUE_DELIMITER, ($_POST[$this->name] ?? '')));

        $this->values = array_intersect_key($this->values, $newValuesMap);

        foreach (array_keys(array_diff_key($newValuesMap, $this->values)) as $newValue) {
            if ($newValue) {
                $this->addValue(new ElementValue($newValue, $newValue));
            }
        }
    }

    public function getEvaluatedValue(): array
    {
        return array_map(
            static function ($value) {
                return tao_helpers_Uri::decode($value);
            },
            $this->values
        );
    }

    public function getRawValue()
    {
        return $this->values;
    }

    public function addValue($value): void
    {
        if ($value instanceof ElementValue) {
            $this->values[$value->getUri()] = $value;
        } else {
            $encodedValue = tao_helpers_Uri::encode($value);
            $this->values[$encodedValue] = new ElementValue($encodedValue, $value);
        }
    }
}
