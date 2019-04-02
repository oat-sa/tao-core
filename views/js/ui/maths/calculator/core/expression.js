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
 * Helper that takes care of expressions
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'ui/maths/calculator/core/terms',
    'ui/maths/calculator/core/tokens',
    'ui/maths/calculator/core/tokenizer',
    'tpl!ui/maths/calculator/core/tpl/terms'
], function (_, registeredTerms, tokensHelper, tokenizerFactory, termsTpl) {
    'use strict';

    /**
     * @typedef {term} renderTerm - Represents a renderable tokenizable term
     * @property {Array} startExponent - List of exponent starts (will produce exponent notation for the term)
     * @property {Array} endExponent - List of exponent ends (will finish exponent notation for the term)
     * @property {Boolean} prefixed - Tells if the term is prefixed (i.e. function treated as binary operator)
     * @property {Boolean} elide - Allows to hide the term when operands exist on each side
     */

    /**
     * Name of the variable that contains the last result
     * @type {String}
     */
    var lastResultVariableName = registeredTerms.ANS.value;

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
    var reAnsVar = new RegExp('\\b' + lastResultVariableName + '\\b', 'g');

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
     * Default number of significant digits used to round displayed variables
     * @type {Number}
     */
    var defaultSignificantDigits = 8;

    /**
     * List of helpers that apply on expression
     * @type {Object}
     */
    var expressionHelper = {
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
        replaceLastResult: function replaceLastResult(expression, value) {
            return tokensHelper.stringValue(expression).replace(reAnsVar, tokensHelper.stringValue(value || '0'));
        },

        /**
         * Rounds the value of the last result variable, and casts it to String
         * @param {Object} variables
         * @param {Number} [significantDigits=8]
         * @returns {Object}
         */
        roundLastResultVariable: function roundLastResultVariable(variables, significantDigits) {
            var lastResult;
            if (variables && 'undefined' !== typeof variables[lastResultVariableName]) {
                lastResult = variables[lastResultVariableName];
                if (lastResult.result && lastResult.result.toSD) {
                    lastResult = lastResult.result.toSD(significantDigits || defaultSignificantDigits);
                    variables[lastResultVariableName] = lastResult.toString();
                }
            }
            return variables;
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
         * Renders an expression into a HTML string, using the display label of each extracted token .
         * @param {String|Number|Object|token[]} expression
         * @param {Object} [variables]
         * @param {calculatorTokenizer} [tokenizer]
         * @returns {String}
         */
        render: function render(expression, variables, tokenizer) {
            var tokens = expression;
            var exponents = [];
            var terms = [];
            var previous;

            /**
             * Transform an operator to a sign
             * @param {renderTerm} term
             * @param {String} token
             */
            function toSignOperator(term, token) {
                if (!previous || tokensHelper.isModifier(previous.type) || previous.token === 'LPAR' || previous.token === 'EXP10') {
                    term.label = registeredTerms[token].label;
                    term.token = token;
                }
            }

            // the expression might be already tokenized, if not we need to tokenize it
            if (!_.isArray(expression)) {
                // we need a valid tokenizer, so if none is provided we must build one
                if (!tokenizer || !tokenizer.tokenize) {
                    tokenizer = tokenizerFactory();
                }
                tokens = tokenizer.tokenize(expression);
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
                    if (term.value === lastResultVariableName && 'undefined' !== typeof variables[term.value]) {
                        term.label = expressionHelper.render(variables[term.value], variables, tokenizer);
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

    return expressionHelper;
});
