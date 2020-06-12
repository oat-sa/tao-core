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

use oat\tao\helpers\form\elements\xhtml\XhtmlRenderingTrait;

class tao_helpers_form_elements_xhtml_Searchtextbox extends tao_helpers_form_elements_Searchtextbox
{
    use XhtmlRenderingTrait;

    protected $attributes = [
        'class' => 'form_radlst',
    ];

    /**
     * @inheritDoc
     */
    public function setValue($value): void
    {
        $this->addValue($value);
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $htmlPieces = [$this->renderLabel()];

        $hasUnit = !empty($this->unit);

        if ($hasUnit) {
            $this->addClass('has-unit');
        }

        // TODO: Implement the new widget here
        $htmlPieces[] = "<div {$this->renderAttributes()}>";

        foreach ($this->getValues() as $value) {
            $htmlPieces[] = '<p>';
            $htmlPieces[] = (new core_kernel_classes_Resource(tao_helpers_Uri::decode($value)))->getLabel();
            $htmlPieces[] = '</p>';

            $htmlPieces[] = sprintf('<input type="hidden" name="%s[]" value="%s">', $this->name, _dh($value));
        }

        if ($hasUnit) {
            $htmlPieces[] = '<label class="unit" for="' . $this->name . '">' . _dh($this->unit) . '</label>';
        }

        $htmlPieces[] = '</div>';

        return implode('', $htmlPieces);
    }
}
