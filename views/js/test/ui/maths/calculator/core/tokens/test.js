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
    'ui/maths/calculator/core/terms',
    'util/mathsEvaluator'
], function ($, _, tokensHelper, calculatorTokenizerFactory, registeredTerms, mathsEvaluatorFactory) {
    'use strict';

    var tokens = {
        NUM0: {
            type: 'NUM0',
            value: '0'
        },
        NUM1: {
            type: 'NUM1',
            value: '1'
        },
        NUM2: {
            type: 'NUM2',
            value: '2'
        },
        NUM3: {
            type: 'NUM3',
            value: '3'
        },
        NUM4: {
            type: 'NUM4',
            value: '4'
        },
        NUM5: {
            type: 'NUM5',
            value: '5'
        },
        NUM6: {
            type: 'NUM6',
            value: '6'
        },
        NUM7: {
            type: 'NUM7',
            value: '7'
        },
        NUM8: {
            type: 'NUM8',
            value: '8'
        },
        NUM9: {
            type: 'NUM9',
            value: '9'
        },
        ADD: {
            type: 'ADD',
            value: '+'
        },
        SUB: {
            type: 'SUB',
            value: '-'
        },
        MUL: {
            type: 'MUL',
            value: '*'
        },
        POW: {
            type: 'POW',
            value: '^'
        },
        FAC: {
            type: 'FAC',
            value: '!'
        },
        NTHRT: {
            type: 'NTHRT',
            value: '@nthrt'
        },
        LPAR: {
            type: 'LPAR',
            value: '('
        },
        RPAR: {
            type: 'RPAR',
            value: ')'
        },
        CEIL: {
            type: 'CEIL',
            value: 'ceil'
        },
        COS: {
            type: 'COS',
            value: 'cos'
        },
        PI: {
            type: 'PI',
            value: 'PI'
        },
        ANS: {
            type: 'ANS',
            value: 'ans'
        },
        VAR_X: {
            type: 'term',
            value: 'x'
        },
        VAR_Y: {
            type: 'term',
            value: 'y'
        }
    };
    var renderedTokens = {
        NUM0: '<span class="term term-digit" data-value="0" data-token="NUM0" data-type="digit">' + registeredTerms.NUM0.label + '</span>',
        NUM1: '<span class="term term-digit" data-value="1" data-token="NUM1" data-type="digit">' + registeredTerms.NUM1.label + '</span>',
        NUM2: '<span class="term term-digit" data-value="2" data-token="NUM2" data-type="digit">' + registeredTerms.NUM2.label + '</span>',
        NUM3: '<span class="term term-digit" data-value="3" data-token="NUM3" data-type="digit">' + registeredTerms.NUM3.label + '</span>',
        NUM4: '<span class="term term-digit" data-value="4" data-token="NUM4" data-type="digit">' + registeredTerms.NUM4.label + '</span>',
        NUM5: '<span class="term term-digit" data-value="5" data-token="NUM5" data-type="digit">' + registeredTerms.NUM5.label + '</span>',
        NUM6: '<span class="term term-digit" data-value="6" data-token="NUM6" data-type="digit">' + registeredTerms.NUM6.label + '</span>',
        NUM7: '<span class="term term-digit" data-value="7" data-token="NUM7" data-type="digit">' + registeredTerms.NUM7.label + '</span>',
        NUM8: '<span class="term term-digit" data-value="8" data-token="NUM8" data-type="digit">' + registeredTerms.NUM8.label + '</span>',
        NUM9: '<span class="term term-digit" data-value="9" data-token="NUM9" data-type="digit">' + registeredTerms.NUM9.label + '</span>',
        ADD: '<span class="term term-operator" data-value="+" data-token="ADD" data-type="operator">' + registeredTerms.ADD.label + '</span>',
        SUB: '<span class="term term-operator" data-value="-" data-token="SUB" data-type="operator">' + registeredTerms.SUB.label + '</span>',
        POS: '<span class="term term-operator" data-value="+" data-token="POS" data-type="operator">' + registeredTerms.POS.label + '</span>',
        NEG: '<span class="term term-operator" data-value="-" data-token="NEG" data-type="operator">' + registeredTerms.NEG.label + '</span>',
        MUL: '<span class="term term-operator" data-value="*" data-token="MUL" data-type="operator">' + registeredTerms.MUL.label + '</span>',
        POW: '<span class="term term-operator" data-value="^" data-token="POW" data-type="operator">' + registeredTerms.POW.label + '</span>',
        POWEL: '<span class="term term-operator term-elide" data-value="^" data-token="POW" data-type="operator">' + registeredTerms.POW.label + '</span>',
        FAC: '<span class="term term-operator" data-value="!" data-token="FAC" data-type="operator">' + registeredTerms.FAC.label + '</span>',
        NTHRT: '<span class="term term-function" data-value="nthrt" data-token="NTHRT" data-type="function">' + registeredTerms.NTHRT.label + '</span>',
        LPAR: '<span class="term term-aggregator" data-value="(" data-token="LPAR" data-type="aggregator">' + registeredTerms.LPAR.label + '</span>',
        RPAR: '<span class="term term-aggregator" data-value=")" data-token="RPAR" data-type="aggregator">' + registeredTerms.RPAR.label + '</span>',
        CEIL: '<span class="term term-function" data-value="ceil" data-token="CEIL" data-type="function">' + registeredTerms.CEIL.label + '</span>',
        COS: '<span class="term term-function" data-value="cos" data-token="COS" data-type="function">' + registeredTerms.COS.label + '</span>',
        PI: '<span class="term term-constant" data-value="PI" data-token="PI" data-type="constant">' + registeredTerms.PI.label + '</span>',
        ANS: '<span class="term term-variable" data-value="ans" data-token="ANS" data-type="variable">{{ans}}</span>',
        VAR_ANS: '<span class="term term-variable" data-value="ans" data-token="ANS" data-type="variable">' + registeredTerms.ANS.label + '</span>',
        VAR_X: '<span class="term term-variable" data-value="x" data-token="term" data-type="variable">x</span>',
        VAR_Y: '<span class="term term-variable" data-value="y" data-token="term" data-type="variable">y</span>',
        UNKNOWN_Y: '<span class="term term-unknown" data-value="y" data-token="term" data-type="unknown">y</span>'
    };

    var mathsEvaluator = mathsEvaluatorFactory();

    QUnit.module('Factory');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);
        assert.equal(typeof tokensHelper, 'object', "The module exposes an object");
    });

    QUnit.cases([
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
        QUnit.expect(22);

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
        QUnit.expect(22);

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
        QUnit.expect(22);

        assert.equal(tokensHelper.isOperand(registeredTerms.NUM0.type), true, registeredTerms.NUM0.type + ' should be an operand');
        assert.equal(tokensHelper.isOperand(registeredTerms.SUB.type), false, registeredTerms.SUB.type +  ' should not be an operand');
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
        QUnit.expect(22);

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
        QUnit.expect(22);

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
        QUnit.expect(22);

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
        QUnit.expect(22);

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
        QUnit.expect(22);

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
        QUnit.expect(22);

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
        QUnit.expect(22);

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
        QUnit.expect(22);

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
        QUnit.expect(22);

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
        title: 'Computed expression: 4 @nthrt 45',
        expression: mathsEvaluator('4 @nthrt 45'),
        expected: '2.5900200641113514527'
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
        tokens: [
            tokens.NUM4,
            tokens.NUM2
        ],
        variables: {},
        expected: [
            renderedTokens.NUM4,
            renderedTokens.NUM2
        ].join('')
    }, {
        title: 'Simple expression',
        tokens: [
            tokens.NUM4,
            tokens.NUM0,
            tokens.ADD,
            tokens.NUM2
        ],
        variables: {},
        expected: [
            renderedTokens.NUM4,
            renderedTokens.NUM0,
            renderedTokens.ADD,
            renderedTokens.NUM2
        ].join('')
    }, {
        title: 'Negative value',
        tokens: [
            tokens.SUB,
            tokens.NUM4,
            tokens.NUM2
        ],
        variables: {},
        expected: [
            renderedTokens.NEG,
            renderedTokens.NUM4,
            renderedTokens.NUM2
        ].join('')
    }, {
        title: 'Negative expression',
        tokens: [
            tokens.SUB,
            tokens.LPAR,
            tokens.NUM4,
            tokens.ADD,
            tokens.NUM2,
            tokens.RPAR
        ],
        variables: {},
        expected: [
            renderedTokens.NEG,
            renderedTokens.LPAR,
            renderedTokens.NUM4,
            renderedTokens.ADD,
            renderedTokens.NUM2,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Explicit positive value',
        tokens: [
            tokens.ADD,
            tokens.NUM4,
            tokens.NUM2
        ],
        variables: {},
        expected: [
            renderedTokens.POS,
            renderedTokens.NUM4,
            renderedTokens.NUM2
        ].join('')
    }, {
        title: 'Explicit positive expression',
        tokens: [
            tokens.ADD,
            tokens.LPAR,
            tokens.NUM4,
            tokens.ADD,
            tokens.NUM2,
            tokens.RPAR
        ],
        variables: {},
        expected: [
            renderedTokens.POS,
            renderedTokens.LPAR,
            renderedTokens.NUM4,
            renderedTokens.ADD,
            renderedTokens.NUM2,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Last result, no variable',
        tokens: [
            tokens.ANS
        ],
        variables: {},
        expected: renderedTokens.VAR_ANS
    }, {
        title: 'Last result, positive value',
        tokens: [
            tokens.ANS
        ],
        variables: {
            ans: '42'
        },
        expected: renderedTokens.ANS.replace('{{ans}}', '42')
    }, {
        title: 'Last result, negative value',
        tokens: [
            tokens.ANS
        ],
        variables: {
            ans: '-42'
        },
        expected: renderedTokens.ANS.replace('{{ans}}', registeredTerms.NEG.label + '42')
    }, {
        title: 'Last result, explicit positive value',
        tokens: [
            tokens.ANS
        ],
        variables: {
            ans: '+42'
        },
        expected: renderedTokens.ANS.replace('{{ans}}', registeredTerms.POS.label + '42')
    }, {
        title: 'Expression with variables',
        tokens: [
            tokens.NUM4,
            tokens.NUM0,
            tokens.ADD,
            tokens.NUM2,
            tokens.SUB,
            tokens.VAR_X,
            tokens.MUL,
            tokens.ANS,
            tokens.MUL,
            tokens.VAR_Y
        ],
        variables: {
            ans: 5,
            x: 0
        },
        expected: [
            renderedTokens.NUM4,
            renderedTokens.NUM0,
            renderedTokens.ADD,
            renderedTokens.NUM2,
            renderedTokens.SUB,
            renderedTokens.VAR_X,
            renderedTokens.MUL,
            renderedTokens.ANS.replace('{{ans}}', '5'),
            renderedTokens.MUL,
            renderedTokens.UNKNOWN_Y
        ].join('')
    }, {
        title: 'Left exponent: nthrt',
        tokens: [
            tokens.NTHRT
        ],
        variables: {},
        expected: [
            renderedTokens.NTHRT
        ].join('')
    }, {
        title: 'Left exponent: 4 nthrt',
        tokens: [
            tokens.NUM4,
            tokens.NTHRT
        ],
        variables: {},
        expected: [
            '<sup>',
            renderedTokens.NUM4,
            '</sup>',
            renderedTokens.NTHRT
        ].join('')
    }, {
        title: 'Left exponent: nthrt nthrt',
        tokens: [
            tokens.NTHRT,
            tokens.NTHRT
        ],
        variables: {},
        expected: [
            '<sup>',
            renderedTokens.NTHRT,
            '</sup>',
            renderedTokens.NTHRT
        ].join('')
    }, {
        title: 'Left exponent: nthrt 16',
        tokens: [
            tokens.NTHRT,
            tokens.NUM1,
            tokens.NUM6
        ],
        variables: {},
        expected: [
            renderedTokens.NTHRT,
            renderedTokens.NUM1,
            renderedTokens.NUM6
        ].join('')
    }, {
        title: 'Left exponent: 4 nthrt 16',
        tokens: [
            tokens.NUM4,
            tokens.NTHRT,
            tokens.NUM1,
            tokens.NUM6
        ],
        variables: {},
        expected: [
            '<sup>',
            renderedTokens.NUM4,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM1,
            renderedTokens.NUM6
        ].join('')
    }, {
        title: 'Left exponent: -4 nthrt 16',
        tokens: [
            tokens.SUB,
            tokens.NUM4,
            tokens.NTHRT,
            tokens.NUM1,
            tokens.NUM6
        ],
        variables: {},
        expected: [
            renderedTokens.NEG,
            '<sup>',
            renderedTokens.NUM4,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM1,
            renderedTokens.NUM6
        ].join('')
    }, {
        title: 'Left exponent: (4 nthrt 16)',
        tokens: [
            tokens.LPAR,
            tokens.NUM4,
            tokens.NTHRT,
            tokens.NUM1,
            tokens.NUM6,
            tokens.RPAR
        ],
        variables: {},
        expected: [
            renderedTokens.LPAR,
            '<sup>',
            renderedTokens.NUM4,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM1,
            renderedTokens.NUM6,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Left exponent: (5+4) nthrt 16',
        tokens: [
            tokens.LPAR,
            tokens.NUM5,
            tokens.ADD,
            tokens.NUM4,
            tokens.RPAR,
            tokens.NTHRT,
            tokens.NUM1,
            tokens.NUM6
        ],
        variables: {},
        expected: [
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.NUM5,
            renderedTokens.ADD,
            renderedTokens.NUM4,
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM1,
            renderedTokens.NUM6
        ].join('')
    }, {
        title: 'Left exponent: -(5+4) nthrt 16',
        tokens: [
            tokens.SUB,
            tokens.LPAR,
            tokens.NUM5,
            tokens.ADD,
            tokens.NUM4,
            tokens.RPAR,
            tokens.NTHRT,
            tokens.NUM1,
            tokens.NUM6
        ],
        variables: {},
        expected: [
            renderedTokens.NEG,
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.NUM5,
            renderedTokens.ADD,
            renderedTokens.NUM4,
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM1,
            renderedTokens.NUM6
        ].join('')
    }, {
        title: 'Left exponent: ((5+4*(2-x)) nthrt 16)',
        tokens: [
            tokens.LPAR,
            tokens.LPAR,
            tokens.NUM5,
            tokens.ADD,
            tokens.NUM4,
            tokens.MUL,
            tokens.LPAR,
            tokens.NUM2,
            tokens.SUB,
            tokens.VAR_X,
            tokens.RPAR,
            tokens.RPAR,
            tokens.NTHRT,
            tokens.NUM1,
            tokens.NUM6,
            tokens.RPAR
        ],
        variables: {
            x: 2
        },
        expected: [
            renderedTokens.LPAR,
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.NUM5,
            renderedTokens.ADD,
            renderedTokens.NUM4,
            renderedTokens.MUL,
            renderedTokens.LPAR,
            renderedTokens.NUM2,
            renderedTokens.SUB,
            renderedTokens.VAR_X,
            renderedTokens.RPAR,
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM1,
            renderedTokens.NUM6,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Left exponent: 5+4 nthrt 16',
        tokens: [
            tokens.NUM5,
            tokens.ADD,
            tokens.NUM4,
            tokens.NTHRT,
            tokens.NUM1,
            tokens.NUM6
        ],
        variables: {},
        expected: [
            renderedTokens.NUM5,
            renderedTokens.ADD,
            '<sup>',
            renderedTokens.NUM4,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM1,
            renderedTokens.NUM6
        ].join('')
    }, {
        title: 'Left exponent: 5+(4 nthrt 16)',
        tokens: [
            tokens.NUM5,
            tokens.ADD,
            tokens.LPAR,
            tokens.NUM4,
            tokens.NTHRT,
            tokens.NUM1,
            tokens.NUM6,
            tokens.RPAR
        ],
        variables: {},
        expected: [
            renderedTokens.NUM5,
            renderedTokens.ADD,
            renderedTokens.LPAR,
            '<sup>',
            renderedTokens.NUM4,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM1,
            renderedTokens.NUM6,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Left exponent: 114 nthrt (ans*3)',
        tokens: [
            tokens.NUM1,
            tokens.NUM1,
            tokens.NUM4,
            tokens.NTHRT,
            tokens.LPAR,
            tokens.ANS,
            tokens.MUL,
            tokens.NUM3,
            tokens.RPAR
        ],
        variables: {
            ans: 5
        },
        expected: [
            '<sup>',
            renderedTokens.NUM1,
            renderedTokens.NUM1,
            renderedTokens.NUM4,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.LPAR,
            renderedTokens.ANS.replace('{{ans}}', '5'),
            renderedTokens.MUL,
            renderedTokens.NUM3,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Left exponent: (5+114) nthrt (ans*3)',
        tokens: [
            tokens.LPAR,
            tokens.NUM5,
            tokens.ADD,
            tokens.NUM1,
            tokens.NUM1,
            tokens.NUM4,
            tokens.RPAR,
            tokens.NTHRT,
            tokens.LPAR,
            tokens.ANS,
            tokens.MUL,
            tokens.NUM3,
            tokens.RPAR
        ],
        variables: {
            ans: 5
        },
        expected: [
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.NUM5,
            renderedTokens.ADD,
            renderedTokens.NUM1,
            renderedTokens.NUM1,
            renderedTokens.NUM4,
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.LPAR,
            renderedTokens.ANS.replace('{{ans}}', '5'),
            renderedTokens.MUL,
            renderedTokens.NUM3,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Left exponent: 5+114 nthrt (ans*3)',
        tokens: [
            tokens.NUM5,
            tokens.ADD,
            tokens.NUM1,
            tokens.NUM1,
            tokens.NUM4,
            tokens.NTHRT,
            tokens.LPAR,
            tokens.ANS,
            tokens.MUL,
            tokens.NUM3,
            tokens.RPAR
        ],
        variables: {
            ans: 5
        },
        expected: [
            renderedTokens.NUM5,
            renderedTokens.ADD,
            '<sup>',
            renderedTokens.NUM1,
            renderedTokens.NUM1,
            renderedTokens.NUM4,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.LPAR,
            renderedTokens.ANS.replace('{{ans}}', '5'),
            renderedTokens.MUL,
            renderedTokens.NUM3,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Left exponent: 3*(4+2)nthrt(ans*3)',
        tokens: [
            tokens.NUM3,
            tokens.MUL,
            tokens.LPAR,
            tokens.NUM4,
            tokens.ADD,
            tokens.NUM2,
            tokens.RPAR,
            tokens.NTHRT,
            tokens.LPAR,
            tokens.ANS,
            tokens.MUL,
            tokens.NUM3,
            tokens.RPAR
        ],
        variables: {
            ans: 5
        },
        expected: [
            renderedTokens.NUM3,
            renderedTokens.MUL,
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.NUM4,
            renderedTokens.ADD,
            renderedTokens.NUM2,
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.LPAR,
            renderedTokens.ANS.replace('{{ans}}', '5'),
            renderedTokens.MUL,
            renderedTokens.NUM3,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Left exponent: ceil cos PI nthrt 4',
        tokens: [
            tokens.CEIL,
            tokens.COS,
            tokens.PI,
            tokens.NTHRT,
            tokens.NUM4
        ],
        variables: {},
        expected: [
            renderedTokens.CEIL,
            renderedTokens.COS,
            '<sup>',
            renderedTokens.PI,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM4
        ].join('')
    }, {
        title: 'Left exponent: cos PI nthrt 4',
        tokens: [
            tokens.COS,
            tokens.PI,
            tokens.NTHRT,
            tokens.NUM4
        ],
        variables: {},
        expected: [
            renderedTokens.COS,
            '<sup>',
            renderedTokens.PI,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM4
        ].join('')
    }, {
        title: 'Left exponent: (cos PI) nthrt 4',
        tokens: [
            tokens.LPAR,
            tokens.COS,
            tokens.PI,
            tokens.RPAR,
            tokens.NTHRT,
            tokens.NUM4
        ],
        variables: {},
        expected: [
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.COS,
            renderedTokens.PI,
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM4
        ].join('')
    }, {
        title: 'Left exponent: cos (PI) nthrt 4',
        tokens: [
            tokens.COS,
            tokens.LPAR,
            tokens.PI,
            tokens.RPAR,
            tokens.NTHRT,
            tokens.NUM4
        ],
        variables: {},
        expected: [
            '<sup>',
            renderedTokens.COS,
            renderedTokens.LPAR,
            renderedTokens.PI,
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM4
        ].join('')
    }, {
        title: 'Left exponent: PI nthrt PI nthrt 4',
        tokens: [
            tokens.PI,
            tokens.NTHRT,
            tokens.PI,
            tokens.NTHRT,
            tokens.NUM4
        ],
        variables: {},
        expected: [
            '<sup>',
            renderedTokens.PI,
            '</sup>',
            renderedTokens.NTHRT,
            '<sup>',
            renderedTokens.PI,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM4
        ].join('')
    }, {
        title: 'Left exponent: PI nthrt (PI+4) nthrt 4',
        tokens: [
            tokens.PI,
            tokens.NTHRT,
            tokens.LPAR,
            tokens.PI,
            tokens.ADD,
            tokens.NUM4,
            tokens.RPAR,
            tokens.NTHRT,
            tokens.NUM4
        ],
        variables: {},
        expected: [
            '<sup>',
            renderedTokens.PI,
            '</sup>',
            renderedTokens.NTHRT,
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.PI,
            renderedTokens.ADD,
            renderedTokens.NUM4,
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM4
        ].join('')
    }, {
        title: 'Left exponent: (PI nthrt PI) nthrt 4',
        tokens: [
            tokens.LPAR,
            tokens.PI,
            tokens.NTHRT,
            tokens.PI,
            tokens.RPAR,
            tokens.NTHRT,
            tokens.NUM4
        ],
        variables: {},
        expected: [
            '<sup>',
            renderedTokens.LPAR,
            '<sup>',
            renderedTokens.PI,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.PI,
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM4
        ].join('')
    }, {
        title: 'Left exponent: PI nthrt (PI nthrt 4)',
        tokens: [
            tokens.PI,
            tokens.NTHRT,
            tokens.LPAR,
            tokens.PI,
            tokens.NTHRT,
            tokens.NUM4,
            tokens.RPAR
        ],
        variables: {},
        expected: [
            '<sup>',
            renderedTokens.PI,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.LPAR,
            '<sup>',
            renderedTokens.PI,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM4,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Left exponent: 10 nthrt PI nthrt 4',
        tokens: [
            tokens.NUM1,
            tokens.NUM0,
            tokens.NTHRT,
            tokens.PI,
            tokens.NTHRT,
            tokens.NUM4
        ],
        variables: {},
        expected: [
            '<sup>',
            renderedTokens.NUM1,
            renderedTokens.NUM0,
            '</sup>',
            renderedTokens.NTHRT,
            '<sup>',
            renderedTokens.PI,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM4
        ].join('')
    }, {
        title: 'Left exponent: 5+10 nthrt PI nthrt 4',
        tokens: [
            tokens.NUM5,
            tokens.ADD,
            tokens.NUM1,
            tokens.NUM0,
            tokens.NTHRT,
            tokens.PI,
            tokens.NTHRT,
            tokens.NUM4
        ],
        variables: {},
        expected: [
            renderedTokens.NUM5,
            renderedTokens.ADD,
            '<sup>',
            renderedTokens.NUM1,
            renderedTokens.NUM0,
            '</sup>',
            renderedTokens.NTHRT,
            '<sup>',
            renderedTokens.PI,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM4
        ].join('')
    }, {
        title: 'Left exponent: 5+10 nthrt cos PI',
        tokens: [
            tokens.NUM5,
            tokens.ADD,
            tokens.NUM1,
            tokens.NUM0,
            tokens.NTHRT,
            tokens.COS,
            tokens.PI
        ],
        variables: {},
        expected: [
            renderedTokens.NUM5,
            renderedTokens.ADD,
            '<sup>',
            renderedTokens.NUM1,
            renderedTokens.NUM0,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.COS,
            renderedTokens.PI
        ].join('')
    }, {
        title: 'Right exponent: ^',
        tokens: [
            tokens.POW
        ],
        variables: {},
        expected: [
            renderedTokens.POW
        ].join('')
    }, {
        title: 'Right exponent: 2^',
        tokens: [
            tokens.NUM2,
            tokens.POW
        ],
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POW
        ].join('')
    }, {
        title: 'Right exponent: 2^4',
        tokens: [
            tokens.NUM2,
            tokens.POW,
            tokens.NUM4
        ],
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM4,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 2^4^',
        tokens: [
            tokens.NUM2,
            tokens.POW,
            tokens.NUM4,
            tokens.POW
        ],
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM4,
            renderedTokens.POW,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 2^4^-',
        tokens: [
            tokens.NUM2,
            tokens.POW,
            tokens.NUM4,
            tokens.POW,
            tokens.SUB
        ],
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM4,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NEG,
            '</sup>',
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 2^4^-2',
        tokens: [
            tokens.NUM2,
            tokens.POW,
            tokens.NUM4,
            tokens.POW,
            tokens.SUB,
            tokens.NUM2
        ],
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM4,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NEG,
            renderedTokens.NUM2,
            '</sup>',
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 2^4^+',
        tokens: [
            tokens.NUM2,
            tokens.POW,
            tokens.NUM4,
            tokens.POW,
            tokens.ADD
        ],
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM4,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.POS,
            '</sup>',
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 2^4^+2',
        tokens: [
            tokens.NUM2,
            tokens.POW,
            tokens.NUM4,
            tokens.POW,
            tokens.ADD,
            tokens.NUM2
        ],
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM4,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.POS,
            renderedTokens.NUM2,
            '</sup>',
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: (2^4)',
        tokens: [
            tokens.LPAR,
            tokens.NUM2,
            tokens.POW,
            tokens.NUM4,
            tokens.RPAR
        ],
        variables: {},
        expected: [
            renderedTokens.LPAR,
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM4,
            '</sup>',
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Right exponent: 2^PI',
        tokens: [
            tokens.NUM2,
            tokens.POW,
            tokens.PI
        ],
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.PI,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: (2^PI)',
        tokens: [
            tokens.LPAR,
            tokens.NUM2,
            tokens.POW,
            tokens.PI,
            tokens.RPAR
        ],
        variables: {},
        expected: [
            renderedTokens.LPAR,
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.PI,
            '</sup>',
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Right exponent: 2^2^2',
        tokens: [
            tokens.NUM2,
            tokens.POW,
            tokens.NUM2,
            tokens.POW,
            tokens.NUM2
        ],
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM2,
            '</sup>',
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: (2^2^2)',
        tokens: [
            tokens.LPAR,
            tokens.NUM2,
            tokens.POW,
            tokens.NUM2,
            tokens.POW,
            tokens.NUM2,
            tokens.RPAR
        ],
        variables: {},
        expected: [
            renderedTokens.LPAR,
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM2,
            '</sup>',
            '</sup>',
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Right exponent: (2^2)^2',
        tokens: [
            tokens.LPAR,
            tokens.NUM2,
            tokens.POW,
            tokens.NUM2,
            tokens.RPAR,
            tokens.POW,
            tokens.NUM2
        ],
        variables: {},
        expected: [
            renderedTokens.LPAR,
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM2,
            '</sup>',
            renderedTokens.RPAR,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM2,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 2^(2^2)',
        tokens: [
            tokens.NUM2,
            tokens.POW,
            tokens.LPAR,
            tokens.NUM2,
            tokens.POW,
            tokens.NUM2,
            tokens.RPAR
        ],
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM2,
            '</sup>',
            renderedTokens.RPAR,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 2^cos PI',
        tokens: [
            tokens.NUM2,
            tokens.POW,
            tokens.COS,
            tokens.PI
        ],
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.COS,
            renderedTokens.PI,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 2^cos(PI * 2)',
        tokens: [
            tokens.NUM2,
            tokens.POW,
            tokens.COS,
            tokens.LPAR,
            tokens.PI,
            tokens.MUL,
            tokens.NUM2,
            tokens.RPAR
        ],
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.COS,
            renderedTokens.LPAR,
            renderedTokens.PI,
            renderedTokens.MUL,
            renderedTokens.NUM2,
            renderedTokens.RPAR,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 2^ceil cos(PI * 2)',
        tokens: [
            tokens.NUM2,
            tokens.POW,
            tokens.CEIL,
            tokens.COS,
            tokens.LPAR,
            tokens.PI,
            tokens.MUL,
            tokens.NUM2,
            tokens.RPAR
        ],
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.CEIL,
            renderedTokens.COS,
            renderedTokens.LPAR,
            renderedTokens.PI,
            renderedTokens.MUL,
            renderedTokens.NUM2,
            renderedTokens.RPAR,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 42^123',
        tokens: [
            tokens.NUM4,
            tokens.NUM2,
            tokens.POW,
            tokens.NUM1,
            tokens.NUM2,
            tokens.NUM3
        ],
        variables: {},
        expected: [
            renderedTokens.NUM4,
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM1,
            renderedTokens.NUM2,
            renderedTokens.NUM3,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 5^-1',
        tokens: [
            tokens.NUM5,
            tokens.POW,
            tokens.SUB,
            tokens.NUM1
        ],
        variables: {},
        expected: [
            renderedTokens.NUM5,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NEG,
            renderedTokens.NUM1,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 5^+2',
        tokens: [
            tokens.NUM5,
            tokens.POW,
            tokens.ADD,
            tokens.NUM2
        ],
        variables: {},
        expected: [
            renderedTokens.NUM5,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.POS,
            renderedTokens.NUM2,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: (4+2)^3+5',
        tokens: [
            tokens.LPAR,
            tokens.NUM4,
            tokens.ADD,
            tokens.NUM2,
            tokens.RPAR,
            tokens.POW,
            tokens.NUM3,
            tokens.ADD,
            tokens.NUM5
        ],
        variables: {},
        expected: [
            renderedTokens.LPAR,
            renderedTokens.NUM4,
            renderedTokens.ADD,
            renderedTokens.NUM2,
            renderedTokens.RPAR,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM3,
            '</sup>',
            renderedTokens.ADD,
            renderedTokens.NUM5
        ].join('')
    }, {
        title: 'Right exponent: 4+2^(3+5)',
        tokens: [
            tokens.NUM4,
            tokens.ADD,
            tokens.NUM2,
            tokens.POW,
            tokens.LPAR,
            tokens.NUM3,
            tokens.ADD,
            tokens.NUM5,
            tokens.RPAR
        ],
        variables: {},
        expected: [
            renderedTokens.NUM4,
            renderedTokens.ADD,
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.NUM3,
            renderedTokens.ADD,
            renderedTokens.NUM5,
            renderedTokens.RPAR,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: (4+2)^x+5',
        tokens: [
            tokens.LPAR,
            tokens.NUM4,
            tokens.ADD,
            tokens.NUM2,
            tokens.RPAR,
            tokens.POW,
            tokens.VAR_X,
            tokens.ADD,
            tokens.NUM5
        ],
        variables: {
            x: '3'
        },
        expected: [
            renderedTokens.LPAR,
            renderedTokens.NUM4,
            renderedTokens.ADD,
            renderedTokens.NUM2,
            renderedTokens.RPAR,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.VAR_X,
            '</sup>',
            renderedTokens.ADD,
            renderedTokens.NUM5
        ].join('')
    }, {
        title: 'Right exponent: 4+2^(x+5)',
        tokens: [
            tokens.NUM4,
            tokens.ADD,
            tokens.NUM2,
            tokens.POW,
            tokens.LPAR,
            tokens.VAR_X,
            tokens.ADD,
            tokens.NUM5,
            tokens.RPAR
        ],
        variables: {
            x: '3'
        },
        expected: [
            renderedTokens.NUM4,
            renderedTokens.ADD,
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.VAR_X,
            renderedTokens.ADD,
            renderedTokens.NUM5,
            renderedTokens.RPAR,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: (4+2)^123+5',
        tokens: [
            tokens.LPAR,
            tokens.NUM4,
            tokens.ADD,
            tokens.NUM2,
            tokens.RPAR,
            tokens.POW,
            tokens.NUM1,
            tokens.NUM2,
            tokens.NUM3,
            tokens.ADD,
            tokens.NUM5
        ],
        variables: {},
        expected: [
            renderedTokens.LPAR,
            renderedTokens.NUM4,
            renderedTokens.ADD,
            renderedTokens.NUM2,
            renderedTokens.RPAR,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM1,
            renderedTokens.NUM2,
            renderedTokens.NUM3,
            '</sup>',
            renderedTokens.ADD,
            renderedTokens.NUM5
        ].join('')
    }, {
        title: 'Right exponent: 4+2^(123+5)',
        tokens: [
            tokens.NUM4,
            tokens.ADD,
            tokens.NUM2,
            tokens.POW,
            tokens.LPAR,
            tokens.NUM1,
            tokens.NUM2,
            tokens.NUM3,
            tokens.ADD,
            tokens.NUM5,
            tokens.RPAR
        ],
        variables: {},
        expected: [
            renderedTokens.NUM4,
            renderedTokens.ADD,
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.NUM1,
            renderedTokens.NUM2,
            renderedTokens.NUM3,
            renderedTokens.ADD,
            renderedTokens.NUM5,
            renderedTokens.RPAR,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: (4+2)^(3*4)+5',
        tokens: [
            tokens.LPAR,
            tokens.NUM4,
            tokens.ADD,
            tokens.NUM2,
            tokens.RPAR,
            tokens.POW,
            tokens.LPAR,
            tokens.NUM3,
            tokens.MUL,
            tokens.NUM4,
            tokens.RPAR,
            tokens.ADD,
            tokens.NUM5
        ],
        variables: {},
        expected: [
            renderedTokens.LPAR,
            renderedTokens.NUM4,
            renderedTokens.ADD,
            renderedTokens.NUM2,
            renderedTokens.RPAR,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.NUM3,
            renderedTokens.MUL,
            renderedTokens.NUM4,
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.ADD,
            renderedTokens.NUM5
        ].join('')
    }, {
        title: 'Right exponent: x^((4+2)^(3*4))+5',
        tokens: [
            tokens.VAR_X,
            tokens.POW,
            tokens.LPAR,
            tokens.LPAR,
            tokens.NUM4,
            tokens.ADD,
            tokens.NUM2,
            tokens.RPAR,
            tokens.POW,
            tokens.LPAR,
            tokens.NUM3,
            tokens.MUL,
            tokens.NUM4,
            tokens.RPAR,
            tokens.RPAR,
            tokens.ADD,
            tokens.NUM5
        ],
        variables: {
            x: 0
        },
        expected: [
            renderedTokens.VAR_X,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.LPAR,
            renderedTokens.NUM4,
            renderedTokens.ADD,
            renderedTokens.NUM2,
            renderedTokens.RPAR,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.NUM3,
            renderedTokens.MUL,
            renderedTokens.NUM4,
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.ADD,
            renderedTokens.NUM5
        ].join('')
    }, {
        title: 'Right exponent: (4+2^3)^(ans*3^2)+5',
        tokens: [
            tokens.LPAR,
            tokens.NUM4,
            tokens.ADD,
            tokens.NUM2,
            tokens.POW,
            tokens.NUM3,
            tokens.RPAR,
            tokens.POW,
            tokens.LPAR,
            tokens.ANS,
            tokens.MUL,
            tokens.NUM3,
            tokens.POW,
            tokens.NUM2,
            tokens.RPAR,
            tokens.ADD,
            tokens.NUM5
        ],
        variables: {
            ans: 5
        },
        expected: [
            renderedTokens.LPAR,
            renderedTokens.NUM4,
            renderedTokens.ADD,
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM3,
            '</sup>',
            renderedTokens.RPAR,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.ANS.replace('{{ans}}', '5'),
            renderedTokens.MUL,
            renderedTokens.NUM3,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM2,
            '</sup>',
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.ADD,
            renderedTokens.NUM5
        ].join('')
    }, {
        title: 'Exponent: !3 nthrt 2^3!',
        tokens: [
            tokens.FAC,
            tokens.NUM3,
            tokens.NTHRT,
            tokens.NUM2,
            tokens.POW,
            tokens.NUM3,
            tokens.FAC
        ],
        variables: {
            ans: 5
        },
        expected: [
            renderedTokens.FAC,
            '<sup>',
            renderedTokens.NUM3,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM3,
            renderedTokens.FAC,
            '</sup>'
        ].join('')
    }, {
        title: 'Exponent: (!3) nthrt (2^(3!))',
        tokens: [
            tokens.LPAR,
            tokens.FAC,
            tokens.NUM3,
            tokens.RPAR,
            tokens.NTHRT,
            tokens.LPAR,
            tokens.NUM2,
            tokens.POW,
            tokens.LPAR,
            tokens.NUM3,
            tokens.FAC,
            tokens.RPAR,
            tokens.RPAR
        ],
        variables: {
            ans: 5
        },
        expected: [
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.FAC,
            renderedTokens.NUM3,
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.LPAR,
            renderedTokens.NUM2,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.NUM3,
            renderedTokens.FAC,
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Exponent: 8^8 nthrt 8',
        tokens: [
            tokens.NUM8,
            tokens.POW,
            tokens.NUM8,
            tokens.NTHRT,
            tokens.NUM8
        ],
        variables: {
            ans: 5
        },
        expected: [
            renderedTokens.NUM8,
            renderedTokens.POWEL,
            '<sup>',
            '<sup>',
            renderedTokens.NUM8,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM8,
            '</sup>'
        ].join('')
    }, {
        title: 'Exponent: 8^(8 nthrt 8)',
        tokens: [
            tokens.NUM8,
            tokens.POW,
            tokens.LPAR,
            tokens.NUM8,
            tokens.NTHRT,
            tokens.NUM8,
            tokens.RPAR
        ],
        variables: {
            ans: 5
        },
        expected: [
            renderedTokens.NUM8,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.LPAR,
            '<sup>',
            renderedTokens.NUM8,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM8,
            renderedTokens.RPAR,
            '</sup>'
        ].join('')
    }, {
        title: 'Exponent: (8^8) nthrt 8',
        tokens: [
            tokens.LPAR,
            tokens.NUM8,
            tokens.POW,
            tokens.NUM8,
            tokens.RPAR,
            tokens.NTHRT,
            tokens.NUM8
        ],
        variables: {
            ans: 5
        },
        expected: [
            '<sup>',
            renderedTokens.LPAR,
            renderedTokens.NUM8,
            renderedTokens.POWEL,
            '<sup>',
            renderedTokens.NUM8,
            '</sup>',
            renderedTokens.RPAR,
            '</sup>',
            renderedTokens.NTHRT,
            renderedTokens.NUM8
        ].join('')
    }])
        .test('render', function (data, assert) {
            QUnit.expect(1);

            assert.equal(tokensHelper.render(data.tokens, data.variables), data.expected, 'Should render the tokens properly');
        });

});
