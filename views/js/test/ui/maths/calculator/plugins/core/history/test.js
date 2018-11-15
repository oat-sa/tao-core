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
    'ui/maths/calculator/plugins/core/history'
], function ($, _, Promise, calculatorBoardFactory, historyPluginFactory) {
    'use strict';

    QUnit.module('module');

    QUnit.test('history', function (assert) {
        var calculator = calculatorBoardFactory();

        QUnit.expect(3);

        assert.equal(typeof historyPluginFactory, 'function', "The plugin module exposes a function");
        assert.equal(typeof historyPluginFactory(calculator), 'object', "The plugin factory produces an instance");
        assert.notStrictEqual(historyPluginFactory(calculator), historyPluginFactory(calculator), "The plugin factory provides a different instance on each call");
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
        var plugin = historyPluginFactory(calculator);
        QUnit.expect(1);
        assert.equal(typeof plugin[data.title], 'function', 'The plugin instances expose a "' + data.title + '" function');
    });

    QUnit.module('behavior');

    QUnit.asyncTest('install', function (assert) {
        var $container = $('#fixture-install');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = historyPluginFactory(calculator, areaBroker);

                QUnit.expect(9);

                assert.ok(!calculator.hasCommand('historyClear'), 'The command historyClear is not yet registered');
                assert.ok(!calculator.hasCommand('historyUp'), 'The command historyUp is not yet registered');
                assert.ok(!calculator.hasCommand('historyDown'), 'The command historyDown is not yet registered');
                assert.ok(!calculator.hasCommand('historyGet'), 'The command historyGet is not yet registered');

                calculator
                    .on('plugin-install.history', function() {
                        assert.ok(true, 'The plugin has been installed');
                    })
                    .on('destroy', function() {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        assert.ok(calculator.hasCommand('historyClear'), 'The command historyClear is now registered');
                        assert.ok(calculator.hasCommand('historyUp'), 'The command historyUp is now registered');
                        assert.ok(calculator.hasCommand('historyDown'), 'The command historyDown is now registered');
                        assert.ok(calculator.hasCommand('historyGet'), 'The command historyGet is now registered');
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
                var plugin = historyPluginFactory(calculator, areaBroker);

                QUnit.expect(33);

                assert.ok(!calculator.hasCommand('historyClear'), 'The command historyClear is not yet registered');
                assert.ok(!calculator.hasCommand('historyUp'), 'The command historyUp is not yet registered');
                assert.ok(!calculator.hasCommand('historyDown'), 'The command historyDown is not yet registered');
                assert.ok(!calculator.hasCommand('historyGet'), 'The command historyGet is not yet registered');

                calculator
                    .on('plugin-init.history', function() {
                        assert.ok(plugin.getState('init'), 'The plugin has been initialized');
                    })
                    .on('destroy', function() {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function() {
                        return plugin.init();
                    })
                    .then(function () {
                        assert.ok(calculator.hasCommand('historyClear'), 'The command historyClear is now registered');
                        assert.ok(calculator.hasCommand('historyUp'), 'The command historyUp is now registered');
                        assert.ok(calculator.hasCommand('historyDown'), 'The command historyDown is now registered');
                        assert.ok(calculator.hasCommand('historyGet'), 'The command historyGet is now registered');

                        return new Promise(function(resolve) {
                            calculator.on('history.read', function(h1) {
                                calculator.off('history.read');
                                assert.deepEqual(h1, [], 'history is empty');

                                calculator.replace('cos PI');
                                assert.equal(calculator.evaluate(), '-1', 'The expression is computed');

                                calculator.on('history.read', function(h2) {
                                    calculator.off('history.read');
                                    assert.deepEqual(h2, ['cos PI'], 'history now contains evaluated expression');

                                    calculator.replace('cos PI');
                                    assert.equal(calculator.evaluate(), '-1', 'The expression is computed');

                                    calculator.on('history.read', function(h3) {
                                        calculator.off('history.read');
                                        assert.deepEqual(h3, ['cos PI'], 'since the same expression has been computed, history did not change');

                                        calculator.replace('.1+.2');
                                        assert.equal(calculator.evaluate(), '0.3', 'The expression is computed');

                                        calculator.on('history.read', function(h4) {
                                            calculator.off('history.read');
                                            assert.deepEqual(h4, ['cos PI', '.1+.2'], 'history has been completed');

                                            calculator.clear();
                                            assert.equal(calculator.getExpression(), '', 'The expression is cleared');

                                            calculator.on('replace.set', function() {
                                                calculator.off('replace.set');
                                                assert.equal(calculator.getExpression(), h4[h4.length - 1], 'The last expression has been reminded');

                                                calculator.replace('1+2');
                                                assert.equal(calculator.evaluate(), '3', 'The expression is computed');

                                                calculator.on('history.read', function(h5) {
                                                    calculator.off('history.read');
                                                    assert.deepEqual(h5, ['cos PI', '1+2'], 'history has been updated');

                                                    calculator.replace('3*4');
                                                    assert.equal(calculator.evaluate(), '12', 'The expression is computed');

                                                    calculator.on('history.read', function(h6) {
                                                        calculator.off('history.read');
                                                        assert.deepEqual(h6, ['cos PI', '1+2', '3*4'], 'history has been completed');

                                                        calculator.on('replace.set', function() {
                                                            calculator.off('replace.set');
                                                            assert.equal(calculator.getExpression(), h6[h6.length - 1], 'The last expression has been reminded');

                                                            calculator.on('replace.set', function() {
                                                                calculator.off('replace.set');
                                                                assert.equal(calculator.getExpression(), h6[h6.length - 2], 'The N-1 expression has been reminded');

                                                                calculator.on('replace.set', function() {
                                                                    calculator.off('replace.set');
                                                                    assert.equal(calculator.getExpression(), h6[h6.length - 3], 'The N-2 expression has been reminded');

                                                                    calculator.after('command-historyUp.stop', function() {
                                                                        calculator.off('command-historyUp.stop');
                                                                        assert.equal(calculator.getExpression(), h6[h6.length - 3], 'The expression is still the same');

                                                                        calculator.on('replace.set', function() {
                                                                            calculator.off('replace.set');
                                                                            assert.equal(calculator.getExpression(), h6[h6.length - 2], 'The N-1 expression has been reminded');

                                                                            calculator.on('replace.set', function() {
                                                                                calculator.off('replace.set');
                                                                                assert.equal(calculator.getExpression(), h6[h6.length - 1], 'The last expression has been reminded');

                                                                                calculator.on('replace.set', function() {
                                                                                    calculator.off('replace.set');
                                                                                    assert.equal(calculator.getExpression(), '', 'The expression has been reset');

                                                                                    calculator.after('command-historyDown.stop', function() {
                                                                                        calculator.off('command-historyDown.stop');
                                                                                        assert.equal(calculator.getExpression(), '', 'The expression did not change');

                                                                                        calculator.on('replace.set', function() {
                                                                                            calculator.off('replace.set');
                                                                                            assert.equal(calculator.getExpression(), h6[h6.length - 1], 'The last expression has been reminded');

                                                                                            calculator.after('command-historyClear.set', function() {
                                                                                                calculator.off('command-historyClear.set');

                                                                                                calculator.on('history.read', function(h7) {
                                                                                                    calculator.off('history.read');
                                                                                                    assert.deepEqual(h7, [], 'history is empty');
                                                                                                    assert.equal(calculator.getExpression(), h6[h6.length - 1], 'The expression is still there');

                                                                                                    resolve();
                                                                                                });
                                                                                                calculator.useCommand('historyGet');
                                                                                            });
                                                                                            calculator.useCommand('historyClear');
                                                                                        });
                                                                                        calculator.useCommand('historyUp');
                                                                                    });
                                                                                    calculator.useCommand('historyDown');
                                                                                });
                                                                                calculator.useCommand('historyDown');
                                                                            });
                                                                            calculator.useCommand('historyDown');
                                                                        });
                                                                        calculator.useCommand('historyDown');
                                                                    });
                                                                    calculator.useCommand('historyUp');
                                                                });
                                                                calculator.useCommand('historyUp');
                                                            });
                                                            calculator.useCommand('historyUp');
                                                        });
                                                        calculator.useCommand('historyUp');
                                                    });
                                                    calculator.useCommand('historyGet');
                                                });
                                                calculator.useCommand('historyGet');
                                            });
                                            calculator.useCommand('historyUp');
                                        });
                                        calculator.useCommand('historyGet');
                                    });
                                    calculator.useCommand('historyGet');
                                });
                                calculator.useCommand('historyGet');
                            });
                            calculator.useCommand('historyGet');
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
                var plugin = historyPluginFactory(calculator, areaBroker);

                QUnit.expect(12);

                assert.ok(!calculator.hasCommand('historyClear'), 'The command historyClear is not yet registered');
                assert.ok(!calculator.hasCommand('historyUp'), 'The command historyUp is not yet registered');
                assert.ok(!calculator.hasCommand('historyDown'), 'The command historyDown is not yet registered');
                assert.ok(!calculator.hasCommand('historyGet'), 'The command historyGet is not yet registered');

                calculator
                    .on('destroy', function() {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function() {
                        return plugin.init();
                    })
                    .then(function () {
                        assert.ok(calculator.hasCommand('historyClear'), 'The command historyClear is now registered');
                        assert.ok(calculator.hasCommand('historyUp'), 'The command historyUp is now registered');
                        assert.ok(calculator.hasCommand('historyDown'), 'The command historyDown is now registered');
                        assert.ok(calculator.hasCommand('historyGet'), 'The command historyGet is now registered');

                        return plugin.destroy();
                    })
                    .then(function () {
                        assert.ok(!calculator.hasCommand('historyClear'), 'The command historyClear is removed');
                        assert.ok(!calculator.hasCommand('historyUp'), 'The command historyUp is removed');
                        assert.ok(!calculator.hasCommand('historyDown'), 'The command historyDown is removed');
                        assert.ok(!calculator.hasCommand('historyGet'), 'The command historyGet is removed');
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
