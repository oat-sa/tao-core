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
    'tpl!ui/generis/widget/hiddenBox/hiddenBox'
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
     * @param {Object} [config.confirmation]
     * @param {String} config.label
     * @param {String} [confgi.required = false]
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
            /**
             * Overrides get method
             * @returns {Object}
             */
            get: function get() {
                var $el;
                var ret = {
                    value: this.config.value,
                    confirmation: this.config.confirmation.value
                };

                if (this.is('rendered')) {
                    $el = this.getElement();
                    ret.value = $el.find('[name="' + this.config.uri + '"]').val();
                    ret.confirmation = $el.find('[name="' + this.config.confirmation.uri + '"]').val();
                }

                return ret;
            },

            /**
             * Overrides set method
             * @param {String} value
             * @returns {Object}
             */
            set: function set(value) {
                var $el;

                this.config.value = this.config.confirmation.value = value;

                if (this.is('rendered')) {
                    $el = this.getElement();
                    $el.find('[name="' + this.config.uri + '"]').val(value);
                    $el.find('[name="' + this.config.confirmation.uri + '"]').val(value);
                }

                return {
                    value: this.config.value,
                    confirmation: this.config.confirmation.value
                };
            },

            /**
             * Overrides serialize method
             * @returns {Object}
             */
            serialize: function serialize() {
                return {
                    name: this.config.uri,
                    value: this.get().value
                };
            }
        })
        .setTemplate(tpl)
        .init({
            confirmation: {
                label: __('%s Confirmation', config.label),
                uri: config.uri + '_confirmation',
                value: config.value || ''
            },
            label: config.label,
            required: config.required || false,
            uri: config.uri,
            value: config.value || ''
        });

        // Validations
        if (widget.config.required) {
            widget.validator
            .addValidation({
                message: __('This field is required'),
                predicate: function (value) {
                    return /\S+/.test(value.value);
                },
                precedence: 1
            });
        }

        widget.validator.addValidation({
            message: __('Fields must match'),
            predicate: function (value) {
                return value.value === value.confirmation;
            },
            precedence: 2
        });

        return widget;
    }

    return factory;
});
