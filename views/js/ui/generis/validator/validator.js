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
    'tpl!ui/generis/validator/validator',
    'css!ui/generis/validator/validator'
], function (
    $,
    _,
    __,
    componentFactory,
    tpl
) {
    'use strict';

    /**
     * The factory
     * @param {Object[]} [options.validations]
     * @return {ui/component}
     */
    function factory(options) {
        var validator;

        options = options || {};

        validator = componentFactory({
            /**
             * Run all validations (i.e. populate errors property)
             * @param {String} value
             * @return {this}
             */
            run: function run(value) {
                this.errors = _(this.validations)
                // run validations
                .reject(function (validation) {
                    if (validation.predicate instanceof RegExp) {
                        return validation.predicate.test(value);
                    }
                    else if (typeof validation.predicate === 'function') {
                        return validation.predicate(value);
                    }
                }, this)
                // sort validations by precedence
                .sortBy('precedence')
                // return validations' message
                .map(function (validation) {
                    return validation.message;
                })
                .value();

                return this;
            },

            /**
             * Clears validation errors from dom
             * @return {this}
             */
            clear: function clear() {
                this.errors = [];

                if (this.is('rendered')) {
                    this.getElement().empty();
                }

                return this;
            },

            /**
             * Displays validation errors in dom
             * @return {this}
             */
            display: function display() {
                var $this = this.getElement();

                if (this.is('rendered')) {
                    $this.empty();
                    _.each(this.errors, function (error) {
                        $this.append(
                            $('<div>', {
                                class: 'validation-error'
                            })
                            .text(error)
                        );
                    });
                }

                return this;
            },

            /**
             * Adds validation
             * @param {Object} validation
             * @return {this}
             */
            addValidation: function addValidation(validation) {
                this.validations.push(validation);

                return this;
            },

            /**
             * Removes all validations
             * @return {this}
             */
            removeValidations: function removeValidations() {
                this.validations = [];

                return this;
            }
        })
        .setTemplate(tpl)
        .init();

        validator.errors = [];
        validator.validations = options.validations || [];

        return validator;
    }

    return factory;
});
