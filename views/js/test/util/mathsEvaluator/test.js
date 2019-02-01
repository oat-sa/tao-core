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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'ui/scroller',
    'util/mathsEvaluator'
], function ($, _, scrollHelper, mathsEvaluatorFactory) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function (assert) {
        QUnit.expect(3);

        assert.equal(typeof mathsEvaluatorFactory, 'function', "The module exposes a function");
        assert.equal(typeof mathsEvaluatorFactory(), 'function', "The factory produces a function");
        assert.notStrictEqual(mathsEvaluatorFactory(), mathsEvaluatorFactory(), "The factory provides a different function on each call");
    });


    QUnit.module('Behavior');

    QUnit
        .cases([{
            title: 'round-off',
            expression: '.1+.2',
            expected: '0.3'
        }, {
            title: 'round-off exp',
            expression: '(.1+.2)*10^20',
            expected: '30000000000000000000'
        }, {
            title: 'round off root and exp',
            expression: 'nthrt(4, 2)^4',
            expected: '2'
        }, {
            title: 'precision below native data type',
            expression: '2^-100',
            expected: '7.888609052210118e-31'
        }, {
            title: 'internal precision',
            expression: '(3^-300)*10^140',
            expected: '0.000730505658114782'
        }, {
            title: 'unary +',
            expression: '+.2',
            expected: '0.2'
        }, {
            title: 'unary -',
            expression: '-.2',
            expected: '-0.2'
        }, {
            title: 'natural operator precedence',
            expression: '3+4*5-6/2*5',
            expected: '8'
        }, {
            title: 'forced operator precedence',
            expression: '(3+4)*(5-6)/(2*5)',
            expected: '-0.7'
        }, {
            title: 'factorial prefix',
            expression: '!10',
            expected: '3628800'
        }, {
            title: 'factorial suffix',
            expression: '10!',
            expected: '3628800'
        }, {
            title: 'factorial function',
            expression: 'fac(11)',
            expected: '39916800'
        }, {
            title: 'floor',
            expression: 'floor(3.14)',
            expected: '3'
        }, {
            title: 'ceil',
            expression: 'ceil(3.14)',
            expected: '4'
        }, {
            title: 'round',
            expression: 'round(3.14)',
            expected: '3'
        }, {
            title: 'square root',
            expression: 'sqrt(9)',
            expected: '3'
        }, {
            title: 'cube root',
            expression: 'cbrt(27)',
            expected: '3'
        }, {
            title: 'nth root 2',
            expression: 'nthrt(2, 16)',
            expected: '4'
        }, {
            title: 'nth root 3',
            expression: 'nthrt(3, 27)',
            expected: '3'
        }, {
            title: 'negative nth root 3',
            expression: 'nthrt(3, -27)',
            expected: '-3'
        }, {
            title: 'nth root 4',
            expression: 'nthrt(4, 81)',
            expected: '3'
        }, {
            title: 'negative nth root 4',
            expression: 'nthrt(4, -81)',
            expected: 'NaN'
        }, {
            title: 'nth root 2',
            expression: '2 @nthrt 16',
            expected: '4'
        }, {
            title: 'nth root 3',
            expression: '3 @nthrt 27',
            expected: '3'
        }, {
            title: 'negative nth root 3',
            expression: '3 @nthrt -27',
            expected: '-3'
        }, {
            title: 'nth root 4',
            expression: '4 @nthrt 81',
            expected: '3'
        }, {
            title: 'negative nth root 4',
            expression: '4 @nthrt -81',
            expected: 'NaN'
        }, {
            title: 'log 0',
            expression: 'log 0',
            expected: '-Infinity'
        }, {
            title: 'log 1',
            expression: 'log 1',
            expected: '0'
        }, {
            title: 'log 10',
            expression: 'log 10',
            expected: '1'
        }, {
            title: 'ln 0',
            expression: 'ln 0',
            expected: '-Infinity'
        }, {
            title: 'ln 1',
            expression: 'ln 1',
            expected: '0'
        }, {
            title: 'ln e',
            expression: 'ln E',
            expected: '1'
        }])
        .test('arithmetic expression', function (data, assert) {
            var evaluate = mathsEvaluatorFactory(data.config);
            var output = evaluate(data.expression, data.variables);

            QUnit.expect(4);
            if (!_.isBoolean(output.value)) {
                output.value = String(output.value);
            }
            assert.equal(output.value, data.expected, "The expression " + data.expression + " is correctly computed to " + data.expected);
            assert.equal(output.expression, data.expression, "The expression is provided in the output");
            assert.equal(output.variables, data.variables, "The variables are provided in the output");
            assert.notEqual(typeof output.result, 'undefined', "The internal result is provided in the output");
        });

    QUnit
        .cases([{
            title: 'equal: true',
            expression: '3*4==12',
            expected: true
        }, {
            title: 'equal: false',
            expression: '3*4==10',
            expected: false
        }, {
            title: 'not equal: true',
            expression: '3*4!=15',
            expected: true
        }, {
            title: 'not equal: false',
            expression: '3*4!=12',
            expected: false
        }, {
            title: 'greater than: true',
            expression: '3*4>10',
            expected: true
        }, {
            title: 'greater than: false',
            expression: '3*4>20',
            expected: false
        }, {
            title: 'greater or equal than: true',
            expression: '3*4>=10',
            expected: true
        }, {
            title: 'greater or equal than: equal',
            expression: '3*4>=12',
            expected: true
        }, {
            title: 'greater or equal than: false',
            expression: '3*4>=20',
            expected: false
        }, {
            title: 'lesser than: true',
            expression: '3*3<12',
            expected: true
        }, {
            title: 'lesser than: false',
            expression: '4*4<12',
            expected: false
        }, {
            title: 'lesser or equal than: true',
            expression: '3*3<=12',
            expected: true
        }, {
            title: 'lesser or equal than: equal',
            expression: '3*4<=12',
            expected: true
        }, {
            title: 'lesser or equal than: false',
            expression: '4*4<=12',
            expected: false
        }, {
            title: 'or: first condition is true',
            expression: '2+2==4 or 3-1==4',
            expected: true
        }, {
            title: 'or: second condition is true',
            expression: '2+2==3 or 3-1==2',
            expected: true
        }, {
            title: 'or: none is true',
            expression: '2+2==2 or 3-1==4',
            expected: false
        }, {
            title: 'or: all is true',
            expression: '2+2==4 or 3-1==2',
            expected: true
        }, {
            title: 'and: first condition is true',
            expression: '2+2==4 and 3-1==4',
            expected: false
        }, {
            title: 'and: second condition is true',
            expression: '2+2==3 and 3-1==2',
            expected: false
        }, {
            title: 'and: none is true',
            expression: '2+2==2 and 3-1==4',
            expected: false
        }, {
            title: 'and: all is true',
            expression: '2+2==4 and 3-1==2',
            expected: true
        }, {
            title: 'not: true',
            expression: 'not true',
            expected: false
        }, {
            title: 'not: false',
            expression: 'not false',
            expected: true
        }, {
            title: 'pipe',
            expression: '10-6 || sqrt(4)',
            expected: '42'
        }])
        .test('logical expression', function (data, assert) {
            var evaluate = mathsEvaluatorFactory(data.config);
            var output = evaluate(data.expression, data.variables);

            QUnit.expect(4);
            if (!_.isBoolean(output.value)) {
                output.value = String(output.value);
            }
            assert.equal(output.value, data.expected, "The expression " + data.expression + " is correctly computed to " + data.expected);
            assert.equal(output.expression, data.expression, "The expression is provided in the output");
            assert.equal(output.variables, data.variables, "The variables are provided in the output");
            assert.notEqual(typeof output.result, 'undefined', "The internal result is provided in the output");
        });

    QUnit
        .cases([{
            title: '2*a*x+b',
            expression: '2*a*x+b',
            variables: {a: 5, x: 3, b: 15},
            expected: '45'
        }])
        .test('parametric expression', function (data, assert) {
            var evaluate = mathsEvaluatorFactory(data.config);
            var output = evaluate(data.expression, data.variables);

            QUnit.expect(4);
            if (!_.isBoolean(output.value)) {
                output.value = String(output.value);
            }
            assert.equal(output.value, data.expected, "The expression " + data.expression + " is correctly computed to " + data.expected);
            assert.equal(output.expression, data.expression, "The expression is provided in the output");
            assert.equal(output.variables, data.variables, "The variables are provided in the output");
            assert.notEqual(typeof output.result, 'undefined', "The internal result is provided in the output");
        });

    QUnit
        .cases([{
            title: 'PI',
            expression: 'PI',
            config: {degree: false},
            expected: '3.141592653589793'
        }, {
            title: 'cos 0',
            expression: 'cos 0',
            config: {degree: false},
            expected: '1'
        }, {
            title: 'cos 0 + cos 0',
            expression: 'cos 0 + cos 0',
            config: {degree: false},
            expected: '2'
        }, {
            title: 'cos 1',
            expression: 'cos 1',
            config: {degree: false},
            expected: '0.5403023058681398'
        }, {
            title: 'cos (PI/2)',
            expression: 'cos (PI/2)',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'cos (PI/2) + cos (PI/2)',
            expression: 'cos (PI/2) + cos (PI/2)',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'cos PI',
            expression: 'cos PI',
            config: {degree: false},
            expected: '-1'
        }, {
            title: 'sin 0',
            expression: 'sin 0',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'sin 1',
            expression: 'sin 1',
            config: {degree: false},
            expected: '0.8414709848078965'
        }, {
            title: 'sin PI',
            expression: 'sin PI',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'sin (PI/2)',
            expression: 'sin (PI/2)',
            config: {degree: false},
            expected: '1'
        }, {
            title: 'sin (PI*3)*10^20',
            expression: 'sin (PI*3)*10^20',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'tan 0',
            expression: 'tan 0',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'tan 1',
            expression: 'tan 1',
            config: {degree: false},
            expected: '1.5574077246549023'
        }, {
            title: 'tan PI',
            expression: 'tan PI',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'tan (PI/2)',
            expression: 'tan (PI/2)',
            config: {degree: false},
            expected: 'NaN'
        }, {
            title: 'acos -1',
            expression: 'acos -1',
            config: {degree: false},
            expected: '3.141592653589793'
        }, {
            title: 'acos 0',
            expression: 'acos 0',
            config: {degree: false},
            expected: '1.5707963267948966'
        }, {
            title: 'acos 1',
            expression: 'acos 1',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'asin -1',
            expression: 'asin -1',
            config: {degree: false},
            expected: '-1.5707963267948966'
        }, {
            title: 'asin 0',
            expression: 'asin 0',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'asin 1',
            expression: 'asin 1',
            config: {degree: false},
            expected: '1.5707963267948966'
        }, {
            title: 'atan -1',
            expression: 'atan -1',
            config: {degree: false},
            expected: '-0.7853981633974483'
        }, {
            title: 'atan 0',
            expression: 'atan 0',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'atan 1',
            expression: 'atan 1',
            config: {degree: false},
            expected: '0.7853981633974483'
        }, {
            title: 'cosh 0',
            expression: 'cosh 0',
            config: {degree: false},
            expected: '1'
        }, {
            title: 'cosh 1',
            expression: 'cosh 1',
            config: {degree: false},
            expected: '1.5430806348152437'
        }, {
            title: 'cosh PI',
            expression: 'cosh PI',
            config: {degree: false},
            expected: '11.59195327552152'
        }, {
            title: 'sinh 0',
            expression: 'sinh 0',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'sinh 1',
            expression: 'sinh 1',
            config: {degree: false},
            expected: '1.1752011936438014'
        }, {
            title: 'sinh PI',
            expression: 'sinh PI',
            config: {degree: false},
            expected: '11.548739357257748'
        }, {
            title: '(sinh 0)*10^20',
            expression: '(sinh 0)*10^20',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'tanh 0',
            expression: 'tanh 0',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'tanh 1',
            expression: 'tanh 1',
            config: {degree: false},
            expected: '0.7615941559557649'
        }, {
            title: 'tanh PI',
            expression: 'tanh PI',
            config: {degree: false},
            expected: '0.99627207622075'
        }, {
            title: 'acosh 0',
            expression: 'acosh 0',
            config: {degree: false},
            expected: 'NaN'
        }, {
            title: 'acosh 1',
            expression: 'acosh 1',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'acosh 2',
            expression: 'acosh 2',
            config: {degree: false},
            expected: '1.3169578969248168'
        }, {
            title: 'asinh -1',
            expression: 'asinh -1',
            config: {degree: false},
            expected: '-0.881373587019543'
        }, {
            title: 'asinh 0',
            expression: 'asinh 0',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'asinh 1',
            expression: 'asinh 1',
            config: {degree: false},
            expected: '0.881373587019543'
        }, {
            title: 'atanh -1',
            expression: 'atanh -1',
            config: {degree: false},
            expected: '-Infinity'
        }, {
            title: 'atanh 0',
            expression: 'atanh 0',
            config: {degree: false},
            expected: '0'
        }, {
            title: 'atanh 0.5',
            expression: 'atanh 0.5',
            config: {degree: false},
            expected: '0.5493061443340549'
        }, {
            title: 'atanh 1',
            expression: 'atanh 1',
            config: {degree: false},
            expected: 'Infinity'
        }])
        .test('trigo - radian', function (data, assert) {
            var evaluate = mathsEvaluatorFactory(data.config);
            var output = evaluate(data.expression, data.variables);

            QUnit.expect(4);
            if (!_.isBoolean(output.value)) {
                output.value = String(output.value);
            }
            assert.equal(output.value, data.expected, "The expression " + data.expression + " is correctly computed to " + data.expected);
            assert.equal(output.expression, data.expression, "The expression is provided in the output");
            assert.equal(output.variables, data.variables, "The variables are provided in the output");
            assert.notEqual(typeof output.result, 'undefined', "The internal result is provided in the output");
        });

    QUnit
        .cases([{
            title: 'PI',
            expression: 'PI',
            config: {degree: true},
            expected: '3.141592653589793'
        }, {
            title: 'cos 0',
            expression: 'cos 0',
            config: {degree: true},
            expected: '1'
        }, {
            title: 'cos 1',
            expression: 'cos 1',
            config: {degree: true},
            expected: '0.9998476951563913'
        }, {
            title: 'cos PI',
            expression: 'cos PI',
            config: {degree: true},
            expected: '0.9984971498638638'
        }, {
            title: 'cos 90',
            expression: 'cos 90',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'cos 90 + cos 90',
            expression: 'cos 90 + cos 90',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'cos 180',
            expression: 'cos 180',
            config: {degree: true},
            expected: '-1'
        }, {
            title: 'cos 180 + cos 180',
            expression: 'cos 180 + cos 180',
            config: {degree: true},
            expected: '-2'
        }, {
            title: 'cos 0 + cos 30 + cos 45 + cos 60 + cos 90',
            expression: 'cos 0 + cos 30 + cos 45 + cos 60 + cos 90',
            config: {degree: true},
            expected: '3.0731321849709863'
        }, {
            title: 'sin 0',
            expression: 'sin 0',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'sin 1',
            expression: 'sin 1',
            config: {degree: true},
            expected: '0.01745240643728351'
        }, {
            title: 'sin PI',
            expression: 'sin PI',
            config: {degree: true},
            expected: '0.05480366514878953'
        }, {
            title: 'sin 90',
            expression: 'sin 90',
            config: {degree: true},
            expected: '1'
        }, {
            title: 'sin 180',
            expression: 'sin 180',
            config: {degree: true},
            expected: '0'
        }, {
            title: '(sin 720)*10^20',
            expression: '(sin 720)*10^20',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'tan 0',
            expression: 'tan 0',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'tan 1',
            expression: 'tan 1',
            config: {degree: true},
            expected: '0.017455064928217585'
        }, {
            title: 'tan PI',
            expression: 'tan PI',
            config: {degree: true},
            expected: '0.054886150808003326'
        }, {
            title: 'tan 90',
            expression: 'tan 90',
            config: {degree: true},
            expected: 'NaN'
        }, {
            title: 'tan 180',
            expression: 'tan 180',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'acos -1',
            expression: 'acos -1',
            config: {degree: true},
            expected: '180'
        }, {
            title: 'acos 0',
            expression: 'acos 0',
            config: {degree: true},
            expected: '90'
        }, {
            title: 'acos 1',
            expression: 'acos 1',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'asin -1',
            expression: 'asin -1',
            config: {degree: true},
            expected: '-90'
        }, {
            title: 'asin 0',
            expression: 'asin 0',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'asin 1',
            expression: 'asin 1',
            config: {degree: true},
            expected: '90'
        }, {
            title: 'atan -1',
            expression: 'atan -1',
            config: {degree: true},
            expected: '-45'
        }, {
            title: 'atan 0',
            expression: 'atan 0',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'atan 1',
            expression: 'atan 1',
            config: {degree: true},
            expected: '45'
        }, {
            title: 'cosh 0',
            expression: 'cosh 0',
            config: {degree: true},
            expected: '1'
        }, {
            title: 'cosh 1',
            expression: 'cosh 1',
            config: {degree: true},
            expected: '1.5430806348152437'
        }, {
            title: 'cosh PI',
            expression: 'cosh PI',
            config: {degree: true},
            expected: '11.59195327552152'
        }, {
            title: 'sinh 0',
            expression: 'sinh 0',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'sinh 1',
            expression: 'sinh 1',
            config: {degree: true},
            expected: '1.1752011936438014'
        }, {
            title: 'sinh PI',
            expression: 'sinh PI',
            config: {degree: true},
            expected: '11.548739357257748'
        }, {
            title: '(sinh 0)*10^20',
            expression: '(sinh 0)*10^20',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'tanh 0',
            expression: 'tanh 0',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'tanh 1',
            expression: 'tanh 1',
            config: {degree: true},
            expected: '0.7615941559557649'
        }, {
            title: 'tanh PI',
            expression: 'tanh PI',
            config: {degree: true},
            expected: '0.99627207622075'
        }, {
            title: 'acosh 0',
            expression: 'acosh 0',
            config: {degree: true},
            expected: 'NaN'
        }, {
            title: 'acosh 1',
            expression: 'acosh 1',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'acosh 2',
            expression: 'acosh 2',
            config: {degree: true},
            expected: '1.3169578969248168'
        }, {
            title: 'asinh -1',
            expression: 'asinh -1',
            config: {degree: true},
            expected: '-0.881373587019543'
        }, {
            title: 'asinh 0',
            expression: 'asinh 0',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'asinh 1',
            expression: 'asinh 1',
            config: {degree: true},
            expected: '0.881373587019543'
        }, {
            title: 'atanh -1',
            expression: 'atanh -1',
            config: {degree: true},
            expected: '-Infinity'
        }, {
            title: 'atanh 0',
            expression: 'atanh 0',
            config: {degree: true},
            expected: '0'
        }, {
            title: 'atanh 0.5',
            expression: 'atanh 0.5',
            config: {degree: true},
            expected: '0.5493061443340549'
        }, {
            title: 'atanh 1',
            expression: 'atanh 1',
            config: {degree: true},
            expected: 'Infinity'
        }])
        .test('trigo - degree', function (data, assert) {
            var evaluate = mathsEvaluatorFactory(data.config);
            var output = evaluate(data.expression, data.variables);

            QUnit.expect(4);
            if (!_.isBoolean(output.value)) {
                output.value = String(output.value);
            }
            assert.equal(output.value, data.expected, "The expression " + data.expression + " is correctly computed to " + data.expected);
            assert.equal(output.expression, data.expression, "The expression is provided in the output");
            assert.equal(output.variables, data.variables, "The variables are provided in the output");
            assert.notEqual(typeof output.result, 'undefined', "The internal result is provided in the output");
        });

    QUnit.test('expression as object', function (assert) {
        var evaluate = mathsEvaluatorFactory();
        var mathsExpression = {
            expression: '3*x + 1',
            variables: {
                x: 2
            }
        };
        var variables = {
            x: 3
        };

        var output = evaluate(mathsExpression);

        QUnit.expect(8);

        assert.equal(output.value, '7', "The expression " + mathsExpression.expression + " is correctly computed to 7");
        assert.equal(output.expression, mathsExpression.expression, "The expression is provided in the output");
        assert.equal(output.variables, mathsExpression.variables, "The variables are provided in the output");
        assert.notEqual(typeof output.result, 'undefined', "The internal result is provided in the output");


        output = evaluate(mathsExpression, variables);

        assert.equal(output.value, '10', "The expression " + mathsExpression.expression + " is correctly computed to 10");
        assert.equal(output.expression, mathsExpression.expression, "The expression is provided in the output");
        assert.equal(output.variables, variables, "The variables are provided in the output");
        assert.notEqual(typeof output.result, 'undefined', "The internal result is provided in the output");
    });

    /** Visual Test **/

    $.fn.extend({
        /**
         * Inserts a text at the cursor position inside a textbox.
         * Code from: http://stackoverflow.com/questions/946534/insert-text-into-textarea-with-jquery/946556#946556
         * @param {String} myValue
         * @returns {jQuery}
         */
        insertAtCaret: function (myValue) {
            return this.each(function () {
                var sel, startPos, endPos, scrollTop;
                if (document.selection) {
                    //For browsers like Internet Explorer
                    this.focus();
                    sel = document.selection.createRange();
                    sel.text = myValue;
                    this.focus();
                } else if (this.selectionStart || this.selectionStart === '0') {
                    //For browsers like Firefox and Webkit based
                    startPos = this.selectionStart;
                    endPos = this.selectionEnd;
                    scrollTop = this.scrollTop;
                    this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
                    this.focus();
                    this.selectionStart = startPos + myValue.length;
                    this.selectionEnd = startPos + myValue.length;
                    this.scrollTop = scrollTop;
                } else {
                    this.value += myValue;
                    this.focus();
                }
            });
        }
    });

    QUnit.test('Visual test', function (assert) {
        var evaluate = mathsEvaluatorFactory();
        var $container = $('#visual-test');
        var $screen = $container.find('.screen');
        var $input = $container.find('.input input');
        var $keyboard = $container.find('.keyboard');
        var degree = false;

        function setupMathsEvaluator() {
            evaluate = mathsEvaluatorFactory({
                degree: degree
            });
        }

        function processExpression(expr, variables) {
            try {
                return evaluate(expr, variables).value;
            } catch (err) {
                console.log(err);
                return 'Syntax error!';
            }
        }

        function showResult(expression, result) {
            var $expr = $('<p class="expression">' + expression + '</p>');
            var $res = $('<p class="result">' + result + '</p>');
            $screen.append($expr);
            $screen.append($res);
            scrollHelper.scrollTo($expr, $screen);
        }

        function compute() {
            var input = $input.val();
            var parts = input.split('$');
            var expression = (parts.shift() || '').trim();
            var lines = [];
            var variables = _.reduce(parts, function (acc, part) {
                var s = part.split('=');
                var name = (s[0] || '').trim();
                var value = (s[1] || '').trim();
                if (name && value) {
                    value = processExpression(value);
                    acc[name] = value;
                    lines.push(name + '=' + value);
                }
                return acc;
            }, {});
            lines.push(expression);
            showResult(lines.join('<br >'), processExpression(expression, variables));
        }

        function clear() {
            $input.val('');
        }

        $keyboard.find('[data-switch="radian"]').click();

        $keyboard
            .on('change', 'input', function () {
                switch (this.name) {
                    case 'degree':
                        degree = !!parseInt(this.value, 10);
                        setupMathsEvaluator();
                }
            })
            .on('click', 'button', function () {
                switch (this.dataset.action) {
                    case 'compute':
                        compute();
                    case 'clear':
                        clear();
                        break;
                    default:
                        $input.insertAtCaret(this.dataset.operator);
                }
            });

        $input.on('keydown', function (e) {
            switch (e.keyCode) {
                case 13:
                    e.preventDefault();
                    compute();
                    break;

                case 27:
                    e.preventDefault();
                    clear();
                    break;
            }
        });

        QUnit.expect(1);
        assert.ok(true, 'Visual test ready');
    });

});
