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
    'ui/maths/calculator/plugins/core/stepNavigation'
], function ($, _, Promise, calculatorBoardFactory, stepNavigationPluginFactory) {
    'use strict';

    QUnit.module('module');

    QUnit.test('stepNavigation', function (assert) {
        var calculator = calculatorBoardFactory();

        QUnit.expect(3);

        assert.equal(typeof stepNavigationPluginFactory, 'function', "The plugin module exposes a function");
        assert.equal(typeof stepNavigationPluginFactory(calculator), 'object', "The plugin factory produces an instance");
        assert.notStrictEqual(stepNavigationPluginFactory(calculator), stepNavigationPluginFactory(calculator), "The plugin factory provides a different instance on each call");
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
        var plugin = stepNavigationPluginFactory(calculator);
        QUnit.expect(1);
        assert.equal(typeof plugin[data.title], 'function', 'The plugin instances expose a "' + data.title + '" function');
    });

    QUnit.module('behavior');

    QUnit.asyncTest('install', function (assert) {
        var $container = $('#fixture-install');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = stepNavigationPluginFactory(calculator, areaBroker);

                QUnit.expect(9);

                assert.ok(!calculator.hasCommand('stepMoveLeft'), 'The command stepMoveLeft is not yet registered');
                assert.ok(!calculator.hasCommand('stepMoveRight'), 'The command stepMoveRight is not yet registered');
                assert.ok(!calculator.hasCommand('stepDeleteLeft'), 'The command stepDeleteLeft is not yet registered');
                assert.ok(!calculator.hasCommand('stepDeleteRight'), 'The command stepDeleteRight is not yet registered');

                calculator
                    .on('plugin-install.stepNavigation', function () {
                        assert.ok(true, 'The plugin has been installed');
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        assert.ok(calculator.hasCommand('stepMoveLeft'), 'The command stepMoveLeft is now registered');
                        assert.ok(calculator.hasCommand('stepMoveRight'), 'The command stepMoveRight is now registered');
                        assert.ok(calculator.hasCommand('stepDeleteLeft'), 'The command stepDeleteLeft is now registered');
                        assert.ok(calculator.hasCommand('stepDeleteRight'), 'The command stepDeleteRight is now registered');
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
                var plugin = stepNavigationPluginFactory(calculator, areaBroker);

                QUnit.expect(9);

                assert.ok(!calculator.hasCommand('stepMoveLeft'), 'The command stepMoveLeft is not yet registered');
                assert.ok(!calculator.hasCommand('stepMoveRight'), 'The command stepMoveRight is not yet registered');
                assert.ok(!calculator.hasCommand('stepDeleteLeft'), 'The command stepDeleteLeft is not yet registered');
                assert.ok(!calculator.hasCommand('stepDeleteRight'), 'The command stepDeleteRight is not yet registered');

                calculator
                    .on('plugin-init.stepNavigation', function () {
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
                        assert.ok(calculator.hasCommand('stepMoveLeft'), 'The command stepMoveLeft is now registered');
                        assert.ok(calculator.hasCommand('stepMoveRight'), 'The command stepMoveRight is now registered');
                        assert.ok(calculator.hasCommand('stepDeleteLeft'), 'The command stepDeleteLeft is now registered');
                        assert.ok(calculator.hasCommand('stepDeleteRight'), 'The command stepDeleteRight is now registered');
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
                var plugin = stepNavigationPluginFactory(calculator, areaBroker);

                QUnit.expect(12);

                assert.ok(!calculator.hasCommand('stepMoveLeft'), 'The command stepMoveLeft is not yet registered');
                assert.ok(!calculator.hasCommand('stepMoveRight'), 'The command stepMoveRight is not yet registered');
                assert.ok(!calculator.hasCommand('stepDeleteLeft'), 'The command stepDeleteLeft is not yet registered');
                assert.ok(!calculator.hasCommand('stepDeleteRight'), 'The command stepDeleteRight is not yet registered');

                calculator
                    .on('destroy', function () {
                        QUnit.start();
                    });

                plugin.install()
                    .then(function () {
                        return plugin.init();
                    })
                    .then(function () {
                        assert.ok(calculator.hasCommand('stepMoveLeft'), 'The command stepMoveLeft is now registered');
                        assert.ok(calculator.hasCommand('stepMoveRight'), 'The command stepMoveRight is now registered');
                        assert.ok(calculator.hasCommand('stepDeleteLeft'), 'The command stepDeleteLeft is now registered');
                        assert.ok(calculator.hasCommand('stepDeleteRight'), 'The command stepDeleteRight is now registered');

                        return plugin.destroy();
                    })
                    .then(function () {
                        assert.ok(!calculator.hasCommand('stepMoveLeft'), 'The command stepMoveLeft is removed');
                        assert.ok(!calculator.hasCommand('stepMoveRight'), 'The command stepMoveRight is removed');
                        assert.ok(!calculator.hasCommand('stepDeleteLeft'), 'The command stepDeleteLeft is removed');
                        assert.ok(!calculator.hasCommand('stepDeleteRight'), 'The command stepDeleteRight is removed');
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

    QUnit.asyncTest('navigation', function (assert) {
        var expression = ' (.1+.2) * 10^3 / cos PI - sin sqrt 2';
        var $container = $('#fixture-navigation');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = stepNavigationPluginFactory(calculator, areaBroker);

                QUnit.expect(38);

                assert.ok(!calculator.hasCommand('stepMoveLeft'), 'The command stepMoveLeft is not yet registered');
                assert.ok(!calculator.hasCommand('stepMoveRight'), 'The command stepMoveRight is not yet registered');
                assert.ok(!calculator.hasCommand('stepDeleteLeft'), 'The command stepDeleteLeft is not yet registered');
                assert.ok(!calculator.hasCommand('stepDeleteRight'), 'The command stepDeleteRight is not yet registered');

                calculator
                    .on('plugin-init.stepNavigation', function () {
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
                        assert.ok(calculator.hasCommand('stepMoveLeft'), 'The command stepMoveLeft is now registered');
                        assert.ok(calculator.hasCommand('stepMoveRight'), 'The command stepMoveRight is now registered');
                        assert.ok(calculator.hasCommand('stepDeleteLeft'), 'The command stepDeleteLeft is now registered');
                        assert.ok(calculator.hasCommand('stepDeleteRight'), 'The command stepDeleteRight is now registered');

                        calculator.replace(expression);
                        assert.equal(calculator.getExpression(), expression, 'The expression is properly set');
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            var tokens = calculator.getTokens();

                            assert.ok(_.isArray(tokens), 'Got a lis of tokens');
                            assert.equal(tokens.length, 19, 'Found the expected number of tokens');
                            assert.equal(calculator.getPosition(), expression.length, 'The position is at the end of the expression');
                            assert.equal(calculator.getTokenIndex(), tokens.length - 1, 'Current token is the last one');

                            calculator
                                .after('command-stepMoveLeft.test', function () {
                                    calculator.off('command-stepMoveLeft.test');
                                    assert.equal(calculator.getPosition(), expression.length - 1, 'The position has been moved a term back');
                                    assert.equal(calculator.getTokenIndex(), tokens.length - 1, 'The current token is still the same');

                                    resolve();
                                })
                                .useCommand('stepMoveLeft');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            var tokens = calculator.getTokens();

                            calculator
                                .after('command-stepMoveLeft.test', function () {
                                    calculator.off('command-stepMoveLeft.test');
                                    assert.equal(calculator.getPosition(), expression.length - 6, 'The position has been again moved a term back');
                                    assert.equal(calculator.getTokenIndex(), tokens.length - 2, 'The current token should now have changed');

                                    calculator.setPosition(2);
                                    assert.equal(calculator.getPosition(), 2, 'The position has been set to 2');
                                    assert.equal(calculator.getTokenIndex(), 1, 'The current token has been set to index 1');

                                    resolve();
                                })
                                .useCommand('stepMoveLeft');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-stepMoveLeft.test', function () {
                                    calculator.off('command-stepMoveLeft.test');
                                    assert.equal(calculator.getPosition(), 1, 'The position should have been moved 1 step back');
                                    assert.equal(calculator.getTokenIndex(), 0, 'The current token should have moved 1 step back');

                                    resolve();
                                })
                                .useCommand('stepMoveLeft');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-stepMoveLeft.test', function () {
                                    calculator.off('command-stepMoveLeft.test');
                                    assert.equal(calculator.getPosition(), 0, 'The position should be now at the beginning');
                                    assert.equal(calculator.getTokenIndex(), 0, 'The current token should be the first');

                                    resolve();
                                })
                                .useCommand('stepMoveLeft');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-stepMoveLeft.test', function () {
                                    calculator.off('command-stepMoveLeft.test');
                                    assert.equal(calculator.getPosition(), 0, 'The position should not have changed');
                                    assert.equal(calculator.getTokenIndex(), 0, 'The current token should not have changed');

                                    resolve();
                                })
                                .useCommand('stepMoveLeft');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-stepMoveRight.test', function () {
                                    calculator.off('command-stepMoveRight.test');
                                    assert.equal(calculator.getPosition(), 2, 'The position should be moved 1 step forward');
                                    assert.equal(calculator.getTokenIndex(), 1, 'The current token should be moved 1 step forward');

                                    resolve();
                                })
                                .useCommand('stepMoveRight');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            var tokens = calculator.getTokens();

                            calculator
                                .after('command-stepMoveRight.test', function () {
                                    calculator.off('command-stepMoveRight.test');
                                    assert.equal(calculator.getPosition(), 3, 'The position should be moved again 1 step forward');
                                    assert.equal(calculator.getTokenIndex(), 2, 'The current token should be moved again 1 step forward');

                                    calculator.setPosition(expression.length - 4);
                                    assert.equal(calculator.getPosition(), expression.length - 4, 'The position has been set to the middle of the N-1 term');
                                    assert.equal(calculator.getTokenIndex(), tokens.length - 2, 'The current token has been set to index N-1');

                                    resolve();
                                })
                                .useCommand('stepMoveRight');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            var tokens = calculator.getTokens();

                            calculator
                                .after('command-stepMoveRight.test', function () {
                                    calculator.off('command-stepMoveRight.test');
                                    assert.equal(calculator.getPosition(), expression.length - 1, 'The position should have been set the last term');
                                    assert.equal(calculator.getTokenIndex(), tokens.length - 1, 'The current token should be the last one');

                                    resolve();
                                })
                                .useCommand('stepMoveRight');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            var tokens = calculator.getTokens();

                            calculator
                                .after('command-stepMoveRight.test', function () {
                                    calculator.off('command-stepMoveRight.test');
                                    assert.equal(calculator.getPosition(), expression.length, 'The position should have been set the end');
                                    assert.equal(calculator.getTokenIndex(), tokens.length - 1, 'The current token should not have changed');

                                    resolve();
                                })
                                .useCommand('stepMoveRight');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            var tokens = calculator.getTokens();

                            calculator
                                .after('command-stepMoveRight.test', function () {
                                    calculator.off('command-stepMoveRight.test');
                                    assert.equal(calculator.getPosition(), expression.length, 'The position should not have changed');
                                    assert.equal(calculator.getTokenIndex(), tokens.length - 1, 'The current token should not have changed');
                                    resolve();
                                })
                                .useCommand('stepMoveRight');
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

    QUnit.asyncTest('deletion', function (assert) {
        var expression = ' (.1+.2) * 10^3 / cos PI - sin sqrt 2';
        var $container = $('#fixture-deletion');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                var areaBroker = calculator.getAreaBroker();
                var plugin = stepNavigationPluginFactory(calculator, areaBroker);

                QUnit.expect(56);

                assert.ok(!calculator.hasCommand('stepMoveLeft'), 'The command stepMoveLeft is not yet registered');
                assert.ok(!calculator.hasCommand('stepMoveRight'), 'The command stepMoveRight is not yet registered');
                assert.ok(!calculator.hasCommand('stepDeleteLeft'), 'The command stepDeleteLeft is not yet registered');
                assert.ok(!calculator.hasCommand('stepDeleteRight'), 'The command stepDeleteRight is not yet registered');

                calculator
                    .on('plugin-init.stepNavigation', function () {
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
                        assert.ok(calculator.hasCommand('stepMoveLeft'), 'The command stepMoveLeft is now registered');
                        assert.ok(calculator.hasCommand('stepMoveRight'), 'The command stepMoveRight is now registered');
                        assert.ok(calculator.hasCommand('stepDeleteLeft'), 'The command stepDeleteLeft is now registered');
                        assert.ok(calculator.hasCommand('stepDeleteRight'), 'The command stepDeleteRight is now registered');

                        calculator.replace(expression);
                        assert.equal(calculator.getExpression(), expression, 'The expression is properly set');
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            var tokens = calculator.getTokens();

                            assert.ok(_.isArray(tokens), 'Got a lis of tokens');
                            assert.equal(tokens.length, 19, 'Found the expected number of tokens');

                            calculator.setPosition(28);
                            assert.equal(calculator.getPosition(), 28, 'The position is 28');
                            assert.equal(calculator.getTokenIndex(), 16, 'Current token is at index 16');

                            calculator
                                .after('command-stepDeleteLeft.test', function () {
                                    calculator.off('command-stepDeleteLeft.test');
                                    expression = ' (.1+.2) * 10^3 / cos PI - sqrt 2';
                                    assert.equal(calculator.getExpression(), expression, 'The term SIN should have been removed from the expression');
                                    assert.equal(calculator.getPosition(), 27, 'The position is now on 27');
                                    assert.equal(calculator.getTokenIndex(), 16, 'The current token is the next one, and the index should not have changed');
                                    assert.equal(calculator.getTokens().length, 18, 'One token should have gone');

                                    calculator.setPosition(28);
                                    assert.equal(calculator.getPosition(), 28, 'The position is 28');
                                    assert.equal(calculator.getTokenIndex(), 16, 'Current token is at index 16');

                                    resolve();
                                })
                                .useCommand('stepDeleteLeft');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-stepDeleteRight.test', function () {
                                    calculator.off('command-stepDeleteRight.test');
                                    expression = ' (.1+.2) * 10^3 / cos PI - 2';
                                    assert.equal(calculator.getExpression(), expression, 'The term SQRT should have been removed from the expression');
                                    assert.equal(calculator.getPosition(), 27, 'The position is now on 27');
                                    assert.equal(calculator.getTokenIndex(), 16, 'The current token is the next one, but index should not have change');
                                    assert.equal(calculator.getTokens().length, 17, 'Another token should have gone');

                                    resolve();
                                })
                                .useCommand('stepDeleteRight');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-stepDeleteLeft.test', function () {
                                    calculator.off('command-stepDeleteLeft.test');
                                    expression = ' (.1+.2) * 10^3 / cos PI 2';
                                    assert.equal(calculator.getExpression(), expression, 'The term SUB should have been removed from the expression');
                                    assert.equal(calculator.getPosition(), 25, 'The position is still on 25');
                                    assert.equal(calculator.getTokenIndex(), 15, 'The current token is the previous one, but the index should be the same');
                                    assert.equal(calculator.getTokens().length, 16, 'Yet another token should have gone');

                                    resolve();
                                })
                                .useCommand('stepDeleteLeft');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-stepDeleteRight.test', function () {
                                    calculator.off('command-stepDeleteRight.test');
                                    expression = ' (.1+.2) * 10^3 / cos PI ';
                                    assert.equal(calculator.getExpression(), expression, 'The term NUM2 should have been removed from the expression');
                                    assert.equal(calculator.getPosition(), expression.length, 'The position is now at the end');
                                    assert.equal(calculator.getTokenIndex(), 14, 'The current token is the last one');
                                    assert.equal(calculator.getTokens().length, 15, 'And another token should have gone');

                                    resolve();
                                })
                                .useCommand('stepDeleteRight');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-stepDeleteLeft.test', function () {
                                    calculator.off('command-stepDeleteLeft.test');
                                    expression = ' (.1+.2) * 10^3 / cos ';
                                    assert.equal(calculator.getExpression(), expression, 'The term PI should have been removed from the expression');
                                    assert.equal(calculator.getPosition(), expression.length, 'The position is still at the end');
                                    assert.equal(calculator.getTokenIndex(), 13, 'The current token is the last one, but the index should have decreased');
                                    assert.equal(calculator.getTokens().length, 14, 'Another token should have gone');

                                    resolve();
                                })
                                .useCommand('stepDeleteLeft');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-stepDeleteRight.test', function () {
                                    calculator.off('command-stepDeleteRight.test');
                                    assert.equal(calculator.getExpression(), expression, 'Nothing should have changed');
                                    assert.equal(calculator.getPosition(), expression.length, 'The position should not have changed');
                                    assert.equal(calculator.getTokenIndex(), 13, 'The current token should not have changed');
                                    assert.equal(calculator.getTokens().length, 14, 'No token should have been removed');

                                    calculator.setPosition(1);
                                    assert.equal(calculator.getPosition(), 1, 'Moved to the beginning');
                                    assert.equal(calculator.getTokenIndex(), 0, 'Current token is the first');

                                    resolve();
                                })
                                .useCommand('stepDeleteRight');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-stepDeleteLeft.test', function () {
                                    calculator.off('command-stepDeleteLeft.test');
                                    expression = '(.1+.2) * 10^3 / cos ';
                                    assert.equal(calculator.getExpression(), expression, 'The leading space should have been removed');
                                    assert.equal(calculator.getPosition(), 0, 'The position should be now the very beginning');
                                    assert.equal(calculator.getTokenIndex(), 0, 'The current token should be the first');
                                    assert.equal(calculator.getTokens().length, 14, 'No token should have been removed');

                                    resolve();
                                })
                                .useCommand('stepDeleteLeft');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-stepDeleteLeft.test', function () {
                                    calculator.off('command-stepDeleteLeft.test');
                                    assert.equal(calculator.getExpression(), expression, 'Nothing should have changed');
                                    assert.equal(calculator.getPosition(), 0, 'The position should not have changed');
                                    assert.equal(calculator.getTokenIndex(), 0, 'The current token should be the first');
                                    assert.equal(calculator.getTokens().length, 14, 'No token should have been removed');

                                    calculator.setPosition(11);
                                    assert.equal(calculator.getPosition(), 11, 'Moved to the offset 11');
                                    assert.equal(calculator.getTokenIndex(), 9, 'Current token is at index 9');

                                    resolve();
                                })
                                .useCommand('stepDeleteLeft');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            calculator
                                .after('command-stepDeleteRight.test', function () {
                                    calculator.off('command-stepDeleteRight.test');
                                    expression = '(.1+.2) * 1^3 / cos ';
                                    assert.equal(calculator.getExpression(), expression, 'The term NUM0 should have been removed from the expression');
                                    assert.equal(calculator.getPosition(), 11, 'The position is still at the offset 11');
                                    assert.equal(calculator.getTokenIndex(), 9, 'The current token is at the same index, but should be the next one');
                                    assert.equal(calculator.getTokens().length, 13, 'Another token should have gone');

                                    resolve();
                                })
                                .useCommand('stepDeleteRight');
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
