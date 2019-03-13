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
    'jquery',
    'lodash',
    'core/promise',
    'ui/maths/calculator/core/board',
    'ui/maths/calculator/plugins/modifiers/sign',
    'json!test/ui/maths/calculator/plugins/modifiers/sign/testCases.json'
], function ($, _, Promise, calculatorBoardFactory, signPluginFactory, testCases) {
    'use strict';

    QUnit.module('module');

    QUnit.test('sign', function (assert) {
        var calculator = calculatorBoardFactory();

        QUnit.expect(3);

        assert.equal(typeof signPluginFactory, 'function', "The plugin module exposes a function");
        assert.equal(typeof signPluginFactory(calculator), 'object', "The plugin factory produces an instance");
        assert.notStrictEqual(signPluginFactory(calculator), signPluginFactory(calculator), "The plugin factory provides a different instance on each call");
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
        var plugin = signPluginFactory(calculator);
        QUnit.expect(1);
        assert.equal(typeof plugin[data.title], 'function', 'The plugin instances expose a "' + data.title + '" function');
    });

    QUnit.module('behavior');

    QUnit.asyncTest('install', function (assert) {
        var $container = $('#fixture-install');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = signPluginFactory(calculator, areaBroker);

                QUnit.expect(3);

                assert.ok(!calculator.hasCommand('sign'), 'The command sign is not yet registered');

                calculator
                    .on('plugin-install.sign', function () {
                        assert.ok(true, 'The plugin has been installed');
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        assert.ok(calculator.hasCommand('sign'), 'The command sign is now registered');
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
                var plugin = signPluginFactory(calculator, areaBroker);

                QUnit.expect(3);

                assert.ok(!calculator.hasCommand('sign'), 'The command sign is not yet registered');

                calculator
                    .on('plugin-init.sign', function () {
                        assert.ok(plugin.getState('init'), 'The plugin has been initialized');
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        return plugin.init();
                    })
                    .then(function () {
                        assert.ok(calculator.hasCommand('sign'), 'The command sign is now registered');
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
                var plugin = signPluginFactory(calculator, areaBroker);

                QUnit.expect(4);

                assert.ok(!calculator.hasCommand('sign'), 'The command sign is not yet registered');

                calculator
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        return plugin.init();
                    })
                    .then(function () {
                        assert.ok(plugin.getState('init'), 'The plugin has been initialized');
                        assert.ok(calculator.hasCommand('sign'), 'The command sign is now registered');

                        return plugin.destroy();
                    })
                    .then(function () {
                        assert.ok(!calculator.hasCommand('sign'), 'The command sign is removed');
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

    /**
     * @typedef {Object} testCase
     * @property {String} expression - the expression to test
     * @property {String} expected - the expected result
     * @property {Number} from - the start position from which the result is expected
     * @property {Number} to - the end position until which the result is expected
     * @property {Number} move - the expected move in the position after change
     */

    QUnit.cases(testCases).asyncTest('toggle', function (data, assert) {
        var $container = $('#fixture-sign');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = signPluginFactory(calculator, areaBroker);

                QUnit.expect(5 + 2 * (data.to - data.from));

                assert.ok(!calculator.hasCommand('sign'), 'The command sign is not yet registered');

                calculator
                    .on('plugin-init.sign', function () {
                        assert.ok(plugin.getState('init'), 'The plugin has been initialized');
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        return plugin.init();
                    })
                    .then(function () {
                        assert.ok(calculator.hasCommand('sign'), 'The command sign is now registered');
                    })
                    .then(function () {
                        var position = data.from;

                        calculator.setLastResult(data.lastResult || 0);

                        // apply the command on successive positions with respect to the provided data
                        function applyCommand() {
                            return Promise.resolve()
                                .then(function () {
                                    return new Promise(function (resolve) {
                                        calculator
                                            .setExpression(data.expression)
                                            .setPosition(position)
                                            .after('command-sign', resolve)
                                            .useCommand('sign');
                                    });
                                })
                                .then(function () {
                                    var pos =  'undefined' !== typeof data.position ? data.position : Math.max(0, position + data.move);

                                    assert.equal(calculator.getExpression(), data.expected, 'Applying the sign change on ' + data.expression + ' at position ' + position + ' produced ' + data.expected);
                                    assert.equal(calculator.getPosition(), pos, 'The position has changed from ' + position + ' to ' + pos);
                                })
                                .then(function () {
                                    if (++position <= data.to) {
                                        return applyCommand();
                                    }
                                });
                        }

                        return applyCommand();
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
