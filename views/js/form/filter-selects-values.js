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

    function filterSelectOptions(allowedOptions, $secondarySelect) {
        allowedOptions.forEach((selectOption) => {
            if (!$secondarySelect.find(`option[value="${selectOption.uri}"]`).length) {
                $secondarySelect.append(new Option(selectOption.label, selectOption.uri));
            }
        });
        return;
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

    function filterMultipleSelect2({ selects, allowedOptions }) {
        selects.forEach($secondarySelect => {
            filterSelect2Options(allowedOptions, $secondarySelect);
        })
    }

    async function processFiltering(selects, requests, fromMultiplePrimary) {
        await Promise.all(requests);
        
        if (!fromMultiplePrimary) {
            $secondarySelect.empty().append(new Option('', ' '));

            requests.forEach((selectOption) => {
                $secondarySelect.append(new Option(selectOption.label, selectOption.uri));
            });
        }

        const multiselects = await selects.reduce(async (accumulator, { request, $secondarySelect }) => {
            if (!$secondarySelect) {
                return accumulator;
            }
            
            const { data } = await request;
            let isSelect2 = $secondarySelect.hasClass('select2-container');
            let isMultiselect = $secondarySelect.hasClass('select2-container-multi');
            
            // Collect all multiselects
            if (isSelect2) {
                accumulator.selects.push($secondarySelect);
                accumulator.allowedOptions.push(...data);
                return accumulator;
            }
            
            // single value select2
            if (isSelect2) {
                filterSelect2Options(data, $secondarySelect);
                return accumulator;
            }

            // <select>
            filterSelectOptions(data, $secondarySelect);  

            return accumulator;
        
        }, {
            selects: [],
            allowedOptions: []
        });
        
        filterMultipleSelect2(multiselects);
    }

    function getAllowedSecondaryValues(data) {
        return request({ url: context.root_url + 'tao/PropertyValues/get', data, method: 'GET', dataType: 'json'});
    }

    async function filterSecondaryValues($container, selectedPrimaryProperty, fromMultiplePrimary) {
        const $secondaryList = $container.find('.secondary-props-list > li > *');

        const requests = [];
        const selects = [];

        for (let secondaryProp of $secondaryList.toArray()) {
            let $secondarySelect = $(secondaryProp).find('select, .select2-container');
            if (!$secondarySelect.length) { return; }

            const data = {
                propertyUri: $secondarySelect.attr('id'),
                parentListValues: selectedPrimaryProperty
            }

            requests.push(getAllowedSecondaryValues(data));
            selects.push({
                request: requests[requests.length - 1],
                $secondarySelect,
            });
        }

        processFiltering(selects, requests, fromMultiplePrimary);
    }

    return filterSecondaryValues
});