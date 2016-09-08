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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define(['lib/gamp/gamp'], function(gamp){
    'use strict';

    QUnit.module('gamp');

    QUnit.test('module', function (assert) {
        QUnit.expect(8);
        assert.equal(typeof gamp, 'function', 'The gamp module exposes a function');
        assert.equal(typeof gamp.normalize, 'function', 'The gamp API exposes the normalize() function');
        assert.equal(typeof gamp.round, 'function', 'The gamp API exposes the round() function');
        assert.equal(typeof gamp.add, 'function', 'The gamp API exposes the add() function');
        assert.equal(typeof gamp.sub, 'function', 'The gamp API exposes the sub() function');
        assert.equal(typeof gamp.mul, 'function', 'The gamp API exposes the mul() function');
        assert.equal(typeof gamp.div, 'function', 'The gamp API exposes the div() function');
        assert.equal(typeof gamp.pow, 'function', 'The gamp API exposes the div() function');
    });

    QUnit.test('gamp', function (assert) {
        var checks = [
            {numbers: [42], precision: 1},
            {numbers: [0.1], precision: 10},
            {numbers: [0.123], precision: 1000},
            {numbers: [1.333339, 42.4242], precision: 1000000},
            {numbers: [18.3332, 874671.85302617], precision: 100000000},
            {numbers: [42, 0.1, 0.123, 18], precision: 1000}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            assert.equal(gamp.apply(gamp, check.numbers), check.precision, 'The approached decimal precision for ' + check.numbers.join(', ') + ' is ' + check.precision);
        });
    });

    QUnit.test('normalize', function (assert) {
        var checks = [
            {value: 42, factor: 10, result: 420},
            {value: 4.2, factor: 10, result: 42},
            {value: 3.1415, factor: 10000, result: 31415},
            {value: 3.1415, factor: 1000000, result: 3141500}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            assert.equal(gamp.normalize(check.value, check.factor), check.result, 'The normalized value of ' + check.value + ' by ' + check.factor + ' is ' + check.result);
        });
    });

    QUnit.test('round', function (assert) {
        var checks = [
            {value: 42, result: 42},
            {value: 5.0000000000000002, result: 5},
            {value: 2.9999999999999996, result: 3},
            {value: 2.999999999999999, result: 2.999999999999999},
            {value: 9.9999999999999996, result: 10},
            {value: 9.999999999999999, result: 9.999999999999999},
            {value: 1.2345678, precision: 6, result: 1.23457},
            {value: 11.38, precision: 3, result: 11.4}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            assert.equal(gamp.round(check.value, check.precision), check.result, 'The formated value of ' + check.value + ' is ' + check.result);
        });
    });


    QUnit.module('simple');

    QUnit.test('add', function (assert) {
        var checks = [
            {left: 3, right: 4, result: 7},
            {left: 0.1, right: 0.2, result: 0.3},
            {left: 4.52, right: 4.49, result: 9.01},
            {left: 76.65, right: 38.45, result: 115.1},
            {left: -5.63, right: -67.22, result: -72.85}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            assert.equal(gamp.add(check.left, check.right), check.result, 'The result of ' + check.left + ' + ' + check.right + ' is ' + check.result);
        });
    });

    QUnit.test('sub', function (assert) {
        var checks = [
            {left: 17, right: 11, result: 6},
            {left: 0.1, right: 0.1, result: 0},
            {left: 4.52, right: 4.49, result: 0.03},
            {left: 10.21, right: 10.2, result: 0.01},
            {left: 1, right: 1.13, result: -0.13}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            assert.equal(gamp.sub(check.left, check.right), check.result, 'The result of ' + check.left + ' - ' + check.right + ' is ' + check.result);
        });
    });

    QUnit.test('mul', function (assert) {
        var checks = [
            {left: 6, right: 7, result: 42},
            {left: 0.1, right: 0.2, result: 0.02},
            {left: 1.11, right: 5, result: 5.55},
            {left: 10, right: 2332226616, result: 23322266160},
            {left: 123456789.9876543, right: 987654321.123456, result: 1.21932632103338e+17},
            {left: 2.23606797749979, right: 2.23606797749979, result: 5}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            assert.equal(gamp.mul(check.left, check.right), check.result, 'The result of ' + check.left + ' x ' + check.right + ' is ' + check.result);
        });
    });

    QUnit.test('div', function (assert) {
        var checks = [
            {left: 77, right: 7, result: 11},
            {left: 0.1, right: 0.2, result: 0.5},
            {left: 1.11, right: 5, result: 0.222},
            {left: 123.456, right: 3.14, result: 39.3171974522293},
            {left: 10, right: 3, result: 3.333333333333333}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            assert.equal(gamp.div(check.left, check.right), check.result, 'The result of ' + check.left + ' / ' + check.right + ' is ' + check.result);
        });
    });

    QUnit.test('pow', function (assert) {
        var checks = [
            {left: 2, right: 4, result: 16},
            {left: 10, right: 3, result: 1000},
            {left: 3.14, right: 3, result: 30.959144},
            {left: 3.14, right: 1.2, result: 3.94744036460688},
            {left: 2.2, right: 1, result: 2.2},
            {left: 2.2, right: 0, result: 1}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            assert.equal(gamp.pow(check.left, check.right), check.result, 'The result of ' + check.left + ' ^ ' + check.right + ' is ' + check.result);
        });
    });


    QUnit.module('reciprocal');

    QUnit.test('add', function (assert) {
        var checks = [
            {left: 3, right: 4},
            {left: 0.1, right: 0.2},
            {left: 4.52, right: 4.49},
            {left: 76.65, right: 38.45},
            {left: -5.63, right: -67.22},
            {left: 17, right: 11},
            {left: 0.1, right: 0.1},
            {left: 10.21, right: 10.2},
            {left: 1, right: 1.13}
        ];

        QUnit.expect(checks.length * 2);
        checks.forEach(function (check) {
            var result = gamp.add(check.left, check.right);
            assert.equal(gamp.sub(result, check.right), check.left, 'The result of ' + check.left + ' + ' + check.right + ' should be reversed to the left operand');
            assert.equal(gamp.sub(result, check.left), check.right, 'The result of ' + check.left + ' + ' + check.right + ' should be reversed to the right operand');
        });
    });

    QUnit.test('sub', function (assert) {
        var checks = [
            {left: 3, right: 4},
            {left: 0.1, right: 0.2},
            {left: 4.52, right: 4.49},
            {left: 76.65, right: 38.45},
            {left: -5.63, right: -67.22},
            {left: 17, right: 11},
            {left: 0.1, right: 0.1},
            {left: 10.21, right: 10.2},
            {left: 1, right: 1.13}
        ];

        QUnit.expect(checks.length * 2);
        checks.forEach(function (check) {
            var result = gamp.sub(check.left, check.right);
            assert.equal(gamp.add(result, check.right), check.left, 'The result of ' + check.left + ' - ' + check.right + ' should be reversed to the left operand');
            assert.equal(gamp.sub(check.left, result), check.right, 'The result of ' + check.left + ' - ' + check.right + ' should be reversed to the right operand');
        });
    });

    QUnit.test('mul', function (assert) {
        var checks = [
            {left: 6, right: 7},
            {left: 0.1, right: 0.2},
            {left: 1.11, right: 5},
            {left: 10, right: 2332226616},
            {left: 12345, right: 54321},
            {left: 2.23606797749, right: 2.23606797749}
        ];

        QUnit.expect(checks.length * 2);
        checks.forEach(function (check) {
            var result = gamp.mul(check.left, check.right);
            assert.equal(gamp.div(result, check.right), check.left, 'The result of ' + check.left + ' * ' + check.right + ' should be reversed to the left operand');
            assert.equal(gamp.div(result, check.left), check.right, 'The result of ' + check.left + ' * ' + check.right + ' should be reversed to the right operand');
        });
    });

    QUnit.test('div', function (assert) {
        var checks = [
            {left: 77, right: 7},
            {left: 0.1, right: 0.2},
            {left: 1.11, right: 5},
            {left: 123.456, right: 3.14},
            {left: 10, right: 3}
        ];

        QUnit.expect(checks.length * 2);
        checks.forEach(function (check) {
            var result = gamp.div(check.left, check.right);
            assert.equal(gamp.round(result * check.right), check.left, 'The result of ' + check.left + ' / ' + check.right + ' should be reversed to the left operand');
            assert.equal(gamp.round(check.left / result), check.right, 'The result of ' + check.left + ' / ' + check.right + ' should be reversed to the right operand');
        });
    });

    QUnit.test('pow', function (assert) {
        var checks = [1, 2, 3, 4, 5];

        QUnit.expect(checks.length * 2);
        checks.forEach(function (value) {
            var result = Math.sqrt(value);
            assert.equal(Math.sqrt(gamp.pow(value, 2)), value, 'The result of ' + value + ' ^ 2 should be reversed to the original value');
            assert.equal(gamp.pow(result, 2), value, 'The result of sqrt(' + value + ') should be reversed to the original value');
        });
    });


    QUnit.module('cumulative');

    QUnit.test('add', function (assert) {
        var checks = [
            {operands: [3, 4, 5, 6], result: 18},
            {operands: [4.52, 4.49, 4.45, 4.51, 3.26, 4.38], result: 25.61},
            {operands: [3, 3, 3, 3], result: 12}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            var result = 0;
            check.operands.forEach(function(operand) {
                result = gamp.add(result, operand);
            });

            assert.equal(result, check.result, 'The result of ' + check.operands.join(' + ') + ' is ' + check.result);
        });
    });

    QUnit.test('sub', function (assert) {
        var checks = [
            {operands: [3, 4, 5, 6], result: -12},
            {operands: [4.52, 4.49, 4.45, 4.51, 3.26, 4.38], result: -16.57},
            {operands: [3, 3, 3, 3], result: -6}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            var i, len, result = check.operands[0];
            for(i = 1, len = check.operands.length; i < len; i++) {
                result = gamp.sub(result, check.operands[i]);
            }

            assert.equal(result, check.result, 'The result of ' + check.operands.join(' - ') + ' is ' + check.result);
        });
    });

    QUnit.test('mul', function (assert) {
        var checks = [
            {operands: [3, 4, 5, 6], result: 360},
            {operands: [4.52, 4.49, 4.45, 4.51, 3.26, 4.38], result: 5815.84788942168},
            {operands: [10, 3, 3, 3], result: 270}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            var result = 1;
            check.operands.forEach(function(operand) {
                result = gamp.mul(result, operand);
            });

            assert.equal(result, check.result, 'The result of ' + check.operands.join(' * ') + ' is ' + check.result);
        });
    });

    QUnit.test('div', function (assert) {
        var checks = [
            {operands: [3, 4, 5, 6], result: 0.025},
            {operands: [4.52, 4.49, 4.45, 4.51, 3.26, 4.38], result: 0.003512884172428308},
            {operands: [10, 3, 3, 3], result: 0.3703703703703703},
            {operands: [3, 3, 3, 3], result: 0.1111111111111111}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            var i, len, result = check.operands[0];
            for(i = 1, len = check.operands.length; i < len; i++) {
                result = gamp.div(result, check.operands[i]);
            }

            assert.equal(result, check.result, 'The result of ' + check.operands.join(' / ') + ' is ' + check.result);
        });
    });

    QUnit.test('pow', function (assert) {
        var checks = [
            {operands: [3, 4, 5, 6], result: 1.79701029991443e57},
            {operands: [4.52, 2, 4], result: 174223.798626316},
            {operands: [10, 3, 3, 3], result: 1e27},
            {operands: [3, 3, 3, 3], result: 7625597484987}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            var i, len, result = check.operands[0];
            for(i = 1, len = check.operands.length; i < len; i++) {
                result = gamp.pow(result, check.operands[i]);
            }

            assert.equal(result, check.result, 'The result of ' + check.operands.join(' ^ ') + ' is ' + check.result);
        });
    });


    QUnit.module('complex');

    QUnit.test('expression', function (assert) {
        var checks = [
            {expression: [3, '+', 4, '-', 5, '*', 6, '/', 7], result: 1.714285714285714},
            {expression: [1.11, '*', 5, '/', 5, '*', 5, '/', 5], result: 1.11},
            {expression: [5, '^', 2, '/', 5, '^', 2, '/', 5], result: 5}
        ];

        QUnit.expect(checks.length);
        checks.forEach(function (check) {
            var expression = check.expression.slice();
            var result = expression.shift();
            var operator, operand;
            while(expression.length) {
                operator = expression.shift();
                operand = expression.shift();
                switch(operator) {
                    case '+': result = gamp.add(result, operand); break;
                    case '-': result = gamp.sub(result, operand); break;
                    case '*': result = gamp.mul(result, operand); break;
                    case '/': result = gamp.div(result, operand); break;
                    case '^': result = gamp.pow(result, operand); break;
                }
            }

            assert.equal(result, check.result, 'The result of ' + check.expression.join(' ') + ' is ' + check.result);
        });
    });
});
