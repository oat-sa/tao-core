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

        $htmlPieces[] = "<div {$this->renderAttributes()}>";

        $htmlPieces[] = $this->createHiddenInput()->render();

        if ($hasUnit) {
            $htmlPieces[] = '<label class="unit" for="' . $this->name . '">' . _dh($this->unit) . '</label>';
        }

        $htmlPieces[] = '</div>';

        $htmlPieces[] = $this->createClientCode();

        return implode('', $htmlPieces);
    }

    private function createClientCode(): string
    {
        $delimiter     = static::VALUE_DELIMITER;
        $searchUrl     = tao_helpers_Uri::url('get', 'PropertyValues', 'tao');
        $initSelection = json_encode($this->createInitSelectionValues());

        return <<<javascript
<script>
    require(['jquery'], function ($) {
        var \$input = $('#$this->name');
        
        var normalizeItem = function (item) {
            return {
                id: item.uri,
                text: item.label
            }
        };

        var getCurrentValues = function () {
            return \$input.val().split('$delimiter');
        };

        \$input.select2({
            width: '100%',
            multiple: true,
            minimumInputLength: 3,
            ajax: {
                quietMillis: 200,
                url: '$searchUrl',
                data: function (term) {
                    return {
                        propertyUri: '$this->name',
                        subject: term,
                        exclude: getCurrentValues()
                    }
                },
                results: function (data) {
                    return {
                        results: data.data.map(normalizeItem)
                    };
                }
            },
            initSelection: function (element, callback) {
                callback($initSelection);
            }
        });
    });
</script>
javascript;
    }

    private function createInitSelectionValues(): iterable
    {
        $result = [];

        foreach ($this->getValues() as $value) {
            $result[] = [
                'id'   => $value,
                'text' => (new core_kernel_classes_Resource(tao_helpers_Uri::decode($value)))->getLabel(),
            ];
        }

        return $result;
    }

    private function createHiddenInput(): tao_helpers_form_elements_xhtml_Hidden
    {
        $input = new tao_helpers_form_elements_xhtml_Hidden($this->name);
        $input->setValue(implode(static::VALUE_DELIMITER, $this->getValues()));

        return $input;
    }
}
