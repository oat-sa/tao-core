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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/form/widget/widget'
], function (
    $,
    _,
    __,
    widgetFactory
) {
    'use strict';

    widgetFactory.registerProvider('text', {
        init: function init() {

        }
    });

    QUnit.module('Factory');

    QUnit.test('module', function (assert) {
        function getInstance() {
            return widgetFactory('#fixture-api', {widget: 'text', uri: 'foo'})
                .on('ready', function () {
                    this.destroy();
                });
        }

        assert.expect(3);

        assert.equal(typeof widgetFactory, 'function', 'The module exposes a function');
        assert.equal(typeof getInstance(), 'object', 'The factory produces an object');
        assert.notStrictEqual(getInstance(), getInstance(), 'The factory provides a different object on each call');
    });

    QUnit.cases.init([
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
        var instance = widgetFactory('#fixture-api', {widget: 'text', uri: 'foo'})
            .on('ready', function () {
                this.destroy();
            });
        assert.expect(1);
        assert.equal(typeof instance[data.title], 'function', 'The instance exposes a "' + data.title + '" function');
    });

    QUnit.cases.init([
        {title: 'on'},
        {title: 'off'},
        {title: 'trigger'},
        {title: 'spread'}
    ]).test('event API ', function (data, assert) {
        var instance = widgetFactory('#fixture-api', {widget: 'text', uri: 'foo'})
            .on('ready', function () {
                this.destroy();
            });
        assert.expect(1);
        assert.equal(typeof instance[data.title], 'function', 'The instance exposes a "' + data.title + '" function');
    });

    QUnit.cases.init([
        {title: 'getUri'},
        {title: 'getValue'},
        {title: 'setValue'},
        {title: 'getValidator'},
        {title: 'setValidator'},
        {title: 'setDefaultValidators'},
        {title: 'reset'},
        {title: 'serialize'},
        {title: 'validate'},
        {title: 'notify'},
        {title: 'getWidgetElement'}
    ]).test('component API ', function (data, assert) {
        var instance = widgetFactory('#fixture-api', {widget: 'text', uri: 'foo'})
            .on('ready', function () {
                this.destroy();
            });
        assert.expect(1);
        assert.equal(typeof instance[data.title], 'function', 'The instance exposes a "' + data.title + '" function');
    });

    QUnit.module('Life cycle');

    QUnit.cases.init([{
        title: 'missing config'
    }, {
        title: 'bad config type',
        config: 'widget'
    }, {
        title: 'missing uri',
        config: {
            widget: 'text'
        }
    }, {
        title: 'empty uri',
        config: {
            widget: 'text',
            uri: ''
        }
    }, {
        title: 'missing type',
        config: {
            uri: 'foo'
        }
    }, {
        title: 'empty type',
        config: {
            widget: '',
            uri: 'foo'
        }
    }]).test('error ', function (data, assert) {
        var $container = $('#fixture-error');

        assert.expect(1);

        assert.throws(function() {
            widgetFactory($container, data.config);
        }, 'The factory should raise an error');
    });

    QUnit.cases.init([{
        title: 'default',
        config: {
            widget: 'text',
            uri: 'foo'
        },
        expected: {
            widgetType: 'input-box',
            required: false,
            label: __('Label'),
            value: '',
            widget: 'text',
            uri: 'foo',
            range: []
        }
    }, {
        title: 'explicit values',
        config: {
            widgetType: 'input-box-foo',
            required: false,
            label: 'FOO',
            value: 'bar',
            widget: 'text',
            uri: 'foo',
            range: [{
                uri: 'bar',
                label: 'Bar'
            }]
        },
        expected: {
            widgetType: 'input-box-foo',
            required: false,
            label: 'FOO',
            value: 'bar',
            widget: 'text',
            uri: 'foo',
            range: [{
                uri: 'bar',
                label: 'Bar'
            }]
        }
    }, {
        title: 'single range',
        config: {
            widgetType: 'input-box-foo',
            required: false,
            label: 'FOO',
            value: 'bar',
            widget: 'text',
            uri: 'foo',
            range: {
                uri: 'bar',
                label: 'Bar'
            }
        },
        expected: {
            widgetType: 'input-box-foo',
            required: false,
            label: 'FOO',
            value: 'bar',
            widget: 'text',
            uri: 'foo',
            range: [{
                uri: 'bar',
                label: 'Bar'
            }]
        }
    }]).test('init ', function (data, assert) {
        var ready = assert.async();
        var $container = $('#fixture-init');
        var instance = widgetFactory($container, data.config);

        assert.expect(2);

        instance
            .after('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
                assert.deepEqual(this.getConfig(), data.expected, 'The config is as expected');
            })
            .on('ready', function () {
                this.destroy();
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('init provider', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-init');
        var instance;

        assert.expect(4);

        widgetFactory.registerProvider('initProvider', {
            init: function init(config) {
                assert.ok(true, 'The provider init() method is called');
                assert.equal(typeof this.is, 'function', 'The lexical scope is the widget');
                config.foo = 'bar';
            }
        });

        instance = widgetFactory($container, {widget: 'initProvider', uri: 'foo'})
            .after('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(instance.getConfig().foo, 'bar', 'The config has been modified');
            })
            .on('ready', function () {
                this.destroy();
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('init provider change config', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-init');
        var instance;

        assert.expect(4);

        widgetFactory.registerProvider('initProviderConfig', {
            init: function init() {
                assert.ok(true, 'The provider init() method is called');
                assert.equal(typeof this.is, 'function', 'The lexical scope is the widget');
                return {
                    uri: 'bar'
                };
            }
        });

        instance = widgetFactory($container, {widget: 'initProviderConfig', uri: 'foo'})
            .after('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(instance.getConfig().uri, 'bar', 'The config has been changed');
            })
            .on('ready', function () {
                this.destroy();
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('render', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-render');
        var config = {
            widget: 'render',
            uri: 'foo',
            label: 'Foo',
            value: 10
        };
        var instance;

        assert.expect(14);

        widgetFactory.registerProvider(config.widget, {
            init: function init() {
                assert.ok(true, 'The provider init() method is called');
                assert.equal(typeof this.is, 'function', 'The lexical scope is the widget');

                this.on('render', function() {
                    assert.ok(true, 'The listener has been called');
                });
            }
        });

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                assert.equal($container.children().is('.input-box'), true, 'The default type is set');
                assert.ok(instance.is('input-box'), 'The widget type is reflected');
                assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                assert.equal($container.find('.form-widget .widget-label label').text().trim(), config.label, 'The component contains the expected label');
                assert.equal($container.find('.form-widget .widget-field input').attr('name'), config.uri, 'The component contains the expected field');
                assert.equal($container.find('.form-widget .widget-field input').val(), config.value, 'The component contains the expected value');

                this.destroy();
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('render type', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-render');
        var config = {
            widgetType: 'text-box',
            widget: 'render',
            uri: 'foo',
            label: 'Foo',
            value: 10
        };
        var instance;

        assert.expect(14);

        widgetFactory.registerProvider(config.widget, {
            init: function init() {
                assert.ok(true, 'The provider init() method is called');
                assert.equal(typeof this.is, 'function', 'The lexical scope is the widget');

                this.on('render', function() {
                    assert.ok(true, 'The listener has been called');
                });
            }
        });

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                assert.equal($container.children().is('.text-box'), true, 'The widget type is set');
                assert.ok(instance.is(config.widgetType), 'The widget type is reflected');
                assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                assert.equal($container.find('.form-widget .widget-label label').text().trim(), config.label, 'The component contains the expected label');
                assert.equal($container.find('.form-widget .widget-field input').attr('name'), config.uri, 'The component contains the expected field');
                assert.equal($container.find('.form-widget .widget-field input').val(), config.value, 'The component contains the expected value');

                this.destroy();
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('render template', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-render');
        var config = {
            widget: 'renderTemplate',
            uri: 'foo',
            label: 'Foo',
            value: 10
        };
        var instance;

        assert.expect(13);

        widgetFactory.registerProvider(config.widget, {
            init: function init() {
                assert.ok(true, 'The provider init() method is called');
                assert.equal(typeof this.is, 'function', 'The lexical scope is the widget');

                this.on('render', function() {
                    assert.ok(true, 'The listener has been called');
                });
            },
            template: function template() {
                assert.ok(true, 'The template has been called');
                return '<div class="foo"></div>';
            }
        });

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), false, 'The container does not contain a form-widget element');
                assert.equal($container.children().is('.foo'), true, 'The container contains the expected element');
                assert.equal($container.children().is('.input-box'), true, 'The default type is set');
                assert.ok(instance.is('input-box'), 'The widget type is reflected');
                assert.equal($container.find('.form-widget .widget-label').length, 0, 'The component does not contain an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 0, 'The component does not contain an area for the field');

                this.destroy();
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('show', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-show');
        var config = {
            widget: 'text',
            uri: 'foo',
            label: 'Foo',
            value: 10
        };
        var instance;

        assert.expect(23);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                assert.equal($container.find('.form-widget .widget-label label').text().trim(), config.label, 'The component contains the expected label');
                assert.equal($container.find('.form-widget .widget-field input').attr('name'), config.uri, 'The component contains the expected field');

                assert.equal($container.find('.form-widget:visible').length, 1, 'The component is visible');
                assert.equal($container.find('.form-widget .widget-label:visible').length, 1, 'The label area is visible');
                assert.equal($container.find('.form-widget .widget-field:visible').length, 1, 'The field area is visible');
                assert.equal($container.find('.form-widget .widget-label label:visible').length, 1, 'The label is visible');
                assert.equal($container.find('.form-widget .widget-field input:visible').length, 1, 'The field is visible');

                instance.hide();
                assert.equal($container.find('.form-widget:visible').length, 0, 'The component is hidden');
                assert.equal($container.find('.form-widget .widget-label:visible').length, 0, 'The label area is hidden');
                assert.equal($container.find('.form-widget .widget-field:visible').length, 0, 'The field area is hidden');
                assert.equal($container.find('.form-widget .widget-label label:visible').length, 0, 'The label is hidden');
                assert.equal($container.find('.form-widget .widget-field input:visible').length, 0, 'The field is hidden');

                instance.show();
                assert.equal($container.find('.form-widget:visible').length, 1, 'The component is visible again');
                assert.equal($container.find('.form-widget .widget-label:visible').length, 1, 'The label area is visible again');
                assert.equal($container.find('.form-widget .widget-field:visible').length, 1, 'The field area is visible again');
                assert.equal($container.find('.form-widget .widget-label label:visible').length, 1, 'The label is visible again');
                assert.equal($container.find('.form-widget .widget-field input:visible').length, 1, 'The field is visible again');

                instance.destroy();
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('enable', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-enable');
        var config = {
            widget: 'text',
            uri: 'foo',
            label: 'Foo',
            value: 10
        };
        var instance;

        assert.expect(11);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                        assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                        assert.equal($container.find('.form-widget .widget-label label').text().trim(), config.label, 'The component contains the expected label');
                        assert.equal($container.find('.form-widget .widget-field input').attr('name'), config.uri, 'The component contains the expected field');

                        assert.equal($container.find('.form-widget .widget-field input:enabled').length, 1, 'The field is enabled');
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .after('disable.test', function () {
                                    assert.equal($container.find('.form-widget .widget-field input:enabled').length, 0, 'The field is disabled');
                                    resolve();
                                })
                                .disable();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .after('enable.test', function () {
                                    assert.equal($container.find('.form-widget .widget-field input:enabled').length, 1, 'The field is enabled again');
                                    resolve();
                                })
                                .enable();
                        });
                    })
                    .catch(function (err) {
                        assert.ok(false, 'The operation should not fail!');
                        assert.pushResult({
                            result: false,
                            message: err
                        });
                    })
                    .then(function () {
                        instance
                            .off('.test')
                            .destroy();
                    });
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('destroy', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-destroy');
        var instance;

        assert.expect(4);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, {widget: 'text', uri: 'foo'})
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                this.destroy();
            })
            .after('destroy', function () {
                assert.equal($container.children().length, 0, 'The container is now empty');
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.module('API');

    QUnit.test('properties', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-properties');
        var config = {
            widget: 'text',
            uri: 'foo',
            value: 'bar'
        };
        var instance;

        assert.expect(5);

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getUri(), config.uri, 'The expected uri is returned');
                assert.equal(this.getValue(), config.value, 'The expected value is returned');
                assert.equal(this.getWidgetElement(), null, 'There is no form element yet');
            })
            .on('ready', function () {
                assert.ok(this.getWidgetElement().is($container.find('[name="' + config.uri + '"]')), 'The expected form element is returned');
                this.destroy();
            })
            .after('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('properties from provider', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-properties');
        var config = {
            widget: 'properties',
            uri: 'foo',
            value: 'bar'
        };
        var instance;

        assert.expect(8);

        widgetFactory.registerProvider('properties', {
            init: function init() {
                assert.ok(true, 'The provider init() method is called');
            },
            getWidgetElement: function getWidgetElement() {
                assert.ok(true, 'The provider getWidgetElement() method is called');
                return this.getElement()
                    .find('[name="' + this.getUri() + '"]');
            }
        });

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getUri(), config.uri, 'The expected uri is returned');
                assert.equal(this.getValue(), config.value, 'The expected value is returned');
                assert.equal(this.getWidgetElement(), null, 'There is no form element yet');
            })
            .on('ready', function () {
                assert.ok(this.getWidgetElement().is($container.find('[name="' + config.uri + '"]')), 'The expected form element is returned');
                this.destroy();
            })
            .after('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('change', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-change');
        var instance;

        assert.expect(15);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, {widget: 'text', uri: 'foo'})
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                        assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                        assert.equal($container.find('.form-widget .widget-field input').attr('name'), 'foo', 'The component contains the expected field');
                        assert.equal(instance.getValue(), '', 'Empty value');

                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .on('change.test', function (value, uri) {
                                    assert.equal(uri, 'foo', 'The change event has been triggered');
                                    assert.equal(value, 'test', 'The expected value is there');
                                    resolve();
                                })
                                .setValue('test');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            assert.equal(instance.getValue(), 'test', 'The value is set');
                            instance
                                .off('.test')
                                .on('change.test', function (value, uri) {
                                    assert.equal(uri, 'foo', 'The change event has been triggered');
                                    assert.equal(value, 'top', 'The expected value is there');
                                    resolve();
                                });

                            $container.find('.form-widget .widget-field input').val('top').change();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .on('change.test', function () {
                                    assert.ok(false, 'The change event should not be triggered');
                                    resolve();
                                });

                            _.delay(function() {
                                instance.off('.test');
                                assert.ok(true, 'The change event has not been triggered');
                                resolve();
                            }, 200);

                            $container.find('.form-widget .widget-field input').change();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .on('change.test', function () {
                                    assert.ok(false, 'The change event should not be triggered');
                                    resolve();
                                });

                            _.delay(function() {
                                instance.off('.test');
                                assert.ok(true, 'The change event has not been triggered');
                                resolve();
                            }, 200);

                            $container.find('.form-widget .widget-field input').blur();
                        });
                    })
                    .catch(function (err) {
                        assert.ok(false, 'The operation should not fail!');
                        assert.pushResult({
                            result: false,
                            message: err
                        });
                    })
                    .then(function () {
                        instance
                            .off('.test')
                            .destroy();
                    });
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('values', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-value');
        var instance;
        var providerValue;

        assert.expect(16);

        widgetFactory.registerProvider('values', {
            init: function init(config) {
                assert.ok(true, 'The provider init() method is called');
                providerValue = config.value;
            },
            getValue: function getValue() {
                assert.ok(true, 'The provider getValue() method is called');
                return providerValue;
            },
            setValue: function setValue(value) {
                assert.ok(true, 'The provider setValue() method is called');
                providerValue = value;
            }
        });

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, {widget: 'values', uri: 'foo', value: 'bar'})
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                        assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                        assert.equal($container.find('.form-widget .widget-field input').attr('name'), 'foo', 'The component contains the expected field');
                        assert.equal(instance.getValue(), 'bar', 'Init value');

                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .on('change.test', function (value, uri) {
                                    assert.equal(uri, 'foo', 'The change event has been triggered');
                                    assert.equal(value, 'test', 'The expected value is there');
                                    resolve();
                                })
                                .setValue('test');
                        });
                    })
                    .then(function () {
                        assert.equal(providerValue, instance.getValue(), 'The value has been changed');
                    })
                    .catch(function (err) {
                        assert.ok(false, 'The operation should not fail!');
                        assert.pushResult({
                            result: false,
                            message: err
                        });
                    })
                    .then(function () {
                        instance
                            .off('.test')
                            .destroy();
                    });
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('serialize', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-serialize');
        var instance;

        assert.expect(9);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, {widget: 'text', uri: 'foo'})
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                assert.equal($container.find('.form-widget .widget-field input').attr('name'), 'foo', 'The component contains the expected field');

                assert.deepEqual(instance.serialize(), {name: 'foo', value: ''}, 'Empty value');
                instance.setValue('top');
                assert.deepEqual(instance.serialize(), {name: 'foo', value: 'top'}, 'New value');

                instance.destroy();
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('serialize from provider', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-serialize');
        var instance;

        assert.expect(12);

        widgetFactory.registerProvider('serialize', {
            init: function init() {
                assert.ok(true, 'The provider init() method is called');
            },
            serialize: function serialize() {
                assert.ok(true, 'The provider serialize() method is called');
                return {
                    name: this.getUri(),
                    value: this.getValue()
                };
            }
        });

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, {widget: 'serialize', uri: 'foo'})
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                assert.equal($container.find('.form-widget .widget-field input').attr('name'), 'foo', 'The component contains the expected field');

                assert.deepEqual(instance.serialize(), {name: 'foo', value: ''}, 'Empty value');
                instance.setValue('top');
                assert.deepEqual(instance.serialize(), {name: 'foo', value: 'top'}, 'New value');

                instance.destroy();
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('validate with predefined validator', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-validate');
        var instance;

        assert.expect(9);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, {widget: 'text', uri: 'foo', required: true})
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                assert.equal($container.find('.form-widget .widget-field input').attr('name'), 'foo', 'The component contains the expected field');

                instance.validate()
                    .then(function () {
                        assert.ok(instance.is('invalid'), 'The field should not be valid');
                    })
                    .catch(function () {
                        assert.ok(instance.is('invalid'), 'The field has been rejected');
                        assert.equal($container.find('.form-validator .validation-error').length, 1, 'The validation messages are displayed');
                    })
                    .then(function () {
                        instance.destroy();
                    });
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('validate with provider validator', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-validate');
        var instance;

        assert.expect(11);

        widgetFactory.registerProvider('validator', {
            init: function init() {
                assert.ok(true, 'The provider init() method is called');
            },
            setDefaultValidators: function setDefaultValidators() {
                assert.ok(true, 'The provider setDefaultValidators() method is called');
                this.setValidator({
                    id: 'required',
                    predicate: function() {
                        return false;
                    }
                });
            }
        });

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, {widget: 'validator', uri: 'foo', required: true})
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                assert.equal($container.find('.form-widget .widget-field input').attr('name'), 'foo', 'The component contains the expected field');

                instance.validate()
                    .then(function () {
                        assert.ok(instance.is('invalid'), 'The field should not be valid');
                    })
                    .catch(function () {
                        assert.ok(instance.is('invalid'), 'The field has been rejected');
                        assert.equal($container.find('.form-validator .validation-error').length, 1, 'The validation messages are displayed');
                    })
                    .then(function () {
                        instance.destroy();
                    });
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('validate with redefined validator', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-validate');
        var instance;

        assert.expect(12);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, {widget: 'text', uri: 'foo'})
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                assert.equal($container.find('.form-widget .widget-field input').attr('name'), 'foo', 'The component contains the expected field');

                instance.validate()
                    .then(function () {
                        assert.ok(!instance.is('invalid'), 'The field is valid');
                        assert.equal($container.find('.form-validator .validation-error').length, 0, 'No validation messages are displayed');
                    })
                    .catch(function () {
                        assert.ok(!instance.is('invalid'), 'The field should be valid');
                    })
                    .then(function () {
                        instance.setValidator({
                            id: 'required2',
                            predicate: function() {
                                return false;
                            }
                        });
                        return instance.validate()
                            .then(function () {
                                assert.ok(instance.is('invalid'), 'The field should not be valid');
                            })
                            .catch(function () {
                                assert.ok(instance.is('invalid'), 'The field has been rejected');
                                assert.equal($container.find('.form-validator .validation-error').length, 1, 'The validation messages are displayed');
                            });
                    })
                    .then(function () {
                        instance.setDefaultValidators();
                        return instance.validate()
                            .then(function () {
                                assert.ok(!instance.is('invalid'), 'The field should be valid');
                            })
                            .catch(function () {
                                assert.ok(false, 'The field should not be rejected');
                            });
                    })
                    .catch(function (err) {
                        assert.ok(false, 'The operation should not fail!');
                        assert.pushResult({
                            result: false,
                            message: err
                        });
                    })
                    .then(function () {
                        instance.destroy();
                    });
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('validate with object validator', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-validate');
        var instance;

        assert.expect(9);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, {widget: 'text', uri: 'foo'})
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                assert.equal($container.find('.form-widget .widget-field input').attr('name'), 'foo', 'The component contains the expected field');

                instance.validate()
                    .then(function () {
                        assert.ok(!instance.is('invalid'), 'The field is valid');
                    })
                    .catch(function () {
                        assert.ok(!instance.is('invalid'), 'The field should be valid');
                    })
                    .then(function () {
                        instance.setValidator({
                            validate: function() {
                                return Promise.reject(false);
                            }
                        });
                        return instance.validate()
                            .then(function () {
                                assert.ok(instance.is('invalid'), 'The field should not be valid');
                            })
                            .catch(function () {
                                assert.ok(instance.is('invalid'), 'The field has been rejected');
                            });
                    })
                    .catch(function (err) {
                        assert.ok(false, 'The operation should not fail!');
                        assert.pushResult({
                            result: false,
                            message: err
                        });
                    })
                    .then(function () {
                        instance.destroy();
                    });
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('reset', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-reset');
        var instance;

        assert.expect(14);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, {widget: 'text', uri: 'foo', value: 'bar', required: true})
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                        assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                        assert.equal($container.find('.form-widget .widget-field input').attr('name'), 'foo', 'The component contains the expected field');
                        assert.equal(instance.getValue(), 'bar', 'Init value');

                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .on('change.test', function (value, uri) {
                                    assert.equal(uri, 'foo', 'The change event has been triggered');
                                    assert.equal(value, '', 'The expected value is there');
                                    resolve();
                                })
                                .reset();
                        });
                    })
                    .then(function () {
                        assert.equal(instance.getValue(), '', 'The value has been reset');
                        return instance
                            .off('.test')
                            .validate()
                            .then(function () {
                                assert.ok(instance.is('invalid'), 'The field should not be valid');
                            })
                            .catch(function () {
                                assert.ok(instance.is('invalid'), 'The field has been rejected');
                            });
                    })
                    .then(function () {
                        assert.ok(instance.is('invalid'), 'The field is invalid');
                        instance.reset();
                        assert.ok(!instance.is('invalid'), 'The field is now reset to valid state');
                    })
                    .catch(function (err) {
                        assert.ok(false, 'The operation should not fail!');
                        assert.pushResult({
                            result: false,
                            message: err
                        });
                    })
                    .then(function () {
                        instance
                            .off('.test')
                            .destroy();
                    });
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('reset provider', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-reset');
        var instance;

        assert.expect(13);

        widgetFactory.registerProvider('reset', {
            init: function init() {
                assert.ok(true, 'The provider init() method is called');
            },
            reset: function reset() {
                assert.ok(true, 'The provider reset() method is called');
                this.setValue('');
            }
        });

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, {widget: 'reset', uri: 'foo', value: 'bar'})
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-widget .widget-label').length, 1, 'The component contains an area for the label');
                        assert.equal($container.find('.form-widget .widget-field').length, 1, 'The component contains an area for the field');
                        assert.equal($container.find('.form-widget .widget-field input').attr('name'), 'foo', 'The component contains the expected field');
                        assert.equal(instance.getValue(), 'bar', 'Init value');

                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .on('change.test', function (value, uri) {
                                    assert.equal(uri, 'foo', 'The change event has been triggered');
                                    assert.equal(value, '', 'The expected value is there');
                                    resolve();
                                })
                                .reset();
                        });
                    })
                    .then(function () {
                        assert.equal(instance.getValue(), '', 'The value has been reset');
                    })
                    .catch(function (err) {
                        assert.ok(false, 'The operation should not fail!');
                        assert.pushResult({
                            result: false,
                            message: err
                        });
                    })
                    .then(function () {
                        instance
                            .off('.test')
                            .destroy();
                    });
            })
            .on('destroy', function () {
                ready();
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.module('Visual');

    QUnit.test('Visual test', function (assert) {
        var ready = assert.async();
        var $container = $('#visual-test .test');
        var $outputChange = $('#visual-test .change-output');
        var instance = widgetFactory($container, {
            widget: 'text',
            uri: 'foo',
            value: 'bar',
            label: 'Test',
            required: true
        });

        assert.expect(3);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                ready();
            })
            .on('change', function (value, uri) {
                this.validate()
                    .catch(function() {
                    })
                    .then(function() {
                        $outputChange.val('value of [' + uri + '] changed to "' + value + '"\n' + $outputChange.val());
                    });
            })
            .on('error', function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });
});
