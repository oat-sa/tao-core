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
    'util/mathsEvaluator'
], function (mathsEvaluatorFactory) {
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
        }])
        .test('arithmetic expression', function (data, assert) {
            var evaluate = mathsEvaluatorFactory();
            QUnit.expect(1);
            assert.equal(evaluate(data.expression), data.expected, "The expression " + data.expression + " is correctly computed");
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
            title: 'pipe',
            expression: '10-6 || sqrt(4)',
            expected: '42'
        }])
        .test('logical expression', function (data, assert) {
            var evaluate = mathsEvaluatorFactory();
            QUnit.expect(1);
            assert.equal(evaluate(data.expression), data.expected, "The expression " + data.expression + " is correctly computed");
        });

    QUnit
        .cases([{
            title: '2*a*x+b',
            expression: '2*a*x+b',
            variables: {a:5, x:3, b:15},
            expected: '45'
        }])
        .test('parametric expression', function (data, assert) {
            var evaluate = mathsEvaluatorFactory();
            QUnit.expect(1);
            assert.equal(evaluate(data.expression, data.variables), data.expected, "The expression " + data.expression + " is correctly computed");
        });


});
