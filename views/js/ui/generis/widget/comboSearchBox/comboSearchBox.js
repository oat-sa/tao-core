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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

define([
    'jquery',
    'lodash',
    'i18n',
    'ui/generis/widget/widget',
    'tpl!ui/generis/widget/comboSearchBox/comboSearchBox'
], function (
    $,
    _,
    __,
    widgetFactory,
    tpl
) {
    'use strict';

    /**
     * The factory
     * @param {Object[]} [options.validator]
     * @param {String} config.label
     * @param {String} [config.placeholder]
     * @param {String[]} config.range
     * @param {String} [config.required = false]
     * @param {String} config.uri
     * @param {String} [config.value]
     * @returns {ui/component}
     */
    function factory(options, config) {
        var validator = options.validator || [];
        var widget;

        widget = widgetFactory({
            validator: validator
        }, {
            // no overrides
        })
        .setTemplate(tpl)
        .init({
            label: config.label,
            placeholder: config.placeholder || __('Select an option...'),
            range: config.range || [],
            required: config.required || false,
            uri: config.uri,
            value: config.value || ''
        })
        .on('render', function () {
            var $document, $el, $input, $dropdown, $dropdownSearch, $dropdownMenuItem;

            $el = this.getElement();

            $document = $(document);
            $dropdown = $el.find('> .right > .dropdown');
            $dropdownSearch = $el.find('> .right > .dropdown > .search > input');
            $dropdownMenuItem = $el.find('> .right > .dropdown > .menu > .item');
            $input = $el.find('> .right > .input');

            // Document event handlers
            function outsideWizardClickHandler(e) {
                if (!$(e.target).closest($el).length) {
                    if ($dropdown.is(':visible')) {
                        $dropdown.hide();
                        $document.off('click', outsideWizardClickHandler);
                    }
                }
            }

            // Wizard element events

            // Input element events
            $input
                .on('click', function () {
                    $dropdown.show();
                    $dropdownSearch.focus();
                    $document.on('click', outsideWizardClickHandler);
                });

            // Dropdown element events

            // Dropdown search element events
            $dropdownSearch
                .on('keyup', _.debounce(function (e) {
                    var $focused = $dropdownMenuItem.filter('.focused');
                    var $this = $(this);
                    var hasFocus = false;

                    if (e.key === 'Escape') {
                        $dropdown.hide();
                    }

                    if (e.key === 'Enter') {
                        if ($focused.length) {
                            $focused.first().trigger('click');
                        } else {
                            $dropdown.hide();
                        }
                    }

                    $dropdownMenuItem.removeClass('focused');
                    $dropdownMenuItem.each(function (i, item) {
                        var $item = $(item);
                        var haystack;
                        var needle;

                        haystack = $item.data('text').toUpperCase();
                        needle = $this.val().trim().toUpperCase();

                        if (!needle || haystack.includes(needle)) {
                            if (!hasFocus) {
                                hasFocus = true;
                                $item.addClass('focused');
                            }
                            $item.show();
                        } else {
                            $item.hide();
                        }
                    });

                    if ($dropdownMenuItem.is(':visible')) {
                        $dropdown.find('> .menu > .no-results').hide();
                    } else {
                        $dropdown.find('> .menu > .no-results').show();
                    }
                }, 100));

            // Dropdown menu item element events
            $dropdownMenuItem
                .on('click', function () {
                    var $this = $(this);

                    $dropdownMenuItem.removeClass('selected');
                    $this.addClass('selected');

                    $input.find('input')
                        .val($this.data('text'))
                        .data('value', ($this.data('value')));

                    $dropdown.hide();
                })
                .on('hover', function () {
                    $dropdownMenuItem.removeClass('focused');
                    $(this).addClass('focused');
                });
        });

        // Validations
        if (widget.config.required) {
            widget.validator
            .addValidation({
                message: __('This field is required'),
                predicate: /\S+/,
                precedence: 1
            });
        }

        return widget;
    }

    return factory;
});
