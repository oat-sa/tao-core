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
 * Copyright (c) 2018 Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'ui/maths/calculator/core/tokenizer',
    'ui/maths/calculator/core/terms'
], function (_, calculatorTokenizerFactory, registeredTerms) {
    'use strict';

    QUnit.module('Factory');

    QUnit.test('module', function (assert) {
        QUnit.expect(3);
        assert.equal(typeof calculatorTokenizerFactory, 'function', "The module exposes a function");
        assert.equal(typeof calculatorTokenizerFactory(), 'object', "The factory produces an object");
        assert.notStrictEqual(calculatorTokenizerFactory(), calculatorTokenizerFactory(), "The factory provides a different object on each call");
    });

    QUnit.cases([
        {title: 'iterator'},
        {title: 'tokenize'}
    ]).test('API ', function (data, assert) {
        var instance = calculatorTokenizerFactory();
        QUnit.expect(1);
        assert.equal(typeof instance[data.title], 'function', 'The instance exposes a "' + data.title + '" function');
    });

    QUnit.module('API');

    QUnit.test('tokenize - success', function (assert) {
        var tokenizer, tokens;

        QUnit.expect(34);

        tokenizer = calculatorTokenizerFactory();
        tokens = tokenizer.tokenize('(.1 + .2) * 10^8 + 4 @nthrt 8');

        assert.ok(_.isArray(tokens), 'Got a list of tokens');
        assert.equal(tokens.length, 16, 'The expression has been tokenized in 16 tokens');
        assert.equal(tokens[0].type, 'LPAR', 'The expected token is found at position 0');
        assert.equal(tokens[0].offset, 0, 'The expected token is found at offset 0');
        assert.equal(tokens[1].type, 'DOT', 'The expected token is found at position 1');
        assert.equal(tokens[1].offset, 1, 'The expected token is found at offset 1');
        assert.equal(tokens[2].type, 'NUM1', 'The expected token is found at position 2');
        assert.equal(tokens[2].offset, 2, 'The expected token is found at offset 2');
        assert.equal(tokens[3].type, 'ADD', 'The expected token is found at position 3');
        assert.equal(tokens[3].offset, 4, 'The expected token is found at offset 4');
        assert.equal(tokens[4].type, 'DOT', 'The expected token is found at position 4');
        assert.equal(tokens[4].offset, 6, 'The expected token is found at offset 6');
        assert.equal(tokens[5].type, 'NUM2', 'The expected token is found at position 5');
        assert.equal(tokens[5].offset, 7, 'The expected token is found at offset 7');
        assert.equal(tokens[6].type, 'RPAR', 'The expected token is found at position 6');
        assert.equal(tokens[6].offset, 8, 'The expected token is found at offset 8');
        assert.equal(tokens[7].type, 'MUL', 'The expected token is found at position 7');
        assert.equal(tokens[7].offset, 10, 'The expected token is found at offset 10');
        assert.equal(tokens[8].type, 'NUM1', 'The expected token is found at position 8');
        assert.equal(tokens[8].offset, 12, 'The expected token is found at offset 12');
        assert.equal(tokens[9].type, 'NUM0', 'The expected token is found at position 9');
        assert.equal(tokens[9].offset, 13, 'The expected token is found at offset 13');
        assert.equal(tokens[10].type, 'POW', 'The expected token is found at position 10');
        assert.equal(tokens[10].offset, 14, 'The expected token is found at offset 14');
        assert.equal(tokens[11].type, 'NUM8', 'The expected token is found at position 11');
        assert.equal(tokens[11].offset, 15, 'The expected token is found at offset 15');
        assert.equal(tokens[12].type, 'ADD', 'The expected token is found at position 12');
        assert.equal(tokens[12].offset, 17, 'The expected token is found at offset 17');
        assert.equal(tokens[13].type, 'NUM4', 'The expected token is found at position 13');
        assert.equal(tokens[13].offset, 19, 'The expected token is found at offset 19');
        assert.equal(tokens[14].type, 'NTHRT', 'The expected token is found at position 14');
        assert.equal(tokens[14].offset, 21, 'The expected token is found at offset 21');
        assert.equal(tokens[15].type, 'NUM8', 'The expected token is found at position 15');
        assert.equal(tokens[15].offset, 28, 'The expected token is found at offset 28');
    });

    QUnit.test('tokenize - error', function (assert) {
        var tokenizer, tokens;

        QUnit.expect(4);

        tokenizer = calculatorTokenizerFactory();
        tokens = tokenizer.tokenize(' 3+4 *$foo + sinh 1');
        assert.ok(_.isArray(tokens), 'Got a list of tokens');
        assert.equal(tokens.length, 5, 'The expression has been tokenized in 5 tokens');
        assert.equal(tokens[4].type, 'syntaxError', 'The expected error has been found');
        assert.equal(tokens[4].offset, 6, 'The expected error has been found at offset 6');
    });

    QUnit.test('tokenize - additional', function (assert) {
        var tokenizer, tokens;

        QUnit.expect(20);

        tokenizer = calculatorTokenizerFactory({
            symbols: {
                DOLLAR: '$'
            },
            keywords: {
                FOO: 'foo'
            }
        });
        tokens = tokenizer.tokenize(' 3+4 *$foo + sinh PI');

        assert.ok(_.isArray(tokens), 'Got a list of tokens');
        assert.equal(tokens.length, 9, 'The expression has been tokenized in 9 tokens');
        assert.equal(tokens[0].type, 'NUM3', 'The expected token is found at position 0');
        assert.equal(tokens[0].offset, 1, 'The expected token is found at offset 1');
        assert.equal(tokens[1].type, 'ADD', 'The expected token is found at position 1');
        assert.equal(tokens[1].offset, 2, 'The expected token is found at offset 3');
        assert.equal(tokens[2].type, 'NUM4', 'The expected token is found at position 2');
        assert.equal(tokens[2].offset, 3, 'The expected token is found at offset 3');
        assert.equal(tokens[3].type, 'MUL', 'The expected token is found at position 3');
        assert.equal(tokens[3].offset, 5, 'The expected token is found at offset 5');
        assert.equal(tokens[4].type, 'DOLLAR', 'The expected token is found at position 4');
        assert.equal(tokens[4].offset, 6, 'The expected token is found at offset 6');
        assert.equal(tokens[5].type, 'FOO', 'The expected token is found at position 5');
        assert.equal(tokens[5].offset, 7, 'The expected token is found at offset 7');
        assert.equal(tokens[6].type, 'ADD', 'The expected token is found at position 6');
        assert.equal(tokens[6].offset, 11, 'The expected token is found at offset 11');
        assert.equal(tokens[7].type, 'SINH', 'The expected token is found at position 7');
        assert.equal(tokens[7].offset, 13, 'The expected token is found at offset 13');
        assert.equal(tokens[8].type, 'PI', 'The expected token is found at position 8');
        assert.equal(tokens[8].offset, 18, 'The expected token is found at offset 18');
    });

    QUnit.test('tokenize - all', function (assert) {
        var tokenizer, tokens;

        var expectedTokens = [];
        var expression = '';
        _.forEach(registeredTerms, function(term, token) {
            expression += term.value + ' ';
            expectedTokens.push(token);
        });

        QUnit.expect(expectedTokens.length + 2);

        tokenizer = calculatorTokenizerFactory();
        tokens = tokenizer.tokenize(expression);

        assert.ok(_.isArray(tokens), 'Got a list of tokens');
        assert.equal(tokens.length, expectedTokens.length, 'The list contains the expected number of tokens');

        _.forEach(tokens, function(token, index) {
            assert.equal(token.type, expectedTokens[index], 'The expected token ' + expectedTokens[index] + ' is found at index ' + index);
        });
    });

    QUnit.test('iterator - success', function (assert) {
        var tokenizer, next, token;

        QUnit.expect(34);

        tokenizer = calculatorTokenizerFactory();
        next = tokenizer.iterator('(.1 + .2) * 10^8 + 4 @nthrt 8');
        assert.ok(_.isFunction(next), 'Got a function');

        token = next();
        assert.equal(token.type, 'LPAR', 'The expected token is found at position 0');
        assert.equal(token.offset, 0, 'The expected token is found at offset 0');
        token = next();
        assert.equal(token.type, 'DOT', 'The expected token is found at position 1');
        assert.equal(token.offset, 1, 'The expected token is found at offset 1');
        token = next();
        assert.equal(token.type, 'NUM1', 'The expected token is found at position 2');
        assert.equal(token.offset, 2, 'The expected token is found at offset 2');
        token = next();
        assert.equal(token.type, 'ADD', 'The expected token is found at position 3');
        assert.equal(token.offset, 4, 'The expected token is found at offset 4');
        token = next();
        assert.equal(token.type, 'DOT', 'The expected token is found at position 4');
        assert.equal(token.offset, 6, 'The expected token is found at offset 6');
        token = next();
        assert.equal(token.type, 'NUM2', 'The expected token is found at position 5');
        assert.equal(token.offset, 7, 'The expected token is found at offset 7');
        token = next();
        assert.equal(token.type, 'RPAR', 'The expected token is found at position 6');
        assert.equal(token.offset, 8, 'The expected token is found at offset 8');
        token = next();
        assert.equal(token.type, 'MUL', 'The expected token is found at position 7');
        assert.equal(token.offset, 10, 'The expected token is found at offset 10');
        token = next();
        assert.equal(token.type, 'NUM1', 'The expected token is found at position 8');
        assert.equal(token.offset, 12, 'The expected token is found at offset 12');
        token = next();
        assert.equal(token.type, 'NUM0', 'The expected token is found at position 9');
        assert.equal(token.offset, 13, 'The expected token is found at offset 13');
        token = next();
        assert.equal(token.type, 'POW', 'The expected token is found at position 10');
        assert.equal(token.offset, 14, 'The expected token is found at offset 14');
        token = next();
        assert.equal(token.type, 'NUM8', 'The expected token is found at position 11');
        assert.equal(token.offset, 15, 'The expected token is found at offset 15');
        token = next();
        assert.equal(token.type, 'ADD', 'The expected token is found at position 12');
        assert.equal(token.offset, 17, 'The expected token is found at offset 17');
        token = next();
        assert.equal(token.type, 'NUM4', 'The expected token is found at position 13');
        assert.equal(token.offset, 19, 'The expected token is found at offset 19');
        token = next();
        assert.equal(token.type, 'NTHRT', 'The expected token is found at position 14');
        assert.equal(token.offset, 21, 'The expected token is found at offset 21');
        token = next();
        assert.equal(token.type, 'NUM8', 'The expected token is found at position 15');
        assert.equal(token.offset, 28, 'The expected token is found at offset 28');
        token = next();
        assert.equal(typeof token, 'undefined', 'The iterator has completed the expression');
    });

    QUnit.test('iterator - error', function (assert) {
        var tokenizer, next, token;

        QUnit.expect(12);

        tokenizer = calculatorTokenizerFactory();
        next = tokenizer.iterator(' 3+4 *$foo + sinh 1');
        assert.ok(_.isFunction(next), 'Got a function');

        token = next();
        assert.equal(token.type, 'NUM3', 'The expected token is found at position 0');
        assert.equal(token.offset, 1, 'The expected token is found at offset 1');
        token = next();
        assert.equal(token.type, 'ADD', 'The expected token is found at position 1');
        assert.equal(token.offset, 2, 'The expected token is found at offset 3');
        token = next();
        assert.equal(token.type, 'NUM4', 'The expected token is found at position 2');
        assert.equal(token.offset, 3, 'The expected token is found at offset 3');
        token = next();
        assert.equal(token.type, 'MUL', 'The expected token is found at position 3');
        assert.equal(token.offset, 5, 'The expected token is found at offset 5');
        token = next();
        assert.equal(token.type, 'syntaxError', 'The expected error has been found');
        assert.equal(token.offset, 6, 'The expected error has been found at offset 6');
        token = next();
        assert.equal(typeof token, 'undefined', 'The iterator has completed the expression');
    });

    QUnit.test('iterator - additional', function (assert) {
        var tokenizer, next, token;

        QUnit.expect(20);

        tokenizer = calculatorTokenizerFactory({
            symbols: {
                DOLLAR: '$'
            },
            keywords: {
                FOO: 'foo'
            }
        });
        next = tokenizer.iterator(' 3+4 *$foo + sinh PI');
        assert.ok(_.isFunction(next), 'Got a function');

        token = next();
        assert.equal(token.type, 'NUM3', 'The expected token is found at position 0');
        assert.equal(token.offset, 1, 'The expected token is found at offset 1');
        token = next();
        assert.equal(token.type, 'ADD', 'The expected token is found at position 1');
        assert.equal(token.offset, 2, 'The expected token is found at offset 3');
        token = next();
        assert.equal(token.type, 'NUM4', 'The expected token is found at position 2');
        assert.equal(token.offset, 3, 'The expected token is found at offset 3');
        token = next();
        assert.equal(token.type, 'MUL', 'The expected token is found at position 3');
        assert.equal(token.offset, 5, 'The expected token is found at offset 5');
        token = next();
        assert.equal(token.type, 'DOLLAR', 'The expected token is found at position 4');
        assert.equal(token.offset, 6, 'The expected token is found at offset 6');
        token = next();
        assert.equal(token.type, 'FOO', 'The expected token is found at position 5');
        assert.equal(token.offset, 7, 'The expected token is found at offset 7');
        token = next();
        assert.equal(token.type, 'ADD', 'The expected token is found at position 6');
        assert.equal(token.offset, 11, 'The expected token is found at offset 11');
        token = next();
        assert.equal(token.type, 'SINH', 'The expected token is found at position 7');
        assert.equal(token.offset, 13, 'The expected token is found at offset 13');
        token = next();
        assert.equal(token.type, 'PI', 'The expected token is found at position 8');
        assert.equal(token.offset, 18, 'The expected token is found at offset 18');
        token = next();
        assert.equal(typeof token, 'undefined', 'The iterator has completed the expression');
    });

});
