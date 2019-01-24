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
 * Plugin that allows to change the sign of the current operand
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'i18n',
    'util/namespace',
    'ui/maths/calculator/core/plugin',
    'ui/maths/calculator/core/terms'
], function (_, __, nsHelper, pluginFactory, registeredTerms) {
    'use strict';

    var pluginName = 'sign';

    /**
     * List of tokens that refuse explicit positive sign
     * @type {String[]}
     */
    var refuseExplicitPositive = [
        'LPAR', 'SUB', 'ADD', 'MUL', 'DIV', 'MOD', 'POW', 'ASSIGN'
    ];

    /**
     * @typedef {Object} signChange
     * @property {String} sign - The sign to insert
     * @property {Number} offset - The offset where insert the sign
     * @property {Number} length - The length of text to replace
     * @property {Number} move - The move to apply on the current position
     */

    /**
     * List of known strategies to apply on an expression in order to process sign change.
     * Each strategy will return either `null` if cannot apply, or the descriptor of the change to apply.
     * @type {Function[]}
     */
    var strategies = [
        /**
         * Strategy that applies on numeric operands only
         * @param {Number} index - The index of the current token
         * @param {token[]} tokens - The list of tokens that represent the expression
         * @returns {signChange|null} - The result of the strategy: `null` if cannot apply, or the descriptor of the change
         */
        function strategyNumeric(index, tokens) {
            var token = tokens[index] || null;
            var type = getTokenType(token);
            var result = null;

            if (type === 'operand' && index >= 0) {
                // find the first token on the left of the operand
                while (index && type === 'operand') {
                    index--;
                    token = tokens[index] || null;
                    type = getTokenType(token);
                }

                if (type === 'operand' && index === 0) {
                    // the operand is the first of the expression, so the sign is implicit +, simply negate the value
                    result = insertNegativeSign(token);
                } else {
                    // the operand is preceded by something else
                    result = applySignChange(index, tokens);
                }
            }

            return result;
        },

        /**
         * Strategy that applies on operators only
         * @param {Number} index - The index of the current token
         * @param {token[]} tokens - The list of tokens that represent the expression
         * @returns {signChange|null} - The result of the strategy: `null` if cannot apply, or the descriptor of the change
         */
        function strategyOperator(index, tokens) {
            var token = tokens[index] || null;
            var type = getTokenType(token);
            var result = null;

            if (type === 'operator' && index >= 0) {
                if (token.type === 'SUB') {
                    // the operator is -, simply replace it by +
                    result = replaceByPositiveSign(token, index, tokens);
                } else if (token.type === 'ADD') {
                    // the operator is +, simply replace it by -
                    result = replaceByNegativeSign(token);
                } else if (token.type === 'FAC' && index > 0) {
                    // the operator is !, need to identify the operand
                    result = applyStrategies(index - 1, tokens);
                }
            }

            return result;
        },

        /**
         * Strategy that applies on named symbols only (constants, variables, functions)
         * @param {Number} index - The index of the current token
         * @param {token[]} tokens - The list of tokens that represent the expression
         * @returns {signChange|null} - The result of the strategy: `null` if cannot apply, or the descriptor of the change
         */
        function strategySymbol(index, tokens) {
            var token = tokens[index] || null;
            var type = getTokenType(token);
            var result = null;

            if ((type === 'constant' || type === 'function' || type === 'term') && index >= 0) {
                if (index === 0) {
                    // the token is the first of the expression, so the sign is implicit +, simply negate the value
                    result = insertNegativeSign(token);
                } else {
                    // the token is preceded by something else
                    result = applySignChange(index - 1, tokens);
                }
            }

            return result;
        },

        /**
         * Strategy that applies on sub-expression only
         * @param {Number} index - The index of the current token
         * @param {token[]} tokens - The list of tokens that represent the expression
         * @returns {signChange|null} - The result of the strategy: `null` if cannot apply, or the descriptor of the change
         */
        function strategyExpression(index, tokens) {
            var token = tokens[index] || null;
            var type = getTokenType(token);
            var result = null;
            var count = 0;

            if (type === 'modificator' && index >= 0) {
                if (token.type === 'RPAR') {
                    count ++;
                }

                // find the opening parenthesis
                while (index && (token.type !== 'LPAR' || count)) {
                    index--;
                    token = tokens[index] || null;
                    type = getTokenType(token);

                    if (token.type === 'RPAR') {
                        count ++;
                    }
                    if (token.type === 'LPAR') {
                        count --;
                    }
                }

                if (!count && type === 'modificator' && token.type === 'LPAR') {
                    if (index === 0) {
                        // the token is the first of the expression, so the sign is implicit +, simply negate the value
                        result = insertNegativeSign(token);
                    } else {
                        // the token is preceded by something else
                        result = applySignChange(index - 1, tokens);
                    }
                }
            }

            return result;
        }
    ];

    /**
     * Apply strategies to produce a sign change descriptor
     * @param {Number} index - The index of the current token
     * @param {token[]} tokens - The list of tokens that represent the expression
     * @returns {signChange|null} - The result of the strategy: `null` if cannot apply, or the descriptor of the change
     */
    function applyStrategies(index, tokens) {
        var result = null;

        _.forEach(strategies, function (strategy) {
            result = strategy(index, tokens);
            return !result;
        });

        return result;
    }

    /**
     * Apply a sign change at the current index
     * @param {Number} index - The index of the current token
     * @param {token[]} tokens - The list of tokens that represent the expression
     * @returns {signChange|null} - The result of the strategy: `null` if cannot apply, or the descriptor of the change
     */
    function applySignChange(index, tokens) {
        var token = tokens[index] || null;
        var nextToken = tokens[index + 1] || null;
        var type = getTokenType(token);
        var result = null;

        if (token) {
            if (type === 'operator') {
                // an operator precedes the operand
                if (token.type === 'SUB') {
                    // the operator is -, simply replace it by +
                    result = replaceByPositiveSign(token, index, tokens);
                } else if (token.type === 'ADD') {
                    // the operator is +, simply replace it by -
                    result = replaceByNegativeSign(token);
                } else if (nextToken) {
                    // the operator is not + or -, simply negate the value
                    result = insertNegativeSign(nextToken);
                }
            } else if (nextToken && (type === 'function' || (type === 'modificator' && token.type === 'LPAR'))) {
                // a function or a left parenthesis precedes the operand, simply negate the operand
                result = insertNegativeSign(nextToken);
            }
        }

        return result;
    }

    /**
     * Checks if a token accept an explicit positive sign on the right
     * @param {Object} token
     * @returns {Boolean}
     */
    function acceptExplicitPositive(token) {
        return !token || (refuseExplicitPositive.indexOf(token.type) === -1 && getTokenType(token) !== 'function');
    }

    /**
     * Produces a descriptor to insert a negative sign
     * @param {token} token
     * @returns {signChange}
     */
    function insertNegativeSign(token) {
        return {
            offset: token.offset,
            length: 0,
            sign: registeredTerms.SUB.value,
            move: registeredTerms.SUB.value.length
        };
    }

    /**
     * Produces a descriptor to replace a sign by a negative sign
     * @param {token} token
     * @returns {signChange}
     */
    function replaceByNegativeSign(token) {
        return {
            offset: token.offset,
            length: token.value.length,
            sign: registeredTerms.SUB.value,
            move: registeredTerms.SUB.value.length - token.value.length
        };
    }

    /**
     * Produces a descriptor to replace a sign by a positive sign
     * @param {token} token
     * @param {Number} index
     * @param {token[]} tokens
     * @returns {signChange}
     */
    function replaceByPositiveSign(token, index, tokens) {
        var allowExplicit = index && acceptExplicitPositive(tokens[index - 1]);
        return {
            offset: token.offset,
            length: token.value.length,
            sign: allowExplicit ? registeredTerms.ADD.value : '',
            move: (allowExplicit ? registeredTerms.ADD.value.length : 0) - token.value.length
        };
    }

    /**
     * Replaces text at position
     * @param {String} str - The string in which apply the replacement
     * @param {Number} index - The index from which applying the replacement
     * @param {Number} length - The length of the string to replace
     * @param {String} sub - The replacement string
     * @returns {String}
     */
    function splice(str, index, length, sub) {
        return str.substring(0, index) + sub + str.substring(index + length);
    }


    /**
     * Identifies the type of a given token
     * @param {Object} token
     * @returns {String|null}
     */
    function getTokenType(token) {
        var term, type = null;
        if (token) {
            type = token.type;
            term = registeredTerms[token.type];
            if (term) {
                type = term.type;
            }
        }
        return type;
    }

    return pluginFactory({
        name: pluginName,

        /**
         * Called when the plugin is installing in its host.
         */
        install: function install() {
            var calculator = this.getCalculator();
            calculator
                .setCommand('sign', __('Sign change'), __('Change the sign of the current operand'));
        },

        /**
         * Called when the plugin should be initialized.
         */
        init: function init() {
            var calculator = this.getCalculator();

            // applies a sign change based on strategies
            calculator
                .on(nsHelper.namespaceAll('command-sign', pluginName), function () {
                    var tokens = calculator.getTokens();
                    var index = calculator.getTokenIndex();
                    var expression = calculator.getExpression();
                    var result;

                    if (expression !== '0') {
                        result = applyStrategies(index, tokens);
                        if (result) {
                            expression = splice(expression, result.offset, result.length, result.sign);
                            calculator.setExpression(expression);
                            calculator.setPosition(calculator.getPosition() + result.move);
                        }
                    }
                });
        },

        /**
         * Called when the plugin is destroyed. Mostly when the host is destroyed itself.
         */
        destroy: function destroy() {
            var calculator = this.getCalculator();
            calculator
                .deleteCommand('sign')
                .off('.' + pluginName);
        }
    });
});
