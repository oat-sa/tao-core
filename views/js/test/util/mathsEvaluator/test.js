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
    'util/mathsEvaluator',
    'json!test/util/mathsEvaluator/testCases.json'
], function ($, _, scrollHelper, mathsEvaluatorFactory, testCases) {
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
        .cases(testCases)
        .test('expression ', function (data, assert) {
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
