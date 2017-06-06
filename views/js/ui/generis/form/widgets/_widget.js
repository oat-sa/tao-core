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
    'ui/component',
    'css!tao/ui/generis/form/widgets/_widget'
], function(
    $,
    _,
    __,
    componentFactory
) {
    'use strict';

    /**
     * The factory
     * @param {String} [validationContainer = '.validation-container']
     * @param {Array} [validations = []]
     * @returns {ui/component}
     */
    function factory(options) {
        options = options || {};

        return componentFactory({
            /**
             * Gets widget value
             * @returns {String|this}
             */
            get: function (callback) {
                var ret = this.config.value || '';

                if (this.is('rendered')) {
                    ret = this.getElement()
                    .find('[name="' + this.config.uri + '"]')
                    .val();
                }

                if (typeof callback === 'function') {
                    callback.apply(this, [ret]);
                    return this;
                }

                return ret;
            },

            /**
             * Sets widget value
             * @returns {String|this}
             */
            set: function (value, callback) {
                this.config.value = value;

                if (this.is('rendered')) {
                    this.getElement()
                    .find('[name=' + this.config.uri + ']')
                    .val(value);
                }

                if (typeof callback === 'function') {
                    callback.apply(this, [value]);
                    return this;
                }

                return value;
            },

            /**
             * Validates widget
             */
            validate: function (callback) {
                var $el = this.getElement();
                var $input;
                var ret;
                var value = this.get();

                if ($el) {
                    $el.find(this.validationContainer).empty();

                    $input = $el.find('[name]');
                    $input.removeClass('error');
                }

                ret = _(this.validations)
                // run validations
                .reject(function (validation) {
                    if (validation.predicate instanceof RegExp) {
                        return validation.predicate.test(value);
                    }
                    else if (typeof validation.predicate === 'function') {
                        return validation.predicate(value);
                    }
                }, this)
                // display validations' message
                .each(function (validation) {
                    if ($el) {
                        $el.find(this.validationContainer)
                        .append(
                            $('<div>', {
                                class: 'error',
                                text: validation.message
                            })
                        );
                        $input.addClass('error');
                    }
                }, this)
                // return validations' message
                .map(function (validation) {
                    return validation.message;
                })
                .value();

                if (typeof callback === 'function') {
                    callback.apply(this, [ret]);
                    return this;
                }

                return ret;
            },

            /**
             * Serializes widget into a name/value object for form submission
             * @param {Function} [callback]
             * @returns {Object|this}
             */
            serialize: function serialize(callback) {
                var ret = {
                    name: this.config.uri,
                    value: this.get()
                };

                if (typeof callback === 'function') {
                    callback.apply(this, [ret]);
                    return this;
                }

                return ret;
            }
        }, {
            hidden: false,
            required: false
        })

        .on('init', function () {
            this.validationContainer = options.validationContainer || '.validation-container';
            this.validations = options.validations || [];
        })

        .on('render', function () {
            var $el = this.getElement();

            // Configure hidden option
            if (this.config.hidden) {
                $el.attr('hidden', 'hidden');
            }

            // Configure required option
            if (this.config.required) {
                $el.find('label[for="' + this.config.uri + '"]').append(
                    $('<abbr>', {
                        title: __('This field is required'),
                        text: '*'
                    })
                );
                this.validations.unshift({
                    predicate: /\S+/,
                    message: 'This field is required'
                });
            }

            // Add error container
            $el.append(
                $('<div>', {
                    class: this.validationContainer.substring(1)
                })
            );
        });
    }

    return factory;
});