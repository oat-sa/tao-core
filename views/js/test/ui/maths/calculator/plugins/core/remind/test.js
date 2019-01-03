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
    'ui/maths/calculator/plugins/core/remind'
], function ($, _, Promise, calculatorBoardFactory, remindPluginFactory) {
    'use strict';

    QUnit.module('module');

    QUnit.test('remind', function (assert) {
        var calculator = calculatorBoardFactory();

        QUnit.expect(3);

        assert.equal(typeof remindPluginFactory, 'function', "The plugin module exposes a function");
        assert.equal(typeof remindPluginFactory(calculator), 'object', "The plugin factory produces an instance");
        assert.notStrictEqual(remindPluginFactory(calculator), remindPluginFactory(calculator), "The plugin factory provides a different instance on each call");
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
        var plugin = remindPluginFactory(calculator);
        QUnit.expect(1);
        assert.equal(typeof plugin[data.title], 'function', 'The plugin instances expose a "' + data.title + '" function');
    });

    QUnit.module('behavior');

    QUnit.asyncTest('install', function (assert) {
        var $container = $('#fixture-install');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = remindPluginFactory(calculator, areaBroker);

                QUnit.expect(9);

                assert.ok(!calculator.hasCommand('remind'), 'The command remind is not yet registered');
                assert.ok(!calculator.hasCommand('remindLast'), 'The command remindLast is not yet registered');
                assert.ok(!calculator.hasCommand('remindStore'), 'The command remindStore is not yet registered');
                assert.ok(!calculator.hasCommand('remindClear'), 'The command remindClear is not yet registered');

                calculator
                    .on('plugin-install.remind', function () {
                        assert.ok(true, 'The plugin has been installed');
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        assert.ok(calculator.hasCommand('remind'), 'The command remind is now registered');
                        assert.ok(calculator.hasCommand('remindLast'), 'The command remindLast is now registered');
                        assert.ok(calculator.hasCommand('remindStore'), 'The command remindStore is now registered');
                        assert.ok(calculator.hasCommand('remindClear'), 'The command remindClear is now registered');
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

    QUnit.asyncTest('init', function (assert) {
        var $container = $('#fixture-init');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = remindPluginFactory(calculator, areaBroker);

                QUnit.expect(17);

                assert.ok(!calculator.hasCommand('remind'), 'The command remind is not yet registered');
                assert.ok(!calculator.hasCommand('remindLast'), 'The command remindLast is not yet registered');
                assert.ok(!calculator.hasCommand('remindStore'), 'The command remindStore is not yet registered');
                assert.ok(!calculator.hasCommand('remindClear'), 'The command remindClear is not yet registered');

                calculator
                    .on('plugin-init.remind', function () {
                        assert.ok(plugin.getState('init'), 'The plugin has been initialized');
                    })
                    .after('destroy', function () {
                        assert.ok(!calculator.hasVariable('mem'), 'The remind variable should have been removed');
                        assert.ok(!calculator.hasVariable('last'), 'The remind last variable should have been removed');

                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        return plugin.init();
                    })
                    .then(function () {
                        assert.ok(calculator.hasCommand('remind'), 'The command remind is now registered');
                        assert.ok(calculator.hasCommand('remindLast'), 'The command remindLast is now registered');
                        assert.ok(calculator.hasCommand('remindStore'), 'The command remindStore is now registered');
                        assert.ok(calculator.hasCommand('remindClear'), 'The command remindClear is now registered');

                        assert.ok(!calculator.hasVariable('mem'), 'The remind variable does not exist at this time');
                        assert.ok(!calculator.hasVariable('last'), 'The remind last variable does not exist at this time');

                        return new Promise(function (resolve) {
                            calculator
                                .after('evaluate.test', function (r1) {
                                    calculator.off('evaluate.test');

                                    assert.ok(!calculator.hasVariable('mem'), 'The remind variable still does not exist');
                                    assert.ok(calculator.hasVariable('last'), 'The remind last variable should now exist');
                                    assert.deepEqual(calculator.getVariable('last'), r1, 'The remind last variable is equal to the last result');
                                    assert.equal(r1.value, '7', 'The result is correct');

                                    resolve();
                                })
                                .replace('3+4')
                                .evaluate();
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

    QUnit.asyncTest('destroy', function (assert) {
        var $container = $('#fixture-destroy');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = remindPluginFactory(calculator, areaBroker);

                QUnit.expect(12);

                assert.ok(!calculator.hasCommand('remind'), 'The command remind is not yet registered');
                assert.ok(!calculator.hasCommand('remindLast'), 'The command remindLast is not yet registered');
                assert.ok(!calculator.hasCommand('remindStore'), 'The command remindStore is not yet registered');
                assert.ok(!calculator.hasCommand('remindClear'), 'The command remindClear is not yet registered');

                calculator
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        return plugin.init();
                    })
                    .then(function () {
                        assert.ok(calculator.hasCommand('remind'), 'The command remind is now registered');
                        assert.ok(calculator.hasCommand('remindLast'), 'The command remindLast is now registered');
                        assert.ok(calculator.hasCommand('remindStore'), 'The command remindStore is now registered');
                        assert.ok(calculator.hasCommand('remindClear'), 'The command remindClear is now registered');

                        return plugin.destroy();
                    })
                    .then(function () {
                        assert.ok(!calculator.hasCommand('remind'), 'The command remind is removed');
                        assert.ok(!calculator.hasCommand('remindLast'), 'The command remindLast is removed');
                        assert.ok(!calculator.hasCommand('remindStore'), 'The command remindStore is removed');
                        assert.ok(!calculator.hasCommand('remindClear'), 'The command remindClear is removed');
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

    QUnit.asyncTest('remind', function (assert) {
        var $container = $('#fixture-remind');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = remindPluginFactory(calculator, areaBroker);

                QUnit.expect(40);

                assert.ok(!calculator.hasCommand('remind'), 'The command remind is not yet registered');
                assert.ok(!calculator.hasCommand('remindLast'), 'The command remindLast is not yet registered');
                assert.ok(!calculator.hasCommand('remindStore'), 'The command remindStore is not yet registered');
                assert.ok(!calculator.hasCommand('remindClear'), 'The command remindClear is not yet registered');

                calculator
                    .on('plugin-init.remind', function () {
                        assert.ok(plugin.getState('init'), 'The plugin has been initialized');
                    })
                    .after('destroy', function () {
                        assert.ok(!calculator.hasVariable('mem'), 'The remind variable should have been removed');
                        assert.ok(!calculator.hasVariable('last'), 'The remind last variable should have been removed');

                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        return plugin.init();
                    })
                    .then(function () {
                        assert.ok(calculator.hasCommand('remind'), 'The command remind is now registered');
                        assert.ok(calculator.hasCommand('remindLast'), 'The command remindLast is now registered');
                        assert.ok(calculator.hasCommand('remindStore'), 'The command remindStore is now registered');
                        assert.ok(calculator.hasCommand('remindClear'), 'The command remindClear is now registered');

                        assert.ok(!calculator.hasVariable('mem'), 'The remind variable does not exist at this time');
                        assert.ok(!calculator.hasVariable('last'), 'The remind last variable does not exist at this time');
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('evaluate.test', function (result) {
                                    calculator.off('evaluate.test');

                                    assert.ok(!calculator.hasVariable('mem'), 'The remind variable still does not exist');
                                    assert.ok(calculator.hasVariable('last'), 'The remind last variable should now exist');
                                    assert.deepEqual(calculator.getVariable('last'), result, 'The remind last variable is equal to the last result');
                                    assert.equal(result.value, '7', 'The result is correct');

                                    resolve();
                                })
                                .replace('3+4')
                                .evaluate();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('evaluate.test', function (result) {
                                    calculator.off('evaluate.test');

                                    assert.ok(!calculator.hasVariable('mem'), 'The remind variable still does not exist');
                                    assert.ok(calculator.hasVariable('last'), 'The remind last variable should now exist');
                                    assert.deepEqual(calculator.getVariable('last'), result, 'The remind last variable is equal to the last result');
                                    assert.equal(result.value, '20', 'The result is correct');

                                    resolve();
                                })
                                .replace('4*5')
                                .evaluate();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-remindStore.test', function () {
                                    calculator.off('command-remindStore.test');

                                    assert.ok(calculator.hasVariable('mem'), 'The remind variable should now exist');
                                    assert.ok(calculator.hasVariable('last'), 'The remind last variable should now exist');
                                    assert.deepEqual(calculator.getVariable('mem'), calculator.getVariable('last'), 'The remind last variable is equal to the last result');
                                    assert.equal(calculator.getVariable('mem').value, '20', 'The variable contains the correct value');

                                    resolve();
                                })
                                .useCommand('remindStore');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-remind.test', function () {
                                    calculator.off('command-remind.test');

                                    assert.equal(calculator.getExpression(), '10+mem', 'The expression has been updated with the remind variable');

                                    resolve();
                                })
                                .replace('10+')
                                .useCommand('remind');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('evaluate.test', function (result) {
                                    calculator.off('evaluate.test');

                                    assert.ok(calculator.hasVariable('mem'), 'The remind variable still exist');
                                    assert.ok(calculator.hasVariable('last'), 'The remind last variable still exist');
                                    assert.equal(calculator.getVariable('mem').value, '20', 'The remind variable contains the correct value');
                                    assert.deepEqual(calculator.getVariable('last'), result, 'The remind last variable is equal to the last result');
                                    assert.equal(result.value, '30', 'The result is correct');

                                    resolve();
                                })
                                .evaluate();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('evaluate.test', function (result) {
                                    calculator.off('evaluate.test');

                                    assert.equal(calculator.getExpression(), 'last+mem', 'The expression has been properly updated');
                                    assert.ok(calculator.hasVariable('mem'), 'The remind variable still exist');
                                    assert.ok(calculator.hasVariable('last'), 'The remind last variable still exist');
                                    assert.equal(calculator.getVariable('mem').value, '20', 'The remind variable contains the correct value');
                                    assert.deepEqual(calculator.getVariable('last'), result, 'The remind last variable is equal to the last result');
                                    assert.equal(result.value, '50', 'The result is correct');

                                    resolve(result);
                                })
                                .clear()
                                .useCommand('remindLast')
                                .useTerm('ADD')
                                .useCommand('remind')
                                .evaluate();
                        });
                    })
                    .then(function (result) {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-remindClear.test', function () {
                                    calculator.off('command-remindClear.test');

                                    assert.ok(!calculator.hasVariable('mem'), 'The remind variable has been destroyed');
                                    assert.ok(calculator.hasVariable('last'), 'The remind last variable still exist');
                                    assert.deepEqual(calculator.getVariable('last'), result, 'The remind last variable is equal to the last result');

                                    resolve();
                                })
                                .useCommand('remindClear');
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
});
