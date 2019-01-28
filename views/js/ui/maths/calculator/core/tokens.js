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
    'ui/maths/calculator/core/terms',
    'tpl!ui/maths/calculator/core/terms'
], function (registeredTerms, termsTpl) {
    'use strict';

    /**
     * Regex that matches the usual error tokens in a result
     * @type {RegExp}
     */
    var reErrorValue = /(NaN|[+-]?Infinity)/;

    /**
     * Regex that matches the last result variable
     * @type {RegExp}
     */
    var reAnsVar = new RegExp('\\b' + registeredTerms.ANS.value + '\\b', 'g');

    /**
     * List of helpers that apply on tokens
     * @type {Object}
     */
    var tokensHelper = {
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
                type = tokensHelper.getType(type);
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
                type = tokensHelper.getType(type);
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
                type = tokensHelper.getType(type);
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
                type = tokensHelper.getType(type);
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
                type = tokensHelper.getType(type);
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
                type = tokensHelper.getType(type);
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
                type = tokensHelper.getType(type);
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
                type = tokensHelper.getType(type);
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
                type = tokensHelper.getType(type);
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
                type = tokensHelper.getType(type);
            }
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
                } else if (type === 'object' || type === 'undefined' || expression === null) {
                    expression = '';
                }
                expression = String(expression);
            }
            return expression;
        },

        /**
         * Checks if an expression contains an error token
         * @param {String|Number|Object} expression
         * @returns {Boolean}
         */
        containsError: function containsError(expression) {
            return reErrorValue.test(tokensHelper.stringValue(expression));
        },

        /**
         * Replace the last result variable by a particular value in an expression
         * @param {String|Number|Object} expression
         * @param {String|Number|Object} value
         * @returns {String}
         */
        renderLastResult: function renderLastResult(expression, value) {
            return tokensHelper.stringValue(expression).replace(reAnsVar, tokensHelper.stringValue(value));
        },

        /**
         * Renders a list of tokens into a HTML string, using the display label of each token.
         * @param {token[]} tokens
         * @param {Object} [variables]
         * @returns {String}
         */
        render: function render(tokens, variables) {
            var previous;
            variables = variables || {};

            return termsTpl(_.map(tokens, function (token) {
                var term = {
                    type: token.type,
                    token: token.type,
                    value: token.value,
                    label: token.value
                };

                if (registeredTerms[token.type]) {
                    term.type = registeredTerms[token.type].type;
                    term.label = registeredTerms[token.type].label;

                    // always display the actual value of the last result variable
                    if (token.type === 'ANS' && 'undefined' !== typeof variables[token.value]) {
                        term.label = String(variables.ans || '0').replace(registeredTerms.SUB.value, registeredTerms.NEG.label);
                    }
                } else if (token.type === 'term') {
                    if ('undefined' !== typeof variables[token.value]) {
                        term.type = 'variable';
                    } else {
                        term.type = 'unknown';
                    }
                }

                if (token.type === 'SUB') {
                    if (!previous || tokensHelper.isModifier(previous.type) || previous.token === 'LPAR') {
                        term.label = registeredTerms.NEG.label;
                        term.token = 'NEG';
                    }
                }

                previous = term;

                return term;
            }));
        }
    };

    return tokensHelper;
});
