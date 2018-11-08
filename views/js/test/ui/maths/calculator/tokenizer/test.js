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
    'ui/maths/calculator/tokenizer'
], function (_, calculatorTokenizerFactory) {
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
        var tokenizer, terms;

        QUnit.expect(26);

        tokenizer = calculatorTokenizerFactory();
        terms = tokenizer.tokenize('(.1 + .2) * 10^8');

        assert.ok(_.isArray(terms), 'Got a lis of terms');
        assert.equal(terms.length, 12, 'The expression has been tokenized in 12 terms');
        assert.equal(terms[0].type, 'LPAR', 'The expected term is found at position 0');
        assert.equal(terms[0].offset, 0, 'The expected term is found at offset 0');
        assert.equal(terms[1].type, 'DOT', 'The expected term is found at position 1');
        assert.equal(terms[1].offset, 1, 'The expected term is found at offset 1');
        assert.equal(terms[2].type, 'NUM1', 'The expected term is found at position 2');
        assert.equal(terms[2].offset, 2, 'The expected term is found at offset 2');
        assert.equal(terms[3].type, 'ADD', 'The expected term is found at position 3');
        assert.equal(terms[3].offset, 4, 'The expected term is found at offset 4');
        assert.equal(terms[4].type, 'DOT', 'The expected term is found at position 4');
        assert.equal(terms[4].offset, 6, 'The expected term is found at offset 6');
        assert.equal(terms[5].type, 'NUM2', 'The expected term is found at position 5');
        assert.equal(terms[5].offset, 7, 'The expected term is found at offset 7');
        assert.equal(terms[6].type, 'RPAR', 'The expected term is found at position 6');
        assert.equal(terms[6].offset, 8, 'The expected term is found at offset 8');
        assert.equal(terms[7].type, 'MUL', 'The expected term is found at position 7');
        assert.equal(terms[7].offset, 10, 'The expected term is found at offset 10');
        assert.equal(terms[8].type, 'NUM1', 'The expected term is found at position 8');
        assert.equal(terms[8].offset, 12, 'The expected term is found at offset 12');
        assert.equal(terms[9].type, 'NUM0', 'The expected term is found at position 9');
        assert.equal(terms[9].offset, 13, 'The expected term is found at offset 13');
        assert.equal(terms[10].type, 'POW', 'The expected term is found at position 10');
        assert.equal(terms[10].offset, 14, 'The expected term is found at offset 14');
        assert.equal(terms[11].type, 'NUM8', 'The expected term is found at position 11');
        assert.equal(terms[11].offset, 15, 'The expected term is found at offset 15');
    });

    QUnit.test('tokenize - error', function (assert) {
        var tokenizer, terms;

        QUnit.expect(4);

        tokenizer = calculatorTokenizerFactory();
        terms = tokenizer.tokenize(' 3+4 *$foo + sinh 1');
        assert.ok(_.isArray(terms), 'Got a lis of terms');
        assert.equal(terms.length, 5, 'The expression has been tokenized in 5 terms');
        assert.equal(terms[4].type, 'syntaxError', 'The expected error has been found');
        assert.equal(terms[4].offset, 6, 'The expected error has been found at offset 6');
    });

    QUnit.test('tokenize - additional', function (assert) {
        var tokenizer, terms;

        QUnit.expect(20);

        tokenizer = calculatorTokenizerFactory({
            symbols: {
                DOLLAR: '$'
            },
            keywords: {
                FOO: 'foo'
            }
        });
        terms = tokenizer.tokenize(' 3+4 *$foo + sinh 1');

        assert.ok(_.isArray(terms), 'Got a lis of terms');
        assert.equal(terms.length, 9, 'The expression has been tokenized in 9 terms');
        assert.equal(terms[0].type, 'NUM3', 'The expected term is found at position 0');
        assert.equal(terms[0].offset, 1, 'The expected term is found at offset 1');
        assert.equal(terms[1].type, 'ADD', 'The expected term is found at position 1');
        assert.equal(terms[1].offset, 2, 'The expected term is found at offset 3');
        assert.equal(terms[2].type, 'NUM4', 'The expected term is found at position 2');
        assert.equal(terms[2].offset, 3, 'The expected term is found at offset 3');
        assert.equal(terms[3].type, 'MUL', 'The expected term is found at position 3');
        assert.equal(terms[3].offset, 5, 'The expected term is found at offset 5');
        assert.equal(terms[4].type, 'DOLLAR', 'The expected term is found at position 4');
        assert.equal(terms[4].offset, 6, 'The expected term is found at offset 6');
        assert.equal(terms[5].type, 'FOO', 'The expected term is found at position 5');
        assert.equal(terms[5].offset, 7, 'The expected term is found at offset 7');
        assert.equal(terms[6].type, 'ADD', 'The expected term is found at position 6');
        assert.equal(terms[6].offset, 11, 'The expected term is found at offset 11');
        assert.equal(terms[7].type, 'SINH', 'The expected term is found at position 7');
        assert.equal(terms[7].offset, 13, 'The expected term is found at offset 13');
        assert.equal(terms[8].type, 'NUM1', 'The expected term is found at position 8');
        assert.equal(terms[8].offset, 18, 'The expected term is found at offset 18');
    });

    QUnit.test('iterator - success', function (assert) {
        var tokenizer, next, term;

        QUnit.expect(26);

        tokenizer = calculatorTokenizerFactory();
        next = tokenizer.iterator('(.1 + .2) * 10^8');
        assert.ok(_.isFunction(next), 'Got a function');

        term = next();
        assert.equal(term.type, 'LPAR', 'The expected term is found at position 0');
        assert.equal(term.offset, 0, 'The expected term is found at offset 0');
        term = next();
        assert.equal(term.type, 'DOT', 'The expected term is found at position 1');
        assert.equal(term.offset, 1, 'The expected term is found at offset 1');
        term = next();
        assert.equal(term.type, 'NUM1', 'The expected term is found at position 2');
        assert.equal(term.offset, 2, 'The expected term is found at offset 2');
        term = next();
        assert.equal(term.type, 'ADD', 'The expected term is found at position 3');
        assert.equal(term.offset, 4, 'The expected term is found at offset 4');
        term = next();
        assert.equal(term.type, 'DOT', 'The expected term is found at position 4');
        assert.equal(term.offset, 6, 'The expected term is found at offset 6');
        term = next();
        assert.equal(term.type, 'NUM2', 'The expected term is found at position 5');
        assert.equal(term.offset, 7, 'The expected term is found at offset 7');
        term = next();
        assert.equal(term.type, 'RPAR', 'The expected term is found at position 6');
        assert.equal(term.offset, 8, 'The expected term is found at offset 8');
        term = next();
        assert.equal(term.type, 'MUL', 'The expected term is found at position 7');
        assert.equal(term.offset, 10, 'The expected term is found at offset 10');
        term = next();
        assert.equal(term.type, 'NUM1', 'The expected term is found at position 8');
        assert.equal(term.offset, 12, 'The expected term is found at offset 12');
        term = next();
        assert.equal(term.type, 'NUM0', 'The expected term is found at position 9');
        assert.equal(term.offset, 13, 'The expected term is found at offset 13');
        term = next();
        assert.equal(term.type, 'POW', 'The expected term is found at position 10');
        assert.equal(term.offset, 14, 'The expected term is found at offset 14');
        term = next();
        assert.equal(term.type, 'NUM8', 'The expected term is found at position 11');
        assert.equal(term.offset, 15, 'The expected term is found at offset 15');
        term = next();
        assert.equal(typeof term, 'undefined', 'The iterator has completed the expression');
    });

    QUnit.test('iterator - error', function (assert) {
        var tokenizer, next, term;

        QUnit.expect(12);

        tokenizer = calculatorTokenizerFactory();
        next = tokenizer.iterator(' 3+4 *$foo + sinh 1');
        assert.ok(_.isFunction(next), 'Got a function');

        term = next();
        assert.equal(term.type, 'NUM3', 'The expected term is found at position 0');
        assert.equal(term.offset, 1, 'The expected term is found at offset 1');
        term = next();
        assert.equal(term.type, 'ADD', 'The expected term is found at position 1');
        assert.equal(term.offset, 2, 'The expected term is found at offset 3');
        term = next();
        assert.equal(term.type, 'NUM4', 'The expected term is found at position 2');
        assert.equal(term.offset, 3, 'The expected term is found at offset 3');
        term = next();
        assert.equal(term.type, 'MUL', 'The expected term is found at position 3');
        assert.equal(term.offset, 5, 'The expected term is found at offset 5');
        term = next();
        assert.equal(term.type, 'syntaxError', 'The expected error has been found');
        assert.equal(term.offset, 6, 'The expected error has been found at offset 6');
        term = next();
        assert.equal(typeof term, 'undefined', 'The iterator has completed the expression');
    });

    QUnit.test('iterator - additional', function (assert) {
        var tokenizer, next, term;

        QUnit.expect(20);

        tokenizer = calculatorTokenizerFactory({
            symbols: {
                DOLLAR: '$'
            },
            keywords: {
                FOO: 'foo'
            }
        });
        next = tokenizer.iterator(' 3+4 *$foo + sinh 1');
        assert.ok(_.isFunction(next), 'Got a function');

        term = next();
        assert.equal(term.type, 'NUM3', 'The expected term is found at position 0');
        assert.equal(term.offset, 1, 'The expected term is found at offset 1');
        term = next();
        assert.equal(term.type, 'ADD', 'The expected term is found at position 1');
        assert.equal(term.offset, 2, 'The expected term is found at offset 3');
        term = next();
        assert.equal(term.type, 'NUM4', 'The expected term is found at position 2');
        assert.equal(term.offset, 3, 'The expected term is found at offset 3');
        term = next();
        assert.equal(term.type, 'MUL', 'The expected term is found at position 3');
        assert.equal(term.offset, 5, 'The expected term is found at offset 5');
        term = next();
        assert.equal(term.type, 'DOLLAR', 'The expected term is found at position 4');
        assert.equal(term.offset, 6, 'The expected term is found at offset 6');
        term = next();
        assert.equal(term.type, 'FOO', 'The expected term is found at position 5');
        assert.equal(term.offset, 7, 'The expected term is found at offset 7');
        term = next();
        assert.equal(term.type, 'ADD', 'The expected term is found at position 6');
        assert.equal(term.offset, 11, 'The expected term is found at offset 11');
        term = next();
        assert.equal(term.type, 'SINH', 'The expected term is found at position 7');
        assert.equal(term.offset, 13, 'The expected term is found at offset 13');
        term = next();
        assert.equal(term.type, 'NUM1', 'The expected term is found at position 8');
        assert.equal(term.offset, 18, 'The expected term is found at offset 18');
        term = next();
        assert.equal(typeof term, 'undefined', 'The iterator has completed the expression');
    });

});
