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
], function($, _, Promise, calculatorBoardFactory, historyPluginFactory) {
    'use strict';

    QUnit.module('module');

    QUnit.test('history', function(assert) {
        var calculator = calculatorBoardFactory();

        assert.expect(3);

        assert.equal(typeof historyPluginFactory, 'function', 'The plugin module exposes a function');
        assert.equal(typeof historyPluginFactory(calculator), 'object', 'The plugin factory produces an instance');
        assert.notStrictEqual(historyPluginFactory(calculator), historyPluginFactory(calculator), 'The plugin factory provides a different instance on each call');
    });

    QUnit.module('api');

    QUnit.cases.init([
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
    ]).test('plugin API ', function(data, assert) {
        var calculator = calculatorBoardFactory();
        var plugin = historyPluginFactory(calculator);
        assert.expect(1);
        assert.equal(typeof plugin[data.title], 'function', 'The plugin instances expose a "' + data.title + '" function');
    });

    QUnit.module('behavior');

    QUnit.test('install', function(assert) {
        var ready = assert.async();
        var $container = $('#fixture-install');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function() {
                var areaBroker = calculator.getAreaBroker();
                var plugin = historyPluginFactory(calculator, areaBroker);

                assert.expect(9);

                assert.ok(!calculator.hasCommand('historyClear'), 'The command historyClear is not yet registered');
                assert.ok(!calculator.hasCommand('historyUp'), 'The command historyUp is not yet registered');
                assert.ok(!calculator.hasCommand('historyDown'), 'The command historyDown is not yet registered');
                assert.ok(!calculator.hasCommand('historyGet'), 'The command historyGet is not yet registered');

                calculator
                    .on('plugin-install.history', function() {
                        assert.ok(true, 'The plugin has been installed');
                    })
                    .on('destroy', function() {
                        ready();
                    });

                plugin.install()
                    .then(function() {
                        assert.ok(calculator.hasCommand('historyClear'), 'The command historyClear is now registered');
                        assert.ok(calculator.hasCommand('historyUp'), 'The command historyUp is now registered');
                        assert.ok(calculator.hasCommand('historyDown'), 'The command historyDown is now registered');
                        assert.ok(calculator.hasCommand('historyGet'), 'The command historyGet is now registered');
                    })
                    .catch(function(err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function() {
                        calculator.destroy();
                    });
            })
            .on('error', function(err) {
                // eslint-disable-next-line no-console
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                ready();
            });
    });

    QUnit.test('init', function(assert) {
        var ready = assert.async();
        var $container = $('#fixture-init');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function() {
                var areaBroker = calculator.getAreaBroker();
                var plugin = historyPluginFactory(calculator, areaBroker);

                assert.expect(14);

                assert.ok(!calculator.hasCommand('historyClear'), 'The command historyClear is not yet registered');
                assert.ok(!calculator.hasCommand('historyUp'), 'The command historyUp is not yet registered');
                assert.ok(!calculator.hasCommand('historyDown'), 'The command historyDown is not yet registered');
                assert.ok(!calculator.hasCommand('historyGet'), 'The command historyGet is not yet registered');

                calculator
                    .on('plugin-init.history', function() {
                        assert.ok(plugin.getState('init'), 'The plugin has been initialized');
                    })
                    .on('destroy', function() {
                        ready();
                    });

                plugin.install()
                    .then(function() {
                        return plugin.init();
                    })
                    .then(function() {
                        assert.ok(calculator.hasCommand('historyClear'), 'The command historyClear is now registered');
                        assert.ok(calculator.hasCommand('historyUp'), 'The command historyUp is now registered');
                        assert.ok(calculator.hasCommand('historyDown'), 'The command historyDown is now registered');
                        assert.ok(calculator.hasCommand('historyGet'), 'The command historyGet is now registered');

                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, [], 'history is empty');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            assert.equal(calculator.getLastResult().value, '0', 'The value of the last result is defaulted');
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '-1', 'The expression is computed');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The result of "cos PI" is stored');
                                    resolve();
                                })
                                .replace('cos PI')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, ['cos PI'], 'history now contains evaluated expression');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .catch(function(err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function() {
                        calculator.destroy();
                    });
            })
            .on('error', function(err) {
                // eslint-disable-next-line no-console
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                ready();
            });
    });

    QUnit.test('destroy', function(assert) {
        var ready = assert.async();
        var $container = $('#fixture-destroy');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function() {
                var areaBroker = calculator.getAreaBroker();
                var plugin = historyPluginFactory(calculator, areaBroker);

                assert.expect(12);

                assert.ok(!calculator.hasCommand('historyClear'), 'The command historyClear is not yet registered');
                assert.ok(!calculator.hasCommand('historyUp'), 'The command historyUp is not yet registered');
                assert.ok(!calculator.hasCommand('historyDown'), 'The command historyDown is not yet registered');
                assert.ok(!calculator.hasCommand('historyGet'), 'The command historyGet is not yet registered');

                calculator
                    .on('destroy', function() {
                        ready();
                    });

                plugin.install()
                    .then(function() {
                        return plugin.init();
                    })
                    .then(function() {
                        assert.ok(calculator.hasCommand('historyClear'), 'The command historyClear is now registered');
                        assert.ok(calculator.hasCommand('historyUp'), 'The command historyUp is now registered');
                        assert.ok(calculator.hasCommand('historyDown'), 'The command historyDown is now registered');
                        assert.ok(calculator.hasCommand('historyGet'), 'The command historyGet is now registered');

                        return plugin.destroy();
                    })
                    .then(function() {
                        assert.ok(!calculator.hasCommand('historyClear'), 'The command historyClear is removed');
                        assert.ok(!calculator.hasCommand('historyUp'), 'The command historyUp is removed');
                        assert.ok(!calculator.hasCommand('historyDown'), 'The command historyDown is removed');
                        assert.ok(!calculator.hasCommand('historyGet'), 'The command historyGet is removed');
                    })
                    .catch(function(err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function() {
                        calculator.destroy();
                    });
            })
            .on('error', function(err) {
                // eslint-disable-next-line no-console
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                ready();
            });
    });

    QUnit.test('history - fill and clear', function(assert) {
        var ready = assert.async();
        var $container = $('#fixture-history-fill');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function() {
                var areaBroker = calculator.getAreaBroker();
                var plugin = historyPluginFactory(calculator, areaBroker);

                assert.expect(27);

                calculator
                    .on('plugin-init.history', function() {
                        assert.ok(plugin.getState('init'), 'The plugin has been initialized');
                    })
                    .on('destroy', function() {
                        ready();
                    });

                plugin.install()
                    .then(function() {
                        return plugin.init();
                    })
                    .then(function() {
                        assert.ok(calculator.hasCommand('historyClear'), 'The command historyClear is registered');
                        assert.ok(calculator.hasCommand('historyUp'), 'The command historyUp is registered');
                        assert.ok(calculator.hasCommand('historyDown'), 'The command historyDown is registered');
                        assert.ok(calculator.hasCommand('historyGet'), 'The command historyGet is registered');
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, [], 'history is empty');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            assert.equal(calculator.getLastResult().value, '0', 'The value of the last result is defaulted');
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '-1', 'The expression is computed');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The result of "cos PI" is stored');
                                    resolve();
                                })
                                .replace('cos PI')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result is accurate');
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, ['cos PI'], 'history now contains evaluated expression');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '-1', 'The expression is computed');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The result of "cos PI" is stored');
                                    resolve();
                                })
                                .replace('cos PI')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, ['cos PI'], 'since the same expression has been computed, history did not change');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result is still -1');
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '0.3', 'The expression is computed');
                                    assert.equal(calculator.getLastResult().value, '0.3', 'The result of ".1+.2" is stored');
                                    resolve();
                                })
                                .replace('.1+.2')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, ['cos PI', '.1+.2'], 'history has been completed');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            assert.equal(calculator.getLastResult().value, '0.3', 'The value of the last result is still 0.3');
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '0.6', 'The expression is computed');
                                    assert.equal(calculator.getLastResult().value, '0.6', 'The result of "ans*2" is stored');
                                    resolve();
                                })
                                .replace('ans*2')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.equal(calculator.getExpression(), '', 'The expression is cleared');
                                    assert.deepEqual(history, ['cos PI', '.1+.2', 'ans*2'], 'history still contains expressions');
                                    resolve();
                                })
                                .clear()
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.equal(calculator.getExpression(), '', 'The expression is cleared');
                                    assert.deepEqual(history, ['cos PI', '.1+.2', 'ans*2'], 'history still contains expressions');
                                    resolve();
                                })
                                .clear()
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, [], 'history has been cleared');
                                    assert.equal(calculator.getExpression(), '', 'The expression is still empty');
                                    resolve();
                                })
                                .useCommand('clearAll')
                                .useCommand('historyGet');
                        });
                    })
                    .catch(function(err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function() {
                        calculator.destroy();
                    });
            })
            .on('error', function(err) {
                // eslint-disable-next-line no-console
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                ready();
            });
    });

    QUnit.test('history - navigate and remind', function(assert) {
        var ready = assert.async();
        var $container = $('#fixture-history-navigate');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function() {
                var areaBroker = calculator.getAreaBroker();
                var plugin = historyPluginFactory(calculator, areaBroker);

                assert.expect(52);

                calculator
                    .on('plugin-init.history', function() {
                        assert.ok(plugin.getState('init'), 'The plugin has been initialized');
                    })
                    .on('destroy', function() {
                        ready();
                    });

                plugin.install()
                    .then(function() {
                        return plugin.init();
                    })
                    .then(function() {
                        assert.ok(calculator.hasCommand('historyClear'), 'The command historyClear is registered');
                        assert.ok(calculator.hasCommand('historyUp'), 'The command historyUp is registered');
                        assert.ok(calculator.hasCommand('historyDown'), 'The command historyDown is registered');
                        assert.ok(calculator.hasCommand('historyGet'), 'The command historyGet is registered');
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, [], 'history is empty');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            assert.equal(calculator.getLastResult().value, '0', 'The value of the last result is defaulted');
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '-1', 'The expression "cos PI" is computed');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The result of "cos PI" is stored');
                                    resolve();
                                })
                                .replace('cos PI')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '-1', 'The expression "cos PI" is computed again');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The result of "cos PI" is stored');
                                    resolve();
                                })
                                .replace('cos PI')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '0.3', 'The expression ".1+.2" is computed');
                                    assert.equal(calculator.getLastResult().value, '0.3', 'The result of ".1+.2" is stored');
                                    resolve();
                                })
                                .replace('.1+.2')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, ['cos PI', '.1+.2'], 'history has been completed with 2 entries');
                                    resolve(history);
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function(history) {
                        return new Promise(function(resolve) {
                            calculator
                                .on('clear.test', function() {
                                    calculator.off('clear.test');
                                    assert.equal(calculator.getExpression(), '', 'The expression is cleared');
                                    resolve(history);
                                })
                                .clear();
                        });
                    })
                    .then(function(history) {
                        return new Promise(function(resolve) {
                            assert.equal(calculator.getLastResult().value, '0.3', 'The value of the last result did not change yet');
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), history[history.length - 1], 'The last expression has been reminded');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '3', 'The expression "1+2" is computed');
                                    assert.equal(calculator.getLastResult().value, '3', 'The result of "1+2" is stored');
                                    resolve();
                                })
                                .replace('1+2')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, ['cos PI', '.1+.2', '1+2'], 'history has been updated');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '12', 'The expression "3*4" is computed');
                                    assert.equal(calculator.getLastResult().value, '12', 'The result of "3*4" is stored');
                                    resolve();
                                })
                                .replace('3*4')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, ['cos PI', '.1+.2', '1+2', '3*4'], 'history has been completed');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '6*7', 'The expression is changed to "6*7"');
                                    resolve();
                                })
                                .replace('6*7');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            assert.equal(calculator.getLastResult().value, '12', 'The value of the last result is 12');
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '3*4', 'The last expression has been reminded: "3*4"');
                                    assert.equal(calculator.getLastResult().value, '3', 'The value of the last result has been restored to 3');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '1+2', 'The N-1 expression has been reminded: "1+2"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '.1+.2', 'The N-2 expression has been reminded: ".1+.2"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), 'cos PI', 'The N-3 expression has been reminded: "cos PI"');
                                    assert.equal(calculator.getLastResult().value, '0', 'The value of the last result has been restored to 0');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .after('command-historyUp.stop', function() {
                                    calculator.off('command-historyUp.stop');
                                    assert.equal(calculator.getExpression(), 'cos PI', 'The expression is still the same: "cos PI"');
                                    assert.equal(calculator.getLastResult().value, '0', 'The value of the last result did not change');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '.1+.2', 'The N-2 expression has been reminded: ".1+.2"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyDown');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '1+2', 'The N-1 expression has been reminded: "1+2"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyDown');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '3*4', 'The last expression has been reminded: "3*4"');
                                    assert.equal(calculator.getLastResult().value, '3', 'The value of the last result has been restored to 3');
                                    resolve();
                                })
                                .useCommand('historyDown');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '6*7', 'The expression has been reset');
                                    assert.equal(calculator.getLastResult().value, '12', 'The value of the last result has been restored to 12');
                                    resolve();
                                })
                                .useCommand('historyDown');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .after('command-historyDown.stop', function() {
                                    calculator.off('command-historyDown.stop');
                                    assert.equal(calculator.getExpression(), '6*7', 'The expression did not change');
                                    assert.equal(calculator.getLastResult().value, '12', 'The value of the last result is still 12');
                                    resolve();
                                })
                                .useCommand('historyDown');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '3*4', 'The last expression has been reminded: "3*4"');
                                    assert.equal(calculator.getLastResult().value, '3', 'The value of the last result has been restored to 3');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('command-historyClear.set', function() {
                                    calculator.off('command-historyClear.set');
                                    assert.ok(true, 'The history should be cleared now');
                                    resolve();
                                })
                                .useCommand('historyClear');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, [], 'history is empty');
                                    assert.equal(calculator.getExpression(), '3*4', 'The expression is still there: "3*4"');
                                    assert.equal(calculator.getLastResult().value, '3', 'The value of the last result is still 3');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .catch(function(err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function() {
                        calculator.destroy();
                    });
            })
            .on('error', function(err) {
                // eslint-disable-next-line no-console
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                ready();
            });
    });

    QUnit.test('history - navigate and replace', function(assert) {
        var ready = assert.async();
        var $container = $('#fixture-history-replace');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function() {
                var areaBroker = calculator.getAreaBroker();
                var plugin = historyPluginFactory(calculator, areaBroker);

                assert.expect(55);

                calculator
                    .on('plugin-init.history', function() {
                        assert.ok(plugin.getState('init'), 'The plugin has been initialized');
                    })
                    .on('destroy', function() {
                        ready();
                    });

                plugin.install()
                    .then(function() {
                        return plugin.init();
                    })
                    .then(function() {
                        assert.ok(calculator.hasCommand('historyClear'), 'The command historyClear is registered');
                        assert.ok(calculator.hasCommand('historyUp'), 'The command historyUp is registered');
                        assert.ok(calculator.hasCommand('historyDown'), 'The command historyDown is registered');
                        assert.ok(calculator.hasCommand('historyGet'), 'The command historyGet is registered');
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, [], 'history is empty');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            assert.equal(calculator.getLastResult().value, '0', 'The value of the last result is defaulted');
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '-1', 'The expression "cos PI" is computed');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The result of "cos PI" is stored');
                                    resolve();
                                })
                                .replace('cos PI')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '-1', 'The expression "cos PI" is computed again');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The result of "cos PI" is stored');
                                    resolve();
                                })
                                .replace('cos PI')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '0.3', 'The expression ".1+.2" is computed');
                                    assert.equal(calculator.getLastResult().value, '0.3', 'The result of ".1+.2" is stored');
                                    resolve();
                                })
                                .replace('.1+.2')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, ['cos PI', '.1+.2'], 'history has been completed with 2 entries');
                                    resolve(history);
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function(history) {
                        return new Promise(function(resolve) {
                            calculator
                                .on('clear.test', function() {
                                    calculator.off('clear.test');
                                    assert.equal(calculator.getExpression(), '', 'The expression is cleared');
                                    resolve(history);
                                })
                                .clear();
                        });
                    })
                    .then(function(history) {
                        return new Promise(function(resolve) {
                            assert.equal(calculator.getLastResult().value, '0.3', 'The value of the last result is still 0.3');
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), history[history.length - 1], 'The last expression has been reminded');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .after('evaluate.test', function(result) {
                                    calculator.off('evaluate.test');
                                    assert.equal(result.value, '3', 'The expression "1+2" is computed');
                                    assert.equal(calculator.getLastResult().value, '3', 'The result of "1+2" is stored');
                                    resolve();
                                })
                                .replace('1+2')
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, ['cos PI', '.1+.2', '1+2'], 'history has been updated');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '6*7', 'The expression is changed to "6*7"');
                                    resolve();
                                })
                                .replace('6*7');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            assert.equal(calculator.getLastResult().value, '3', 'The value of the last result is still 3');
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '1+2', 'The last expression has been reminded: "1+2"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '.1+.2', 'The N-1 expression has been reminded: ".1+.2"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '.1+.2+.3', 'The expression has been replaced by ".1+.2+.3"');
                                    resolve();
                                })
                                .replace('.1+.2+.3');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result is still -1');
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '1+2', 'The last expression has been reminded: "1+2"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyDown');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '.1+.2+.3', 'The replaced N-1 expression has been reminded: ".1+.2+.3"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .after('evaluate.set', function(result) {
                                    calculator.off('evaluate.set');
                                    assert.equal(result.value, '0.6', 'The expression is computed');
                                    assert.equal(calculator.getLastResult().value, '0.6', 'The result of ".1+.2+.3" is stored');
                                    resolve();
                                })
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, ['cos PI', '.1+.2', '1+2', '.1+.2+.3'], 'history has been completed');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '.1+.2+.3', 'The last expression has been reminded: ".1+.2+.3"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '1+2', 'The N-1 expression has been reminded: "1+2"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '1+2-1', 'The expression has been replaced by 1+2-1');
                                    resolve();
                                })
                                .replace('1+2-1');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result is still -1');
                            calculator
                                .after('command-historyDown.set', function() {
                                    calculator.off('command-historyDown.set');
                                    assert.equal(calculator.getExpression(), '.1+.2+.3', 'The last expression has been reminded: ".1+.2+.3"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyDown');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '1+2-1', 'The replaced N-1 expression has been reminded: "1+2-1"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .after('evaluate.set', function(result) {
                                    calculator.off('evaluate.set');
                                    assert.equal(result.value, '2', 'The expression is computed');
                                    assert.equal(calculator.getLastResult().value, '2', 'The result of "1+2-1" is stored');
                                    resolve();
                                })
                                .evaluate();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('history.read', function(history) {
                                    calculator.off('history.read');
                                    assert.deepEqual(history, ['cos PI', '.1+.2', '1+2', '.1+.2+.3', '1+2-1'], 'history has been completed');
                                    resolve();
                                })
                                .useCommand('historyGet');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '1+2-1', 'The last expression has been reminded: "1+2-1"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '.1+.2+.3', 'The N-1 expression has been reminded: ".1+.2+.3"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            calculator
                                .on('replace.set', function() {
                                    calculator.off('replace.set');
                                    assert.equal(calculator.getExpression(), '1+2', 'The replaced N-2 expression has been restored: "1+2"');
                                    assert.equal(calculator.getLastResult().value, '-1', 'The value of the last result has been restored to -1');
                                    resolve();
                                })
                                .useCommand('historyUp');
                        });
                    })
                    .catch(function(err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function() {
                        calculator.destroy();
                    });
            })
            .on('error', function(err) {
                // eslint-disable-next-line no-console
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                ready();
            });
    });

});
