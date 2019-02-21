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
    'ui/maths/calculator/core/board',
    'ui/maths/calculator/plugins/core/degrad'
], function ($, _, calculatorBoardFactory, degradPluginFactory) {
    'use strict';

    QUnit.module('module');

    QUnit.test('degrad', function (assert) {
        var calculator = calculatorBoardFactory();

        QUnit.expect(3);

        assert.equal(typeof degradPluginFactory, 'function', "The plugin module exposes a function");
        assert.equal(typeof degradPluginFactory(calculator), 'object', "The plugin factory produces an instance");
        assert.notStrictEqual(degradPluginFactory(calculator), degradPluginFactory(calculator), "The plugin factory provides a different instance on each call");
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
        var plugin = degradPluginFactory(calculator);
        QUnit.expect(1);
        assert.equal(typeof plugin[data.title], 'function', 'The plugin instances expose a "' + data.title + '" function');
    });

    QUnit.module('behavior');

    QUnit.asyncTest('install', function (assert) {
        var $container = $('#fixture-install');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = degradPluginFactory(calculator, areaBroker);

                QUnit.expect(5);

                assert.ok(!calculator.hasCommand('degree'), 'The command degree is not yet registered');
                assert.ok(!calculator.hasCommand('radian'), 'The command radian is not yet registered');

                calculator
                    .on('plugin-install.degrad', function() {
                        assert.ok(true, 'The plugin has been installed');
                    })
                    .on('destroy', function() {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        assert.ok(calculator.hasCommand('degree'), 'The command degree is now registered');
                        assert.ok(calculator.hasCommand('radian'), 'The command radian is now registered');
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
                var plugin = degradPluginFactory(calculator, areaBroker);

                QUnit.expect(7);

                assert.ok(!calculator.hasCommand('degree'), 'The command degree is not yet registered');
                assert.ok(!calculator.hasCommand('radian'), 'The command radian is not yet registered');
                assert.ok(!calculator.is('degree'), 'The state degree is not set');
                assert.ok(!calculator.is('radian'), 'The state radian is not set');

                calculator
                    .on('plugin-init.degrad', function() {
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
                        assert.ok(calculator.hasCommand('degree'), 'The command degree is now registered');
                        assert.ok(calculator.hasCommand('radian'), 'The command radian is now registered');
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
                var plugin = degradPluginFactory(calculator, areaBroker);

                QUnit.expect(13);

                assert.ok(!calculator.hasCommand('degree'), 'The command degree is not yet registered');
                assert.ok(!calculator.hasCommand('radian'), 'The command radian is not yet registered');
                assert.ok(!calculator.is('degree'), 'The state degree is not set');
                assert.ok(!calculator.is('radian'), 'The state radian is not set');

                calculator
                    .on('destroy', function() {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function() {
                        return plugin.init();
                    })
                    .then(function () {
                        assert.ok(plugin.getState('init'), 'The plugin has been initialized');
                        assert.ok(calculator.hasCommand('degree'), 'The command degree is now registered');
                        assert.ok(calculator.hasCommand('radian'), 'The command radian is now registered');

                        assert.ok(!calculator.is('degree'), 'The state degree is not set');
                        assert.ok(calculator.is('radian'), 'The state radian is set');

                        return plugin.destroy();
                    })
                    .then(function () {
                        assert.ok(!calculator.hasCommand('degree'), 'The command degree is removed');
                        assert.ok(!calculator.hasCommand('radian'), 'The command radian is removed');

                        assert.ok(!calculator.is('degree'), 'The state degree is removed');
                        assert.ok(!calculator.is('radian'), 'The state radian is removed');
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

    QUnit.asyncTest('toggle', function (assert) {
        var $container = $('#fixture-toggle');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = degradPluginFactory(calculator, areaBroker);

                QUnit.expect(16);

                assert.ok(!calculator.hasCommand('degree'), 'The command degree is not yet registered');
                assert.ok(!calculator.hasCommand('radian'), 'The command radian is not yet registered');
                assert.ok(!calculator.is('degree'), 'The state degree is not set');
                assert.ok(!calculator.is('radian'), 'The state radian is not set');

                calculator
                    .on('plugin-init.degrad', function() {
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
                        assert.ok(calculator.hasCommand('degree'), 'The command degree is now registered');
                        assert.ok(calculator.hasCommand('radian'), 'The command radian is now registered');

                        assert.ok(!calculator.is('degree'), 'The state degree is not set');
                        assert.ok(calculator.is('radian'), 'The state radian is set');

                        calculator.replace('cos PI');
                        assert.equal(calculator.evaluate().value, '-1', 'The expression is computed in radian mode');

                        calculator.useCommand('degree');

                        assert.ok(calculator.is('degree'), 'The state degree is set');
                        assert.ok(!calculator.is('radian'), 'The state radian is not set');

                        calculator.replace('cos 180');
                        assert.equal(calculator.evaluate().value, '-1', 'The expression is computed in degree mode');

                        calculator.useCommand('radian');

                        assert.ok(!calculator.is('degree'), 'The state degree is not set');
                        assert.ok(calculator.is('radian'), 'The state radian is set');

                        calculator.replace('cos PI');
                        assert.equal(calculator.evaluate().value, '-1', 'The expression is computed in radian mode');
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

    QUnit.cases([{
        title: 'Set config to degree',
        config: {
            maths: {
                degree: true
            }
        },
        expected: 'degree'
    }, {
        title: 'Set config to radian',
        config: {
            maths: {
                degree: false
            }
        },
        expected: 'radian'
    }])
        .asyncTest('config', function (data, assert) {
            var $container = $('#fixture-config');
            var calculator = calculatorBoardFactory($container, [], data.config)
                .on('ready', function () {
                    var areaBroker = calculator.getAreaBroker();
                    var plugin = degradPluginFactory(calculator, areaBroker);

                    QUnit.expect(8);

                    assert.ok(!calculator.hasCommand('degree'), 'The command degree is not yet registered');
                    assert.ok(!calculator.hasCommand('radian'), 'The command radian is not yet registered');
                    assert.ok(!calculator.is('degree'), 'The state degree is not set');
                    assert.ok(!calculator.is('radian'), 'The state radian is not set');

                    calculator
                        .on('plugin-init.degrad', function() {
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
                            assert.ok(calculator.hasCommand('degree'), 'The command degree is now registered');
                            assert.ok(calculator.hasCommand('radian'), 'The command radian is now registered');

                            assert.ok(calculator.is(data.expected), 'The expected state is set: ' + data.expected);
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
