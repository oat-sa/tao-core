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
    'lodash',
    'ui/maths/calculator/core/terms',
    'tpl!ui/maths/calculator/core/tpl/terms'
], function (_, registeredTerms, termsTpl) {
    'use strict';

    /**
     * @typedef {term} renderTerm - Represents a renderable tokenizable term
     * @property {Array} startExponent - List of exponent starts (will produce exponent notation for the term)
     * @property {Array} endExponent - List of exponent ends (will finish exponent notation for the term)
     * @property {Boolean} prefixed - Tells if the term is prefixed (i.e. function treated as binary operator)
     * @property {Boolean} elide - Allows to hide the term when operands exist on each side
     */

    /**
     * Regex that matches the prefixed function operators
     * @type {RegExp}
     */
    var rePrefixedTerm = /^@[a-zA-Z_]\w*$/;

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
     * List of tokens representing sign or sum
     * @type {String[]}
     */
    var signOperators = ['NEG', 'POS', 'SUB', 'ADD'];

    /**
     * List of tokens representing sub exponent parts to continue
     * @type {String[]}
     */
    var continueExponent = ['POW', 'NTHRT'];

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
                if (expression && 'undefined' !== typeof expression.result) {
                    expression = expression.result;
                } else if (expression && 'undefined' !== typeof expression.value) {
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
         * Replace sign operators by a proper symbol
         * @param {String|Number|Object} expression
         * @returns {String}
         */
        renderSign: function renderSign(expression) {
            return tokensHelper.stringValue(expression)
                .replace(registeredTerms.SUB.value, registeredTerms.NEG.label)
                .replace(registeredTerms.ADD.value, registeredTerms.POS.label);
        },

        /**
         * Renders a list of tokens into a HTML string, using the display label of each token.
         * @param {token[]} tokens
         * @param {Object} [variables]
         * @returns {String}
         */
        render: function render(tokens, variables) {
            var exponents = [];
            var terms = [];
            var previous;

            /**
             * Transform an operator to a sign
             * @param {renderTerm} term
             * @param {String} token
             */
            function toSignOperator(term, token) {
                if (!previous || tokensHelper.isModifier(previous.type) || previous.token === 'LPAR') {
                    term.label = registeredTerms[token].label;
                    term.token = token;
                }
            }

            variables = variables || {};

            // each token needs to be translated into a displayable term
            _.forEach(tokens, function (token, index) {
                var registeredTerm = registeredTerms[token.type];
                /**
                 * @type {renderTerm}
                 */
                var term = {
                    type: token.type,
                    token: token.type,
                    value: token.value,
                    label: token.value,
                    description: token.value,
                    exponent: null,
                    startExponent: [],
                    endExponent: [],
                    prefixed: rePrefixedTerm.test(token.value),
                    elide: false
                };

                if (registeredTerm) {
                    _.merge(term, registeredTerm);

                    // always display the actual value of the last result variable
                    // also takes care of the value's sign
                    if (term.token === 'ANS' && 'undefined' !== typeof variables[term.value]) {
                        term.label = tokensHelper.renderSign(String(variables.ans || '0'));
                    }
                } else if (term.token === 'term') {
                    // unspecified token can be a variable
                    if ('undefined' !== typeof variables[term.value]) {
                        term.type = 'variable';
                    } else {
                        term.type = 'unknown';
                    }
                }

                // take care of the value's sign
                if (term.token === 'SUB') {
                    toSignOperator(term, 'NEG');
                } else if (term.token === 'ADD') {
                    toSignOperator(term, 'POS');
                }

                terms.push(term);

                // exponents will be processed in a second pass
                // for now we just need to keep track of the position
                if (term.exponent) {
                    exponents.push(index);
                }

                previous = term;
            });

            // if any exponent has been discovered, we need to process them now
            _.forEach(exponents, function (index) {
                var term = terms[index];
                if (term.exponent === 'left' && index > 0) {
                    exponentOnTheLeft(index, terms);
                } else if (term.exponent === 'right' && index < terms.length - 1) {
                    exponentOnTheRight(index, terms);
                }
            });

            return termsTpl(terms);
        }
    };

    /**
     * Search for the full operand on the left, then tag the edges with exponent flags
     * @param {Number} index
     * @param {renderTerm[]} terms
     */
    function exponentOnTheLeft(index, terms) {
        var parenthesis = 0;
        var next = terms[index];
        var term = terms[--index];

        /**
         * Simply moves the cursor to the next term to examine.
         * Here the move is made from the right to the left.
         */
        function nextTerm() {
            next = term;
            term = terms[--index];
        }

        // only take care of actual operand value or sub expression (starting from the right)
        if (term && (tokensHelper.isOperand(term.type) || term.token === 'RPAR')) {
            term.endExponent.push(term.endExponent.length);

            if (term.token === 'RPAR') {
                // closing parenthesis, we need to find the opening parenthesis
                parenthesis++;
                while (index > 0 && parenthesis > 0) {
                    nextTerm();

                    if (term.token === 'RPAR') {
                        parenthesis++;
                    } else if (term.token === 'LPAR') {
                        parenthesis--;
                    }
                }

                // a function could be attached to the sub expression, if so we must keep the link
                // however, the prefixed functions are particular as they act as a binary operators,
                // and therefore are not considered as function here
                if (index > 0 && tokensHelper.isFunction(terms[index - 1]) && !terms[index - 1].prefixed) {
                    nextTerm();
                }
            } else if (tokensHelper.isDigit(term.type)) {
                // chain of digits should be treated as a single operand
                while (index && tokensHelper.isDigit(term.type)) {
                    nextTerm();
                }
                // if the end of the chain has been overflown, we must step back one token
                if (!tokensHelper.isDigit(term.type)) {
                    term = next;
                }
            }
            term.startExponent.push(term.startExponent.length);
        }
    }

    /**
     * Search for the full operand on the right, then tag the edges with exponent flags
     * @param {Number} index
     * @param {renderTerm[]} terms
     */
    function exponentOnTheRight(index, terms) {
        var last = terms.length - 1;
        var parenthesis = 0;
        var startAt = index;
        var previous = terms[index];
        var term = terms[++index];
        var shouldContinue;

        /**
         * Simply moves the cursor to the next term to examine.
         * Here the move is made from the left to the right.
         */
        function nextTerm() {
            previous = term;
            term = terms[++index];
        }

        /**
         * Simply moves back the cursor to the previous term.
         * Here the move is made from the right to the left.
         */
        function previousTerm() {
            term = previous;
            previous = terms[--index];
        }

        // only take care of actual operand value or sub expression (starting from the left)
        if (term && (tokensHelper.isOperand(term.type) || term.token === 'LPAR' || signOperators.indexOf(term.token) >= 0)) {
            term.startExponent.push(term.startExponent.length);

            // we use an internal loop as exponents could be chained
            do {
                shouldContinue = false;

                // functions are attached to an operand, and this link should be kept
                while (index < last && (tokensHelper.isFunction(term.type) || signOperators.indexOf(term.token) >= 0)) {
                    nextTerm();
                }

                // if the end has been reached, step back one token
                if (!term) {
                    previousTerm();
                }

                if (term.token === 'LPAR') {
                    // opening parenthesis, we need to find the closing parenthesis
                    parenthesis++;
                    while (index < last && parenthesis > 0) {
                        nextTerm();

                        if (term.token === 'LPAR') {
                            parenthesis++;
                        } else if (term.token === 'RPAR') {
                            parenthesis--;
                        }
                    }
                } else if (tokensHelper.isDigit(term.type)) {
                    // chain of digits should be treated as a single operand
                    while (index < last && tokensHelper.isDigit(term.type)) {
                        nextTerm();
                    }
                    // if the end of the chain has been overflown, we must step back one token
                    if (!term || !tokensHelper.isDigit(term.type)) {
                        previousTerm();
                    }
                }

                // factorial is a special case, as the operator can be placed either on the right or on the left
                // in any case it should be attached to its operand
                while (index < last && terms[index + 1].token === 'FAC') {
                    nextTerm();
                }

                // sometimes a sub exponent continues the chain and should be part of the expression to put in exponent
                if ((index < last && continueExponent.indexOf(terms[index + 1].token) >= 0)) {
                    // the next term should be ignored as we already know it is an exponent operator
                    // then the term after have to be set as the current one
                    nextTerm();
                    nextTerm();
                    shouldContinue = true;
                }
            } while (shouldContinue);

            term.endExponent.push(term.endExponent.length);

            // elide the operator if operands are complete
            if ( startAt > 0 && startAt < last && terms[startAt].token === 'POW' && terms[startAt + 1].startExponent.length) {
                terms[startAt].elide = true;
            }
        }
    }

    return tokensHelper;
});
