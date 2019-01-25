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
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'ui/maths/calculator/core/terms'
], function (registeredTerms) {
    'use strict';

    /**
     * Regex that matches the usual error tokens in a result
     * @type {RegExp}
     */
    var reErrorValue = /(NaN|[+-]?Infinity)/;

    return {
        /**
         * Identifies the type of a given token
         * @param {Object} token
         * @returns {String|null}
         */
        getType: function getType(token) {
            var type = token && token.type || null;
            var term = registeredTerms[type];
            return term && term.type || type;
        },

        /**
         * Checks if the type is related to a digit value
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isDigit: function isDigit(type) {
            if ('string' !== typeof type) {
                type = this.getType(type);
            }
            return type === 'digit';
        },

        /**
         * Checks if the type is related to an operator
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isOperator: function isOperator(type) {
            if ('string' !== typeof type) {
                type = this.getType(type);
            }
            return type === 'operator';
        },

        /**
         * Checks if the type is related to an aggregator
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isAggregator: function isAggregator(type) {
            if ('string' !== typeof type) {
                type = this.getType(type);
            }
            return type === 'aggregator';
        },

        /**
         * Checks if the type is related to an error
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isError: function isError(type) {
            if ('string' !== typeof type) {
                type = this.getType(type);
            }
            return type === 'error';
        },

        /**
         * Checks if the type is related to a constant
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isConstant: function isConstant(type) {
            if ('string' !== typeof type) {
                type = this.getType(type);
            }
            return type === 'constant';
        },

        /**
         * Checks if the type is related to a variable
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isVariable: function isVariable(type) {
            if ('string' !== typeof type) {
                type = this.getType(type);
            }
            return type === 'variable'
                || type === 'term';
        },

        /**
         * Checks if the type is related to a function
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isFunction: function isFunction(type) {
            if ('string' !== typeof type) {
                type = this.getType(type);
            }
            return type === 'function';
        },

        /**
         * Checks if the type is related to an identifier
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isIdentifier: function isIdentifier(type) {
            if ('string' !== typeof type) {
                type = this.getType(type);
            }
            return type === 'constant'
                || type === 'variable'
                || type === 'term'
                || type === 'function'
                || type === 'error';
        },

        /**
         * Checks if the type is related to a separator
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isSeparator: function isSeparator(type) {
            if ('string' !== typeof type) {
                type = this.getType(type);
            }
            return type === 'operator'
                || type === 'aggregator';
        },

        /**
         * Checks if the type is related to a modifier
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isModifier: function isModifier(type) {
            if ('string' !== typeof type) {
                type = this.getType(type);
            }
            return type === 'operator'
                || type === 'function';
        },

        /**
         * Checks if an expression contains an error token
         * @param {String|Number|Object} expression
         * @returns {Boolean}
         */
        containsError: function containsError(expression) {
            if ('string' !== typeof expression) {
                if (expression && 'undefined' !== typeof expression.value) {
                    expression = expression.value;
                }
                expression = String(expression);
            }
            return reErrorValue.test(expression);
        }
    };
});
