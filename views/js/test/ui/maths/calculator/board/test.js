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
    'ui/maths/calculator/plugin',
    'ui/maths/calculator/board',
    'ui/maths/calculator/terms'
], function ($, _, Promise, pluginFactory, calculatorBoardFactory, registeredTerms) {
    'use strict';

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
        {title: 'getVariable'},
        {title: 'setVariable'},
        {title: 'deleteVariable'},
        {title: 'getVariables'},
        {title: 'setVariables'},
        {title: 'deleteVariables'},
        {title: 'runPlugins'},
        {title: 'getPlugins'},
        {title: 'getPlugin'},
        {title: 'getAreaBroker'}
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
            .on('error', function(err) {
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
            .on('error', function(err) {
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
            .after('render', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal(typeof this.getAreaBroker(), 'object', 'The area broker is created');

                this.destroy();
            })
            .after('destroy', function () {
                assert.equal($container.children().length, 0, 'The container is now empty');
                assert.equal(this.getAreaBroker(), null, 'The area broker is destroyed');

                QUnit.start();
            })
            .on('error', function(err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.module('API');

    QUnit.asyncTest('plugins', function (assert) {
        var $container = $('#fixture-plugins');
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

        QUnit.expect(20);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container, plugins);
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
            .on('error', function(err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('expression', function (assert) {
        var $container = $('#fixture-expression');
        var instance;

        QUnit.expect(6);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                var newExpression = '3+1';
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
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
            .on('error', function(err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('position', function (assert) {
        var $container = $('#fixture-position');
        var instance;

        QUnit.expect(8);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                var newExpression = '3+1';
                var newPosition = 2;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                return new Promise(function (resolve) {
                    self
                        .on('positionchange.set1', function (position) {
                            self.off('positionchange.set1');

                            assert.equal(position, newPosition, 'New position as been provided');
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
            .on('error', function(err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('variable', function (assert) {
        var $container = $('#fixture-variable');
        var instance;

        QUnit.expect(10);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                return new Promise(function (resolve) {
                    assert.equal(typeof self.getVariable('x'), 'undefined', 'The variable x does not exist');
                    self
                        .on('variableadd', function(name, value) {
                            assert.equal(name, 'x', 'Variable x added');
                            assert.equal(value, '42', 'Value of variable x provided');
                            assert.equal(self.getVariable('x'), '42', 'The variable x now exists');

                            self.deleteVariable('x');
                        })
                        .on('variabledelete', function(name) {
                            assert.equal(name, 'x', 'Variable x deleted');
                            assert.equal(typeof self.getVariable('x'), 'undefined', 'The variable x does not exist anymore');

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
            .on('error', function(err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('variables', function (assert) {
        var expectedVariables = {
            'foo': 'bar',
            'x': '42',
            'y': '3'
        };
        var $container = $('#fixture-variables');
        var instance;

        QUnit.expect(17);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                var addedVariables = 0;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                return new Promise(function (resolve) {
                    assert.deepEqual(self.getVariables(), {}, 'No variable set for now');
                    self
                        .on('variableadd', function(name, value) {
                            assert.equal(typeof expectedVariables[name], 'string', 'Variable ' + name + ' added');
                            assert.equal(value, expectedVariables[name], 'Value of variable ' + name + ' provided');
                            assert.equal(self.getVariable(name), expectedVariables[name], 'The variable ' + name + ' now exists');

                            if ( ++addedVariables >= _.size(expectedVariables)) {
                                assert.deepEqual(self.getVariables(), expectedVariables, 'All expected variables now set');
                                self.deleteVariables();
                            }
                        })
                        .on('variabledelete', function(name) {
                            assert.equal(name, null, 'Variables deleted');
                            assert.deepEqual(self.getVariables(), {}, 'No variable set anymore');

                            resolve();
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
            .on('error', function(err) {
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

        QUnit.expect(9);

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
            .on('error termerror', function(err) {
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
            .on('error', function(err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('useTerm - success', function (assert) {
        var $container = $('#fixture-useterm');
        var instance;

        QUnit.expect(16);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');

                return new Promise(function (resolve) {
                    self
                        .on('termadd.test', function (n1, term1) {
                            self.off('termadd.test');

                            assert.equal(n1, 'NUM4', 'The right term has been received');
                            assert.equal(term1, registeredTerms.NUM4, 'The right term has been added');
                            assert.equal(self.getExpression(), '4', 'Expression has been properly updated');
                            assert.equal(self.getPosition(), 1, 'New position has been set');

                            self
                                .on('termadd.test', function (n2, term2) {
                                    self.off('termadd.test');

                                    assert.equal(n2, 'NUM2', 'The right term has been received');
                                    assert.equal(term2, registeredTerms.NUM2, 'The right term has been added');
                                    assert.equal(self.getExpression(), '42', 'Expression has been properly updated');
                                    assert.equal(self.getPosition(), 2, 'New position has been set');

                                    self
                                        .on('termadd.test', function (n3, term3) {
                                            self.off('termadd.test');

                                            assert.equal(n3, 'SUB', 'The right term has been received');
                                            assert.equal(term3, registeredTerms.SUB, 'The right term has been added');
                                            assert.equal(self.getExpression(), '-42', 'Expression has been properly updated');
                                            assert.equal(self.getPosition(), 1, 'New position has been set');

                                            resolve();
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
            .on('error termerror', function(err) {
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
                        .on('termadd.test', function (name, term) {
                            self.off('termadd.test');

                            assert.equal(name, 'NUM4', 'The right term has been received');
                            assert.equal(term, registeredTerms.NUM4, 'The right term has been added');
                            assert.equal(self.getExpression(), '4', 'Expression has been properly updated');
                            assert.equal(self.getPosition(), 1, 'New position has been set');

                            self
                                .on('termadd.test', function () {
                                    self.off('termadd.test');

                                    assert.ok(false, 'The term should not be added!');

                                    resolve();
                                })
                                .on('termerror.test', function (e) {
                                    self.off('termerror.test');

                                    assert.ok(e instanceof TypeError, 'The term cannot be added');
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
            .on('error', function(err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('useVariable - success', function (assert) {
        var expectedVariables = {
            'foo': 'bar',
            'x': '42',
            'y': '3'
        };
        var $container = $('#fixture-usevariable');
        var instance;

        QUnit.expect(23);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = calculatorBoardFactory($container);
        instance
            .on('init', function () {
                var self = this;
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getExpression(), '', 'The expression is empty');
                assert.deepEqual(self.getVariables(), {}, 'No variable set for now');

                self.setVariables(expectedVariables);
                assert.deepEqual(self.getVariables(), expectedVariables, 'All expected variables now set');

                return new Promise(function (resolve) {
                    self
                        .on('termadd.test', function (n1, term1) {
                            self.off('termadd.test');

                            assert.equal(n1, 'VAR_X', 'The right term has been received');
                            assert.equal(typeof term1, 'object', 'A term has been added');
                            assert.equal(term1.label, 'x', 'The expected term has been added');
                            assert.equal(term1.value, 'x', 'The expected term value has been added');
                            assert.equal(self.getExpression(), 'x', 'Expression has been properly updated');
                            assert.equal(self.getPosition(), 1, 'New position has been set');

                            self
                                .on('termadd.test', function (n2, term2) {
                                    self.off('termadd.test');

                                    assert.equal(n2, 'VAR_Y', 'The right term has been received');
                                    assert.equal(typeof term2, 'object', 'A term has been added');
                                    assert.equal(term2.label, 'y', 'The expected term has been added');
                                    assert.equal(term2.value, 'y', 'The expected term value has been added');
                                    assert.equal(self.getExpression(), 'xy', 'Expression has been properly updated');
                                    assert.equal(self.getPosition(), 2, 'New position has been set');

                                    self
                                        .on('termadd.test', function (n3, term3) {
                                            self.off('termadd.test');

                                            assert.equal(n3, 'VAR_FOO', 'The right term has been received');
                                            assert.equal(term3.label, 'foo', 'The expected term has been added');
                                            assert.equal(term3.value, 'foo', 'The expected term value has been added');
                                            assert.equal(self.getExpression(), 'fooxy', 'Expression has been properly updated');
                                            assert.equal(self.getPosition(), 3, 'New position has been set');

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
            .on('error termerror', function(err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('useVariable - failure', function (assert) {
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
                assert.deepEqual(self.getVariables(), {}, 'No variable set for now');

                return new Promise(function (resolve) {
                    self
                        .on('termadd.test', function () {
                            self.off('termadd.test');

                            assert.ok(false, 'The term should not be added!');

                            resolve();
                        })
                        .on('termerror.test', function (e) {
                            self.off('termerror.test');

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
            .on('error', function(err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('command', function (assert) {
        var $container = $('#fixture-command');
        var instance;

        QUnit.expect(18);

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
                        .on('command.test', function (n1, p1) {
                            self.off('command.test');

                            assert.equal(n1, 'foo', 'The right command has been received');
                            assert.ok(_.isArray(p1), 'The list of command parameters has been received');
                            assert.equal(p1.length, 0, 'The list of command parameters is empty');
                            assert.equal(self.getExpression(), '', 'The expression is still empty');
                            assert.equal(self.getPosition(), 0, 'Position did not change');

                            self
                                .on('command.test', function (n2, p2) {
                                    self.off('command.test');

                                    assert.equal(n2, 'bar', 'The right command has been received');
                                    assert.ok(_.isArray(p2), 'The list of command parameters has been received');
                                    assert.equal(p2.length, 3, 'The list of command parameters contains 3 parameters');
                                    assert.equal(p2[0], 'tip', 'The first parameter is correct');
                                    assert.equal(p2[1], 'top', 'The second parameter is correct');
                                    assert.equal(p2[2], 42, 'The third parameter is correct');
                                    assert.equal(self.getExpression(), '', 'The expression is still empty');
                                    assert.equal(self.getPosition(), 0, 'Position did not change');

                                    resolve();
                                })
                                .command('bar', 'tip', 'top', 42);
                        })
                        .command('foo');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error', function(err) {
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
                    self.on('evaluate', function(result) {
                        assert.equal(result, expectedResult, 'The expression has been properly evaluated');
                        resolve();
                    });
                    assert.equal(self.evaluate(), expectedResult, 'The expression is successfully evaluated');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error syntaxerror', function(err) {
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
                        .on('evaluate', function() {
                            assert.ok(false, 'The expression should not be evaluated');
                            resolve();
                        })
                        .on('syntaxerror', function(e) {
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
            .on('error', function(err) {
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
                    self.on('evaluate', function(result) {
                        assert.equal(result, expectedResult, 'The expression has been properly evaluated');
                        resolve();
                    });
                    self.setVariable('x', '3');
                    assert.equal(self.evaluate(), expectedResult, 'The expression is successfully evaluated');
                });
            })
            .after('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                QUnit.start();
            })
            .on('error syntaxerror', function(err) {
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
                        .on('evaluate', function() {
                            assert.ok(false, 'The expression should not be evaluated');
                            resolve();
                        })
                        .on('syntaxerror', function(e) {
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
            .on('error', function(err) {
                console.error(err);
                assert.ok(false, 'The operation should not fail!');
                QUnit.start();
            });
    });

});
