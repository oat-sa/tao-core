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
    'lodash',
    'i18n',
    'ui/component',
    'tpl!ui/form/field/tpl/checkBox',
    'tpl!ui/form/field/tpl/comboBox',
    'tpl!ui/form/field/tpl/hiddenBox',
    'tpl!ui/form/field/tpl/textBox'
], function (
    $,
    _,
    __,
    component,
    checkBoxTpl,
    comboBoxTpl,
    hiddenBoxTpl,
    textBoxTpl
) {
    'use strict';


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
     */
    var fieldFactory = function fieldFactory(config) {
        // Check for required parameters
        if (!config &&
            !config.input && !config.input.name &&
            !config.type && !_templates[config.type]) {
            throw Error('Invalid config parameters.');
        }

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

        .init(config);
    };


    return fieldFactory;
});