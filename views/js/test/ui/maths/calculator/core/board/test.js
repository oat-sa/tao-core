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
    'ui/maths/calculator/core/plugin',
    'ui/maths/calculator/core/board',
    'ui/maths/calculator/core/terms'
], function ($, _, Promise, pluginFactory, calculatorBoardFactory, registeredTerms) {
    'use strict';

    var builtInCommands = {
        clear: {
            description: 'Clear expression',
            label: 'Clear',
            name: 'clear'
        },
        clearAll: {
            description: 'Clear all data',
            label: 'Clear All',
            name: 'clearAll'
        },
        execute: {
            description: 'Compute the expression',
            label: 'Execute',
            name: 'execute'
        },
        var: {
            description: 'Use a variable',
            label: 'Variable',
            name: 'var'
        },
        term: {
            description: 'Use a term',
            label: 'Term',
            name: 'term'
        }
    };

    QUnit.module('Factory');

    QUnit.test('module', function (assert) {
        QUnit.expect(3);
        assert.equal(typeof calculatorBoardFactory, 'function', "The module exposes a function");
        assert.equal(typeof calculatorBoardFactory('#fixture-api'), 'object', "The factory produces an object");
        assert.notStrictEqual(calculatorBoardFactory('#fixture-api'), calculatorBoardFactory('#fixture-api'), "The factory provides a different object on each call");
    });

    QUnit.cases([
        {title: 'init'},
        {title: 'destroy'},
        {title: 'render'},
        {title: 'setSize'},
        {title: 'show'},
        {title: 'hide'},
        {title: 'enable'},
        {title: 'disable'},
        {title: 'is'},
        {title: 'setState'},
        {title: 'getContainer'},
        {title: 'getElement'},
        {title: 'getTemplate'},
        {title: 'setTemplate'},
        {title: 'getConfig'}
    ]).test('inherited API ', function (data, assert) {
        var instance = calculatorBoardFactory('#fixture-api');
        QUnit.expect(1);
        assert.equal(typeof instance[data.title], 'function', 'The instance exposes a "' + data.title + '" function');
    });

    QUnit.cases([
        {title: 'on'},
        {title: 'off'},
        {title: 'trigger'},
        {title: 'spread'}
    ]).test('event API ', function (data, assert) {
        var instance = calculatorBoardFactory('#fixture-api');
        QUnit.expect(1);
        assert.equal(typeof instance[data.title], 'function', 'The instance exposes a "' + data.title + '" function');
    });

    QUnit.cases([
        {title: 'getExpression'},
        {title: 'setExpression'},
        {title: 'getPosition'},
        {title: 'setPosition'},
        {title: 'getTokens'},
        {title: 'getToken'},
        {title: 'getTokenIndex'},
        {title: 'getTokenizer'},
        {title: 'getVariable'},
        {title: 'hasVariable'},
        {title: 'setVariable'},
        {title: 'deleteVariable'},
        {title: 'getVariables'},
        {title: 'setVariables'},
        {title: 'deleteVariables'},
        {title: 'setLastResult'},
        {title: 'getLastResult'},
        {title: 'getCommand'},
        {title: 'hasCommand'},
        {title: 'getCommands'},
        {title: 'setCommand'},
        {title: 'deleteCommand'},
        {title: 'addTerm'},
        {title: 'useTerm'},
        {title: 'useTerms'},
        {title: 'useVariable'},
        {title: 'useCommand'},
        {title: 'replace'},
        {title: 'insert'},
        {title: 'clear'},
        {title: 'evaluate'},
        {title: 'runPlugins'},
        {title: 'getPlugins'},
        {title: 'getPlugin'},
        {title: 'getAreaBroker'},
        {title: 'setupMathsEvaluator'},
        {title: 'getMathsEvaluator'}
    ]).test('calculatorBoard API ', function (data, assert) {
        var instance = calculatorBoardFactory('#fixture-api');
        QUnit.expect(1);
        assert.equal(typeof instance[data.title], 'function', 'The instance exposes a "' + data.title + '" function');
    });

    QUnit.module('Life cycle');

    QUnit.asyncTest('init', function (assert) {
        var $container = $('#fixture-init');
        var initExpression = '.1+.2';
        var instance = calculatorBoardFactory($container, null, {
            expression: initExpression,
            position: initExpression.length
        });

        QUnit.expect(3);

        instance
            .after('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), initExpression, 'The expression is initialized');
                assert.equal(this.getPosition(), initExpression.length, 'The expression is initialized');
            })
            .after('render', function () {
                this.destroy();
            })
            .on('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('render', function (assert) {
        var $container = $('#fixture-render');

        var plugin1 = pluginFactory({
            name: 'plugin1',
            install: function installPlugin() {
                assert.ok(true, 'Plugin1 has been installed');
            },
            init: function initPlugin() {
                assert.ok(true, 'Plugin1 has been initialized');
            },
            render: function renderPlugin() {
                assert.ok(true, 'Plugin1 has been rendered');
            }
        });

        var instance;

        QUnit.expect(17);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container, [plugin1]);
        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(typeof areaBroker, 'undefined', 'The area broker is not yet created');
            })
            .on('ready', function () {
                var areaBroker = this.getAreaBroker();

                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.ok($container.children().first().is('.calculator'), 'The expected element is rendered');
                assert.equal($container.children().get(0), this.getElement().get(0), 'The container contains the right element');

                assert.equal(typeof areaBroker, 'object', 'The area broker is created');
                assert.equal(areaBroker.getContainer(), this.getElement(), 'The area broker is built on top of the element');

                assert.equal(typeof areaBroker.getScreenArea, 'function', 'The area broker has a getScreenArea() method');
                assert.equal(typeof areaBroker.getInputArea, 'function', 'The area broker has a getInputArea() method');
                assert.equal(typeof areaBroker.getKeyboardArea, 'function', 'The area broker has a getKeyboardArea() method');

                assert.equal(areaBroker.getScreenArea().get(0), $container.find('.screen').get(0), 'The sreen area is rendered');
                assert.equal(areaBroker.getInputArea().get(0), $container.find('.input').get(0), 'The input area is rendered');
                assert.equal(areaBroker.getKeyboardArea().get(0), $container.find('.keyboard').get(0), 'The keyboard area is rendered');

                this.destroy();
            })
            .on('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('destroy', function (assert) {
        var $container = $('#fixture-destroy');
        var plugin1 = pluginFactory({
            name: 'plugin1',
            install: function installPlugin() {
                assert.ok(true, 'Plugin1 has been installed');
            },
            init: function initPlugin() {
                assert.ok(true, 'Plugin1 has been initialized');
            },
            render: function renderPlugin() {
                assert.ok(true, 'Plugin1 has been rendered');
            },
            destroy: function renderPlugin() {
                assert.ok(true, 'Plugin1 has been destroyed');
            }
        });
        var instance;

        QUnit.expect(11);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container, [plugin1]);
        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(typeof areaBroker, 'undefined', 'The area broker is not yet created');
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal(typeof this.getAreaBroker(), 'object', 'The area broker is created');

                this.destroy();
            })
            .after('destroy', function () {
                assert.equal($container.children().length, 0, 'The container is now empty');
                assert.equal(this.getAreaBroker(), null, 'The area broker is destroyed');

                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.module('API');

    QUnit.asyncTest('plugins', function (assert) {
        var $container = $('#fixture-plugins');
        var config = {
            plugins: {
                plugin1: {
                    foo: 'bar'
                },
                plugin2: {
                    bar: 'foo'
                }
            }
        };
        var plugins = [
            pluginFactory({
                name: 'plugin1',
                install: function installPlugin() {
                    assert.ok(true, 'Plugin1 has been installed');
                },
                init: function initPlugin() {
                    assert.ok(true, 'Plugin1 has been initialized');
                },
                render: function renderPlugin() {
                    assert.ok(true, 'Plugin1 has been rendered');
                },
                destroy: function renderPlugin() {
                    assert.ok(true, 'Plugin1 has been destroyed');
                },
                enable: function enablePlugin() {
                    assert.ok(true, 'Plugin1 has been enabled');
                },
                disable: function disablePlugin() {
                    assert.ok(true, 'Plugin1 has been disabled');
                }
            }),
            pluginFactory({
                name: 'plugin2',
                install: function installPlugin() {
                    assert.ok(true, 'Plugin2 has been installed');
                },
                init: function initPlugin() {
                    assert.ok(true, 'Plugin2 has been initialized');
                },
                render: function renderPlugin() {
                    assert.ok(true, 'Plugin2 has been rendered');
                },
                destroy: function renderPlugin() {
                    assert.ok(true, 'Plugin2 has been destroyed');
                },
                enable: function enablePlugin() {
                    assert.ok(true, 'Plugin2 has been enabled');
                },
                disable: function disablePlugin() {
                    assert.ok(true, 'Plugin2 has been disabled');
                }
            })
        ];
        var instance;

        QUnit.expect(22);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container, plugins, config);
        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                var self = this;
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal(this.getPlugins().length, 2, 'Plugins are registered');
                assert.equal(this.getPlugin('plugin1').getName(), 'plugin1', 'Plugin1 is registered');
                assert.equal(this.getPlugin('plugin2').getName(), 'plugin2', 'Plugin2 is registered');
                assert.deepEqual(this.getPlugin('plugin1').getConfig(), config.plugins.plugin1, 'Plugin1 has the expected config');
                assert.deepEqual(this.getPlugin('plugin2').getConfig(), config.plugins.plugin2, 'Plugin2 has the expected config');

                this.runPlugins('disable')
                    .then(function () {
                        assert.ok(_.every(self.getPlugins(), function (plugin) {
                            return !plugin.getState('enabled');
                        }), 'Plugins have been disabled');
                        return self.runPlugins('enable');
                    })
                    .then(function () {
                        assert.ok(_.every(self.getPlugins(), function (plugin) {
                            return plugin.getState('enabled');
                        }), 'Plugins have been enabled');
                        self.destroy();
                    });
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('plugins - failure', function (assert) {
        var $container = $('#fixture-plugins');
        var pluginError = new TypeError('Should break here');
        var plugins = [
            pluginFactory({
                name: 'plugin1',
                install: function installPlugin() {
                    assert.ok(true, 'Plugin1 has been installed');
                },
                init: function initPlugin() {
                    assert.ok(true, 'Plugin1 has been initialized');
                },
                render: function renderPlugin() {
                    throw pluginError;
                },
                destroy: function renderPlugin() {
                    assert.ok(true, 'Plugin1 has been destroyed');
                }
            }),
            pluginFactory({
                name: 'plugin2',
                install: function installPlugin() {
                    assert.ok(true, 'Plugin2 has been installed');
                },
                init: function initPlugin() {
                    assert.ok(true, 'Plugin2 has been initialized');
                },
                render: function renderPlugin() {
                    assert.ok(false, 'Should not reach that point!');
                },
                destroy: function renderPlugin() {
                    assert.ok(true, 'Plugin2 has been destroyed');
                }
            })
        ];
        var instance;

        QUnit.expect(9);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container, plugins);
        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('error', function (err) {
                assert.equal(err, pluginError, 'The error has been catch!');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            });
    });

    QUnit.asyncTest('expression', function (assert) {
        var $container = $('#fixture-expression');
        var instance;

        QUnit.expect(7);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                var newExpression = '3+1';
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                this.setExpression();
                assert.equal(this.getExpression(), '', 'The expression is still empty');
                return new Promise(function (resolve) {
                    self
                        .on('expressionchange.set1', function (expression) {
                            self.off('expressionchange.set1');
                            assert.equal(expression, newExpression, 'New expression as been provided');
                            assert.equal(self.getExpression(), newExpression, 'New expression has been set');
                            resolve();
                        })
                        .setExpression(newExpression);
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('position', function (assert) {
        var $container = $('#fixture-position');
        var instance;

        QUnit.expect(10);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                var newExpression = '3+1';
                var newPosition = 2;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                assert.equal(this.getPosition(), 0, 'The position is 0');
                this.setPosition();
                assert.equal(this.getPosition(), 0, 'The position is still 0');
                return new Promise(function (resolve) {
                    self
                        .on('positionchange.set1', function (position) {
                            self.off('positionchange.set1');

                            assert.equal(position, newPosition, 'New position has been provided');
                            assert.equal(self.getPosition(), newPosition, 'New position has been set');

                            self.setPosition(-1);
                            assert.equal(self.getPosition(), 0, 'Negative position has been fixed');

                            self.setPosition(10);
                            assert.equal(self.getPosition(), newExpression.length, 'Too big position has been fixed');

                            resolve();
                        })
                        .setExpression(newExpression)
                        .setPosition(newPosition);
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('tokens', function (assert) {
        var $container = $('#fixture-tokens');
        var instance;

        QUnit.expect(52);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var tokens;

                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                assert.equal(this.getPosition(), 0, 'The position is 0');

                assert.deepEqual(this.getTokens(), [], 'No token for now');
                assert.equal(this.getTokenIndex(), 0, 'Token index is 0');
                assert.equal(this.getToken(), null, 'There is no token for now');

                this.setExpression('(.1 + .2) * 10^8');

                tokens = this.getTokens();
                assert.ok(_.isArray(tokens), 'Got a lis of tokens');
                assert.equal(tokens.length, 12, 'Found the expected number of tokens');
                assert.equal(tokens[0].type, 'LPAR', 'The expected term is found at position 0');
                assert.equal(tokens[0].offset, 0, 'The expected term is found at offset 0');
                assert.equal(tokens[1].type, 'DOT', 'The expected term is found at position 1');
                assert.equal(tokens[1].offset, 1, 'The expected term is found at offset 1');
                assert.equal(tokens[2].type, 'NUM1', 'The expected term is found at position 2');
                assert.equal(tokens[2].offset, 2, 'The expected term is found at offset 2');
                assert.equal(tokens[3].type, 'ADD', 'The expected term is found at position 3');
                assert.equal(tokens[3].offset, 4, 'The expected term is found at offset 4');
                assert.equal(tokens[4].type, 'DOT', 'The expected term is found at position 4');
                assert.equal(tokens[4].offset, 6, 'The expected term is found at offset 6');
                assert.equal(tokens[5].type, 'NUM2', 'The expected term is found at position 5');
                assert.equal(tokens[5].offset, 7, 'The expected term is found at offset 7');
                assert.equal(tokens[6].type, 'RPAR', 'The expected term is found at position 6');
                assert.equal(tokens[6].offset, 8, 'The expected term is found at offset 8');
                assert.equal(tokens[7].type, 'MUL', 'The expected term is found at position 7');
                assert.equal(tokens[7].offset, 10, 'The expected term is found at offset 10');
                assert.equal(tokens[8].type, 'NUM1', 'The expected term is found at position 8');
                assert.equal(tokens[8].offset, 12, 'The expected term is found at offset 12');
                assert.equal(tokens[9].type, 'NUM0', 'The expected term is found at position 9');
                assert.equal(tokens[9].offset, 13, 'The expected term is found at offset 13');
                assert.equal(tokens[10].type, 'POW', 'The expected term is found at position 10');
                assert.equal(tokens[10].offset, 14, 'The expected term is found at offset 14');
                assert.equal(tokens[11].type, 'NUM8', 'The expected term is found at position 11');
                assert.equal(tokens[11].offset, 15, 'The expected term is found at offset 15');

                assert.equal(typeof this.getTokenizer(), 'object', 'The tokenizer is provided');
                assert.equal(typeof this.getTokenizer().tokenize, 'function', 'The provided tokenizer is valid');
                assert.deepEqual(this.getTokens(), this.getTokenizer().tokenize(this.getExpression()), 'The tokenizer works as expected');

                this.setPosition(7);
                assert.equal(this.getTokenIndex(), 5, 'Token index at position 7 is 5');
                assert.equal(this.getToken().type, 'NUM2', 'Token is NUM2');

                this.setPosition(0);
                assert.equal(this.getTokenIndex(), 0, 'Token index at position 0 is 0');
                assert.equal(this.getToken().type, 'LPAR', 'Token is LPAR');

                this.setPosition(16);
                assert.equal(this.getTokenIndex(), 11, 'Token index at position 16 is 11');
                assert.equal(this.getToken().type, 'NUM8', 'Token is NUM8');


                this.setExpression(' 3+4 *$foo + sinh 1');
                tokens = this.getTokens();
                assert.ok(_.isArray(tokens), 'Got a lis of terms');
                assert.equal(tokens.length, 5, 'The expression has been tokenized in 5 terms');
                assert.equal(tokens[4].type, 'syntaxError', 'The expected error has been found');
                assert.equal(tokens[4].offset, 6, 'The expected error has been found at offset 6');

                this.setPosition(7);
                assert.equal(this.getTokenIndex(), 4, 'Token index at position 7 is 4');
                assert.equal(this.getToken().type, 'syntaxError', 'Token is syntaxError');

                this.setPosition(0);
                assert.equal(this.getTokenIndex(), 0, 'Token index at position 0 is 0');
                assert.equal(this.getToken().type, 'NUM3', 'Token is NUM3');

                this.setPosition(2);
                assert.equal(this.getTokenIndex(), 1, 'Token index at position 2 is 1');
                assert.equal(this.getToken().type, 'ADD', 'Token is ADD');

            })
            .after('ready', function () {
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('command', function (assert) {
        var expectedCommand = {
            name: 'FOO',
            label: 'bar',
            description: 'Command FOO bar'
        }
        var $container = $('#fixture-command');
        var instance;

        QUnit.expect(14);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                return new Promise(function (resolve) {
                    assert.equal(typeof self.getCommand('FOO'), 'undefined', 'The command FOO does not exist');
                    assert.ok(!self.hasCommand('FOO'), 'The command FOO is not registered');
                    assert.deepEqual(self.getCommands(), builtInCommands, 'Only builtin commands registered');
                    self
                        .on('commandadd', function (name) {
                            assert.equal(name, 'FOO', 'Command FOO added');
                            assert.ok(self.hasCommand('FOO'), 'The command FOO is now registered');
                            assert.deepEqual(self.getCommand('FOO'), expectedCommand, 'A descriptor is defined for command FOO');
                            assert.deepEqual(self.getCommands(), _.merge({FOO: expectedCommand}, builtInCommands), 'Can get the list of registered commands');

                            self.deleteCommand('FOO');
                        })
                        .on('commanddelete', function (name) {
                            assert.equal(name, 'FOO', 'Command FOO deleted');
                            assert.equal(typeof self.getCommand('FOO'), 'undefined', 'The command FOO does not exist anymore');
                            assert.ok(!self.hasCommand('FOO'), 'The command FOO is not registered');

                            resolve();
                        })
                        .setCommand(expectedCommand.name, expectedCommand.label, expectedCommand.description);
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('variable', function (assert) {
        var $container = $('#fixture-variable');
        var instance;

        QUnit.expect(17);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                return new Promise(function (resolve) {
                    assert.ok(!self.hasVariable('x'), 'The variable x is not registered');
                    assert.equal(typeof self.getVariable('x'), 'undefined', 'The variable x does not exist');
                    self
                        .on('variableadd', function (name, value) {
                            assert.equal(name, 'x', 'Variable x added');
                            assert.equal(typeof value, 'object', 'Value descriptor of variable x provided');
                            assert.equal(value.expression, '42', 'Expression of variable x provided');
                            assert.equal(value.value, '42', 'Value of variable x provided');
                            assert.equal(typeof self.getVariable('x'), 'object', 'The variable now x exists');
                            assert.equal(self.getVariable('x').expression, '42', 'The expression of variable x is available');
                            assert.equal(self.getVariable('x').value, '42', 'The value of variable x is available');
                            assert.ok(self.hasVariable('x'), 'The variable x is registered');

                            self.deleteVariable('x');
                        })
                        .on('variabledelete', function (name) {
                            assert.equal(name, 'x', 'Variable x deleted');
                            assert.equal(typeof self.getVariable('x'), 'undefined', 'The variable x does not exist anymore');
                            assert.ok(!self.hasVariable('x'), 'The variable x is not registered anymore');

                            resolve();
                        })
                        .setVariable('x', '42');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('variables', function (assert) {
        var defaultVariables = {
            'ans': 0
        };
        var expectedVariables = {
            'foo': 'bar',
            'x': '42',
            'y': '3'
        };
        var expectedResults = {
            'ans': 0,
            'foo': 0,
            'x': 42,
            'y': 3
        };
        var $container = $('#fixture-variables');
        var instance;

        QUnit.expect(20);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                var addedVariables = 0;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                return new Promise(function (resolve) {
                    assert.deepEqual(self.getVariables(), defaultVariables, 'Only default variables set for now');
                    self
                        .on('variableadd.set', function (name, value) {
                            assert.equal(typeof expectedVariables[name], 'string', 'Variable ' + name + ' added');
                            assert.equal(value.expression, expectedVariables[name], 'Value of variable ' + name + ' provided');
                            assert.equal(self.getVariable(name).expression, expectedVariables[name], 'The variable ' + name + ' now exists');

                            if (++addedVariables >= _.size(expectedVariables)) {
                                assert.deepEqual(self.getVariables(), expectedResults, 'All expected variables now set');
                                self.deleteVariables();
                            }
                        })
                        .on('variabledelete', function (name) {
                            self.off('.set');
                            assert.equal(name, null, 'Variables deleted');
                            assert.deepEqual(self.getVariables(), {}, 'No variable set anymore');

                            self.on('variableadd.reset', function (name, value) {
                                self.off('.reset');
                                assert.equal(name, registeredTerms.ANS.value, 'Variable ans added');
                                assert.equal(value.expression, '0', 'Variable ans reset');
                                assert.equal(self.getVariable(name).expression, '0', 'The variable ans now exists');

                                resolve();
                            });
                        })
                        .setVariables(expectedVariables);
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('addTerm - success', function (assert) {
        var expectedTermName = 'FOO';
        var expectedTerm = {
            label: 'Foo',
            value: 'bar'
        };
        var $container = $('#fixture-addterm');
        var instance;

        QUnit.expect(13);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                assert.equal(this.getPosition(), 0, 'The position is at the beginning');

                return new Promise(function (resolve) {
                    self
                        .on('termadd-FOO.test', function (term1) {
                            self.off('termadd-FOO.test');

                            assert.ok(true, 'The term FOO has been received');
                            assert.equal(term1, expectedTerm, 'The right term has been added');
                            assert.equal(self.getExpression(), expectedTerm.value, 'Expression has been properly updated');
                            assert.equal(self.getPosition(), expectedTerm.value.length, 'New position has been set');
                        })
                        .on('termadd.test', function (n1, term1) {
                            self.off('termadd.test');

                            assert.equal(n1, expectedTermName, 'The right term has been received');
                            assert.equal(term1, expectedTerm, 'The right term has been added');
                            assert.equal(self.getExpression(), expectedTerm.value, 'Expression has been properly updated');
                            assert.equal(self.getPosition(), expectedTerm.value.length, 'New position has been set');

                            resolve();
                        })
                        .addTerm(expectedTermName, expectedTerm);
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error termerror', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('addTerm - failure', function (assert) {
        var $container = $('#fixture-addterm');
        var instance;

        QUnit.expect(6);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');

                return new Promise(function (resolve) {
                    self
                        .on('termadd.test', function () {
                            self.off('termadd.test');

                            assert.ok(false, 'The term should not be added!');

                            resolve();
                        })
                        .on('termerror.test', function (err) {
                            self.off('termerror.test');

                            assert.ok(err instanceof TypeError, 'An error is triggered: the term is invalid');

                            self
                                .on('termerror.test', function (err) {
                                    self.off('termerror.test');

                                    assert.ok(err instanceof TypeError, 'An error is triggered: the term is invalid');

                                    resolve();
                                })
                                .addTerm('FOO', {});
                        })
                        .addTerm('FOO', 'BAR');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('useTerm - success', function (assert) {
        var $container = $('#fixture-useterm');
        var instance;

        QUnit.expect(36);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');

                return new Promise(function (resolve) {
                    self
                        .on('termadd-NUM2.test', function (term) {
                            self.off('termadd-NUM2.test');

                            assert.ok(true, 'The term NUM2 has been added');
                            assert.equal(term, registeredTerms.NUM2, 'The right term descriptor has been received for NUM2');
                        })
                        .on('termadd-NUM4.test', function (term) {
                            self.off('termadd-NUM4.test');

                            assert.ok(true, 'The term NUM4 has been added');
                            assert.equal(term, registeredTerms.NUM4, 'The right term descriptor has been received for NUM4');
                        })
                        .on('termadd-SUB.test', function (term) {
                            self.off('termadd-SUB.test');

                            assert.ok(true, 'The term SUB has been added');
                            assert.equal(term, registeredTerms.SUB, 'The right term descriptor has been received for SUB');
                        })
                        .on('termadd-SIN.test', function (term) {
                            self.off('termadd-SIN.test');

                            assert.ok(true, 'The term SIN has been added');
                            assert.equal(term, registeredTerms.SIN, 'The right term descriptor has been received for SIN');
                        })
                        .on('termadd.NUM4', function (n1, term1) {
                            self.off('termadd.NUM4');

                            assert.equal(n1, 'NUM4', 'The term NUM4 has been received');
                            assert.equal(term1, registeredTerms.NUM4, 'The term NUM4 has been added');
                            assert.equal(self.getExpression(), '4', 'Expression has been properly updated');
                            assert.equal(self.getPosition(), 1, 'New position has been set');

                            self
                                .on('termadd.NUM2', function (n2, term2) {
                                    self.off('termadd.NUM2');

                                    assert.equal(n2, 'NUM2', 'The term NUM2 has been received');
                                    assert.equal(term2, registeredTerms.NUM2, 'The term NUM2 has been added');
                                    assert.equal(self.getExpression(), '42', 'Expression has been properly updated');
                                    assert.equal(self.getPosition(), 2, 'New position has been set');

                                    self
                                        .on('termadd.SUB', function (n3, term3) {
                                            self.off('termadd.SUB');

                                            assert.equal(n3, 'SUB', 'The term SUB has been received');
                                            assert.equal(term3, registeredTerms.SUB, 'The term SUB has been added');
                                            assert.equal(self.getExpression(), '-42', 'Expression has been properly updated');
                                            assert.equal(self.getPosition(), 1, 'New position has been set');

                                            self
                                                .on('termadd.SIN', function (n4, term4) {
                                                    self.off('termadd.SIN');

                                                    assert.equal(n4, 'SIN', 'The term SIN has been received');
                                                    assert.equal(term4, registeredTerms.SIN, 'The term SIN has been added');
                                                    assert.equal(self.getExpression(), 'sin -42', 'Expression has been properly updated');
                                                    assert.equal(self.getPosition(), 4, 'New position has been set');

                                                    self
                                                        .on('termadd.NTHRT', function (n5, term5) {
                                                            self.off('termadd.NTHRT');

                                                            assert.equal(n5, 'NTHRT', 'The term NTHRT has been received');
                                                            assert.deepEqual(term5, _.defaults({value: '@nthrt'}, registeredTerms.NTHRT), 'The term NTHRT has been added');
                                                            assert.equal(self.getExpression(), '@nthrt sin -42', 'Expression has been properly updated');
                                                            assert.equal(self.getPosition(), 7, 'New position has been set');

                                                            self
                                                                .on('termadd.NUM4', function (n6, term6) {
                                                                    self.off('termadd.NUM4');

                                                                    assert.equal(n6, 'NUM4', 'The term NUM4 has been received');
                                                                    assert.deepEqual(term6, registeredTerms.NUM4, 'The term NUM4 has been added');
                                                                    assert.equal(self.getExpression(), '4 @nthrt sin -42', 'Expression has been properly updated');
                                                                    assert.equal(self.getPosition(), 2, 'New position has been set');

                                                                    resolve();
                                                                })
                                                                .setPosition(0)
                                                                .useTerm('NUM4');
                                                        })
                                                        .setPosition(0)
                                                        .useTerm('@NTHRT');
                                                })
                                                .setPosition(0)
                                                .useTerm('SIN');
                                        })
                                        .setPosition(0)
                                        .useTerm('SUB');
                                })
                                .useTerm('NUM2');
                        })
                        .useTerm('NUM4');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error termerror', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('useTerm - failure', function (assert) {
        var $container = $('#fixture-useterm');
        var instance;

        QUnit.expect(11);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');

                return new Promise(function (resolve) {
                    self
                        .on('termadd.NUM4', function (name, term) {
                            self.off('termadd.NUM4');

                            assert.equal(name, 'NUM4', 'The term NUM4 has been received');
                            assert.equal(term, registeredTerms.NUM4, 'The term NUM4 has been added');
                            assert.equal(self.getExpression(), '4', 'Expression has been properly updated');
                            assert.equal(self.getPosition(), 1, 'New position has been set');

                            self
                                .on('termadd.foo', function (n) {
                                    self.off('termadd.foo');

                                    assert.equal(n, 'foo', 'The term foo has been received');
                                    assert.ok(false, 'The term foo should not be added!');

                                    resolve();
                                })
                                .on('termerror.foo', function (e) {
                                    self.off('.foo');

                                    assert.ok(e instanceof TypeError, 'The term foo cannot be added');
                                    assert.equal(self.getExpression(), '4', 'Expression did not change');
                                    assert.equal(self.getPosition(), 1, 'Position did not change');

                                    resolve();
                                })
                                .useTerm('foo');
                        })
                        .useTerm('NUM4');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('useTerms - success', function (assert) {
        var $container = $('#fixture-useterms');
        var instance;

        QUnit.expect(41);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');

                return Promise.resolve()
                    .then(function() {
                        self.clear();
                        assert.equal(self.getExpression(), '', 'The expression is empty');
                        return new Promise(function (resolve) {
                            self
                                .on('termadd-NUM4.test', function (term) {
                                    self.off('termadd-NUM4.test');

                                    assert.ok(true, 'The term NUM4 has been added');
                                    assert.equal(term, registeredTerms.NUM4, 'The right term descriptor has been received for NUM4');
                                })
                                .on('termadd-SUB.test', function (term) {
                                    self.off('termadd-SUB.test');

                                    assert.ok(true, 'The term SUB has been added');
                                    assert.equal(term, registeredTerms.SUB, 'The right term descriptor has been received for SUB');
                                })
                                .on('termadd-NUM2.test', function (term) {
                                    self.off('termadd-NUM2.test');

                                    assert.ok(true, 'The term NUM2 has been added');
                                    assert.equal(term, registeredTerms.NUM2, 'The right term descriptor has been received for NUM2');
                                })
                                .on('termadd.NUM4', function (n1, term1) {
                                    self.off('termadd.NUM4');

                                    assert.equal(n1, 'NUM4', 'The term NUM4 has been received');
                                    assert.equal(term1, registeredTerms.NUM4, 'The term NUM4 has been added');
                                    assert.equal(self.getExpression(), '4', 'Expression has been properly updated');
                                    assert.equal(self.getPosition(), 1, 'New position has been set');

                                    self.on('termadd.SUB', function (n2, term2) {
                                        self.off('termadd.SUB');

                                        assert.equal(n2, 'SUB', 'The term SUB has been received');
                                        assert.equal(term2, registeredTerms.SUB, 'The term SUB has been added');
                                        assert.equal(self.getExpression(), '4-', 'Expression has been properly updated');
                                        assert.equal(self.getPosition(), 2, 'New position has been set');

                                        self.on('termadd.NUM2', function (n3, term3) {
                                            self.off('termadd.NUM2');

                                            assert.equal(n3, 'NUM2', 'The term NUM2 has been received');
                                            assert.equal(term3, registeredTerms.NUM2, 'The term NUM2 has been added');
                                            assert.equal(self.getExpression(), '4-2', 'Expression has been properly updated');
                                            assert.equal(self.getPosition(), 3, 'New position has been set');

                                            resolve();
                                        });
                                    });
                                })
                                .useTerms('NUM4 SUB NUM2');
                        });
                    })
                    .then(function() {
                        self.clear();
                        assert.equal(self.getExpression(), '', 'The expression is empty');
                        return new Promise(function (resolve) {
                            self
                                .on('termadd-NUM3.test', function (term) {
                                    self.off('termadd-NUM3.test');

                                    assert.ok(true, 'The term NUM3 has been added');
                                    assert.equal(term, registeredTerms.NUM3, 'The right term descriptor has been received for NUM3');
                                })
                                .on('termadd-ADD.test', function (term) {
                                    self.off('termadd-ADD.test');

                                    assert.ok(true, 'The term ADD has been added');
                                    assert.equal(term, registeredTerms.ADD, 'The right term descriptor has been received for ADD');
                                })
                                .on('termadd-NUM5.test', function (term) {
                                    self.off('termadd-NUM5.test');

                                    assert.ok(true, 'The term NUM5 has been added');
                                    assert.equal(term, registeredTerms.NUM5, 'The right term descriptor has been received for NUM5');
                                })
                                .on('termadd.NUM3', function (n1, term1) {
                                    self.off('termadd.NUM3');

                                    assert.equal(n1, 'NUM3', 'The term NUM3 has been received');
                                    assert.equal(term1, registeredTerms.NUM3, 'The term NUM3 has been added');
                                    assert.equal(self.getExpression(), '3', 'Expression has been properly updated');
                                    assert.equal(self.getPosition(), 1, 'New position has been set');

                                    self.on('termadd.ADD', function (n2, term2) {
                                        self.off('termadd.ADD');

                                        assert.equal(n2, 'ADD', 'The term ADD has been received');
                                        assert.equal(term2, registeredTerms.ADD, 'The term ADD has been added');
                                        assert.equal(self.getExpression(), '3+', 'Expression has been properly updated');
                                        assert.equal(self.getPosition(), 2, 'New position has been set');

                                        self.on('termadd.NUM5', function (n3, term3) {
                                            self.off('termadd.NUM5');

                                            assert.equal(n3, 'NUM5', 'The term NUM5 has been received');
                                            assert.equal(term3, registeredTerms.NUM5, 'The term NUM5 has been added');
                                            assert.equal(self.getExpression(), '3+5', 'Expression has been properly updated');
                                            assert.equal(self.getPosition(), 3, 'New position has been set');

                                            resolve();
                                        });
                                    });
                                })
                                .useTerms(['NUM3', 'ADD', 'NUM5']);
                        });
                    });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error termerror', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('useTerms - failure', function (assert) {
        var $container = $('#fixture-useterms');
        var instance;

        QUnit.expect(19);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');

                return Promise.resolve()
                    .then(function() {
                        self.clear();
                        assert.equal(self.getExpression(), '', 'The expression is empty');
                        return new Promise(function (resolve) {
                            self
                                .on('termadd.NUM4', function (name, term) {
                                    self.off('termadd.NUM4');

                                    assert.equal(name, 'NUM4', 'The term NUM4 has been received');
                                    assert.equal(term, registeredTerms.NUM4, 'The term NUM4 has been added');
                                    assert.equal(self.getExpression(), '4', 'Expression has been properly updated to 4');
                                    assert.equal(self.getPosition(), 1, 'Position has been set to 1');

                                    self
                                        .on('termadd.foo', function (n) {
                                            self.off('termadd.foo');

                                            assert.equal(n, 'foo', 'The term foo has been received');
                                            assert.ok(false, 'The term foo should not be added!');

                                            resolve();
                                        })
                                        .on('termerror.foo', function (e) {
                                            self.off('.foo');

                                            assert.ok(e instanceof TypeError, 'The term foo cannot be added');
                                            assert.equal(self.getExpression(), '4', 'Expression did not change');
                                            assert.equal(self.getPosition(), 1, 'Position did not change');

                                            resolve();
                                        });
                                })
                                .useTerms('NUM4 foo');
                        });
                    })
                    .then(function() {
                        self.clear();
                        assert.equal(self.getExpression(), '', 'The expression is empty');
                        return new Promise(function (resolve) {
                            self
                                .on('termadd.NUM2', function (name, term) {
                                    self.off('termadd.NUM2');

                                    assert.equal(name, 'NUM2', 'The term NUM2 has been received');
                                    assert.equal(term, registeredTerms.NUM2, 'The term NUM2 has been added');
                                    assert.equal(self.getExpression(), '2', 'Expression has been properly updated to 2');
                                    assert.equal(self.getPosition(), 1, 'Position has been set to 1');

                                    self
                                        .on('termadd.bar', function (n) {
                                            self.off('termadd.bar');

                                            assert.equal(n, 'bar', 'The term bar has been received');
                                            assert.ok(false, 'The term bar should not be added!');

                                            resolve();
                                        })
                                        .on('termerror.bar', function (e) {
                                            self.off('.bar');

                                            assert.ok(e instanceof TypeError, 'The term bar cannot be added');
                                            assert.equal(self.getExpression(), '2', 'Expression did not change');
                                            assert.equal(self.getPosition(), 1, 'Position did not change');

                                            resolve();
                                        });
                                })
                                .useTerms(['NUM2', 'bar']);
                        });
                    });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('useVariable - success', function (assert) {
        var defaultVariables = {
            'ans': 0
        };
        var expectedVariables = {
            'foo': 'bar',
            'x': '42',
            'y': '3'
        };
        var expectedResults = {
            'ans': 0,
            'foo': 0,
            'x': 42,
            'y': 3
        };
        var $container = $('#fixture-usevariable');
        var instance;

        QUnit.expect(35);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                assert.deepEqual(self.getVariables(), defaultVariables, 'Only default variables set for now');

                self.setVariables(expectedVariables);
                assert.deepEqual(self.getVariables(), expectedResults, 'All expected variables now set');

                return new Promise(function (resolve) {
                    self
                        .on('termadd-VAR_X.test', function (term) {
                            self.off('termadd-VAR_X.test');

                            assert.ok(true, 'The term VAR_X has been added');
                            assert.equal(typeof term, 'object', 'A term has been added');
                            assert.equal(term.label, 'x', 'The expected term has been added');
                            assert.equal(term.value, 'x', 'The expected term value has been added');
                        })
                        .on('termadd-VAR_Y.test', function (term) {
                            self.off('termadd-VAR_Y.test');

                            assert.ok(true, 'The term VAR_Y has been added');
                            assert.equal(typeof term, 'object', 'A term has been added');
                            assert.equal(term.label, 'y', 'The expected term has been added');
                            assert.equal(term.value, 'y', 'The expected term value has been added');
                        })
                        .on('termadd-VAR_FOO.test', function (term) {
                            self.off('termadd-VAR_FOO.test');

                            assert.ok(true, 'The term VAR_FOO has been added');
                            assert.equal(typeof term, 'object', 'A term has been added');
                            assert.equal(term.label, 'foo', 'The expected term has been added');
                            assert.equal(term.value, 'foo', 'The expected term value has been added');
                        })
                        .on('termadd.VAR_X', function (n1, term1) {
                            self.off('termadd.VAR_X');

                            assert.equal(n1, 'VAR_X', 'The right term has been received');
                            assert.equal(typeof term1, 'object', 'A term has been added');
                            assert.equal(term1.label, 'x', 'The expected term has been added');
                            assert.equal(term1.value, 'x', 'The expected term value has been added');
                            assert.equal(self.getExpression(), 'x', 'Expression has been properly updated');
                            assert.equal(self.getPosition(), 1, 'New position has been set');

                            self
                                .on('termadd.VAR_Y', function (n2, term2) {
                                    self.off('termadd.VAR_Y');

                                    assert.equal(n2, 'VAR_Y', 'The right term has been received');
                                    assert.equal(typeof term2, 'object', 'A term has been added');
                                    assert.equal(term2.label, 'y', 'The expected term has been added');
                                    assert.equal(term2.value, 'y', 'The expected term value has been added');
                                    assert.equal(self.getExpression(), 'x y', 'Expression has been properly updated');
                                    assert.equal(self.getPosition(), 3, 'New position has been set');

                                    self
                                        .on('termadd.VAR_FOO', function (n3, term3) {
                                            self.off('termadd.VAR_FOO');

                                            assert.equal(n3, 'VAR_FOO', 'The right term has been received');
                                            assert.equal(term3.label, 'foo', 'The expected term has been added');
                                            assert.equal(term3.value, 'foo', 'The expected term value has been added');
                                            assert.equal(self.getExpression(), 'foo x y', 'Expression has been properly updated');
                                            assert.equal(self.getPosition(), 4, 'New position has been set');

                                            resolve();
                                        })
                                        .setPosition(0)
                                        .useVariable('foo');
                                })
                                .useVariable('y');
                        })
                        .useVariable('x');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error termerror', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('useVariable - failure', function (assert) {
        var defaultVariables = {
            'ans': 0
        };
        var $container = $('#fixture-usevariable');
        var instance;

        QUnit.expect(8);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                assert.deepEqual(self.getVariables(), defaultVariables, 'Only default variables set for now');

                return new Promise(function (resolve) {
                    self
                        .on('termadd.varx', function (name) {
                            self.off('termadd.varx');

                            assert.equal(name, 'VAR_X', 'The term foo has been received');
                            assert.ok(false, 'The term VAR_X should not be added!');

                            resolve();
                        })
                        .on('termerror.varx', function (e) {
                            self.off('.varx');

                            assert.ok(e instanceof TypeError, 'The term cannot be added');
                            assert.equal(self.getExpression(), '', 'Expression did not change');
                            assert.equal(self.getPosition(), 0, 'Position did not change');

                            resolve();
                        })
                        .useVariable('x');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('useCommand - success', function (assert) {
        var $container = $('#fixture-command');
        var instance;

        QUnit.expect(21);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                assert.equal(this.getPosition(), 0, 'Position is at beginning');

                return new Promise(function (resolve) {
                    self
                        .on('command-foo', function (p) {
                            assert.ok(true, 'The foo command has been called');
                            assert.equal(typeof p, 'undefined', 'No command parameter');
                        })
                        .on('command-bar', function (p1, p2, p3) {
                            assert.ok(true, 'The bar command has been called');
                            assert.equal(p1, 'tip', 'The first parameter is correct');
                            assert.equal(p2, 'top', 'The second parameter is correct');
                            assert.equal(p3, 42, 'The third parameter is correct');
                        })
                        .on('command.test', function (n1, p) {
                            self.off('command.test');

                            assert.equal(n1, 'foo', 'The right command has been received');
                            assert.equal(typeof p, 'undefined', 'No command parameter');
                            assert.equal(self.getExpression(), '', 'The expression is still empty');
                            assert.equal(self.getPosition(), 0, 'Position did not change');

                            self
                                .on('command.test', function (n2, p1, p2, p3) {
                                    self.off('command.test');

                                    assert.equal(n2, 'bar', 'The right command has been received');
                                    assert.equal(p1, 'tip', 'The first parameter is correct');
                                    assert.equal(p2, 'top', 'The second parameter is correct');
                                    assert.equal(p3, 42, 'The third parameter is correct');
                                    assert.equal(self.getExpression(), '', 'The expression is still empty');
                                    assert.equal(self.getPosition(), 0, 'Position did not change');

                                    resolve();
                                })
                                .useCommand('bar', 'tip', 'top', 42);
                        })
                        .setCommand('foo')
                        .setCommand('bar')
                        .useCommand('foo');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error commanderror', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('useCommand - failure', function (assert) {
        var $container = $('#fixture-command');
        var instance;

        QUnit.expect(8);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                assert.equal(this.getPosition(), 0, 'Position is at beginning');

                return new Promise(function (resolve) {
                    self
                        .on('command.test', function (name) {
                            self.off('command.test');

                            assert.equal(name, 'foo', 'The command foo should not be received!');
                            assert.ok(false, 'The command foo should not be called!');

                            resolve();
                        })
                        .on('commanderror.test', function (e) {
                            self.off('commanderror.test');

                            assert.ok(e instanceof TypeError, 'The command cannot be called');
                            assert.equal(self.getExpression(), '', 'Expression did not change');
                            assert.equal(self.getPosition(), 0, 'Position did not change');

                            resolve();
                        })
                        .useCommand('foo');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('clear', function (assert) {
        var $container = $('#fixture-clear');
        var instance;

        QUnit.expect(10);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                var newExpression = '3+1';
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                assert.equal(this.getPosition(), 0, 'The position is 0');

                this.setExpression(newExpression);
                this.setPosition(newExpression.length);

                assert.equal(this.getExpression(), newExpression, 'The expression is set');
                assert.equal(this.getPosition(), newExpression.length, 'The position is set');

                return new Promise(function (resolve) {
                    self
                        .on('clear.test', function () {
                            self.off('clear.test');

                            assert.ok(true, 'The expression is cleared');
                            assert.equal(self.getExpression(), '', 'The expression is empty');
                            assert.equal(self.getPosition(), 0, 'The position is 0');
                            resolve();
                        })
                        .clear();
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('replace', function (assert) {
        var $container = $('#fixture-replace');
        var instance;

        QUnit.expect(21);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                var oldExpression = '3+1';
                var newExpression = '4*(4+1)';
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                assert.equal(this.getPosition(), 0, 'The position is 0');

                this.setExpression(oldExpression);
                this.setPosition(oldExpression.length);

                assert.equal(this.getExpression(), oldExpression, 'The old expression is set');
                assert.equal(this.getPosition(), oldExpression.length, 'The old position is set');

                return Promise.resolve()
                    .then(function() {
                        return new Promise(function (resolve) {
                            self
                                .on('expressionchange.test', function (expr) {
                                    self.off('expressionchange.test');
                                    assert.equal(expr, newExpression, 'The new expression is set');
                                })
                                .on('positionchange.test', function (pos) {
                                    self.off('positionchange.test');
                                    assert.equal(pos, newExpression.length, 'The new position is set');
                                })
                                .on('replace.test', function (oldExpr, oldPos) {
                                    self.off('replace.test');

                                    assert.ok(true, 'The expression is replaced');
                                    assert.equal(self.getExpression(), newExpression, 'The new expression is set');
                                    assert.equal(self.getPosition(), newExpression.length, 'The new position is set');

                                    assert.equal(oldExpr, oldExpression, 'The previous expression is provided');
                                    assert.equal(oldPos, oldExpression.length, 'The previous position is provided');
                                    resolve();
                                })
                                .replace(newExpression);
                        });
                    })
                    .then(function() {
                        return new Promise(function (resolve) {
                            self
                                .on('expressionchange.test', function (expr) {
                                    self.off('expressionchange.test');
                                    assert.equal(expr, oldExpression, 'The old expression is set');
                                })
                                .on('positionchange.test', function (pos) {
                                    self.off('positionchange.test');
                                    assert.equal(pos, 1, 'The arbitrary position is set');
                                })
                                .on('replace.test', function (oldExpr, oldPos) {
                                    self.off('replace.test');

                                    assert.ok(true, 'The expression is replaced');
                                    assert.equal(self.getExpression(), oldExpression, 'The old expression is set');
                                    assert.equal(self.getPosition(), 1, 'The arbitrary position is set');

                                    assert.equal(oldExpr, newExpression, 'The previous expression is provided');
                                    assert.equal(oldPos, newExpression.length, 'The previous position is provided');
                                    resolve();
                                })
                                .replace(oldExpression, 1);
                        });
                    });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('insert', function (assert) {
        var $container = $('#fixture-insert');
        var instance;

        QUnit.expect(14);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                var oldExpression = '3+1';
                var oldPosition = oldExpression.length - 1;
                var insertedExpression = '2*(5-4)-';
                var newExpression = '3+2*(5-4)-1';
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                assert.equal(this.getPosition(), 0, 'The position is 0');

                this.setExpression(oldExpression);
                this.setPosition(oldPosition);

                assert.equal(this.getExpression(), oldExpression, 'The old expression is set');
                assert.equal(this.getPosition(), oldPosition, 'The old position is set');

                return new Promise(function (resolve) {
                    self
                        .on('expressionchange.test', function (expr) {
                            assert.equal(expr, newExpression, 'The new expression is set');
                        })
                        .on('positionchange.test', function (pos) {
                            assert.equal(pos, oldPosition + insertedExpression.length, 'The new position is set');
                        })
                        .on('insert.test', function (oldExpr, oldPos) {
                            self.off('insert.test');

                            assert.ok(true, 'The expression is inserted');
                            assert.equal(self.getExpression(), newExpression, 'The new expression is set');
                            assert.equal(self.getPosition(), oldPosition + insertedExpression.length, 'new The position is set');

                            assert.equal(oldExpr, oldExpression, 'The previous expression is provided');
                            assert.equal(oldPos, oldPosition, 'The previous position is provided');
                            resolve();
                        })
                        .insert(insertedExpression);
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('evaluate - success', function (assert) {
        var $container = $('#fixture-evaluate');
        var initExpression = '.1+.2';
        var expectedResult = '0.3';
        var instance;

        QUnit.expect(10);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container, null, {
            expression: initExpression,
            position: initExpression.length
        });
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), initExpression, 'The expression is initialized');
                assert.equal(this.getPosition(), initExpression.length, 'The expression is initialized');
                return Promise.resolve()
                    .then(function() {
                        return new Promise(function (resolve) {
                            self.on('evaluate.expr', function (result) {
                                self.off('evaluate.expr');
                                assert.equal(result.value, expectedResult, 'The expression has been properly evaluated');
                                resolve();
                            });
                            assert.equal(self.evaluate().value, expectedResult, 'The expression is successfully evaluated');
                        });
                    })
                    .then(function() {
                        return new Promise(function (resolve) {
                            self.clear();
                            assert.equal(self.getExpression(), '', 'The expression is cleared');
                            self.on('evaluate.empty', function (result) {
                                self.off('evaluate.empty');
                                assert.equal(result.value, '0', 'An empty expression should be evaluated as 0');
                                resolve();
                            });
                            assert.equal(self.evaluate().value, '0', 'The empty expression is evaluated to 0');
                        });
                    });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error syntaxerror', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('evaluate - error', function (assert) {
        var $container = $('#fixture-evaluate');
        var initExpression = '.1+*.2';
        var instance;

        QUnit.expect(7);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container, null, {
            expression: initExpression,
            position: initExpression.length
        });
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), initExpression, 'The expression is initialized');
                assert.equal(this.getPosition(), initExpression.length, 'The expression is initialized');
                return new Promise(function (resolve) {
                    self
                        .on('evaluate', function () {
                            assert.ok(false, 'The expression should not be evaluated');
                            resolve();
                        })
                        .on('syntaxerror', function (e) {
                            assert.ok(e instanceof Error, 'The evaluation of the expression has failed');
                            resolve();
                        });
                    assert.equal(self.evaluate(), null, 'The expression cannot be evaluated');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('evaluate variable - success', function (assert) {
        var $container = $('#fixture-evaluate');
        var initExpression = '(.1+.2)*x';
        var expectedResult = '0.9';
        var instance;

        QUnit.expect(7);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container, null, {
            expression: initExpression,
            position: initExpression.length
        });
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), initExpression, 'The expression is initialized');
                assert.equal(this.getPosition(), initExpression.length, 'The expression is initialized');
                return new Promise(function (resolve) {
                    self.on('evaluate', function (result) {
                        assert.equal(result.value, expectedResult, 'The expression has been properly evaluated');
                        resolve();
                    });
                    self.setVariable('x', '3');
                    assert.equal(self.evaluate().value, expectedResult, 'The expression is successfully evaluated');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error syntaxerror', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('evaluate variable - failure', function (assert) {
        var $container = $('#fixture-evaluate');
        var initExpression = '(.1+.2)*x';
        var instance;

        QUnit.expect(7);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container, null, {
            expression: initExpression,
            position: initExpression.length
        });
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), initExpression, 'The expression is initialized');
                assert.equal(this.getPosition(), initExpression.length, 'The expression is initialized');
                return new Promise(function (resolve) {
                    self
                        .on('evaluate', function () {
                            assert.ok(false, 'The expression should not be evaluated');
                            resolve();
                        })
                        .on('syntaxerror', function (e) {
                            assert.ok(e instanceof Error, 'The evaluation of the expression has failed');
                            resolve();
                        });
                    assert.equal(self.evaluate(), null, 'The expression cannot be evaluated');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('ans variable', function (assert) {
        var $container = $('#fixture-ans');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                function evaluatePromise(expression) {
                    return new Promise(function(resolve, reject) {
                        calculator
                            .on('error.test', function(err) {
                                calculator.off('.test');
                                reject(err);
                            })
                            .after('evaluate.test', function(result) {
                                calculator.off('.test');
                                resolve(result);
                            })
                            .replace(expression)
                            .evaluate();
                    });
                }

                Promise.resolve()
                    .then(function () {
                        assert.equal(calculator.hasVariable('ans'), true, 'The variable ans is defined');
                        assert.equal(calculator.getVariable('ans').value, '0', 'The variable ans contains 0');
                        assert.equal(calculator.getLastResult().value, '0', 'The last result contains 0');

                        return evaluatePromise('ans');
                    })
                    .then(function (result) {
                        assert.equal(result.value, '0', 'The expression "ans" is evaluated to 0');
                        assert.equal(calculator.getVariable('ans').value, '0', 'The variable ans now contains 0');
                        assert.equal(calculator.getLastResult().value, '0', 'The last result now contains 0');

                        return evaluatePromise('40+2');
                    })
                    .then(function (result) {
                        assert.equal(result.value, '42', 'The expression "40+2" is evaluated to 42');
                        assert.equal(calculator.getVariable('ans').value, '42', 'The variable ans now contains 42');
                        assert.equal(calculator.getLastResult().value, '42', 'The last result now contains 42');

                        return evaluatePromise('ans*2');
                    })
                    .then(function (result) {
                        assert.equal(result.value, '84', 'The expression "ans*2" is evaluated to 84');
                        assert.equal(calculator.getVariable('ans').value, '84', 'The variable ans now contains 84');
                        assert.equal(calculator.getLastResult().value, '84', 'The last result now contains 84');

                        return evaluatePromise('3*2');
                    })
                    .then(function (result) {
                        assert.equal(result.value, '6', 'The expression "3*2" is evaluated to 6');
                        assert.equal(calculator.getVariable('ans').value, '6', 'The variable ans now contains 6');
                        assert.equal(calculator.getLastResult().value, '6', 'The last result now contains 6');

                        return evaluatePromise('sqrt -2');
                    })
                    .then(function (result) {
                        assert.equal(String(result.value), 'NaN', 'The expression "sqrt -2" is evaluated to NaN');
                        assert.equal(calculator.getVariable('ans').value, '0', 'The variable ans now contains 0');
                        assert.equal(calculator.getLastResult().value, '0', 'The last result now contains 0');
                    })
                    .then(function () {
                        calculator.setLastResult('42');
                        assert.equal(calculator.getVariable('ans').value, '42', 'The variable ans now contains 42');
                        assert.equal(calculator.getLastResult().value, '42', 'The last result now contains 42');
                    })
                    .then(function () {
                        calculator.setLastResult('Infinity');
                        assert.equal(calculator.getVariable('ans').value, '0', 'The variable ans now contains 0');
                        assert.equal(calculator.getLastResult().value, '0', 'The last result now contains 0');
                    })
                    .then(function () {
                        calculator.setLastResult('NaN');
                        assert.equal(calculator.getVariable('ans').value, '0', 'The variable ans now contains 0');
                        assert.equal(calculator.getLastResult().value, '0', 'The last result now contains 0');
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
            })
            .after('destroy', function () {
                assert.equal(calculator.hasVariable('ans'), false, 'The variable ans has been removed');
                QUnit.start();
            });

        QUnit.expect(25);
    });

    QUnit.asyncTest('built-in commands - clear', function (assert) {
        var $container = $('#fixture-builtin');
        var instance;

        QUnit.expect(10);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                var newExpression = '3+1';
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                assert.equal(this.getPosition(), 0, 'The position is 0');

                this.setExpression(newExpression);
                this.setPosition(newExpression.length);

                assert.equal(this.getExpression(), newExpression, 'The expression is set');
                assert.equal(this.getPosition(), newExpression.length, 'The position is set');

                return new Promise(function (resolve) {
                    self
                        .on('clear.test', function () {
                            self.off('clear.test');

                            assert.ok(true, 'The expression is cleared');
                            assert.equal(self.getExpression(), '', 'The expression is empty');
                            assert.equal(self.getPosition(), 0, 'The position is 0');
                            resolve();
                        })
                        .useCommand('clear');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('built-in commands - clearAll', function (assert) {
        var $container = $('#fixture-builtin');
        var instance;

        QUnit.expect(10);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                var newExpression = '3+1';
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                assert.equal(this.getPosition(), 0, 'The position is 0');

                this.setExpression(newExpression);
                this.setPosition(newExpression.length);

                assert.equal(this.getExpression(), newExpression, 'The expression is set');
                assert.equal(this.getPosition(), newExpression.length, 'The position is set');

                return new Promise(function (resolve) {
                    self
                        .on('clear.test', function () {
                            self.off('clear.test');

                            assert.ok(true, 'The expression is cleared');
                            assert.equal(self.getExpression(), '', 'The expression is empty');
                            assert.equal(self.getPosition(), 0, 'The position is 0');
                            resolve();
                        })
                        .useCommand('clearAll');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('built-in commands - execute', function (assert) {
        var $container = $('#fixture-builtin');
        var initExpression = '.1+.2';
        var expectedResult = '0.3';
        var instance;

        QUnit.expect(6);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container, null, {
            expression: initExpression,
            position: initExpression.length
        });
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), initExpression, 'The expression is initialized');
                assert.equal(this.getPosition(), initExpression.length, 'The expression is initialized');
                return new Promise(function (resolve) {
                    self
                        .on('evaluate', function (result) {
                            assert.equal(result.value, expectedResult, 'The expression has been properly evaluated');
                            resolve();
                        })
                        .useCommand('execute');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error syntaxerror', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('built-in commands - var and term', function (assert) {
        var $container = $('#fixture-builtin');
        var initExpression = '.1+.2';
        var expectedExpression = '.1+.2+x^2';
        var expectedResult = '9.3';
        var instance;

        QUnit.expect(8);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container, null, {
            expression: initExpression,
            position: initExpression.length
        });
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), initExpression, 'The expression is initialized');
                assert.equal(this.getPosition(), initExpression.length, 'The expression is initialized');
                return new Promise(function (resolve) {
                    self
                        .after('evaluate.test', function (result) {
                            self.off('evaluate.test');
                            assert.equal(self.getExpression(), expectedExpression, 'The expression has been updated');
                            assert.equal(self.getPosition(), expectedExpression.length, 'The position has been updated');
                            assert.equal(result.value, expectedResult, 'The expression has been properly evaluated');
                            resolve();
                        })
                        .setVariable('x', '3')
                        .useCommand('term', 'ADD')
                        .useCommand('var', 'x')
                        .useCommand('term', 'POW NUM2')
                        .evaluate();
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error syntaxerror', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('mathsEvaluator', function (assert) {
        var $container = $('#fixture-evaluator');
        var instance;

        QUnit.expect(11);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var mathsEvaluator = this.getMathsEvaluator();
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is initialized');
                assert.equal(this.getPosition(), 0, 'The expression is initialized');
                assert.equal(typeof mathsEvaluator, 'function', 'The mathsEvaluator is provided');
                assert.equal(mathsEvaluator('sin(PI/2)').value, '1', 'The mathsEvaluator works in radian');

                this.getConfig().maths = {degree: true};
                assert.equal(this.setupMathsEvaluator(), this, 'setupMathsEvaluator returns the instance');
                assert.notEqual(this.getMathsEvaluator(), mathsEvaluator, 'The mathsEvaluator should have been replaced');
                mathsEvaluator = this.getMathsEvaluator();
                assert.notEqual(mathsEvaluator('sin(PI/2)').value, '1', 'The mathsEvaluator should not work in radian anymore');
                assert.equal(mathsEvaluator('sin 90').value, '1', 'The mathsEvaluator now works in degree');
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error syntaxerror', function (err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('0 and operator', function (assert) {
        var $container = $('#fixture-zero-op');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                function addTermPromise(term) {
                    return new Promise(function(resolve, reject) {
                        calculator
                            .on('error.test', function(err) {
                                calculator.off('.test');
                                reject(err);
                            })
                            .after('termadd.test', function() {
                                calculator.off('.test');
                                resolve();
                            })
                            .useTerm(term);
                    });
                }

                calculator.replace('0');

                Promise.resolve()
                    .then(function () {
                        assert.equal(calculator.getExpression(), '0', 'The expression should be set to 0');
                        assert.equal(calculator.getPosition(), 1, 'The position should be set to 1');
                        return addTermPromise('NUM0');
                    })
                    .then(function () {
                        assert.equal(calculator.getExpression(), '0', 'The expression should still be 0');
                        assert.equal(calculator.getPosition(), 1, 'The position should still be 1');
                        return addTermPromise('ADD');
                    })
                    .then(function () {
                        assert.equal(calculator.getExpression(), '0+', 'The expression should be now 0+');
                        assert.equal(calculator.getPosition(), 2, 'The position should be now 2');
                        return addTermPromise('NUM5');
                    })
                    .then(function () {
                        assert.equal(calculator.getExpression(), '0+5', 'The expression should be now 0+5');
                        assert.equal(calculator.getPosition(), 3, 'The position should be now 3');
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
            })
            .on('destroy', function () {
                QUnit.start();
            });

        QUnit.expect(8);
    });

    QUnit
        .cases([{
            title: 'PI',
            term: 'PI',
            expression: 'PI',
            value: 'PI',
            type: 'constant',
            label: registeredTerms.PI.label
        }, {
            title: '3',
            term: 'NUM3',
            expression: '3',
            value: '3',
            type: 'digit',
            label: '3'
        }, {
            title: '(',
            term: 'LPAR',
            expression: '(',
            value: '(',
            type: 'aggregator',
            label: '('
        }, {
            title: 'sqrt',
            term: 'SQRT',
            expression: 'sqrt',
            value: 'sqrt',
            type: 'function',
            label: registeredTerms.SQRT.label
        }, {
            title: 'nthrt',
            term: '@NTHRT',
            expression: '0 @nthrt',
            value: '@nthrt',
            type: 'function',
            label: registeredTerms.NTHRT.label
        }])
        .asyncTest('0 and const', function (data, assert) {
            var $container = $('#fixture-zero-const');
            var calculator = calculatorBoardFactory($container)
                .on('ready', function () {
                    function addTermPromise(term) {
                        return new Promise(function(resolve, reject) {
                            calculator
                                .on('error.test', function(err) {
                                    calculator.off('.test');
                                    reject(err);
                                })
                                .after('termadd.test', function() {
                                    calculator.off('.test');
                                    resolve();
                                })
                                .useTerm(term);
                        });
                    }

                    calculator.replace('0');

                    Promise.resolve()
                        .then(function () {
                            assert.equal(calculator.getExpression(), '0', 'The expression should be set to 0');
                            assert.equal(calculator.getPosition(), 1, 'The position should be set to 1');
                            return addTermPromise('NUM0');
                        })
                        .then(function () {
                            assert.equal(calculator.getExpression(), '0', 'The expression should still be 0');
                            assert.equal(calculator.getPosition(), 1, 'The position should still be 1');
                            return addTermPromise(data.term);
                        })
                        .then(function () {
                            assert.equal(calculator.getExpression(), data.expression, 'The expression should be ' + data.expression);
                            assert.equal(calculator.getPosition(), data.expression.length, 'The position should be ' + data.expression.length);
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
                })
                .on('destroy', function () {
                    QUnit.start();
                });

            QUnit.expect(6);
        });

    QUnit.asyncTest('ans and operator', function (assert) {
        var $container = $('#fixture-ans-op');
        var calculator = calculatorBoardFactory($container)
            .on('ready', function () {
                function addTermPromise(term) {
                    return new Promise(function(resolve, reject) {
                        calculator
                            .on('error.test', function(err) {
                                calculator.off('.test');
                                reject(err);
                            })
                            .after('termadd.test', function() {
                                calculator.off('.test');
                                resolve();
                            })
                            .useTerm(term);
                    });
                }

                calculator.replace('ans');

                Promise.resolve()
                    .then(function () {
                        assert.equal(calculator.getExpression(), 'ans', 'The expression should be set to ans');
                        assert.equal(calculator.getPosition(), 3, 'The position should be set to 3');
                        return addTermPromise('ANS');
                    })
                    .then(function () {
                        assert.equal(calculator.getExpression(), 'ans', 'The expression should still be ans');
                        assert.equal(calculator.getPosition(), 3, 'The position should still be 3');
                        return addTermPromise('ADD');
                    })
                    .then(function () {
                        assert.equal(calculator.getExpression(), 'ans+', 'The expression should be now ans+');
                        assert.equal(calculator.getPosition(), 4, 'The position should be now 4');
                        return addTermPromise('NUM8');
                    })
                    .then(function () {
                        assert.equal(calculator.getExpression(), 'ans+8', 'The expression should be now ans+8');
                        assert.equal(calculator.getPosition(), 5, 'The position should be now 5');
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
            })
            .on('destroy', function () {
                QUnit.start();
            });

        QUnit.expect(8);
    });

    QUnit
        .cases([{
            title: 'PI',
            term: 'PI',
            expression: 'PI',
            value: 'PI',
            type: 'constant',
            label: registeredTerms.PI.label
        }, {
            title: '3',
            term: 'NUM3',
            expression: '3',
            value: '3',
            type: 'digit',
            label: '3'
        }, {
            title: '(',
            term: 'LPAR',
            expression: '(',
            value: '(',
            type: 'aggregator',
            label: '('
        }, {
            title: 'sqrt',
            term: 'SQRT',
            expression: 'sqrt',
            value: 'sqrt',
            type: 'function',
            label: registeredTerms.SQRT.label
        }, {
            title: 'nthrt',
            term: '@NTHRT',
            expression: 'ans @nthrt',
            value: '@nthrt',
            type: 'function',
            label: registeredTerms.NTHRT.label
        }])
        .asyncTest('ans and const', function (data, assert) {
            var $container = $('#fixture-ans-const');
            var calculator = calculatorBoardFactory($container)
                .on('ready', function () {
                    function addTermPromise(term) {
                        return new Promise(function(resolve, reject) {
                            calculator
                                .on('error.test', function(err) {
                                    calculator.off('.test');
                                    reject(err);
                                })
                                .after('termadd.test', function() {
                                    calculator.off('.test');
                                    resolve();
                                })
                                .useTerm(term);
                        });
                    }

                    calculator.replace('ans');

                    Promise.resolve()
                        .then(function () {
                            assert.equal(calculator.getExpression(), 'ans', 'The expression should be set to ans');
                            assert.equal(calculator.getPosition(), 3, 'The position should be set to 3');
                            return addTermPromise('ANS');
                        })
                        .then(function () {
                            assert.equal(calculator.getExpression(), 'ans', 'The expression should still be ans');
                            assert.equal(calculator.getPosition(), 3, 'The position should still be 3');
                            return addTermPromise(data.term);
                        })
                        .then(function () {
                            assert.equal(calculator.getExpression(), data.expression, 'The expression should be ' + data.expression);
                            assert.equal(calculator.getPosition(), data.expression.length, 'The position should be ' + data.expression.length);
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
                })
                .on('destroy', function () {
                    QUnit.start();
                });

            QUnit.expect(6);
        });
});
