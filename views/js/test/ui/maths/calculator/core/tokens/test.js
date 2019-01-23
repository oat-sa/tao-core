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
    'ui/maths/calculator/core/tokens',
    'ui/maths/calculator/core/tokenizer',
    'ui/maths/calculator/core/terms'
], function (_, calculatorTokensHelper, calculatorTokenizerFactory, registeredTerms) {
    'use strict';

    QUnit.module('Factory');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);
        assert.equal(typeof calculatorTokensHelper, 'object', "The module exposes an object");
    });

    QUnit.cases([
        {title: 'getType'},
        {title: 'isDigit'},
        {title: 'isOperator'},
        {title: 'isAggregator'},
        {title: 'isError'},
        {title: 'isConstant'},
        {title: 'isVariable'},
        {title: 'isFunction'},
        {title: 'isIdentifier'},
        {title: 'isSeparator'},
        {title: 'isModifier'}
    ]).test('API ', function (data, assert) {
        QUnit.expect(1);
        assert.equal(typeof calculatorTokensHelper[data.title], 'function', 'The helper exposes a "' + data.title + '" function');
    });

    QUnit.module('API');

    QUnit.test('getType', function (assert) {
        var tokenizer = calculatorTokenizerFactory();
        QUnit.expect(_.size(registeredTerms) + 5);

        _.forEach(registeredTerms, function(term) {
            assert.equal(calculatorTokensHelper.getType(tokenizer.tokenize(term.value)[0]), term.type, 'Should tell ' + term.value + ' has type ' + term.type);
        });

        assert.equal(calculatorTokensHelper.getType(tokenizer.tokenize('')[0]), null, 'Empty token should be a null');
        assert.equal(calculatorTokensHelper.getType(tokenizer.tokenize('foo')[0]), 'term', 'Generic identifier should be a term');
        assert.equal(calculatorTokensHelper.getType({type: 'foo'}), 'foo', 'Specific type: foo');
        assert.equal(calculatorTokensHelper.getType(), null, 'No token');
        assert.equal(calculatorTokensHelper.getType({}), null, 'Empty token');
    });

    QUnit.test('isDigit', function (assert) {
        QUnit.expect(20);

        assert.equal(calculatorTokensHelper.isDigit(registeredTerms.NUM0.type), true, 'Should be a digit');
        assert.equal(calculatorTokensHelper.isDigit(registeredTerms.SUB.type), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit(registeredTerms.LPAR.type), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit(registeredTerms.NAN.type), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit(registeredTerms.PI.type), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit(registeredTerms.TAN.type), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit('term'), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit('variable'), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit('foo'), false, 'Should not be a digit');


        assert.equal(calculatorTokensHelper.isDigit(registeredTerms.NUM0), true, 'Should be a digit');
        assert.equal(calculatorTokensHelper.isDigit(registeredTerms.SUB), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit(registeredTerms.LPAR), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit(registeredTerms.NAN), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit(registeredTerms.PI), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit(registeredTerms.TAN), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit({type: 'term'}), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit({type: 'variable'}), false, 'Should not be a digit');
        assert.equal(calculatorTokensHelper.isDigit({type: 'foo'}), false, 'Should not be a digit');

        assert.equal(calculatorTokensHelper.isDigit({type: 'NUM1'}), true, 'Should be a digit');
        assert.equal(calculatorTokensHelper.isDigit({type: 'FOO'}), false, 'Should not be a digit');
    });

    QUnit.test('isOperator', function (assert) {
        QUnit.expect(20);

        assert.equal(calculatorTokensHelper.isOperator(registeredTerms.NUM0.type), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator(registeredTerms.SUB.type), true, 'Should be an operator');
        assert.equal(calculatorTokensHelper.isOperator(registeredTerms.LPAR.type), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator(registeredTerms.NAN.type), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator(registeredTerms.PI.type), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator(registeredTerms.TAN.type), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator('term'), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator('variable'), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator('foo'), false, 'Should not be an operator');

        assert.equal(calculatorTokensHelper.isOperator(registeredTerms.NUM0), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator(registeredTerms.SUB), true, 'Should be an operator');
        assert.equal(calculatorTokensHelper.isOperator(registeredTerms.LPAR), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator(registeredTerms.NAN), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator(registeredTerms.PI), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator(registeredTerms.TAN), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator({type: 'term'}), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator({type: 'variable'}), false, 'Should not be an operator');
        assert.equal(calculatorTokensHelper.isOperator({type: 'foo'}), false, 'Should not be an operator');

        assert.equal(calculatorTokensHelper.isOperator({type: 'ADD'}), true, 'Should be an operator');
        assert.equal(calculatorTokensHelper.isOperator({type: 'FOO'}), false, 'Should not be an operator');
    });

    QUnit.test('isAggregator', function (assert) {
        QUnit.expect(20);

        assert.equal(calculatorTokensHelper.isAggregator(registeredTerms.NUM0.type), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator(registeredTerms.SUB.type), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator(registeredTerms.LPAR.type), true, 'Should be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator(registeredTerms.NAN.type), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator(registeredTerms.PI.type), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator(registeredTerms.TAN.type), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator('term'), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator('variable'), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator('foo'), false, 'Should not be an aggregator');

        assert.equal(calculatorTokensHelper.isAggregator(registeredTerms.NUM0), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator(registeredTerms.SUB), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator(registeredTerms.LPAR), true, 'Should be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator(registeredTerms.NAN), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator(registeredTerms.PI), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator(registeredTerms.TAN), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator({type: 'term'}), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator({type: 'variable'}), false, 'Should not be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator({type: 'foo'}), false, 'Should not be an aggregator');

        assert.equal(calculatorTokensHelper.isAggregator({type: 'RPAR'}), true, 'Should be an aggregator');
        assert.equal(calculatorTokensHelper.isAggregator({type: 'FOO'}), false, 'Should not be an aggregator');
    });

    QUnit.test('isError', function (assert) {
        QUnit.expect(20);

        assert.equal(calculatorTokensHelper.isError(registeredTerms.NUM0.type), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError(registeredTerms.SUB.type), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError(registeredTerms.LPAR.type), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError(registeredTerms.NAN.type), true, 'Should be an error');
        assert.equal(calculatorTokensHelper.isError(registeredTerms.PI.type), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError(registeredTerms.TAN.type), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError('term'), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError('variable'), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError('foo'), false, 'Should not be an error');

        assert.equal(calculatorTokensHelper.isError(registeredTerms.NUM0), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError(registeredTerms.SUB), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError(registeredTerms.LPAR), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError(registeredTerms.NAN), true, 'Should be an error');
        assert.equal(calculatorTokensHelper.isError(registeredTerms.PI), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError(registeredTerms.TAN), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError({type: 'term'}), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError({type: 'variable'}), false, 'Should not be an error');
        assert.equal(calculatorTokensHelper.isError({type: 'foo'}), false, 'Should not be an error');

        assert.equal(calculatorTokensHelper.isError({type: 'NAN'}), true, 'Should be an error');
        assert.equal(calculatorTokensHelper.isError({type: 'FOO'}), false, 'Should not be an error');
    });

    QUnit.test('isConstant', function (assert) {
        QUnit.expect(20);

        assert.equal(calculatorTokensHelper.isConstant(registeredTerms.NUM0.type), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant(registeredTerms.SUB.type), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant(registeredTerms.LPAR.type), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant(registeredTerms.NAN.type), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant(registeredTerms.PI.type), true, 'Should be a constant');
        assert.equal(calculatorTokensHelper.isConstant(registeredTerms.TAN.type), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant('term'), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant('variable'), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant('foo'), false, 'Should not be a constant');

        assert.equal(calculatorTokensHelper.isConstant(registeredTerms.NUM0), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant(registeredTerms.SUB), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant(registeredTerms.LPAR), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant(registeredTerms.NAN), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant(registeredTerms.PI), true, 'Should be a constant');
        assert.equal(calculatorTokensHelper.isConstant(registeredTerms.TAN), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant({type: 'term'}), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant({type: 'variable'}), false, 'Should not be a constant');
        assert.equal(calculatorTokensHelper.isConstant({type: 'foo'}), false, 'Should not be a constant');

        assert.equal(calculatorTokensHelper.isConstant({type: 'PI'}), true, 'Should be a constant');
        assert.equal(calculatorTokensHelper.isConstant({type: 'FOO'}), false, 'Should not be a constant');
    });

    QUnit.test('isVariable', function (assert) {
        QUnit.expect(20);

        assert.equal(calculatorTokensHelper.isVariable(registeredTerms.NUM0.type), false, 'Should not be a variable');
        assert.equal(calculatorTokensHelper.isVariable(registeredTerms.SUB.type), false, 'Should not be a variable');
        assert.equal(calculatorTokensHelper.isVariable(registeredTerms.LPAR.type), false, 'Should not be a variable');
        assert.equal(calculatorTokensHelper.isVariable(registeredTerms.NAN.type), false, 'Should not be a variable');
        assert.equal(calculatorTokensHelper.isVariable(registeredTerms.PI.type), false, 'Should not be a variable');
        assert.equal(calculatorTokensHelper.isVariable(registeredTerms.TAN.type), false, 'Should not be a variable');
        assert.equal(calculatorTokensHelper.isVariable('term'), true, 'Should be a variable');
        assert.equal(calculatorTokensHelper.isVariable('variable'), true, 'Should be a variable');
        assert.equal(calculatorTokensHelper.isVariable('foo'), false, 'Should not be a variable');

        assert.equal(calculatorTokensHelper.isVariable(registeredTerms.NUM0), false, 'Should not be a variable');
        assert.equal(calculatorTokensHelper.isVariable(registeredTerms.SUB), false, 'Should not be a variable');
        assert.equal(calculatorTokensHelper.isVariable(registeredTerms.LPAR), false, 'Should not be a variable');
        assert.equal(calculatorTokensHelper.isVariable(registeredTerms.NAN), false, 'Should not be a variable');
        assert.equal(calculatorTokensHelper.isVariable(registeredTerms.PI), false, 'Should not be a variable');
        assert.equal(calculatorTokensHelper.isVariable(registeredTerms.TAN), false, 'Should not be a variable');
        assert.equal(calculatorTokensHelper.isVariable({type: 'term'}), true, 'Should be a variable');
        assert.equal(calculatorTokensHelper.isVariable({type: 'variable'}), true, 'Should be a variable');
        assert.equal(calculatorTokensHelper.isVariable({type: 'foo'}), false, 'Should not be a variable');

        assert.equal(calculatorTokensHelper.isVariable({type: 'X'}), false, 'Should not be a variable');
        assert.equal(calculatorTokensHelper.isVariable({type: 'FOO'}), false, 'Should not be a variable');
    });

    QUnit.test('isFunction', function (assert) {
        QUnit.expect(20);

        assert.equal(calculatorTokensHelper.isFunction(registeredTerms.NUM0.type), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction(registeredTerms.SUB.type), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction(registeredTerms.LPAR.type), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction(registeredTerms.NAN.type), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction(registeredTerms.PI.type), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction(registeredTerms.TAN.type), true, 'Should be a function');
        assert.equal(calculatorTokensHelper.isFunction('term'), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction('variable'), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction('foo'), false, 'Should not be a function');

        assert.equal(calculatorTokensHelper.isFunction(registeredTerms.NUM0), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction(registeredTerms.SUB), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction(registeredTerms.LPAR), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction(registeredTerms.NAN), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction(registeredTerms.PI), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction(registeredTerms.TAN), true, 'Should be a function');
        assert.equal(calculatorTokensHelper.isFunction({type: 'term'}), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction({type: 'variable'}), false, 'Should not be a function');
        assert.equal(calculatorTokensHelper.isFunction({type: 'foo'}), false, 'Should not be a function');

        assert.equal(calculatorTokensHelper.isFunction({type: 'TAN'}), true, 'Should be a function');
        assert.equal(calculatorTokensHelper.isFunction({type: 'FOO'}), false, 'Should not be a function');
    });

    QUnit.test('isIdentifier', function (assert) {
        QUnit.expect(20);

        assert.equal(calculatorTokensHelper.isIdentifier(registeredTerms.NUM0.type), false, 'Should not be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier(registeredTerms.SUB.type), false, 'Should not be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier(registeredTerms.LPAR.type), false, 'Should not be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier(registeredTerms.NAN.type), true, 'Should be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier(registeredTerms.PI.type), true, 'Should be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier(registeredTerms.TAN.type), true, 'Should be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier('term'), true, 'Should be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier('variable'), true, 'Should be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier('foo'), false, 'Should not be an identifier');

        assert.equal(calculatorTokensHelper.isIdentifier(registeredTerms.NUM0), false, 'Should not be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier(registeredTerms.SUB), false, 'Should not be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier(registeredTerms.LPAR), false, 'Should not be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier(registeredTerms.NAN), true, 'Should be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier(registeredTerms.PI), true, 'Should be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier(registeredTerms.TAN), true, 'Should be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier({type: 'term'}), true, 'Should be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier({type: 'variable'}), true, 'Should be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier({type: 'foo'}), false, 'Should not be an identifier');

        assert.equal(calculatorTokensHelper.isIdentifier({type: 'COS'}), true, 'Should be an identifier');
        assert.equal(calculatorTokensHelper.isIdentifier({type: 'POW'}), false, 'Should not be an identifier');
    });

    QUnit.test('isSeparator', function (assert) {
        QUnit.expect(20);

        assert.equal(calculatorTokensHelper.isSeparator(registeredTerms.NUM0.type), false, 'Should not be a separator');
        assert.equal(calculatorTokensHelper.isSeparator(registeredTerms.SUB.type), true, 'Should be a separator');
        assert.equal(calculatorTokensHelper.isSeparator(registeredTerms.LPAR.type), true, 'Should be a separator');
        assert.equal(calculatorTokensHelper.isSeparator(registeredTerms.NAN.type), false, 'Should not be a separator');
        assert.equal(calculatorTokensHelper.isSeparator(registeredTerms.PI.type), false, 'Should not be a separator');
        assert.equal(calculatorTokensHelper.isSeparator(registeredTerms.TAN.type), false, 'Should not be a separator');
        assert.equal(calculatorTokensHelper.isSeparator('term'), false, 'Should not be a separator');
        assert.equal(calculatorTokensHelper.isSeparator('variable'), false, 'Should not be a separator');
        assert.equal(calculatorTokensHelper.isSeparator('foo'), false, 'Should not be a separator');

        assert.equal(calculatorTokensHelper.isSeparator(registeredTerms.NUM0), false, 'Should not be a separator');
        assert.equal(calculatorTokensHelper.isSeparator(registeredTerms.SUB), true, 'Should be a separator');
        assert.equal(calculatorTokensHelper.isSeparator(registeredTerms.LPAR), true, 'Should be a separator');
        assert.equal(calculatorTokensHelper.isSeparator(registeredTerms.NAN), false, 'Should not be a separator');
        assert.equal(calculatorTokensHelper.isSeparator(registeredTerms.PI), false, 'Should not be a separator');
        assert.equal(calculatorTokensHelper.isSeparator(registeredTerms.TAN), false, 'Should not be a separator');
        assert.equal(calculatorTokensHelper.isSeparator({type: 'term'}), false, 'Should not be a separator');
        assert.equal(calculatorTokensHelper.isSeparator({type: 'variable'}), false, 'Should not be a separator');
        assert.equal(calculatorTokensHelper.isSeparator({type: 'foo'}), false, 'Should not be a separator');

        assert.equal(calculatorTokensHelper.isSeparator({type: 'POW'}), true, 'Should be a separator');
        assert.equal(calculatorTokensHelper.isSeparator({type: 'FOO'}), false, 'Should not be a separator');
    });

    QUnit.test('isModifier', function (assert) {
        QUnit.expect(20);

        assert.equal(calculatorTokensHelper.isModifier(registeredTerms.NUM0.type), false, 'Should not be a modifier');
        assert.equal(calculatorTokensHelper.isModifier(registeredTerms.SUB.type), true, 'Should be a modifier');
        assert.equal(calculatorTokensHelper.isModifier(registeredTerms.LPAR.type), false, 'Should not be a modifier');
        assert.equal(calculatorTokensHelper.isModifier(registeredTerms.NAN.type), false, 'Should not be a modifier');
        assert.equal(calculatorTokensHelper.isModifier(registeredTerms.PI.type), false, 'Should not be a modifier');
        assert.equal(calculatorTokensHelper.isModifier(registeredTerms.TAN.type), true, 'Should be a modifier');
        assert.equal(calculatorTokensHelper.isModifier('term'), false, 'Should not be a modifier');
        assert.equal(calculatorTokensHelper.isModifier('variable'), false, 'Should not be a modifier');
        assert.equal(calculatorTokensHelper.isModifier('foo'), false, 'Should not be a modifier');

        assert.equal(calculatorTokensHelper.isModifier(registeredTerms.NUM0), false, 'Should not be a modifier');
        assert.equal(calculatorTokensHelper.isModifier(registeredTerms.SUB), true, 'Should be a modifier');
        assert.equal(calculatorTokensHelper.isModifier(registeredTerms.LPAR), false, 'Should not be a modifier');
        assert.equal(calculatorTokensHelper.isModifier(registeredTerms.NAN), false, 'Should not be a modifier');
        assert.equal(calculatorTokensHelper.isModifier(registeredTerms.PI), false, 'Should not be a modifier');
        assert.equal(calculatorTokensHelper.isModifier(registeredTerms.TAN), true, 'Should be a modifier');
        assert.equal(calculatorTokensHelper.isModifier({type: 'term'}), false, 'Should not be a modifier');
        assert.equal(calculatorTokensHelper.isModifier({type: 'variable'}), false, 'Should not be a modifier');
        assert.equal(calculatorTokensHelper.isModifier({type: 'foo'}), false, 'Should not be a modifier');

        assert.equal(calculatorTokensHelper.isModifier({type: 'POW'}), true, 'Should be a modifier');
        assert.equal(calculatorTokensHelper.isModifier({type: 'FOO'}), false, 'Should not be a modifier');
    });

});
