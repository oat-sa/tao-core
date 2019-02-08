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
 * Copyright (c) 2018-2019 Open Assessment Technologies SA ;
 */

/**
 * Tokenize a mathematical expression based on the list of known terms.
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'ui/maths/calculator/core/terms',
    'ui/maths/calculator/core/tokens',
    'lib/moo/moo'
], function (_, registeredTerms, tokensHelper, moo) {
    'use strict';

    /**
     * List of ignored tokens
     * @type {Object}
     */
    var ignoredTokens = {
        SPACE: {
            match: /\s+/,
            lineBreaks: true
        }
    };

    /**
     * Match keywords
     * @type {RegExp}
     */
    var reKeyword = /[a-zA-Z_]\w*/;

    /**
     * Match keywords prefixed with @
     * @type {RegExp}
     */
    var rePrefixedKeyword = new RegExp('@' + reKeyword.source);

    /**
     * Match keywords only
     * @type {RegExp}
     */
    var reKeywordOnly = new RegExp('^' + reKeyword.source + '$');

    /**
     * List of keywords (functions from the list of registered terms).
     * @type {Object}
     */
    var keywords = _.pick(registeredTerms, filterKeyword);

    /**
     * List of symbols (operators and operands from the list of registered terms).
     * @type {Object}
     */
    var symbols = _.omit(registeredTerms, filterKeyword);

    /**
     * Filter function that checks if the provided term is a keyword.
     * Keywords are all terms that have alphanumeric non digit value from the list of terms.
     * @param term
     * @returns {boolean}
     */
    function filterKeyword(term) {
        return term.value.match(reKeywordOnly);
    }

    /**
     * @typedef {Object} token
     * @property {String} type - The identifier of the token
     * @property {String} value - The actual value of the token
     * @property {String} text - The raw value that produced the token
     * @property {Number} offset - The original offset in the source
     * @property {Number} lineBreaks - How many line breaks are contained in the raw value
     * @property {Number} line - The line number of the token (starting from 1)
     * @property {Number} col - The column number of the token (starting from 1)
     */

    /**
     * Generates an expression tokenizer.
     *
     * @example
     *
     * var expression = '(.1 + .2) * 10^8';
     * var tokenizer = calculatorTokenizerFactory();
     * var terms = tokenizer(expression);
     *
     * // terms now contains an array of terms:
     * // [{type: "LPAR", value: "(", text: "(", offset: 0, lineBreaks: 0, line: 1, col: 1},
     * //  {type: "DOT", value: ".", text: ".", offset: 1, lineBreaks: 0, line: 1, col: 2},
     * //  {type: "NUM1", value: "1", text: "1", offset: 2, lineBreaks: 0, line: 1, col: 3},
     * //  {type: "ADD", value: "+", text: "+", offset: 4, lineBreaks: 0, line: 1, col: 5},
     * //  {type: "DOT", value: ".", text: ".", offset: 6, lineBreaks: 0, line: 1, col: 7},
     * //  {type: "NUM2", value: "2", text: "2", offset: 7, lineBreaks: 0, line: 1, col: 8},
     * //  {type: "RPAR", value: ")", text: ")", offset: 8, lineBreaks: 0, line: 1, col: 9},
     * //  {type: "MUL", value: "*", text: "*", offset: 10, lineBreaks: 0, line: 1, col: 11},
     * //  {type: "NUM1", value: "1", text: "1", offset: 12, lineBreaks: 0, line: 1, col: 13},
     * //  {type: "NUM0", value: "0", text: "0", offset: 13, lineBreaks: 0, line: 1, col: 14},
     * //  {type: "POW", value: "^", text: "^", offset: 14, lineBreaks: 0, line: 1, col: 15},
     * //  {type: "NUM8", value: "8", text: "8", offset: 15, lineBreaks: 0, line: 1, col: 16}]
     *
     * @param {Object} [config]
     * @param {Object} [config.keywords] - List of additional keywords: key being the name, value being the pattern (should be on the domain /[a-zA-Z]+/)
     * @param {Object} [config.symbols] - List of additional symbols: key being the name, value being the pattern
     * @returns {calculatorTokenizer}
     */
    function calculatorTokenizerFactory(config) {
        var keywordsTransform, lexer;

        config = config || {};
        config.keywords = _.defaults(_.mapValues(keywords, 'value'), config.keywords);
        config.symbols = _.defaults(_.mapValues(symbols, 'value'), config.symbols);
        keywordsTransform = moo.keywords(config.keywords);

        // Lexer used to tokenize the expression
        lexer = moo.compile(_.defaults({}, ignoredTokens, {
            prefixed: {
                match: rePrefixedKeyword,
                type: function(token) {
                    // simply rely on the keywords transform to identify the prefixed keyword
                    return keywordsTransform(token.substring(1));
                }
            },
            term: {
                match: reKeyword,
                type: keywordsTransform
            },
            syntaxError: moo.error
        }, config.symbols));

        /**
         * @typedef {Object} calculatorTokenizer
         */
        return {
            /**
             * Gets an iterator that will returns tokens from the provided expression
             * @param {String} expression
             * @returns {function(): String}
             */
            iterator: function iterator(expression) {
                lexer.reset(tokensHelper.stringValue(expression));

                return function next() {
                    var term;
                    do {
                        term = lexer.next();
                    } while (term && ignoredTokens[term.type]);
                    return term;
                };
            },

            /**
             * Tokenizes the expression
             * @param {String} expression
             * @returns {Array}
             */
            tokenize: function tokenize(expression) {
                var terms = [];
                var term;
                lexer.reset(tokensHelper.stringValue(expression));
                do {
                    term = lexer.next();
                    if (term && !ignoredTokens[term.type]) {
                        terms.push(term);
                    }
                } while (term);
                return terms;
            }
        };
    }

    return calculatorTokenizerFactory;
});
