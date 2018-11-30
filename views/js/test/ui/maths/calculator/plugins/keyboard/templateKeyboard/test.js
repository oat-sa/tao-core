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
 * Copyright (c) 2018 Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/promise',
    'ui/maths/calculator/core/board',
    'ui/maths/calculator/plugins/keyboard/templateKeyboard/templateKeyboard'
], function ($, _, Promise, calculatorBoardFactory, templateKeyboardPluginFactory) {
    'use strict';

    QUnit.module('module');

    QUnit.test('templateKeyboard', function (assert) {
        var calculator = calculatorBoardFactory();

        QUnit.expect(3);

        assert.equal(typeof templateKeyboardPluginFactory, 'function', "The plugin module exposes a function");
        assert.equal(typeof templateKeyboardPluginFactory(calculator), 'object', "The plugin factory produces an instance");
        assert.notStrictEqual(templateKeyboardPluginFactory(calculator), templateKeyboardPluginFactory(calculator), "The plugin factory provides a different instance on each call");
    });

    QUnit.module('api');

    QUnit.cases([
        {title: 'install'},
        {title: 'init'},
        {title: 'render'},
        {title: 'destroy'},
        {title: 'trigger'},
        {title: 'getCalculator'},
        {title: 'getAreaBroker'},
        {title: 'getConfig'},
        {title: 'setConfig'},
        {title: 'getState'},
        {title: 'setState'},
        {title: 'show'},
        {title: 'hide'},
        {title: 'enable'},
        {title: 'disable'}
    ]).test('plugin API ', function (data, assert) {
        var calculator = calculatorBoardFactory();
        var plugin = templateKeyboardPluginFactory(calculator);
        QUnit.expect(1);
        assert.equal(typeof plugin[data.title], 'function', 'The plugin instances expose a "' + data.title + '" function');
    });

    QUnit.module('behavior');

    QUnit.asyncTest('install', function (assert) {
        var $container = $('#fixture-install');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = templateKeyboardPluginFactory(calculator, areaBroker);

                QUnit.expect(1);

                calculator
                    .on('plugin-install.templateKeyboard', function () {
                        assert.ok(true, 'The plugin has been installed');
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .catch(function (err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function () {
                        calculator.destroy();
                    });
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });

    });

    QUnit.asyncTest('init', function (assert) {
        var $container = $('#fixture-init');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = templateKeyboardPluginFactory(calculator, areaBroker);

                QUnit.expect(1);

                calculator
                    .on('plugin-init.templateKeyboard', function () {
                        assert.ok(plugin.getState('init'), 'The plugin has been initialized');
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        return plugin.init();
                    })
                    .catch(function (err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function () {
                        calculator.destroy();
                    });
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });

    });

    QUnit.asyncTest('render', function (assert) {
        var $container = $('#fixture-render');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = templateKeyboardPluginFactory(calculator, areaBroker);

                QUnit.expect(29);

                calculator
                    .on('plugin-render.templateKeyboard', function () {
                        assert.ok(plugin.getState('ready'), 'The plugin has been rendered');
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        return plugin.init();
                    })
                    .then(function () {
                        return plugin.render();
                    })
                    .then(function () {
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard').length, 1, 'The keyboard layout has been inserted');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key').length, 25, 'The expected number of keyboard keys have been inserted');

                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM0"]').length, 1, 'The layout contains a key for NUM0');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM1"]').length, 1, 'The layout contains a key for NUM1');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM2"]').length, 1, 'The layout contains a key for NUM2');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM3"]').length, 1, 'The layout contains a key for NUM3');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM4"]').length, 1, 'The layout contains a key for NUM4');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM5"]').length, 1, 'The layout contains a key for NUM5');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM6"]').length, 1, 'The layout contains a key for NUM6');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM7"]').length, 1, 'The layout contains a key for NUM7');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM8"]').length, 1, 'The layout contains a key for NUM8');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM8"]').length, 1, 'The layout contains a key for NUM8');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM9"]').length, 1, 'The layout contains a key for NUM9');

                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="LPAR"]').length, 1, 'The layout contains a key for LPAR');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="RPAR"]').length, 1, 'The layout contains a key for RPAR');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="DOT"]').length, 1, 'The layout contains a key for DOT');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="ADD"]').length, 1, 'The layout contains a key for ADD');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="SUB"]').length, 1, 'The layout contains a key for SUB');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="MUL"]').length, 1, 'The layout contains a key for MUL');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="DIV"]').length, 1, 'The layout contains a key for DIV');

                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="POW"]').length, 1, 'The layout contains a key for POW');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="POW NUM2"]').length, 1, 'The layout contains a key for POW NUM2');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="POW NUM3"]').length, 1, 'The layout contains a key for POW NUM3');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="SQRT"]').length, 1, 'The layout contains a key for SQRT');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="CBRT"]').length, 1, 'The layout contains a key for CBRT');

                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-command="clear"]').length, 1, 'The layout contains a key for clear');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-command="clearAll"]').length, 1, 'The layout contains a key for clearAll');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-command="execute"]').length, 1, 'The layout contains a key for execute');
                    })
                    .catch(function (err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function () {
                        calculator.destroy();
                    });
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('render - failure', function (assert) {
        var $container = $('#fixture-render');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = templateKeyboardPluginFactory(calculator, areaBroker);
                plugin.setConfig({layout: 'foo'});

                QUnit.expect(1);

                calculator
                    .on('plugin-render.templateKeyboard', function () {
                        assert.ok(false, 'Should not reach that point!');
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        return plugin.init();
                    })
                    .then(function () {
                        return plugin.render();
                    })
                    .then(function () {
                        assert.ok(false, 'Should not reach that point!');
                    })
                    .catch(function () {
                        assert.ok(true, 'The operation should fail!');
                    })
                    .then(function () {
                        calculator.destroy();
                    });
            })
            .on('error', function () {
                assert.ok(true, 'The operation should fail!');
                calculator.destroy();
            });
    });

    QUnit.asyncTest('destroy', function (assert) {
        var $container = $('#fixture-destroy');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = templateKeyboardPluginFactory(calculator, areaBroker);

                QUnit.expect(3);

                calculator
                    .on('plugin-render.templateKeyboard', function () {
                        assert.ok(plugin.getState('ready'), 'The plugin has been rendered');
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        return plugin.init();
                    })
                    .then(function () {
                        return plugin.render();
                    })
                    .then(function () {
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard').length, 1, 'The keyboard layout has been inserted');

                        return plugin.destroy();
                    })
                    .then(function () {
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard').length, 0, 'The keyboard layout has been removed');
                    })
                    .catch(function (err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function () {
                        calculator.destroy();
                    });
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('use keys', function (assert) {
        var $container = $('#fixture-keys');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = templateKeyboardPluginFactory(calculator, areaBroker);

                QUnit.expect(15);

                calculator
                    .on('plugin-render.templateKeyboard', function () {
                        assert.ok(plugin.getState('ready'), 'The plugin has been rendered');
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        return plugin.init();
                    })
                    .then(function () {
                        return plugin.render();
                    })
                    .then(function () {
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard').length, 1, 'The keyboard layout has been inserted');
                        assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard .key').length, 25, 'The expected number of keyboard keys have been inserted');

                        assert.equal(calculator.getExpression(), '', 'The expression is empty');
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-term.test', function (term) {
                                    calculator.off('command-term.test');

                                    assert.equal(term, 'NUM4', 'The term NUM4 has been used');
                                    assert.equal(calculator.getExpression(), '4', 'The expression contains 4');

                                    resolve();
                                });
                            areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM4"]').click();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-term.test', function (term) {
                                    calculator.off('command-term.test');

                                    assert.equal(term, 'NUM2', 'The term NUM2 has been used');
                                    assert.equal(calculator.getExpression(), '42', 'The expression contains 42');

                                    resolve();
                                });
                            areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM2"]').click();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-term.test', function (term) {
                                    calculator.off('command-term.test');

                                    assert.equal(term, 'ADD', 'The term ADD has been used');
                                    assert.equal(calculator.getExpression(), '42+', 'The expression contains 42+');

                                    resolve();
                                });
                            areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="ADD"]').click();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-term.test', function (term) {
                                    calculator.off('command-term.test');

                                    assert.equal(term, 'NUM3', 'The term NUM3 has been used');
                                    assert.equal(calculator.getExpression(), '42+3', 'The expression contains 42+3');

                                    resolve();
                                });
                            areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-param="NUM3"]').click();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('evaluate.test', function (result) {
                                    calculator.off('evaluate.test');

                                    assert.equal(result.value, '45', 'The expression has been computed and the result is 45');
                                    assert.equal(calculator.getExpression(), '42+3', 'The expression still contains 42+3');

                                    resolve();
                                });
                            areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-command="execute"]').click();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-clear.test', function () {
                                    calculator.off('command-clear.test');

                                    assert.equal(calculator.getExpression(), '', 'The expression has been cleared');

                                    resolve();
                                });
                            areaBroker.getKeyboardArea().find('.calculator-keyboard .key[data-command="clear"]').click();
                        });
                    })
                    .catch(function (err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function () {
                        calculator.destroy();
                    });
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.module('visual test');

    $.fn.setCursorPosition = function (pos) {
        var range;
        if (this.setSelectionRange) {
            this.setSelectionRange(pos, pos);
        } else if (this.createTextRange) {
            range = this.createTextRange();
            range.collapse(true);
            if (pos < 0) {
                pos = $(this).val().length + pos;
            }
            range.moveEnd('character', pos);
            range.moveStart('character', pos);
            range.select();
        }
    };

    QUnit.asyncTest('keyboard', function (assert) {
        var $container = $('#visual-test');
        var $output = $('#visual-test .output input');
        var $input = $('#visual-test .input input');
        calculatorBoardFactory($container, [templateKeyboardPluginFactory])
            .on('expressionchange', function () {
                $input.val(this.getExpression());
            })
            .on('positionchange', function () {
                $input.setCursorPosition(this.getPosition());
            })
            .on('evaluate', function (result) {
                $output.val(result.value);
            })
            .on('syntaxerror', function (err) {
                $output.val(err);
            })
            .on('command-clearAll', function () {
                $output.val('');
            })
            .on('ready', function () {
                var areaBroker = this.getAreaBroker();
                assert.equal(areaBroker.getKeyboardArea().find('.calculator-keyboard').length, 1, 'The keyboard layout has been inserted');

                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            })
            .clear();
    });
});
