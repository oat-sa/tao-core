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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA ;
 */

 define([
    'jquery',
    'context',
    'core/request',
], function ($, context, request) {
    'use strict';

    function filterSelectOptions(allowedOptions, $secondarySelect, fromMultiple) {
        let currentValue = $secondarySelect.val().trim()

        if (!fromMultiple) {
            $secondarySelect.empty().append(new Option('', ' '));

            allowedOptions.forEach(option => {
                $secondarySelect.append(new Option(option.label, option.uri));
            });
        } else {
            // Remove all except currentValue (if it is allowed to stay) and default " "
            $secondarySelect.find('option').each(function(i, existingOption) {
                if (existingOption.value !== " "
                    && (
                        existingOption.value.trim() !== currentValue
                        || !allowedOptions.find(opt => opt.uri === currentValue)
                    )
                ) {
                    existingOption.remove();
                }
            });

            // Add allowedOptions except currentValue
            allowedOptions.forEach(option => {
                if (option.uri.trim() !== currentValue) {
                    $secondarySelect.append(new Option(option.label, option.uri));
                }
            });
        }
    }

    function filterSelect2Options(allowedOptions, $secondarySelect) {
        let input = $secondarySelect.next('input');
        let newVal = [];

        if (!input) {
            return;
        }

        input.val().split(',').forEach(value => {
            let existingAvailableValue = allowedOptions.find(opt => opt.uri === value);

            if (existingAvailableValue) {
                newVal.push(existingAvailableValue);
            };
        });

        if (newVal.length) {
            newVal = newVal.map(selectedValue => {
                return {id: selectedValue.uri, text: selectedValue.label}
            });

            input.select2('data', newVal);
        } else {
            input.select2('val', '');
        }
    }

    async function processFiltering($secondarySelect, allowedOptions, persistValues) {
            let isSelect2 = $secondarySelect.hasClass('select2-container');

            if (isSelect2) {
                filterSelect2Options(allowedOptions, $secondarySelect);
                return;
            }

            filterSelectOptions(allowedOptions, $secondarySelect, persistValues);
    }

    function getAllowedSecondaryValues(data) {
        return request({ url: context.root_url + 'tao/PropertyValues/get', data, method: 'GET', dataType: 'json', noToken: true });
    }

    async function filterSecondaryValues($container, selectedPrimaryProperty, persistValues) {
        const $secondaryList = $container.find('.secondary-props-list > li > *');

        for (let secondaryProp of $secondaryList.toArray()) {
            let allowedOptions = [];
            let $secondarySelect = $(secondaryProp).find('select, .select2-container');
            if (!$secondarySelect.length) { return; }

            const data = {
                propertyUri: $secondarySelect.attr('id').replace('s2id_', ''),
                parentListValues: selectedPrimaryProperty,
            }

            const response = await getAllowedSecondaryValues(data);
            allowedOptions.push(...response.data);
            processFiltering($secondarySelect, allowedOptions, persistValues);
        }
    }

    return filterSecondaryValues;
});
