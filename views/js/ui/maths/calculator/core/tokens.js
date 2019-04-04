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
     * List of helpers that apply on tokens
     * @type {Object}
     */
    var tokensHelper = {
        /**
         * Identifies the type of a given token
         * @param {String|Object} token
         * @returns {String|null}
         */
        getType: function getType(token) {
            var type, term;
            if ('string' !== typeof token) {
                type = token && token.type || null;
                term = registeredTerms[type];
                return term && term.type || type;
            }
            return token;
        },

        /**
         * Checks if the type is related to a digit value
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isDigit: function isDigit(type) {
            return tokensHelper.getType(type) === 'digit';
        },

        /**
         * Checks if the type is related to an operator
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isOperator: function isOperator(type) {
            return tokensHelper.getType(type) === 'operator';
        },

        /**
         * Checks if the type is related to an operand
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isOperand: function isOperand(type) {
            type = tokensHelper.getType(type);
            return type !== 'operator'
                && type !== 'aggregator'
                && type !== 'separator';
        },

        /**
         * Checks if the type is related to an operand
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isValue: function isValue(type) {
            type = tokensHelper.getType(type);
            return type === 'digit'
                || type === 'constant'
                || type === 'variable'
                || type === 'term'
                || type === 'error';
        },

        /**
         * Checks if the type is related to an aggregator
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isAggregator: function isAggregator(type) {
            return tokensHelper.getType(type) === 'aggregator';
        },

        /**
         * Checks if the type is related to an error
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isError: function isError(type) {
            return tokensHelper.getType(type) === 'error';
        },

        /**
         * Checks if the type is related to a constant
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isConstant: function isConstant(type) {
            return tokensHelper.getType(type) === 'constant';
        },

        /**
         * Checks if the type is related to a variable
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isVariable: function isVariable(type) {
            type = tokensHelper.getType(type);
            return type === 'variable'
                || type === 'term';
        },

        /**
         * Checks if the type is related to a function
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isFunction: function isFunction(type) {
            return tokensHelper.getType(type) === 'function';
        },

        /**
         * Checks if the type is related to an identifier
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isIdentifier: function isIdentifier(type) {
            type = tokensHelper.getType(type);
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
            type = tokensHelper.getType(type);
            return type === 'operator'
                || type === 'aggregator'
                || type === 'separator';
        },

        /**
         * Checks if the type is related to a modifier
         * @param {String|Object} type
         * @returns {Boolean}
         */
        isModifier: function isModifier(type) {
            type = tokensHelper.getType(type);
            return type === 'operator'
                || type === 'function';
        },

        /**
         * Ensures an expression is a string. If a token or a descriptor is provided, extract the value.
         * @param {String|Number|Object} expression
         * @returns {String}
         */
        stringValue: function stringValue(expression) {
            var type = typeof expression;
            if (type !== 'string') {
                if (expression && 'undefined' !== typeof expression.value) {
                    expression = expression.value;
                } else if (expression && 'undefined' !== typeof expression.result) {
                    expression = expression.result;
                } else if (type === 'object' || type === 'undefined' || expression === null) {
                    expression = '';
                }
                expression = String(expression);
            }
            return expression;
        }
    };

    return tokensHelper;
});
