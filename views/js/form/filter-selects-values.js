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

        if (!fromMultiple && !allowedOptions.contains(currentValue)) {
            $secondarySelect.empty().append(new Option('', ' '));
        }

        // TODO: remove old options that are not allowed.
        // current solution leads to duplication of options
        allowedOptions.forEach((selectOption) => {
            $secondarySelect.append(new Option(selectOption.label, selectOption.uri));
        });
    }

    function filterSelect2Options(allowedOptions, $secondarySelect) {
        let input = $secondarySelect.next('input');

        if (!input) {
            return;
        }

        let newVal = input.val().split(',').reduce((accumulator, option) => {
            if (allowedOptions.includes(option)) {
                accumulator.push(option);
            }
            return accumulator;
        }, []).join(',');

        input.val(newVal).trigger('change');
    }

    async function processFiltering(selects, allowedOptions, persistValues) {
        selects.forEach($secondarySelect => {
            let isSelect2 = $secondarySelect.hasClass('select2-container');
            if (isSelect2) {
                filterSelect2Options(allowedOptions, $secondarySelect);
                return;
            }
            filterSelectOptions(allowedOptions, $secondarySelect, persistValues);
        });
    }

    function getAllowedSecondaryValues(data) {
        return request({ url: context.root_url + 'tao/PropertyValues/get', data, method: 'GET', dataType: 'json'});
    }

    async function filterSecondaryValues($container, selectedPrimaryProperty, persistValues) {
        const $secondaryList = $container.find('.secondary-props-list > li > *');
        const allowedOptions = [];
        const selects = [];

        for (let secondaryProp of $secondaryList.toArray()) {
            let $secondarySelect = $(secondaryProp).find('select, .select2-container');
            if (!$secondarySelect.length) { return; }

            const data = {
                propertyUri: $secondarySelect.attr('id').replace('s2id_', ''),
                parentListValues: selectedPrimaryProperty,
            }

            const response = await getAllowedSecondaryValues(data);
            allowedOptions.push(...response.data);
            selects.push($secondarySelect);
        }

        processFiltering(selects, allowedOptions, persistValues);
    }

    return filterSecondaryValues;
});