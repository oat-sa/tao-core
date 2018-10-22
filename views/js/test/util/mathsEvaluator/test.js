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
    'util/mathsEvaluator'
], function ($, mathsEvaluatorFactory) {
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
            expression: 'nthrt(2, 4)^4',
            expected: '2'
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
            expression: 'nthrt(16, 2)',
            expected: '4'
        }, {
            title: 'nth root 3',
            expression: 'nthrt(27, 3)',
            expected: '3'
        }, {
            title: 'negative nth root 3',
            expression: 'nthrt(-27, 3)',
            expected: '-3'
        }, {
            title: 'nth root 4',
            expression: 'nthrt(81, 4)',
            expected: '3'
        }, {
            title: 'negative nth root 4',
            expression: 'nthrt(-81, 4)',
            expected: 'NaN'
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

    /** Visual Test **/

    $.fn.extend({
        /**
         * Inserts a text at the cursor position inside a textbox.
         * Code from: http://stackoverflow.com/questions/946534/insert-text-into-textarea-with-jquery/946556#946556
         * @param {String} myValue
         * @returns {jQuery}
         */
        insertAtCaret : function(myValue) {
            return this.each(function(i) {
                var sel, startPos, endPos, scrollTop;
                if (document.selection) {
                    //For browsers like Internet Explorer
                    this.focus();
                    sel = document.selection.createRange();
                    sel.text = myValue;
                    this.focus();
                } else if (this.selectionStart || this.selectionStart == '0') {
                    //For browsers like Firefox and Webkit based
                    startPos = this.selectionStart;
                    endPos = this.selectionEnd;
                    scrollTop = this.scrollTop;
                    this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos,this.value.length);
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

    QUnit.test('Visual test', function(assert) {
        var evaluate = mathsEvaluatorFactory();
        var $container = $('#visual-test');
        var $screen = $container.find('.screen');
        var $input = $container.find('.input input');
        var $calc = $container.find('.input button');
        var $keyboard = $container.find('.keyboard');

        function compute() {
            var expression = $input.val();
            var result = evaluate(expression);
            $screen.append('<p class="expression">' + expression + '</p>');
            $screen.append('<p class="result">' + result + '</p>');
        }

        $keyboard.on('click', 'button', function() {
            $input.insertAtCaret(this.dataset.operator);
        });

        $calc.on('click', function() {
            compute();
        });
        $input.on('keydown', function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                compute();
            }
        });

        assert.ok(true, 'Visual test ready');
    })

});
