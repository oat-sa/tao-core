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
 * Simple validator engine. Apply a collection of validation rules on a value.
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'i18n'
], function (
    _,
    __
) {
    'use strict';

    /**
     * @typedef {Object} validationRule Defines a validation rule to apply on a value.
     * @property {String} id - The validation identifier
     * @property {RegExp|Function|String|String[]} predicate - The validation rule to apply on the value.
     *                                                         It could be either a RegExp or a Function.
     *                                                         The function must return a boolean value,
     *                                                         it can wrap it in a Promise too.
     * @property {String} [message] - The message returned in case of failed validation
     * @property {Number} [precedence] - The precedence order for sorting
     */

    /**
     * @typedef {Object} validatorConfig Defines the config entries available to setup a form widget validator
     * @property {validationRule[]} validations - The list of validations to apply
     * @property {String} defaultMessage - The default message returned when a validation fails and no message is set
     */

    /**
     * Defaults config for the validator
     * @type {validatorConfig}
     */
    const defaults = {
        defaultMessage: __('Invalid input')
    };

    /**
     * Validates a value
     * @param {String} value
     * @param {validationRule} validation
     * @returns {Boolean|Promise}
     */
    function validateValue(value, validation) {
        if (validation.predicate instanceof RegExp) {
            return validation.predicate.test(value);
        } else if (_.isFunction(validation.predicate)) {
            return validation.predicate(value);
        } else if (_.isArray(validation.predicate)) {
            return _.indexOf(validation.predicate, value) > -1;
        }
        return validation.predicate === value;
    }

    /**
     * Compares validation rules
     * @param {validationRule} a
     * @param {validationRule} b
     * @returns {Number}
     */
    function compareRule(a, b) {
        return ((a && a.precedence) || 0) - ((b && b.precedence) || 0);
    }

    /**
     * Creates a simple form widget's validator.
     * It manages and applies a collection of validation rules on a value.
     *
     * @param {validatorConfig} config
     * @param {validationRule[]} [config.validations] - The list of validation rules to apply
     * @param {String} [config.defaultMessage] - The default message returned when a validation fails and no message is set
     * @returns {validator}
     */
    return function validatorFactory(config) {
        const validations = new Map();

        /**
         * @typedef {Object} validator
         */
        const validator = {
            /**
             * Runs all validation rules on a value
             * @param {String} value
             * @returns {Promise} Will provide the list of error messages if the validation failed.
             */
            validate(value) {
                const rules = this.getValidations();
                rules.sort(compareRule);

                return Promise
                    .all(rules.map(validation => Promise.resolve(validateValue(value, validation))))
                    .then(results => {
                        const errors = _.reduce(results, (list, result, index) => {
                            if (!result) {
                                list.push(rules[index].message || config.defaultMessage);
                            }
                            return list;
                        }, []);

                        if (errors.length) {
                            return Promise.reject(errors);
                        }
                    });
            },

            /**
             * Adds a validation rule
             * @param {validationRule} validation
             * @returns {validator}
             * @throws {TypeError} if the validation object is not valid
             */
            addValidation(validation) {
                if (!_.isPlainObject(validation)) {
                    throw new TypeError('The validation must be an object');
                }
                if (!_.isString(validation.id) || !validation.id) {
                    throw new TypeError('The validation must contain an identifier');
                }
                if (!_.isFunction(validation.predicate) &&
                    !_.isRegExp(validation.predicate) &&
                    !_.isString(validation.predicate) &&
                    !_.isArray(validation.predicate)) {
                    throw new TypeError('The validation must provide a predicate');
                }

                validations.set(validation.id, validation);

                return this;
            },

            /**
             * Gets a validation rule by its identifier
             * @param {String} id
             * @returns {validationRule|null}
             */
            getValidation(id) {
                if (validations.has(id)) {
                    return validations.get(id);
                }
                return null;
            },

            /**
             * Gets the list of validation rules.
             * @returns {validationRule[]}
             */
            getValidations() {
                const list = [];
                for(let validation of validations.values()) {
                    list.push(validation);
                }
                return list;
            },

            /**
             * Removes a validation rule
             * @param {String} id
             * @returns {validator}
             */
            removeValidation(id) {
                if (validations.has(id)) {
                    validations.delete(id);
                }
                return this;
            },

            /**
             * Removes all validation rules
             * @returns {validator}
             */
            removeValidations() {
                validations.clear();

                return this;
            }
        };

        config = _.defaults(_.clone(config) || {}, defaults);
        _.forEach(config.validations, validator.addValidation);

        return validator;
    };
});
