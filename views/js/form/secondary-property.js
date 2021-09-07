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
    'jquery'
], function ($) {
    'use strict';
    function getSecondaryPropsList($primaryProp) {
        let $secondaryPropsList = $primaryProp.find('.secondary-props-list')[0];

        if (!$secondaryPropsList) {
            $secondaryPropsList = $('<ul class="secondary-props-list"></ul>');
            $primaryProp.append($secondaryPropsList);
        }
          
        return $secondaryPropsList;
    }

    function toggleDisableSecondary($container, disable = true) {
        const $secondaryList = $container.find('.secondary-props-list > li > *');
        $secondaryList.each(function() {
            if (disable) {
                $(this).find('[data-depends-on]').attr('disabled', 'disabled');
                $(this).addClass('disabled');
                return;
            }

            $(this).find('[data-depends-on]').removeAttr('disabled');
            $(this).removeClass('disabled');
        });
    }

    function clearSecondary($container) {
        const $secondaryList = $container.find('.secondary-props-list > li > *');
        $secondaryList.each(function() {
            const $select2Chosen = $(this).find('.select2-chosen');
            if ($select2Chosen.length) {
                $(this).find('> div > input').val('').trigger('change');
                $select2Chosen.empty();
                return;
            }

            const $selectElt = $(this).find('select[data-depends-on]');
            if ($selectElt.length) {
                $selectElt.each(function() {
                    $(this).find('option[selected]').removeAttr('selected');
                    $(this).find('option[value=" "]').attr('selected', 'selected');
                    $(this).trigger('change');
                });
                
                return;
            }
            const $inputElt = $(this).find('input');
            if ($inputElt.length) {
                $inputElt.each(function() {
                    $(this).val(null).trigger('change')
                });
            }
        });
    }

    function moveSecondaryProperty($prop, $props) {
        const primaryPropUri = $prop.find('[data-depends-on]').data('depends-on');
        let $primaryProp = $($props.filter(function() {
            return !!$(this).find(`#${primaryPropUri}`).length;
        })[0]);
        let isCheckboxes = false;

        // Might be a set of checkboxes. Search by label
        if(!$primaryProp.length) {
            $primaryProp = $($props.filter(function() {
                return !!$(this).find(`[for="${primaryPropUri}"]`).length;
            })[0]);
            isCheckboxes = true;
        }

        if(!$primaryProp.length) {
            console.error('Primary property not found', primaryPropUri)
            return;
        }
 
        const $secondaryPropsList = getSecondaryPropsList($primaryProp);
        const $wrapper = $('<li></li>');
        $secondaryPropsList.append($wrapper);
        $wrapper.append($prop.detach());
        
        if (isCheckboxes) {
            const $listWrapper = $primaryProp.find('.form_checklst');
            
            $listWrapper.on('change', (e) => {
                const isFilled = !!$listWrapper.find('input:checked').length;
                if (!isFilled) {
                    clearSecondary($primaryProp, primaryPropUri);
                }
                toggleDisableSecondary($primaryProp, !isFilled);
            });

            const isFilled = !!$listWrapper.find('input:checked').length;
            toggleDisableSecondary($primaryProp, !isFilled);

            return;
        }

        $primaryProp.on('change', `[name="${primaryPropUri}"]`, (e) => {
            if (!e.target.value.trim()) {
                clearSecondary($primaryProp, primaryPropUri);
            }
            toggleDisableSecondary($primaryProp, !e.target.value.trim());
        });

        const $primaryElt = $primaryProp.find(`[name="${primaryPropUri}"]`);
        toggleDisableSecondary($primaryProp, !$primaryElt.val().trim());
    }

    function moveSecondaryProperties($container) {
        const $props = $container.children();
        // TODO: fix this before testing
        const $secondaryProps = $props.filter(function(index) {
            // return !!$(this).find('[data-depends-on]').length;
            let isDep = $(this).find('[data-depends-on]');
            if (!isDep.length) return false;
            return index === 6;
        })
        $secondaryProps.each(function () {
            moveSecondaryProperty($(this), $props);
        })
    }

    return {
        move: moveSecondaryProperties
    }
});


