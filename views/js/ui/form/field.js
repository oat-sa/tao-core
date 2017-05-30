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
    'css!tao/ui/form/field'
], function(
    $,
    _,
    __,
    componentFactory
) {
    'use strict';

    /**
     * The field factory
     * @param {Object} [options.templateVars] - Contains the variables for the template/widget
     * @param {Array} [options.validations] - Validations to run on field's value
     * @param {Function} options.widget - Widget html as a handlebar's template
     * @returns {ui/component}
     */
    function fieldFactory(options) {
        var _validationContainer = '.validation-container';
        var _validations = options.validations || [];

        function _test(predicate, value) {
            if (predicate instanceof RegExp) {
                return predicate.test(value);
            } else if (typeof predicate === 'function') {
                return predicate(value);
            } else {
                return false;
            }
        }

        return componentFactory({
            /**
             * Gets field name
             * @returns {String}
             */
            getName: function () {
                return this.getElement().find('[name]').attr('name');
            },

            /**
             * Gets field value
             * @returns {String}
             */
            getValue: function () {
                return this.getElement().find('[name]').val();
            },

            /**
             * Add validation
             * @returns {ui/form/field}
             */
            addValidation: function (predicate, message) {
                _validations.push({
                    predicate: predicate,
                    message: message
                });

                return this;
            },

            /**
             * Clears validations
             * @returns {ui/form/field}
             */
            clearValidations: function () {
                this.getElement().find(_validationContainer).empty();

                return this;
            },

            /**
             * Checks field validity
             * @returns {Boolean}
             */
            isValid: function () {
                var value = this.getValue();

                return _.every(_validations, function(validation) {
                    return _test(validation.predicate, value);
                });
            },

            /**
             * Checks validity and displays errors
             */
            validate: function () {
                var element = this.getElement();
                var value = this.getValue();

                this.clearValidations();

                return _.reduce(_validations, function (acc, validation) {
                    var test = _test(validation.predicate, value);

                    if (!test && element) {
                        element.find(_validationContainer).append(
                            $('<div>', {
                                class: 'error',
                                text: validation.message
                            })
                        );
                    }

                    return test && acc;
                }, true);
            }
        })

        .setTemplate(options.widget)

        .on('render', function () {
            this.getElement().append(
                $('<div>', {
                    class: _validationContainer.substring(1)
                })
            );
        })

        .init(options.templateVars);
    }

    return fieldFactory;
});