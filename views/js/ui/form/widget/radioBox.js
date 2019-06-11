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
 * Copyright (c) 2019 Open Assessment Technologies SA ;
 */
/**
 * Defines a radioBox widget
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'tpl!ui/form/widget/tpl/radioBox'
], function ($, _, __, radioBoxTpl) {
    'use strict';

    /**
     * Makes sure a value is an array
     * @param {*} value
     * @returns {Array}
     */
    function forceArray(value) {
        if (value && !_.isArray(value)) {
            value = [value];
        } else {
            value = value || [];
        }
        return value;
    }

    /**
     * Defines the provider for a radioBox widget.
     *
     * @example
     * widgetFactory.registerProvider('radioBox', radioBoxProvider);
     */
    return {
        /**
         * Initialize the widget.
         * @param {widgetConfig} config
         */
        init: function init(config) {
            // the type will be reflected to the HTML markup
            config.widgetType = 'radio-box';

            // the value must be an array
            config.value = forceArray(config.value);
            config.range = forceArray(config.range);

            // replace the default validation
            if (config.required) {
                this.getValidator()
                    .addValidation({
                        id: 'required',
                        message: __('This field is required'),
                        predicate: function (value) {
                            return value.length > 0;
                        },
                        precedence: 1
                    });
            }
        },

        /**
         * Gets the value of the widget
         * @returns {String}
         */
        getValue: function getValue() {
            var value = this.getConfig().value || '';

            if (this.is('rendered')) {
                value = this.getElement()
                    .find('.option input[name="' + this.getUri() + '"]:checked')
                    .val() || '';
            }

            return value;
        },

        /**
         * Sets the value of the widget
         * @param {String} value
         */
        setValue: function setValue(value) {
            if (this.is('rendered')) {
                this.getWidgetElement()
                    .prop('checked', false);
                this.getElement()
                    .find('.option input[value="' + value + '"]')
                    .prop('checked', true);
            }
        },

        /**
         * Gets access to the actual form element
         * @returns {jQuery|null}
         */
        getWidgetElement: function getWidgetElement() {
            return this.getElement()
                .find('.option input');
        },

        /**
         * Expose the template to the factory and it will apply it
         */
        template: radioBoxTpl
    };
});
