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
], function($, _, Promise, calculatorBoardFactory, signPluginFactory, testCases) {
    'use strict';

    QUnit.module('module');

    QUnit.test('sign', function(assert) {
        var calculator = calculatorBoardFactory();

        assert.expect(3);

        assert.equal(typeof signPluginFactory, 'function', 'The plugin module exposes a function');
        assert.equal(typeof signPluginFactory(calculator), 'object', 'The plugin factory produces an instance');
        assert.notStrictEqual(signPluginFactory(calculator), signPluginFactory(calculator), 'The plugin factory provides a different instance on each call');
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
        var plugin = signPluginFactory(calculator);
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
                var plugin = signPluginFactory(calculator, areaBroker);

                assert.expect(3);

                assert.ok(!calculator.hasCommand('sign'), 'The command sign is not yet registered');

                calculator
                    .on('plugin-install.sign', function() {
                        assert.ok(true, 'The plugin has been installed');
                    })
                    .on('destroy', function() {
                        ready();
                    });

                plugin.install()
                    .then(function() {
                        assert.ok(calculator.hasCommand('sign'), 'The command sign is now registered');
                    })
                    .catch(function(err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function() {
                        calculator.destroy();
                    });
            })
            .on('error', function(err) {
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
                var plugin = signPluginFactory(calculator, areaBroker);

                assert.expect(3);

                assert.ok(!calculator.hasCommand('sign'), 'The command sign is not yet registered');

                calculator
                    .on('plugin-init.sign', function() {
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
                        assert.ok(calculator.hasCommand('sign'), 'The command sign is now registered');
                    })
                    .catch(function(err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function() {
                        calculator.destroy();
                    });
            })
            .on('error', function(err) {
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
                var plugin = signPluginFactory(calculator, areaBroker);

                assert.expect(4);

                assert.ok(!calculator.hasCommand('sign'), 'The command sign is not yet registered');

                calculator
                    .on('destroy', function() {
                        ready();
                    });

                plugin.install()
                    .then(function() {
                        return plugin.init();
                    })
                    .then(function() {
                        assert.ok(plugin.getState('init'), 'The plugin has been initialized');
                        assert.ok(calculator.hasCommand('sign'), 'The command sign is now registered');

                        return plugin.destroy();
                    })
                    .then(function() {
                        assert.ok(!calculator.hasCommand('sign'), 'The command sign is removed');
                    })
                    .catch(function(err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function() {
                        calculator.destroy();
                    });
            })
            .on('error', function(err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                ready();
            });
    });

    /**
     * @typedef {Object} testCase
     * @property {String} expression - the expression to test
     * @property {String} expected - the expected result
     * @property {Number} from - the start position from which the result is expected
     * @property {Number} to - the end position until which the result is expected
     * @property {Number} move - the expected move in the position after change
     * @property {Number} position - the expected position after change
     */

    QUnit.cases.init(testCases).test('command', function(data, assert) {
        var ready = assert.async();
        var $container = $('#fixture-command');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function() {
                var areaBroker = calculator.getAreaBroker();
                var plugin = signPluginFactory(calculator, areaBroker);

                assert.expect(5 + 2 * (data.to - data.from));

                assert.ok(!calculator.hasCommand('sign'), 'The command sign is not yet registered');

                calculator
                    .on('plugin-init.sign', function() {
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
                        assert.ok(calculator.hasCommand('sign'), 'The command sign is now registered');
                    })
                    .then(function() {
                        var position = data.from;

                        calculator.setLastResult(data.lastResult || 0);

                        // apply the command on successive positions with respect to the provided data
                        function applyCommand() {
                            return Promise.resolve()
                                .then(function() {
                                    return new Promise(function(resolve) {
                                        calculator
                                            .setExpression(data.expression)
                                            .setPosition(position)
                                            .after('command-sign', resolve)
                                            .useCommand('sign');
                                    });
                                })
                                .then(function () {
                                    var pos = 'undefined' !== typeof data.position ? data.position : Math.max(0, position + data.move);

                                    assert.equal(calculator.getExpression(), data.expected, 'Applying the sign command on ' + data.expression + ' at position ' + position + ' produced ' + data.expected);
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
                    .catch(function(err) {
                        assert.ok(false, 'Unexpected failure : ' + err.message);
                    })
                    .then(function() {
                        calculator.destroy();
                    });
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                ready();
            });
    });

});
