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
    'ui/maths/calculator/core/terms',
    'util/mathsEvaluator'
], function (_, tokensHelper, calculatorTokenizerFactory, registeredTerms, mathsEvaluatorFactory) {
    'use strict';

    var mathsEvaluator = mathsEvaluatorFactory();

    QUnit.module('Factory');

    QUnit.test('module', function(assert) {
        assert.expect(1);
        assert.equal(typeof tokensHelper, 'object', 'The module exposes an object');
    });

    QUnit.cases.init([
        {title: 'getType'},
        {title: 'isDigit'},
        {title: 'isOperator'},
        {title: 'isOperand'},
        {title: 'isValue'},
        {title: 'isAggregator'},
        {title: 'isError'},
        {title: 'isConstant'},
        {title: 'isVariable'},
        {title: 'isFunction'},
        {title: 'isIdentifier'},
        {title: 'isSeparator'},
        {title: 'isModifier'},
        {title: 'stringValue'}
    ]).test('API ', function (data, assert) {
        assert.expect(1);
        assert.equal(typeof tokensHelper[data.title], 'function', 'The helper exposes a "' + data.title + '" function');
    });

    QUnit.module('API');

    QUnit.test('getType', function(assert) {
        var tokenizer = calculatorTokenizerFactory();
        assert.expect(_.size(registeredTerms) + 6);

        _.forEach(registeredTerms, function(term) {
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
        assert.expect(22);

        assert.equal(tokensHelper.isDigit(registeredTerms.NUM0.type), true, registeredTerms.NUM0.type + ' should be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.SUB.type), false, registeredTerms.SUB.type + ' should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.LPAR.type), false, registeredTerms.LPAR.type + ' should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.COMMA.type), false, registeredTerms.COMMA.type + ' should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.NAN.type), false, registeredTerms.NAN.type + ' should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.PI.type), false, registeredTerms.PI + ' should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.TAN.type), false, registeredTerms.TAN.type + ' should not be a digit');
        assert.equal(tokensHelper.isDigit('term'), false, 'term should not be a digit');
        assert.equal(tokensHelper.isDigit('variable'), false, 'variable should not be a digit');
        assert.equal(tokensHelper.isDigit('foo'), false, 'foo should not be a digit');

        assert.equal(tokensHelper.isDigit(registeredTerms.NUM0), true, registeredTerms.NUM0.type + ' should be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.SUB), false, registeredTerms.SUB.type + ' should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.LPAR), false, registeredTerms.LPAR.type + 'should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.COMMA), false, registeredTerms.COMMA.type + ' should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.NAN), false, registeredTerms.NAN.type + ' should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.PI), false, registeredTerms.PI + ' should not be a digit');
        assert.equal(tokensHelper.isDigit(registeredTerms.TAN), false, registeredTerms.TAN.type + ' should not be a digit');
        assert.equal(tokensHelper.isDigit({type: 'term'}), false, 'term should not be a digit');
        assert.equal(tokensHelper.isDigit({type: 'variable'}), false, 'variable should not be a digit');
        assert.equal(tokensHelper.isDigit({type: 'foo'}), false, 'foo should not be a digit');

        assert.equal(tokensHelper.isDigit({type: 'NUM1'}), true, 'NUM1 should be a digit');
        assert.equal(tokensHelper.isDigit({type: 'FOO'}), false, 'FOO should not be a digit');
    });

    QUnit.test('isOperator', function (assert) {
        assert.expect(22);

        assert.equal(tokensHelper.isOperator(registeredTerms.NUM0.type), false, registeredTerms.NUM0.type + ' should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.SUB.type), true, registeredTerms.SUB.type + ' should be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.LPAR.type), false, registeredTerms.LPAR.type + ' should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.COMMA.type), false, registeredTerms.COMMA.type + ' should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.NAN.type), false, registeredTerms.NAN.type + ' should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.PI.type), false, registeredTerms.PI + ' should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.TAN.type), false, registeredTerms.TAN.type + ' should not be an operator');
        assert.equal(tokensHelper.isOperator('term'), false, 'term should not be an operator');
        assert.equal(tokensHelper.isOperator('variable'), false, 'variable should not be an operator');
        assert.equal(tokensHelper.isOperator('foo'), false, 'foo should not be an operator');

        assert.equal(tokensHelper.isOperator(registeredTerms.NUM0), false, registeredTerms.NUM0.type + ' should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.SUB), true, registeredTerms.SUB.type + ' should be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.LPAR), false, registeredTerms.LPAR.type + ' should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.COMMA), false, registeredTerms.COMMA.type + ' should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.NAN), false, registeredTerms.NAN.type + ' should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.PI), false, registeredTerms.PI + ' should not be an operator');
        assert.equal(tokensHelper.isOperator(registeredTerms.TAN), false, registeredTerms.TAN.type + ' should not be an operator');
        assert.equal(tokensHelper.isOperator({type: 'term'}), false, 'term should not be an operator');
        assert.equal(tokensHelper.isOperator({type: 'variable'}), false, 'variable should not be an operator');
        assert.equal(tokensHelper.isOperator({type: 'foo'}), false, 'foo should not be an operator');

        assert.equal(tokensHelper.isOperator({type: 'ADD'}), true, 'ADD should be an operator');
        assert.equal(tokensHelper.isOperator({type: 'FOO'}), false, 'FOO should not be an operator');
    });

    QUnit.test('isOperand', function (assert) {
        assert.expect(22);

        assert.equal(tokensHelper.isOperand(registeredTerms.NUM0.type), true, registeredTerms.NUM0.type + ' should be an operand');
        assert.equal(tokensHelper.isOperand(registeredTerms.SUB.type), false, registeredTerms.SUB.type + ' should not be an operand');
        assert.equal(tokensHelper.isOperand(registeredTerms.LPAR.type), false, registeredTerms.LPAR.type + ' should not be an operand');
        assert.equal(tokensHelper.isOperand(registeredTerms.COMMA.type), false, registeredTerms.COMMA.type + ' should not be an operand');
        assert.equal(tokensHelper.isOperand(registeredTerms.NAN.type), true, registeredTerms.NAN.type + ' should be an operand');
        assert.equal(tokensHelper.isOperand(registeredTerms.PI.type), true, registeredTerms.PI + ' should be an operand');
        assert.equal(tokensHelper.isOperand(registeredTerms.TAN.type), true, registeredTerms.TAN.type + ' should be an operand');
        assert.equal(tokensHelper.isOperand('term'), true, 'term should be an operand');
        assert.equal(tokensHelper.isOperand('variable'), true, 'variable should be an operand');
        assert.equal(tokensHelper.isOperand('foo'), true, 'foo should be an operand');

        assert.equal(tokensHelper.isOperand(registeredTerms.NUM0), true, registeredTerms.NUM0.type + ' should be an operand');
        assert.equal(tokensHelper.isOperand(registeredTerms.SUB), false, registeredTerms.SUB.type + ' should not be an operand');
        assert.equal(tokensHelper.isOperand(registeredTerms.LPAR), false, registeredTerms.LPAR.type + ' should not be an operand');
        assert.equal(tokensHelper.isOperand(registeredTerms.COMMA), false, registeredTerms.COMMA.type + ' should not be an operand');
        assert.equal(tokensHelper.isOperand(registeredTerms.NAN), true, registeredTerms.NAN.type + ' should be an operand');
        assert.equal(tokensHelper.isOperand(registeredTerms.PI), true, registeredTerms.PI + ' should be an operand');
        assert.equal(tokensHelper.isOperand(registeredTerms.TAN), true, registeredTerms.TAN.type + ' should be an operand');
        assert.equal(tokensHelper.isOperand({type: 'term'}), true, 'term should be an operand');
        assert.equal(tokensHelper.isOperand({type: 'variable'}), true, 'variable should be an operand');
        assert.equal(tokensHelper.isOperand({type: 'foo'}), true, 'foo should be an operand');

        assert.equal(tokensHelper.isOperand({type: 'ADD'}), false, 'ADD should not be an operand');
        assert.equal(tokensHelper.isOperand({type: 'FOO'}), true, 'FOO should be an operand');
    });
    QUnit.test('isValue', function (assert) {
        assert.expect(22);

        assert.equal(tokensHelper.isValue(registeredTerms.NUM0.type), true, registeredTerms.NUM0.type + ' should be a value');
        assert.equal(tokensHelper.isValue(registeredTerms.SUB.type), false, registeredTerms.SUB.type + ' should not be a value');
        assert.equal(tokensHelper.isValue(registeredTerms.LPAR.type), false, registeredTerms.LPAR.type + ' should not be a value');
        assert.equal(tokensHelper.isValue(registeredTerms.COMMA.type), false, registeredTerms.COMMA.type + ' should not be a value');
        assert.equal(tokensHelper.isValue(registeredTerms.NAN.type), true, registeredTerms.NAN.type + ' should be a value');
        assert.equal(tokensHelper.isValue(registeredTerms.PI.type), true, registeredTerms.PI + ' should be a value');
        assert.equal(tokensHelper.isValue(registeredTerms.TAN.type), false, registeredTerms.TAN.type + ' should not be a value');
        assert.equal(tokensHelper.isValue('term'), true, 'term should be a value');
        assert.equal(tokensHelper.isValue('variable'), true, 'variable should be a value');
        assert.equal(tokensHelper.isValue('foo'), false, 'foo should not be a value');

        assert.equal(tokensHelper.isValue(registeredTerms.NUM0), true, registeredTerms.NUM0.type + ' should be a value');
        assert.equal(tokensHelper.isValue(registeredTerms.SUB), false, registeredTerms.SUB.type + ' should not be a value');
        assert.equal(tokensHelper.isValue(registeredTerms.LPAR), false, registeredTerms.LPAR.type + ' should not be a value');
        assert.equal(tokensHelper.isValue(registeredTerms.COMMA), false, registeredTerms.COMMA.type + ' should not be a value');
        assert.equal(tokensHelper.isValue(registeredTerms.NAN), true, registeredTerms.NAN.type + ' should be a value');
        assert.equal(tokensHelper.isValue(registeredTerms.PI), true, registeredTerms.PI + ' should be a value');
        assert.equal(tokensHelper.isValue(registeredTerms.TAN), false, registeredTerms.TAN.type + ' should not be a value');
        assert.equal(tokensHelper.isValue({type: 'term'}), true, 'term should be a value');
        assert.equal(tokensHelper.isValue({type: 'variable'}), true, 'variable should be a value');
        assert.equal(tokensHelper.isValue({type: 'foo'}), false, 'foo should not be a value');

        assert.equal(tokensHelper.isValue({type: 'ADD'}), false, 'ADD should not be a value');
        assert.equal(tokensHelper.isValue({type: 'FOO'}), false, 'FOO should not be a value');
    });

    QUnit.test('isAggregator', function (assert) {
        assert.expect(22);

        assert.equal(tokensHelper.isAggregator(registeredTerms.NUM0.type), false, registeredTerms.NUM0.type + ' should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.SUB.type), false, registeredTerms.SUB.type + ' should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.LPAR.type), true, registeredTerms.LPAR.type + ' should be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.COMMA.type), false, registeredTerms.COMMA.type + ' should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.NAN.type), false, registeredTerms.NAN.type + ' should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.PI.type), false, registeredTerms.PI + ' should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.TAN.type), false, registeredTerms.TAN.type + ' should not be an aggregator');
        assert.equal(tokensHelper.isAggregator('term'), false, 'term should not be an aggregator');
        assert.equal(tokensHelper.isAggregator('variable'), false, 'variable should not be an aggregator');
        assert.equal(tokensHelper.isAggregator('foo'), false, 'foo should not be an aggregator');

        assert.equal(tokensHelper.isAggregator(registeredTerms.NUM0), false, registeredTerms.NUM0.type + ' should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.SUB), false, registeredTerms.SUB.type + ' should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.LPAR), true, registeredTerms.LPAR.type + ' should be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.COMMA), false, registeredTerms.COMMA.type + ' should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.NAN), false, registeredTerms.NAN.type + ' should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.PI), false, registeredTerms.PI + ' should not be an aggregator');
        assert.equal(tokensHelper.isAggregator(registeredTerms.TAN), false, registeredTerms.TAN.type + ' should not be an aggregator');
        assert.equal(tokensHelper.isAggregator({type: 'term'}), false, 'term should not be an aggregator');
        assert.equal(tokensHelper.isAggregator({type: 'variable'}), false, 'variable should not be an aggregator');
        assert.equal(tokensHelper.isAggregator({type: 'foo'}), false, 'foo should not be an aggregator');

        assert.equal(tokensHelper.isAggregator({type: 'RPAR'}), true, 'RPAR should be an aggregator');
        assert.equal(tokensHelper.isAggregator({type: 'FOO'}), false, 'FOO should not be an aggregator');
    });

    QUnit.test('isError', function (assert) {
        assert.expect(22);

        assert.equal(tokensHelper.isError(registeredTerms.NUM0.type), false, registeredTerms.NUM0.type + ' should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.SUB.type), false, registeredTerms.SUB.type + ' should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.LPAR.type), false, registeredTerms.LPAR.type + ' should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.COMMA.type), false, registeredTerms.COMMA.type + ' should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.NAN.type), true, registeredTerms.NAN.type + ' should be an error');
        assert.equal(tokensHelper.isError(registeredTerms.PI.type), false, registeredTerms.PI + ' should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.TAN.type), false, registeredTerms.TAN.type + ' should not be an error');
        assert.equal(tokensHelper.isError('term'), false, 'term should not be an error');
        assert.equal(tokensHelper.isError('variable'), false, 'variable should not be an error');
        assert.equal(tokensHelper.isError('foo'), false, 'foo should not be an error');

        assert.equal(tokensHelper.isError(registeredTerms.NUM0), false, registeredTerms.NUM0.type + ' should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.SUB), false, registeredTerms.SUB.type + ' should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.LPAR), false, registeredTerms.LPAR.type + ' should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.COMMA), false, registeredTerms.COMMA.type + ' should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.NAN), true, registeredTerms.NAN.type + ' should be an error');
        assert.equal(tokensHelper.isError(registeredTerms.PI), false, registeredTerms.PI + ' should not be an error');
        assert.equal(tokensHelper.isError(registeredTerms.TAN), false, registeredTerms.TAN.type + ' should not be an error');
        assert.equal(tokensHelper.isError({type: 'term'}), false, 'term should not be an error');
        assert.equal(tokensHelper.isError({type: 'variable'}), false, 'variable should not be an error');
        assert.equal(tokensHelper.isError({type: 'foo'}), false, 'foo should not be an error');

        assert.equal(tokensHelper.isError({type: 'NAN'}), true, 'NAN should be an error');
        assert.equal(tokensHelper.isError({type: 'FOO'}), false, 'FOO should not be an error');
    });

    QUnit.test('isConstant', function (assert) {
        assert.expect(22);

        assert.equal(tokensHelper.isConstant(registeredTerms.NUM0.type), false, registeredTerms.NUM0.type + ' should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.SUB.type), false, registeredTerms.SUB.type + ' should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.LPAR.type), false, registeredTerms.LPAR.type + ' should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.COMMA.type), false, registeredTerms.COMMA.type + ' should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.NAN.type), false, registeredTerms.NAN.type + ' should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.PI.type), true, registeredTerms.PI + ' should be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.TAN.type), false, registeredTerms.TAN.type + ' should not be a constant');
        assert.equal(tokensHelper.isConstant('term'), false, 'term should not be a constant');
        assert.equal(tokensHelper.isConstant('variable'), false, 'variable should not be a constant');
        assert.equal(tokensHelper.isConstant('foo'), false, 'foo should not be a constant');

        assert.equal(tokensHelper.isConstant(registeredTerms.NUM0), false, registeredTerms.NUM0.type + ' should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.SUB), false, registeredTerms.SUB.type + ' should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.LPAR), false, registeredTerms.LPAR.type + ' should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.COMMA), false, registeredTerms.COMMA.type + ' should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.NAN), false, registeredTerms.NAN.type + ' should not be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.PI), true, registeredTerms.PI + ' should be a constant');
        assert.equal(tokensHelper.isConstant(registeredTerms.TAN), false, registeredTerms.TAN.type + ' should not be a constant');
        assert.equal(tokensHelper.isConstant({type: 'term'}), false, 'term should not be a constant');
        assert.equal(tokensHelper.isConstant({type: 'variable'}), false, 'variable should not be a constant');
        assert.equal(tokensHelper.isConstant({type: 'foo'}), false, 'foo should not be a constant');

        assert.equal(tokensHelper.isConstant({type: 'PI'}), true, 'PI should be a constant');
        assert.equal(tokensHelper.isConstant({type: 'FOO'}), false, 'FOO should not be a constant');
    });

    QUnit.test('isVariable', function (assert) {
        assert.expect(22);

        assert.equal(tokensHelper.isVariable(registeredTerms.NUM0.type), false, registeredTerms.NUM0.type + ' should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.SUB.type), false, registeredTerms.SUB.type + ' should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.LPAR.type), false, registeredTerms.LPAR.type + ' should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.COMMA.type), false, registeredTerms.COMMA.type + ' should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.NAN.type), false, registeredTerms.NAN.type + ' should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.PI.type), false, registeredTerms.PI + ' should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.TAN.type), false, registeredTerms.TAN.type + ' should not be a variable');
        assert.equal(tokensHelper.isVariable('term'), true, 'term should be a variable');
        assert.equal(tokensHelper.isVariable('variable'), true, 'variable should be a variable');
        assert.equal(tokensHelper.isVariable('foo'), false, 'foo should not be a variable');

        assert.equal(tokensHelper.isVariable(registeredTerms.NUM0), false, registeredTerms.NUM0.type + ' should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.SUB), false, registeredTerms.SUB.type + ' should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.LPAR), false, registeredTerms.LPAR.type + ' should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.COMMA), false, registeredTerms.COMMA.type + ' should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.NAN), false, registeredTerms.NAN.type + ' should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.PI), false, registeredTerms.PI + ' should not be a variable');
        assert.equal(tokensHelper.isVariable(registeredTerms.TAN), false, registeredTerms.TAN.type + ' should not be a variable');
        assert.equal(tokensHelper.isVariable({type: 'term'}), true, 'term should be a variable');
        assert.equal(tokensHelper.isVariable({type: 'variable'}), true, 'variable should be a variable');
        assert.equal(tokensHelper.isVariable({type: 'foo'}), false, 'foo should not be a variable');

        assert.equal(tokensHelper.isVariable({type: 'X'}), false, 'X should not be a variable');
        assert.equal(tokensHelper.isVariable({type: 'FOO'}), false, 'FOO should not be a variable');
    });

    QUnit.test('isFunction', function (assert) {
        assert.expect(22);

        assert.equal(tokensHelper.isFunction(registeredTerms.NUM0.type), false, registeredTerms.NUM0.type + ' should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.SUB.type), false, registeredTerms.SUB.type + ' should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.LPAR.type), false, registeredTerms.LPAR.type + ' should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.COMMA.type), false, registeredTerms.COMMA.type + ' should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.NAN.type), false, registeredTerms.NAN.type + ' should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.PI.type), false, registeredTerms.PI + ' should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.TAN.type), true, registeredTerms.TAN.type + ' should be a function');
        assert.equal(tokensHelper.isFunction('term'), false, 'term should not be a function');
        assert.equal(tokensHelper.isFunction('variable'), false, 'variable should not be a function');
        assert.equal(tokensHelper.isFunction('foo'), false, 'foo should not be a function');

        assert.equal(tokensHelper.isFunction(registeredTerms.NUM0), false, registeredTerms.NUM0.type + ' should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.SUB), false, registeredTerms.SUB.type + ' should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.LPAR), false, registeredTerms.LPAR.type + ' should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.COMMA), false, registeredTerms.COMMA.type + ' should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.NAN), false, registeredTerms.NAN.type + ' should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.PI), false, registeredTerms.PI + ' should not be a function');
        assert.equal(tokensHelper.isFunction(registeredTerms.TAN), true, registeredTerms.TAN.type + ' should be a function');
        assert.equal(tokensHelper.isFunction({type: 'term'}), false, 'term should not be a function');
        assert.equal(tokensHelper.isFunction({type: 'variable'}), false, 'variable should not be a function');
        assert.equal(tokensHelper.isFunction({type: 'foo'}), false, 'foo should not be a function');

        assert.equal(tokensHelper.isFunction({type: 'TAN'}), true, 'TAN should be a function');
        assert.equal(tokensHelper.isFunction({type: 'FOO'}), false, 'FOO should not be a function');
    });

    QUnit.test('isIdentifier', function (assert) {
        assert.expect(22);

        assert.equal(tokensHelper.isIdentifier(registeredTerms.NUM0.type), false, registeredTerms.NUM0.type + ' should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.SUB.type), false, registeredTerms.SUB.type + ' should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.LPAR.type), false, registeredTerms.LPAR.type + ' should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.COMMA.type), false, registeredTerms.COMMA.type + ' should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.NAN.type), true, registeredTerms.NAN.type + ' should be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.PI.type), true, registeredTerms.PI + ' should be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.TAN.type), true, registeredTerms.TAN.type + ' should be an identifier');
        assert.equal(tokensHelper.isIdentifier('term'), true, 'term should be an identifier');
        assert.equal(tokensHelper.isIdentifier('variable'), true, 'variable should be an identifier');
        assert.equal(tokensHelper.isIdentifier('foo'), false, 'foo should not be an identifier');

        assert.equal(tokensHelper.isIdentifier(registeredTerms.NUM0), false, registeredTerms.NUM0.type + ' should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.SUB), false, registeredTerms.SUB.type + ' should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.LPAR), false, registeredTerms.LPAR.type + ' should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.COMMA), false, registeredTerms.COMMA.type + ' should not be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.NAN), true, registeredTerms.NAN.type + ' should be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.PI), true, registeredTerms.PI + ' should be an identifier');
        assert.equal(tokensHelper.isIdentifier(registeredTerms.TAN), true, registeredTerms.TAN.type + ' should be an identifier');
        assert.equal(tokensHelper.isIdentifier({type: 'term'}), true, 'term should be an identifier');
        assert.equal(tokensHelper.isIdentifier({type: 'variable'}), true, 'variable should be an identifier');
        assert.equal(tokensHelper.isIdentifier({type: 'foo'}), false, 'foo should not be an identifier');

        assert.equal(tokensHelper.isIdentifier({type: 'COS'}), true, 'COS should be an identifier');
        assert.equal(tokensHelper.isIdentifier({type: 'POW'}), false, 'POW should not be an identifier');
    });

    QUnit.test('isSeparator', function (assert) {
        assert.expect(22);

        assert.equal(tokensHelper.isSeparator(registeredTerms.NUM0.type), false, registeredTerms.NUM0.type + ' should not be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.SUB.type), true, registeredTerms.SUB.type + ' should be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.LPAR.type), true, registeredTerms.LPAR.type + ' should be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.COMMA.type), true, registeredTerms.COMMA.type + ' should be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.NAN.type), false, registeredTerms.NAN.type + ' should not be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.PI.type), false, registeredTerms.PI + ' should not be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.TAN.type), false, registeredTerms.TAN.type + ' should not be a separator');
        assert.equal(tokensHelper.isSeparator('term'), false, 'term should not be a separator');
        assert.equal(tokensHelper.isSeparator('variable'), false, 'variable should not be a separator');
        assert.equal(tokensHelper.isSeparator('foo'), false, 'foo should not be a separator');

        assert.equal(tokensHelper.isSeparator(registeredTerms.NUM0), false, registeredTerms.NUM0.type + ' should not be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.SUB), true, registeredTerms.SUB.type + ' should be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.LPAR), true, registeredTerms.LPAR.type + ' should be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.COMMA), true, registeredTerms.COMMA.type + ' should be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.NAN), false, registeredTerms.NAN.type + ' should not be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.PI), false, registeredTerms.PI + ' should not be a separator');
        assert.equal(tokensHelper.isSeparator(registeredTerms.TAN), false, registeredTerms.TAN.type + ' should not be a separator');
        assert.equal(tokensHelper.isSeparator({type: 'term'}), false, 'term should not be a separator');
        assert.equal(tokensHelper.isSeparator({type: 'variable'}), false, 'variable should not be a separator');
        assert.equal(tokensHelper.isSeparator({type: 'foo'}), false, 'foo should not be a separator');

        assert.equal(tokensHelper.isSeparator({type: 'POW'}), true, 'POW should be a separator');
        assert.equal(tokensHelper.isSeparator({type: 'FOO'}), false, 'FOO should not be a separator');
    });

    QUnit.test('isModifier', function (assert) {
        assert.expect(22);

        assert.equal(tokensHelper.isModifier(registeredTerms.NUM0.type), false, registeredTerms.NUM0.type + ' should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.SUB.type), true, registeredTerms.SUB.type + ' should be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.LPAR.type), false, registeredTerms.LPAR.type + ' should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.COMMA.type), false, registeredTerms.COMMA.type + ' should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.NAN.type), false, registeredTerms.NAN.type + ' should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.PI.type), false, registeredTerms.PI + ' should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.TAN.type), true, registeredTerms.TAN.type + ' should be a modifier');
        assert.equal(tokensHelper.isModifier('term'), false, 'term should not be a modifier');
        assert.equal(tokensHelper.isModifier('variable'), false, 'variable should not be a modifier');
        assert.equal(tokensHelper.isModifier('foo'), false, 'foo should not be a modifier');

        assert.equal(tokensHelper.isModifier(registeredTerms.NUM0), false, registeredTerms.NUM0.type + ' should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.SUB), true, registeredTerms.SUB.type + ' should be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.LPAR), false, registeredTerms.LPAR.type + ' should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.COMMA), false, registeredTerms.COMMA.type + ' should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.NAN), false, registeredTerms.NAN.type + ' should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.PI), false, registeredTerms.PI + ' should not be a modifier');
        assert.equal(tokensHelper.isModifier(registeredTerms.TAN), true, registeredTerms.TAN.type + ' should be a modifier');
        assert.equal(tokensHelper.isModifier({type: 'term'}), false, 'term should not be a modifier');
        assert.equal(tokensHelper.isModifier({type: 'variable'}), false, 'variable should not be a modifier');
        assert.equal(tokensHelper.isModifier({type: 'foo'}), false, 'foo should not be a modifier');

        assert.equal(tokensHelper.isModifier({type: 'POW'}), true, 'POW should be a modifier');
        assert.equal(tokensHelper.isModifier({type: 'FOO'}), false, 'FOO should not be a modifier');
    });

    QUnit.cases.init([{
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
        title: 'Object expression containing value',
        expression: {
            value: 'cos PI * (40 + 2)'
        },
        expected: 'cos PI * (40 + 2)'
    }, {
        title: 'Object expression containing result',
        expression: {
            result: 'cos PI * (40 + 2)'
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
        title: 'Computed expression: 4 @nthrt 45',
        expression: mathsEvaluator('4 @nthrt 45'),
        expected: '2.5900200641113513'
    }])
        .test('stringValue', function (data, assert) {
            assert.expect(1);

            assert.equal(tokensHelper.stringValue(data.expression), data.expected, 'Should cast the value ' + data.expression);

        });
});
