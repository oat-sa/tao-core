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
    'jquery',
    'lodash',
    'ui/maths/calculator/core/tokens',
    'ui/maths/calculator/core/tokenizer',
    'ui/maths/calculator/core/terms'
], function ($, _, tokensHelper, calculatorTokenizerFactory, registeredTerms) {
    'use strict';

    QUnit.module('Factory');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);
        assert.equal(typeof tokensHelper, 'object', "The module exposes an object");
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
        {title: 'isModifier'},
        {title: 'stringValue'},
        {title: 'containsError'},
        {title: 'renderLastResult'},
        {title: 'render'}
    ]).test('API ', function (data, assert) {
        QUnit.expect(1);
        assert.equal(typeof tokensHelper[data.title], 'function', 'The helper exposes a "' + data.title + '" function');
    });

    QUnit.module('API');

    QUnit.test('getType', function (assert) {
        var tokenizer = calculatorTokenizerFactory();
        QUnit.expect(_.size(registeredTerms) + 6);

        _.forEach(registeredTerms, function (term) {
            assert.equal(tokensHelper.getType(tokenizer.tokenize(term.value)[0]), term.type, 'Should tell ' + term.value + ' has type ' + term.type);
        });

        assert.equal(tokensHelper.getType(tokenizer.tokenize('')[0]), null, 'Empty token should be a null');
        assert.equal(tokensHelper.getType(tokenizer.tokenize('foo')[0]), 'term', 'Generic identifier should be a term');
        assert.equal(tokensHelper.getType({type: 'foo'}), 'foo', 'Specific type: foo');
        assert.equal(tokensHelper.getType('foo'), 'foo', 'String type: foo');
        assert.equal(tokensHelper.getType(), null, 'No token');
        assert.equal(tokensHelper.getType({}), null, 'Empty token');
    });

    QUnit.test('isDigit', function (assert) {
        QUnit.expect(20);

        assert.equal(tokensHelper.isDigit(registeredTerms.NUM0.type), true, 'Should be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.SUB.type), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.LPAR.type), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.NAN.type), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.PI.type), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.TAN.type), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit('term'), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit('variable'), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit('foo'), false, 'Should not be a digit');


        assert.equal(tokensHelper.isDigit(registeredTerms.NUM0), true, 'Should be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.SUB), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.LPAR), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.NAN), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.PI), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.TAN), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit({type: 'term'}), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit({type: 'variable'}), false, 'Should not be a digit');
        assert.equal(tokensHelper.isDigit({type: 'foo'}), false, 'Should not be a digit');

        assert.equal(tokensHelper.isDigit({type: 'NUM1'}), true, 'Should be a digit');
        assert.equal(tokensHelper.isDigit({type: 'FOO'}), false, 'Should not be a digit');
    });

    QUnit.test('isOperator', function (assert) {
        QUnit.expect(20);

        assert.equal(tokensHelper.isOperator(registeredTerms.NUM0.type), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.SUB.type), true, 'Should be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.LPAR.type), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.NAN.type), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.PI.type), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.TAN.type), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator('term'), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator('variable'), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator('foo'), false, 'Should not be an operator');

        assert.equal(tokensHelper.isOperator(registeredTerms.NUM0), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.SUB), true, 'Should be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.LPAR), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.NAN), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.PI), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.TAN), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator({type: 'term'}), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator({type: 'variable'}), false, 'Should not be an operator');
        assert.equal(tokensHelper.isOperator({type: 'foo'}), false, 'Should not be an operator');

        assert.equal(tokensHelper.isOperator({type: 'ADD'}), true, 'Should be an operator');
        assert.equal(tokensHelper.isOperator({type: 'FOO'}), false, 'Should not be an operator');
    });

    QUnit.test('isAggregator', function (assert) {
        QUnit.expect(20);

        assert.equal(tokensHelper.isAggregator(registeredTerms.NUM0.type), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.SUB.type), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.LPAR.type), true, 'Should be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.NAN.type), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.PI.type), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.TAN.type), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator('term'), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator('variable'), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator('foo'), false, 'Should not be an aggregator');

        assert.equal(tokensHelper.isAggregator(registeredTerms.NUM0), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.SUB), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.LPAR), true, 'Should be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.NAN), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.PI), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.TAN), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator({type: 'term'}), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator({type: 'variable'}), false, 'Should not be an aggregator');
        assert.equal(tokensHelper.isAggregator({type: 'foo'}), false, 'Should not be an aggregator');

        assert.equal(tokensHelper.isAggregator({type: 'RPAR'}), true, 'Should be an aggregator');
        assert.equal(tokensHelper.isAggregator({type: 'FOO'}), false, 'Should not be an aggregator');
    });

    QUnit.test('isError', function (assert) {
        QUnit.expect(20);

        assert.equal(tokensHelper.isError(registeredTerms.NUM0.type), false, 'Should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.SUB.type), false, 'Should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.LPAR.type), false, 'Should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.NAN.type), true, 'Should be an error');
        assert.equal(tokensHelper.isError(registeredTerms.PI.type), false, 'Should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.TAN.type), false, 'Should not be an error');
        assert.equal(tokensHelper.isError('term'), false, 'Should not be an error');
        assert.equal(tokensHelper.isError('variable'), false, 'Should not be an error');
        assert.equal(tokensHelper.isError('foo'), false, 'Should not be an error');

        assert.equal(tokensHelper.isError(registeredTerms.NUM0), false, 'Should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.SUB), false, 'Should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.LPAR), false, 'Should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.NAN), true, 'Should be an error');
        assert.equal(tokensHelper.isError(registeredTerms.PI), false, 'Should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.TAN), false, 'Should not be an error');
        assert.equal(tokensHelper.isError({type: 'term'}), false, 'Should not be an error');
        assert.equal(tokensHelper.isError({type: 'variable'}), false, 'Should not be an error');
        assert.equal(tokensHelper.isError({type: 'foo'}), false, 'Should not be an error');

        assert.equal(tokensHelper.isError({type: 'NAN'}), true, 'Should be an error');
        assert.equal(tokensHelper.isError({type: 'FOO'}), false, 'Should not be an error');
    });

    QUnit.test('isConstant', function (assert) {
        QUnit.expect(20);

        assert.equal(tokensHelper.isConstant(registeredTerms.NUM0.type), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.SUB.type), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.LPAR.type), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.NAN.type), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.PI.type), true, 'Should be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.TAN.type), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant('term'), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant('variable'), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant('foo'), false, 'Should not be a constant');

        assert.equal(tokensHelper.isConstant(registeredTerms.NUM0), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.SUB), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.LPAR), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.NAN), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.PI), true, 'Should be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.TAN), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant({type: 'term'}), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant({type: 'variable'}), false, 'Should not be a constant');
        assert.equal(tokensHelper.isConstant({type: 'foo'}), false, 'Should not be a constant');

        assert.equal(tokensHelper.isConstant({type: 'PI'}), true, 'Should be a constant');
        assert.equal(tokensHelper.isConstant({type: 'FOO'}), false, 'Should not be a constant');
    });

    QUnit.test('isVariable', function (assert) {
        QUnit.expect(20);

        assert.equal(tokensHelper.isVariable(registeredTerms.NUM0.type), false, 'Should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.SUB.type), false, 'Should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.LPAR.type), false, 'Should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.NAN.type), false, 'Should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.PI.type), false, 'Should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.TAN.type), false, 'Should not be a variable');
        assert.equal(tokensHelper.isVariable('term'), true, 'Should be a variable');
        assert.equal(tokensHelper.isVariable('variable'), true, 'Should be a variable');
        assert.equal(tokensHelper.isVariable('foo'), false, 'Should not be a variable');

        assert.equal(tokensHelper.isVariable(registeredTerms.NUM0), false, 'Should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.SUB), false, 'Should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.LPAR), false, 'Should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.NAN), false, 'Should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.PI), false, 'Should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.TAN), false, 'Should not be a variable');
        assert.equal(tokensHelper.isVariable({type: 'term'}), true, 'Should be a variable');
        assert.equal(tokensHelper.isVariable({type: 'variable'}), true, 'Should be a variable');
        assert.equal(tokensHelper.isVariable({type: 'foo'}), false, 'Should not be a variable');

        assert.equal(tokensHelper.isVariable({type: 'X'}), false, 'Should not be a variable');
        assert.equal(tokensHelper.isVariable({type: 'FOO'}), false, 'Should not be a variable');
    });

    QUnit.test('isFunction', function (assert) {
        QUnit.expect(20);

        assert.equal(tokensHelper.isFunction(registeredTerms.NUM0.type), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.SUB.type), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.LPAR.type), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.NAN.type), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.PI.type), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.TAN.type), true, 'Should be a function');
        assert.equal(tokensHelper.isFunction('term'), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction('variable'), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction('foo'), false, 'Should not be a function');

        assert.equal(tokensHelper.isFunction(registeredTerms.NUM0), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.SUB), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.LPAR), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.NAN), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.PI), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.TAN), true, 'Should be a function');
        assert.equal(tokensHelper.isFunction({type: 'term'}), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction({type: 'variable'}), false, 'Should not be a function');
        assert.equal(tokensHelper.isFunction({type: 'foo'}), false, 'Should not be a function');

        assert.equal(tokensHelper.isFunction({type: 'TAN'}), true, 'Should be a function');
        assert.equal(tokensHelper.isFunction({type: 'FOO'}), false, 'Should not be a function');
    });

    QUnit.test('isIdentifier', function (assert) {
        QUnit.expect(20);

        assert.equal(tokensHelper.isIdentifier(registeredTerms.NUM0.type), false, 'Should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.SUB.type), false, 'Should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.LPAR.type), false, 'Should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.NAN.type), true, 'Should be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.PI.type), true, 'Should be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.TAN.type), true, 'Should be an identifier');
        assert.equal(tokensHelper.isIdentifier('term'), true, 'Should be an identifier');
        assert.equal(tokensHelper.isIdentifier('variable'), true, 'Should be an identifier');
        assert.equal(tokensHelper.isIdentifier('foo'), false, 'Should not be an identifier');

        assert.equal(tokensHelper.isIdentifier(registeredTerms.NUM0), false, 'Should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.SUB), false, 'Should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.LPAR), false, 'Should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.NAN), true, 'Should be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.PI), true, 'Should be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.TAN), true, 'Should be an identifier');
        assert.equal(tokensHelper.isIdentifier({type: 'term'}), true, 'Should be an identifier');
        assert.equal(tokensHelper.isIdentifier({type: 'variable'}), true, 'Should be an identifier');
        assert.equal(tokensHelper.isIdentifier({type: 'foo'}), false, 'Should not be an identifier');

        assert.equal(tokensHelper.isIdentifier({type: 'COS'}), true, 'Should be an identifier');
        assert.equal(tokensHelper.isIdentifier({type: 'POW'}), false, 'Should not be an identifier');
    });

    QUnit.test('isSeparator', function (assert) {
        QUnit.expect(20);

        assert.equal(tokensHelper.isSeparator(registeredTerms.NUM0.type), false, 'Should not be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.SUB.type), true, 'Should be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.LPAR.type), true, 'Should be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.NAN.type), false, 'Should not be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.PI.type), false, 'Should not be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.TAN.type), false, 'Should not be a separator');
        assert.equal(tokensHelper.isSeparator('term'), false, 'Should not be a separator');
        assert.equal(tokensHelper.isSeparator('variable'), false, 'Should not be a separator');
        assert.equal(tokensHelper.isSeparator('foo'), false, 'Should not be a separator');

        assert.equal(tokensHelper.isSeparator(registeredTerms.NUM0), false, 'Should not be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.SUB), true, 'Should be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.LPAR), true, 'Should be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.NAN), false, 'Should not be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.PI), false, 'Should not be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.TAN), false, 'Should not be a separator');
        assert.equal(tokensHelper.isSeparator({type: 'term'}), false, 'Should not be a separator');
        assert.equal(tokensHelper.isSeparator({type: 'variable'}), false, 'Should not be a separator');
        assert.equal(tokensHelper.isSeparator({type: 'foo'}), false, 'Should not be a separator');

        assert.equal(tokensHelper.isSeparator({type: 'POW'}), true, 'Should be a separator');
        assert.equal(tokensHelper.isSeparator({type: 'FOO'}), false, 'Should not be a separator');
    });

    QUnit.test('isModifier', function (assert) {
        QUnit.expect(20);

        assert.equal(tokensHelper.isModifier(registeredTerms.NUM0.type), false, 'Should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.SUB.type), true, 'Should be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.LPAR.type), false, 'Should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.NAN.type), false, 'Should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.PI.type), false, 'Should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.TAN.type), true, 'Should be a modifier');
        assert.equal(tokensHelper.isModifier('term'), false, 'Should not be a modifier');
        assert.equal(tokensHelper.isModifier('variable'), false, 'Should not be a modifier');
        assert.equal(tokensHelper.isModifier('foo'), false, 'Should not be a modifier');

        assert.equal(tokensHelper.isModifier(registeredTerms.NUM0), false, 'Should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.SUB), true, 'Should be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.LPAR), false, 'Should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.NAN), false, 'Should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.PI), false, 'Should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.TAN), true, 'Should be a modifier');
        assert.equal(tokensHelper.isModifier({type: 'term'}), false, 'Should not be a modifier');
        assert.equal(tokensHelper.isModifier({type: 'variable'}), false, 'Should not be a modifier');
        assert.equal(tokensHelper.isModifier({type: 'foo'}), false, 'Should not be a modifier');

        assert.equal(tokensHelper.isModifier({type: 'POW'}), true, 'Should be a modifier');
        assert.equal(tokensHelper.isModifier({type: 'FOO'}), false, 'Should not be a modifier');
    });

    QUnit.cases([{
        title: 'Normal expression',
        expression: '3*4',
        expected: '3*4'
    }, {
        title: 'Number expression',
        expression: 42,
        expected: '42'
    }, {
        title: 'Not a Number',
        expression: NaN,
        expected: 'NaN'
    }, {
        title: 'Object expression',
        expression: {
            value: 'cos PI * (40 + 2)'
        },
        expected: 'cos PI * (40 + 2)'
    }, {
        title: 'Void object expression',
        expression: {},
        expected: ''
    }, {
        title: 'Null expression',
        expression: null,
        expected: ''
    }, {
        title: 'No expression',
        expected: ''
    }])
        .test('stringValue', function (data, assert) {
            QUnit.expect(1);

            assert.equal(tokensHelper.stringValue(data.expression), data.expected, 'Should cast the value ' + data.expression);

        });

    QUnit.cases([{
        title: 'Normal expression',
        expression: '3*4',
        expected: '3*4'
    }, {
        title: 'Number expression',
        expression: 42,
        expected: '42'
    }, {
        title: 'Not a Number',
        expression: NaN,
        expected: 'NaN'
    }, {
        title: 'Object expression',
        expression: {
            value: 'cos PI * (40 + 2)'
        },
        expected: 'cos PI * (40 + 2)'
    }, {
        title: 'Void object expression',
        expression: {},
        expected: ''
    }, {
        title: 'Null expression',
        expression: null,
        expected: ''
    }, {
        title: 'No expression',
        expected: ''
    }, {
        title: 'Simple value',
        expression: 'ans',
        value: '42',
        expected: '42'
    }, {
        title: 'Simple value in expression',
        expression: '3*ans+1',
        value: '42',
        expected: '3*42+1'
    }, {
        title: 'Multiple values in expression',
        expression: '3*ans+1/ans+ans',
        value: '5',
        expected: '3*5+1/5+5'
    }, {
        title: 'Object value in expression',
        expression: '3*ans+1',
        value: {
            value: '42'
        },
        expected: '3*42+1'
    }, {
        title: 'NaN in expression',
        expression: '3*ans+1',
        value: {
            value: NaN
        },
        expected: '3*NaN+1'
    }, {
        title: 'No value',
        expression: 'ans',
        expected: ''
    }])
        .test('renderLastResult', function (data, assert) {
            QUnit.expect(1);

            assert.equal(tokensHelper.renderLastResult(data.expression, data.value), data.expected, 'Should render the last result variable from ' + data.expression + ' to ' + data.expected);

        });

    QUnit.test('containsError', function (assert) {
        QUnit.expect(12);

        assert.equal(tokensHelper.containsError('3*4'), false, 'Should not contain an error');
        assert.equal(tokensHelper.containsError('NaN'), true, 'Should contain an error');
        assert.equal(tokensHelper.containsError('Infinity'), true, 'Should contain an error');
        assert.equal(tokensHelper.containsError('+Infinity'), true, 'Should contain an error');
        assert.equal(tokensHelper.containsError('-Infinity'), true, 'Should contain an error');
        assert.equal(tokensHelper.containsError('2*NaN'), true, 'Should contain an error');
        assert.equal(tokensHelper.containsError('4-Infinity'), true, 'Should contain an error');
        assert.equal(tokensHelper.containsError(NaN), true, 'Should contain an error');
        assert.equal(tokensHelper.containsError(10), false, 'Should not contain an error');
        assert.equal(tokensHelper.containsError({value: NaN}), true, 'Should contain an error');
        assert.equal(tokensHelper.containsError({value: 'NaN'}), true, 'Should contain an error');
        assert.equal(tokensHelper.containsError({value: '0'}), false, 'Should not contain an error');
    });

    QUnit.cases([{
        title: 'Undefined list',
        expected: ''
    }, {
        title: 'Void list',
        tokens: [],
        variables: {},
        expected: ''
    }, {
        title: 'Simple number',
        tokens: [{
            type: 'NUM4',
            value: '4'
        }, {
            type: 'NUM2',
            value: '2'
        }],
        variables: {},
        expected: '' +
            '<span class="term term-digit" data-value="4" data-token="NUM4" data-type="digit">4</span>' +
            '<span class="term term-digit" data-value="2" data-token="NUM2" data-type="digit">2</span>'
    }, {
        title: 'Simple expression',
        tokens: [{
            type: 'NUM4',
            value: '4'
        }, {
            type: 'NUM0',
            value: '0'
        }, {
            type: 'ADD',
            value: '+'
        }, {
            type: 'NUM2',
            value: '2'
        }],
        variables: {},
        expected: '' +
            '<span class="term term-digit" data-value="4" data-token="NUM4" data-type="digit">4</span>' +
            '<span class="term term-digit" data-value="0" data-token="NUM0" data-type="digit">0</span>' +
            '<span class="term term-operator" data-value="+" data-token="ADD" data-type="operator">' + registeredTerms.ADD.label + '</span>' +
            '<span class="term term-digit" data-value="2" data-token="NUM2" data-type="digit">2</span>'
    }, {
        title: 'Negation',
        tokens: [{
            type: 'SUB',
            value: '-'
        }, {
            type: 'NUM4',
            value: '4'
        }, {
            type: 'NUM2',
            value: '2'
        }],
        variables: {},
        expected: '' +
            '<span class="term term-operator" data-value="-" data-token="NEG" data-type="operator">' + registeredTerms.NEG.label + '</span>' +
            '<span class="term term-digit" data-value="4" data-token="NUM4" data-type="digit">4</span>' +
            '<span class="term term-digit" data-value="2" data-token="NUM2" data-type="digit">2</span>'
    }, {
        title: 'Last result, no variable',
        tokens: [{
            type: 'ANS',
            value: 'ans'
        }],
        variables: {},
        expected: '' +
            '<span class="term term-variable" data-value="ans" data-token="ANS" data-type="variable">' + registeredTerms.ANS.label + '</span>'
    }, {
        title: 'Last result, positive value',
        tokens: [{
            type: 'ANS',
            value: 'ans'
        }],
        variables: {
            ans: '42'
        },
        expected: '' +
            '<span class="term term-variable" data-value="ans" data-token="ANS" data-type="variable">42</span>'
    }, {
        title: 'Last result, negative value',
        tokens: [{
            type: 'ANS',
            value: 'ans'
        }],
        variables: {
            ans: '-42'
        },
        expected: '' +
            '<span class="term term-variable" data-value="ans" data-token="ANS" data-type="variable">' + registeredTerms.NEG.label + '42</span>'
    }, {
        title: 'Expression with variables',
        tokens: [{
            type: 'NUM4',
            value: '4'
        }, {
            type: 'NUM0',
            value: '0'
        }, {
            type: 'ADD',
            value: '+'
        }, {
            type: 'NUM2',
            value: '2'
        }, {
            type: 'SUB',
            value: '-'
        }, {
            type: 'term',
            value: 'x'
        }, {
            type: 'MUL',
            value: '*'
        }, {
            type: 'ANS',
            value: 'ans'
        }, {
            type: 'MUL',
            value: '*'
        }, {
            type: 'term',
            value: 'y'
        }],
        variables: {
            ans: 5,
            x: 0
        },
        expected: '' +
            '<span class="term term-digit" data-value="4" data-token="NUM4" data-type="digit">4</span>' +
            '<span class="term term-digit" data-value="0" data-token="NUM0" data-type="digit">0</span>' +
            '<span class="term term-operator" data-value="+" data-token="ADD" data-type="operator">' + registeredTerms.ADD.label + '</span>' +
            '<span class="term term-digit" data-value="2" data-token="NUM2" data-type="digit">2</span>' +
            '<span class="term term-operator" data-value="-" data-token="SUB" data-type="operator">' + registeredTerms.SUB.label + '</span>' +
            '<span class="term term-variable" data-value="x" data-token="term" data-type="variable">x</span>' +
            '<span class="term term-operator" data-value="*" data-token="MUL" data-type="operator">' + registeredTerms.MUL.label + '</span>' +
            '<span class="term term-variable" data-value="ans" data-token="ANS" data-type="variable">5</span>' +
            '<span class="term term-operator" data-value="*" data-token="MUL" data-type="operator">' + registeredTerms.MUL.label + '</span>' +
            '<span class="term term-unknown" data-value="y" data-token="term" data-type="unknown">y</span>'
    }])
        .test('render', function (data, assert) {
            QUnit.expect(1);

            assert.equal(tokensHelper.render(data.tokens, data.variables), data.expected, 'Should render the tokens to ' + data.expected);
        });

});
