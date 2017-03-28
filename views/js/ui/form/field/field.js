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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */

define([
    'jquery',
    'ui/component',
    'tpl!ui/form/field/tpl/checkBox',
    'tpl!ui/form/field/tpl/comboBox',
    'tpl!ui/form/field/tpl/hiddenBox',
    'tpl!ui/form/field/tpl/textBox'
], function (
    $,
    component,
    checkBoxTpl,
    comboBoxTpl,
    hiddenBoxTpl,
    textBoxTpl
) {
    'use strict';


    /**
     * Property for ui/form/field configuration defaults
     */
    var _defaults = {
        input : {
            name : '',
            options : [],
            placeholder : '',
            value : ''
        },
        label : '',
        required : false,
        type : 'default'
    };


    /**
     * Property for ui/form/field templates mapping
     */
    var _templates = {
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox' : checkBoxTpl,
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox' : comboBoxTpl,
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox' : hiddenBoxTpl,
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox' : textBoxTpl
    };


    /**
     * Factory for ui/form/field component
     * @param {Object} config
     * @param {Object} config.input - Config of input element
     * @param {String} config.input.name - Input element's name
     * @param {Array} [config.input.options] - Input element's select options (default is `[]`)
     * @param {String} [config.input.placeholder] - Input element's placeholder (default is `''`)
     * @param {String} [config.input.value] - Input element's value (default is `''`)
     * @param {String} config.label - Label element's text (default is `''`)
     * @param {bool} [config.required] - Flag to determine if ui/form/field is required (default is `false`)
     * @param {String} config.type - Type of ui/form/field
     * @event fieldFactory#change - Fires change event on completion of field change
     */
    var fieldFactory = function fieldFactory(config) {
        return component({
            //todo: show validation errors (on 'change' and a brief period
            // without more changes)
            //note: the thought is to show frontend validation errors per field
            // and backend errors as flash message

            /**
             * Show error message
             * @param {String} message - Error message to display
             */
            showError : function showError(message) {
                var $el, $error;

                $el = this.getElement();
                $error = $el.find('.form-error');

                $el.find('input').addClass('error');

                if (!$error.length) {
                    $el.append(
                        '<div class="form-error"></div>'
                    );

                    $error = $el.find('.form-error');
                }

                $error.append(message + '<br>');
            },

            /**
             * Remove error messages
             */
            removeErrors : function removeErrors() {
                var $el, $error;

                $el = this.getElement();
                $error = $el.find('.form-error');

                $el.find('input').removeClass('error');

                if ($error.length) {
                    $error.remove();
                }
            }
        }, _defaults)

        .setTemplate(_templates[config.type])

        .on('render', function () {
            var self = this,
                timer,
                $input = this.getElement().find('input');

            if ($input.length) {
                $input.on('keyup', function () {
                    clearTimeout(timer);
                    if ($input.val()) {
                        timer = setTimeout(function () {
                            self.trigger('change');
                        }, 2000);
                    }
                });
            }
        })

        .init(config);
    };


    /**
     * @exports ui/form/field
     */
    return fieldFactory;
});