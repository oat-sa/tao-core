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

use oat\generis\model\WidgetRdf;

abstract class tao_helpers_form_elements_Searchtextbox extends tao_helpers_form_FormElement
{
    protected $widget = WidgetRdf::PROPERTY_WIDGET_SEARCH_BOX;

    /** @var string[] */
    private $values = [];

    /**
     * @inheritDoc
     */
    public function feed(): void
    {
        $this->values = [];

        foreach (explode(',', ($_POST[$this->name] ?? '')) as $value) {
            if ($value) {
                $this->values[] = $value;
            }
        }
    }

    public function getEvaluatedValue(): array
    {
        return array_map([tao_helpers_Uri::class, 'decode'], $this->values);
    }

    /**
     * @return string[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function addValue(string $value): void
    {
        $this->values[] = tao_helpers_Uri::encode($value);
    }
}
