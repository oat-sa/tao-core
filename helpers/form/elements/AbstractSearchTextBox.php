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

use oat\generis\model\WidgetRdf;
use tao_helpers_form_elements_MultipleElement;
use tao_helpers_Uri;

abstract class AbstractSearchTextBox extends tao_helpers_form_elements_MultipleElement
{
    protected const VALUE_DELIMITER = ',';

    protected $widget = WidgetRdf::PROPERTY_WIDGET_SEARCH_BOX;

    /** @var string[] */
    protected $values = [];

    /**
     * @inheritDoc
     */
    public function feed(): void
    {
        foreach (explode(static::VALUE_DELIMITER, ($_POST[$this->name] ?? '')) as $value) {
            if ($value) {
                $this->values[] = $value;
            }
        }
    }

    public function getEvaluatedValue(): array
    {
        return array_map([tao_helpers_Uri::class, 'decode'], $this->values);
    }

    public function getRawValue()
    {
        return $this->values;
    }
}
