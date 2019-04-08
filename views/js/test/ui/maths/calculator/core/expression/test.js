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
    'ui/maths/calculator/core/expression',
    'ui/maths/calculator/core/terms',
    'util/mathsEvaluator'
], function (expressionHelper, registeredTerms, mathsEvaluatorFactory) {
    'use strict';

    var mathsEvaluator = mathsEvaluatorFactory();

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
        EXP10: {
            type: 'EXP10',
            value: 'e'
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
        EXP10: '<span class="term term-digit" data-value="e" data-token="EXP10" data-type="digit">' + registeredTerms.EXP10.label + '</span>',
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

    QUnit.module('Factory');

    QUnit.test('module', function(assert) {
        assert.expect(1);
        assert.equal(typeof expressionHelper, 'object', 'The module exposes an object');
    });

    QUnit.cases.init([
        {title: 'containsError'},
        {title: 'replaceLastResult'},
        {title: 'roundVariable'},
        {title: 'roundLastResultVariable'},
        {title: 'renderSign'},
        {title: 'render'}
    ]).test('API ', function (data, assert) {
        assert.expect(1);
        assert.equal(typeof expressionHelper[data.title], 'function', 'The helper exposes a "' + data.title + '" function');
    });

    QUnit.module('API');

    QUnit.test('containsError', function(assert) {
        assert.expect(12);

        assert.equal(expressionHelper.containsError('3*4'), false, 'Should not contain an error');
        assert.equal(expressionHelper.containsError('NaN'), true, 'Should contain an error');
        assert.equal(expressionHelper.containsError('Infinity'), true, 'Should contain an error');
        assert.equal(expressionHelper.containsError('+Infinity'), true, 'Should contain an error');
        assert.equal(expressionHelper.containsError('-Infinity'), true, 'Should contain an error');
        assert.equal(expressionHelper.containsError('2*NaN'), true, 'Should contain an error');
        assert.equal(expressionHelper.containsError('4-Infinity'), true, 'Should contain an error');
        assert.equal(expressionHelper.containsError(NaN), true, 'Should contain an error');
        assert.equal(expressionHelper.containsError(10), false, 'Should not contain an error');
        assert.equal(expressionHelper.containsError({value: NaN}), true, 'Should contain an error');
        assert.equal(expressionHelper.containsError({value: 'NaN'}), true, 'Should contain an error');
        assert.equal(expressionHelper.containsError({value: '0'}), false, 'Should not contain an error');
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
        expected: '0'
    }])
        .test('replaceLastResult', function(data, assert) {
            assert.expect(1);

            assert.equal(expressionHelper.replaceLastResult(data.expression, data.value), data.expected, 'Should replace the last result variable from ' + data.expression + ' to ' + data.expected);

        });

    QUnit.cases.init([{
        title: 'Undefined variable',
        expected: ''
    }, {
        title: 'Empty variable',
        variable: {},
        expected: ''
    }, {
        title: 'Native number',
        variable: 5,
        decimalDigits: 8,
        expected: '5'
    }, {
        title: 'String value',
        variable: '3.14159265358979323846264',
        decimalDigits: 8,
        expected: '3.14159265358979323846264'
    }, {
        title: 'Regular integer result',
        variable: mathsEvaluator('3*4'),
        expected: '12'
    }, {
        title: 'Regular decimal result',
        variable: mathsEvaluator('PI'),
        expected: '3.14159~'
    }, {
        title: '5 significant digits',
        variable: mathsEvaluator('PI'),
        decimalDigits: 3,
        expected: '3.142~'
    }, {
        title: '10 significant digits',
        variable: mathsEvaluator('PI'),
        decimalDigits: 10,
        expected: '3.1415926536~'
    }, {
        title: 'Irrational 1/3',
        variable: mathsEvaluator('1/3'),
        expected: '0.33333~'
    }, {
        title: 'Irrational 2/3',
        variable:mathsEvaluator('2/3'),
        expected: '0.66667~'
    }, {
        title: 'Exponential integer',
        variable: mathsEvaluator('123e50'),
        expected: '1.23e+52'
    }, {
        title: 'Exponential decimal',
        variable: mathsEvaluator('PI*10^50'),
        expected: '3.14159e+50~'
    }])
        .test('roundLastResultVariable', function(data, assert) {
            assert.expect(1);

            assert.equal(expressionHelper.roundVariable(data.variable, data.decimalDigits), data.expected, 'Should round the last result variable');
        });

    QUnit.cases.init([{
        title: 'Undefined list of variables'
    }, {
        title: 'Empty list of variables',
        variables: {},
        expected: {}
    }, {
        title: 'Native number',
        variables: {
            ans: 5
        },
        decimalDigits: 8,
        expected: {
            ans: '5'
        }
    }, {
        title: 'String value',
        variables: {
            ans: '3.14159265358979323846264'
        },
        decimalDigits: 8,
        expected: {
            ans: '3.14159265358979323846264'
        }
    }, {
        title: 'Regular integer result',
        variables: {
            ans: mathsEvaluator('3*4')
        },
        expected: {
            ans: '12'
        }
    }, {
        title: 'Regular decimal result',
        variables: {
            ans: mathsEvaluator('PI')
        },
        expected: {
            ans: '3.14159~'
        }
    }, {
        title: '5 significant digits',
        variables: {
            ans: mathsEvaluator('PI')
        },
        decimalDigits: 3,
        expected: {
            ans: '3.142~'
        }
    }, {
        title: '10 significant digits',
        variables: {
            ans: mathsEvaluator('PI')
        },
        decimalDigits: 10,
        expected: {
            ans: '3.1415926536~'
        }
    }, {
        title: 'Irrational 1/3',
        variables: {
            ans: mathsEvaluator('1/3')
        },
        expected: {
            ans: '0.33333~'
        }
    }, {
        title: 'Irrational 2/3',
        variables: {
            ans: mathsEvaluator('2/3')
        },
        expected: {
            ans: '0.66667~'
        }
    }, {
        title: 'Exponential integer',
        variables: {
            ans: mathsEvaluator('123e50')
        },
        expected: {
            ans: '1.23e+52'
        }
    }, {
        title: 'Exponential decimal',
        variables: {
            ans: mathsEvaluator('PI*10^50')
        },
        expected: {
            ans: '3.14159e+50~'
        }
    }])
        .test('roundLastResultVariable', function(data, assert) {
            assert.expect(1);

            assert.deepEqual(expressionHelper.roundLastResultVariable(data.variables, data.decimalDigits), data.expected, 'Should round the last result variable');
        });

    QUnit.test('renderSign', function (assert) {
        assert.expect(6);

        assert.equal(expressionHelper.renderSign(), '', 'Missing value');
        assert.equal(expressionHelper.renderSign(''), '', 'Empty value');
        assert.equal(expressionHelper.renderSign('42'), '42', 'Simple value');
        assert.equal(expressionHelper.renderSign('-42'), registeredTerms.NEG.label + '42', 'Negative value');
        assert.equal(expressionHelper.renderSign('+42'), registeredTerms.POS.label + '42', 'Positive value');
        assert.equal(expressionHelper.renderSign('3-4+2'), '3' + registeredTerms.NEG.label + '4' + registeredTerms.POS.label + '2', 'Simple expression');
    });

    QUnit.cases.init([{
        title: 'Undefined list',
        expected: ''
    }, {
        title: 'Void list',
        tokens: [],
        variables: {},
        expected: ''
    }, {
        title: 'Simple number',
        expression: '42',
        variables: {},
        expected: [
            renderedTokens.NUM4,
            renderedTokens.NUM2
        ].join('')
    }, {
        title: 'Simple expression',
        expression: '40+2',
        variables: {},
        expected: [
            renderedTokens.NUM4,
            renderedTokens.NUM0,
            renderedTokens.ADD,
            renderedTokens.NUM2
        ].join('')
    }, {
        title: 'Negative value',
        expression: '-42',
        variables: {},
        expected: [
            renderedTokens.NEG,
            renderedTokens.NUM4,
            renderedTokens.NUM2
        ].join('')
    }, {
        title: 'Negative expression',
        expression: '-(4+2)',
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
        expression: '+42',
        variables: {},
        expected: [
            renderedTokens.POS,
            renderedTokens.NUM4,
            renderedTokens.NUM2
        ].join('')
    }, {
        title: 'Explicit positive expression',
        expression: '+(4+2)',
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
        expression: registeredTerms.ANS,
        variables: {},
        expected: renderedTokens.VAR_ANS
    }, {
        title: 'Last result, positive value',
        expression: registeredTerms.ANS,
        variables: {
            ans: '42'
        },
        expected: renderedTokens.ANS.replace('{{ans}}', renderedTokens.NUM4 + renderedTokens.NUM2)
    }, {
        title: 'Last result, negative value',
        expression: registeredTerms.ANS,
        variables: {
            ans: '-42'
        },
        expected: renderedTokens.ANS.replace('{{ans}}', renderedTokens.NEG + renderedTokens.NUM4 + renderedTokens.NUM2)
    }, {
        title: 'Last result, explicit positive value',
        expression: registeredTerms.ANS,
        variables: {
            ans: '+42'
        },
        expected: renderedTokens.ANS.replace('{{ans}}', renderedTokens.POS + renderedTokens.NUM4 + renderedTokens.NUM2)
    }, {
        title: 'Last result, mathsExpression',
        expression: registeredTerms.ANS,
        variables: {
            ans: {
                expression: '40+2',
                value: 42
            }
        },
        expected: renderedTokens.ANS.replace('{{ans}}', renderedTokens.NUM4 + renderedTokens.NUM2)
    }, {
        title: 'Last result, mathsExpression with tokens',
        expression: registeredTerms.ANS,
        variables: {
            ans: {
                expression: '40+2',
                value: 42,
                tokens: [{
                    type: 'NUM4',
                    value: '4'
                }, {
                    type: 'NUM2',
                    value: '2'
                }]
            }
        },
        expected: renderedTokens.ANS.replace('{{ans}}', renderedTokens.NUM4 + renderedTokens.NUM2)
    }, {
        title: 'Expression with variables',
        expression: '40+2-x*ans*y',
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
            renderedTokens.ANS.replace('{{ans}}', renderedTokens.NUM5),
            renderedTokens.MUL,
            renderedTokens.UNKNOWN_Y
        ].join('')
    }, {
        title: 'Left exponent: nthrt',
        expression: '@nthrt',
        variables: {},
        expected: [
            renderedTokens.NTHRT
        ].join('')
    }, {
        title: 'Left exponent: 4 nthrt',
        expression: '4 @nthrt',
        variables: {},
        expected: [
            '<sup>',
            renderedTokens.NUM4,
            '</sup>',
            renderedTokens.NTHRT
        ].join('')
    }, {
        title: 'Left exponent: nthrt nthrt',
        expression: '@nthrt @nthrt',
        variables: {},
        expected: [
            '<sup>',
            renderedTokens.NTHRT,
            '</sup>',
            renderedTokens.NTHRT
        ].join('')
    }, {
        title: 'Left exponent: nthrt 16',
        expression: '@nthrt 16',
        variables: {},
        expected: [
            renderedTokens.NTHRT,
            renderedTokens.NUM1,
            renderedTokens.NUM6
        ].join('')
    }, {
        title: 'Left exponent: 4 nthrt 16',
        expression: '4 @nthrt 16',
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
        expression: '-4 @nthrt 16',
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
        expression: '(4 @nthrt 16)',
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
        expression: '(5+4) @nthrt 16',
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
        expression: '-(5+4) @nthrt 16',
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
        expression: '((5+4*(2-x)) @nthrt 16)',
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
        expression: '5+4 @nthrt 16',
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
        expression: '5+(4 @nthrt 16)',
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
        expression: '114 @nthrt (ans*3)',
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
            renderedTokens.ANS.replace('{{ans}}', renderedTokens.NUM5),
            renderedTokens.MUL,
            renderedTokens.NUM3,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Left exponent: (5+114) nthrt (ans*3)',
        expression: '(5+114) @nthrt (ans*3)',
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
            renderedTokens.ANS.replace('{{ans}}', renderedTokens.NUM5),
            renderedTokens.MUL,
            renderedTokens.NUM3,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Left exponent: 5+114 nthrt (ans*3)',
        expression: '5+114 @nthrt (ans*3)',
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
            renderedTokens.ANS.replace('{{ans}}', renderedTokens.NUM5),
            renderedTokens.MUL,
            renderedTokens.NUM3,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Left exponent: 3*(4+2)nthrt(ans*3)',
        expression: '3*(4+2)@nthrt(ans*3)',
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
            renderedTokens.ANS.replace('{{ans}}', renderedTokens.NUM5),
            renderedTokens.MUL,
            renderedTokens.NUM3,
            renderedTokens.RPAR
        ].join('')
    }, {
        title: 'Left exponent: ceil cos PI nthrt 4',
        expression: 'ceil cos PI @nthrt 4',
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
        expression: 'cos PI @nthrt 4',
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
        expression: '(cos PI) @nthrt 4',
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
        expression: 'cos (PI) @nthrt 4',
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
        expression: 'PI @nthrt PI @nthrt 4',
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
        expression: 'PI @nthrt (PI+4) @nthrt 4',
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
        expression: '(PI @nthrt PI) @nthrt 4',
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
        expression: 'PI @nthrt (PI @nthrt 4)',
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
        expression: '10 @nthrt PI @nthrt 4',
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
        expression: '5+10 @nthrt PI @nthrt 4',
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
        expression: '5+10 @nthrt cos PI',
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
        expression: '^',
        variables: {},
        expected: [
            renderedTokens.POW
        ].join('')
    }, {
        title: 'Right exponent: 2^',
        expression: '2^',
        variables: {},
        expected: [
            renderedTokens.NUM2,
            renderedTokens.POW
        ].join('')
    }, {
        title: 'Right exponent: 2^4',
        expression: '2^4',
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
        expression: '2^4^',
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
        expression: '2^4^-',
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
        expression: '2^4^-2',
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
        expression: '2^4^+',
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
        expression: '2^4^+2',
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
        expression: '(2^4)',
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
        expression: '2^PI',
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
        expression: '(2^PI)',
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
        expression: '2^2^2',
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
        expression: '(2^2^2)',
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
        expression: '(2^2)^2',
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
        expression: '2^(2^2)',
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
        expression: '2^cos PI',
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
        expression: '2^cos(PI * 2)',
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
        expression: '2^ceil cos(PI * 2)',
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
        expression: '42^123',
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
        expression: '5^-1',
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
        expression: '5^+2',
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
        expression: '(4+2)^3+5',
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
        expression: '4+2^(3+5)',
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
        expression: '(4+2)^x+5',
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
        expression: '4+2^(x+5)',
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
        expression: '(4+2)^123+5',
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
        expression: '4+2^(123+5)',
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
        expression: '(4+2)^(3*4)+5',
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
        expression: 'x^((4+2)^(3*4))+5',
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
        expression: '(4+2^3)^(ans*3^2)+5',
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
            renderedTokens.ANS.replace('{{ans}}', renderedTokens.NUM5),
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
        title: 'Right exponent: 5e10',
        expression: '5e10',
        variables: {},
        expected: [
            renderedTokens.NUM5,
            renderedTokens.EXP10,
            '<sup>',
            renderedTokens.NUM1,
            renderedTokens.NUM0,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 5e+10',
        expression: '5e+10',
        variables: {},
        expected: [
            renderedTokens.NUM5,
            renderedTokens.EXP10,
            '<sup>',
            renderedTokens.POS,
            renderedTokens.NUM1,
            renderedTokens.NUM0,
            '</sup>'
        ].join('')
    }, {
        title: 'Right exponent: 5e-10',
        expression: '5e-10',
        variables: {},
        expected: [
            renderedTokens.NUM5,
            renderedTokens.EXP10,
            '<sup>',
            renderedTokens.NEG,
            renderedTokens.NUM1,
            renderedTokens.NUM0,
            '</sup>'
        ].join('')
    }, {
        title: 'Exponent: !3 nthrt 2^3!',
        expression: '!3 @nthrt 2^3!',
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
        expression: '(!3) @nthrt (2^(3!))',
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
        expression: '8^8 @nthrt 8',
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
        expression: '8^(8 @nthrt 8)',
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
        expression: '(8^8) @nthrt 8',
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
    }, {
        title: 'Tokenized expression',
        expression: [
            tokens.NUM5,
            tokens.ADD,
            tokens.NUM1,
            tokens.NUM0,
            tokens.NTHRT,
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
            renderedTokens.NUM5,
            renderedTokens.ADD,
            '<sup>',
            renderedTokens.NUM1,
            renderedTokens.NUM0,
            '</sup>',
            renderedTokens.NTHRT,
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
            renderedTokens.ANS.replace('{{ans}}', renderedTokens.NUM5),
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
    }])
        .test('render', function (data, assert) {
            assert.expect(1);

            assert.equal(expressionHelper.render(data.expression, data.variables), data.expected, 'Should render the tokens properly');
        });

});
