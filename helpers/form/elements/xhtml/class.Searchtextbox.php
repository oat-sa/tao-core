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
use oat\tao\helpers\form\elements\xhtml\XhtmlRenderingTrait;

class tao_helpers_form_elements_xhtml_Searchtextbox extends tao_helpers_form_FormElement
{
    use XhtmlRenderingTrait;

    protected $widget = WidgetRdf::PROPERTY_WIDGET_SEARCH_BOX;

    /** @var string[] */
    private $values = [];

    public function setValue($value): void
    {
        $this->addValue($value);
    }

    public function addValue(string $value): void
    {
        $this->values[] = $value;
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $returnValue = $this->renderLabel();

        $hasUnit = !empty($this->unit);

        if ($hasUnit) {
            $this->addClass('has-unit');
        }
        // TODO: Implement the new widget here
        $returnValue .= "<input type='text' name='{$this->name}' id='{$this->name}' ";
        $returnValue .= $this->renderAttributes();
        $returnValue .= ' value="' . $this->getHtmlValue() . '" />';

        if ($hasUnit) {
            $returnValue .= '<label class="unit" for="' . $this->name . '">' . _dh($this->unit) . '</label>';
        }

        return $returnValue;
    }

    private function getHtmlValue(): string
    {
        return implode(
            ' ',
            array_map([tao_helpers_Uri::class, 'encode'], $this->values)
        );
    }
}
