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
], function ($, context) {
    'use strict';

    function getSecondaryPropsList($primaryProp) {
        let $secondaryPropsList = $primaryProp.find('.secondary-props-list');

        if (!$secondaryPropsList.length) {
            $secondaryPropsList = $('<ul class="secondary-props-list"></ul>');
            $primaryProp.append($secondaryPropsList);
        }

        return $secondaryPropsList;
    }

    function filterSecondaryValues($container, selectedPrimaryProperty) {
        const $secondaryList = $container.find('.secondary-props-list > li > *');

        $secondaryList.each(function() {
            let $secondarySelect = $(this).children('select');

            if ($secondarySelect.length) {
                $.ajax({
                    url: context.root_url + 'tao/PropertyValues/get',
                    type: "GET",
                    data: {
                        propertyUri: $secondarySelect.attr('id'),
                        parentListValues: selectedPrimaryProperty
                    },
                    dataType: 'json',
                    success: function(response) {
                        $secondarySelect.empty().append(new Option('', ' '));

                        response.data.forEach((selectOption) => {
                            $secondarySelect.append(new Option(selectOption.label, selectOption.uri));
                        });
                    }
                });
            }
        });
    }

    function toggleDisableSecondary($container, disable = true) {
        const $secondaryList = $container.find('.secondary-props-list > li > *');

        $secondaryList.each(function() {
            if (disable) {
                $(this).find('[data-depends-on-property]').attr('disabled', 'disabled');
                $(this).addClass('disabled');
                clearSecondary($(this));
                return;
            }

            $(this).find('[data-depends-on-property]').removeAttr('disabled');
            $(this).removeClass('disabled');
        });
    }

    function clearSecondary($secondaryProp) {
        const $select2Chosen = $secondaryProp.find('.select2-chosen');
        if ($select2Chosen.length) {
            $(this).find('> div > input').val('').trigger('change');
            $select2Chosen.empty();
            return;
        }

        const $selectElt = $secondaryProp.find('select[data-depends-on-property]');
        if ($selectElt.length) {
            $selectElt.each(function() {
                $(this).find('option[selected]').removeAttr('selected');
                $(this).find('option[value=" "]').attr('selected', 'selected');
                $(this).trigger('change');
            });
            return;
        }

        const $inputElt = $secondaryProp.find('input');
        if ($inputElt.length) {
            $inputElt.each(function() {
                $(this).val(null).trigger('change');
            });
        }
    }

    function initializeSecondaryProperties($container) {
        const $props = $container.children();
        let primaryPropsMap = new Map();
        let $secondaryProps = $props.filter(function(index) {
            return !!$(this).find('[data-depends-on-property]').length;
        });

        $secondaryProps.each(function () {
            let $prop = $(this);
            const primaryPropUri = $prop.find('[data-depends-on-property]').data('depends-on-property');
            let $primaryProp = $($props.filter(function() {
                return !!$(this).find(`#${primaryPropUri}`).length;
            })[0]);

            if (!$primaryProp.length) {
                return;
            } else {
                primaryPropsMap.has(primaryPropUri) ? null : primaryPropsMap.set(primaryPropUri, $primaryProp);
                moveSecondaryProperty($prop, $primaryProp);
                const $primaryElt = $primaryProp.find(`[name="${primaryPropUri}"]`);
                toggleDisableSecondary($primaryProp, !$primaryElt.val().trim());
            }
        });

        primaryPropsMap.forEach(($primaryProp, primaryPropUri) => {
            addPrimaryPropertyListener($primaryProp, primaryPropUri);
        })
    }

    function filterSecondaryValuesFromMultiple($container, selectedPrimaryProperties, changeEvent) {
        const $secondaryList = $container.find('.secondary-props-list > li > *');

        $secondaryList.each(function() {
            let $secondarySelect = $(this).children('select');
            if ($secondarySelect.length) {
                $.ajax({
                    url: context.root_url + 'tao/PropertyValues/get',
                    type: "GET",
                    data: {
                        propertyUri: $secondarySelect.attr('id'),
                        parentListValues: selectedPrimaryProperties
                    },
                    dataType: 'json',
                    success: function(response) {
                        response.data.forEach((selectOption) => {
                            if (!$secondarySelect.find(`option[value="${selectOption.uri}"]`).length) {
                                $secondarySelect.append(new Option(selectOption.label, selectOption.uri));
                            }
                        });
                    }
                });
            }
        });
    }

    function addPrimaryPropertyListener($primaryProp, primaryPropUri) {
        $primaryProp.on('change', `[name="${primaryPropUri}"]`, (e) => {
            if (e.removed || e.added) {
                filterSecondaryValuesFromMultiple($primaryProp, e.target.value.split(), e);
            } else {
                filterSecondaryValues($primaryProp, e.target.value.split());
            }

            toggleDisableSecondary($primaryProp, !e.target.value.trim());
        });
    }

    function moveSecondaryProperty($secondaryProp, $primaryProp) {
        const $secondaryPropsList = getSecondaryPropsList($primaryProp);
        const $wrapper = $('<li></li>');
        $secondaryPropsList.append($wrapper);
        $wrapper.append($secondaryProp.detach());
    }

    return {
        init: initializeSecondaryProperties,
    }
});


