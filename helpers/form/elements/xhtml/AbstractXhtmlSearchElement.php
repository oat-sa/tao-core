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

namespace oat\tao\helpers\form\elements\xhtml;

use oat\tao\helpers\form\elements\ElementValue;
use oat\tao\helpers\form\elements\AbstractSearchElement;
use tao_helpers_Display;
use tao_helpers_form_elements_xhtml_Hidden;
use tao_helpers_Uri;

abstract class AbstractXhtmlSearchElement extends AbstractSearchElement
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
        if (!empty($this->unit)) {
            $this->addClass('has-unit');
            $label = sprintf(
                '<label class="%s" for="%s">%s</label>',
                'unit',
                $this->name,
                tao_helpers_Display::htmlize($this->unit)
            );
        }

        $html = [];

        $html[] = $this->renderLabel();
        $html[] = sprintf(
            '<script>%s</script>',
            $this->createClientCode()
        );
        $html[] = sprintf(
            '<div%s>%s</div>',
            $this->renderAttributes(),
            $this->createHiddenInput()->render() . ($label ?? '')
        );

        return implode('', $html);
    }

    abstract protected function isMultiValue(): bool;

    private function createClientCode(): string
    {
        $searchUrl = tao_helpers_Uri::url('get', 'PropertyValues', 'tao');

        $baseVariables       = $this->createBaseClientVariables();
        $initSelectionValues = $this->createInitSelectionValues();

        if ($this->isMultiValue()) {
            $multipleValue = 'true';
        } else {
            $initSelectionValues = reset($initSelectionValues);
            $multipleValue       = 'false';
        }

        $initSelection = json_encode($initSelectionValues);

        return <<<javascript
require(['jquery'], function ($) {
    $baseVariables

    var successCallback = function(callback, initialSelection, multiple) {
        $.ajax({
            url: '$searchUrl',
            data: createRequestData('   '),
            global: false,
            success: function(response) {
                let parsedResponse = normalizeResponse(response).results;

                if (multiple) {
                    initialSelection.forEach(selection => {
                        let index = parsedResponse.findIndex(parsedSelection => parsedSelection.id === selection.id);
                        if (index !== -1) {
                            selection.text = parsedResponse[index].text;
                        }
                    });
                } else {
                    initialSelection.text = parsedResponse.find(selection => selection.id === initialSelection.id).text;
                }
                callback(initialSelection);
            }
        });
    }

    \$input.select2({
        width: '100%',
        multiple: $multipleValue,
        minimumInputLength: 3,
        ajax: {
            quietMillis: 200,
            url: '$searchUrl',
            data: createRequestData,
            results: normalizeResponse
        },
        formatSelection: choice => {
            if (!choice) {
                return choice;
            }

            return choice.isValid
                ? choice.text
                : `<span class="invalid-choice">\${choice.text}</span>`;
        },
        initSelection: function (element, callback) {
            let initialSelection = $initSelection;

            if (initialSelection) {
                if (Array.isArray(initialSelection)) {
                    initialSelection.find(function (selection) {
                        return selection.id === selection.text;
                    }) ? successCallback(callback, initialSelection, true) : callback(initialSelection);
                } else {
                    initialSelection.id === initialSelection.text ? successCallback(callback, initialSelection) : callback(initialSelection);
                }
            }
        }
    });

    \$input.parent().find('.select2-search-choice:has(.invalid-choice)').each((index, element) => {
        $(element).addClass('select2-search-choice-error');
    });
});
javascript;
    }

    private function createBaseClientVariables(): string
    {
        $delimiter = static::VALUE_DELIMITER;

        return <<<javascript
var \$input = $('#$this->name');

var normalizeItem = function (item) { return { id: item.uri, text: item.label } };

var normalizeResponse = function (data) { return { results: data.data.map(normalizeItem) } };

var getCurrentValues = function () { return \$input.val().split('$delimiter') };

var getParentListValues = function () {
    var primaryPropUri = \$input.parent().data('depends-on-property');

    return primaryPropUri ? $('#' + primaryPropUri).val().split(',') : '';
}

var createRequestData = function (term) {
    return {
        propertyUri: '$this->name',
        subject: term,
        exclude: getCurrentValues(),
        parentListValues: getParentListValues()
    }
};

javascript;
    }

    private function createInitSelectionValues(): array
    {
        $result = [];
        $invalidValues = $this->getInvalidValues();

        foreach ($this->getRawValue() as $value) {
            $encodedUri = tao_helpers_Uri::encode($value->getUri());

            $result[] = [
                'id' => $encodedUri,
                'text' => $value->getLabel(),
                'isValid' => !array_key_exists($encodedUri, $invalidValues),
            ];
        }

        return $result;
    }

    private function createHiddenInput(): tao_helpers_form_elements_xhtml_Hidden
    {
        $input = new tao_helpers_form_elements_xhtml_Hidden($this->name);

        $uris = array_map(
            static function (ElementValue $value) {
                return $value->getUri();
            },
            $this->getRawValue()
        );

        $input->setValue(implode(static::VALUE_DELIMITER, $uris));

        return $input;
    }
}
