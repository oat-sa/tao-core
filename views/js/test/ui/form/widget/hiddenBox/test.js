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
    'core/promise',
    'ui/form/widget/widget',
    'ui/form/widget/hiddenBox'
], function (
    $,
    _,
    Promise,
    widgetFactory,
    widgetHiddenBoxProvider
) {
    'use strict';

    widgetFactory.registerProvider('hidden', widgetHiddenBoxProvider);

    QUnit.module('Factory');

    QUnit.test('module', function (assert) {
        function getInstance() {
            return widgetFactory('#fixture-api', {widget: 'hidden', uri: 'foo'})
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
        var instance = widgetFactory('#fixture-api', {widget: 'hidden', uri: 'foo'})
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
        var instance = widgetFactory('#fixture-api', {widget: 'hidden', uri: 'foo'})
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
        {title: 'reset'},
        {title: 'serialize'},
        {title: 'validate'},
        {title: 'notify'},
        {title: 'getWidgetElement'}
    ]).test('component API ', function (data, assert) {
        var instance = widgetFactory('#fixture-api', {widget: 'hidden', uri: 'foo'})
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
            widget: 'hidden'
        }
    }, {
        title: 'empty uri',
        config: {
            widget: 'hidden',
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
            widgetFactory($container, data.config)
        }, 'The factory should raise an error');
    });

    QUnit.test('init default', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-init');
        var instance = widgetFactory($container, {widget: 'hidden', uri: 'foo'});

        assert.expect(1);

        instance
            .after('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
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
            widget: 'hidden',
            uri: 'foo',
            label: 'Foo'
        };
        var instance;

        assert.expect(11);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 2, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                assert.equal($container.children().is('.hidden-box'), true, 'The widget type is set');
                assert.ok(instance.is('hidden-box'), 'The widget type is reflected');
                assert.equal($container.find('.form-widget .widget-label').length, 2, 'The component contains an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 2, 'The component contains an area for the field');
                assert.equal($container.find('.form-widget .widget-label label:eq(0)').text().trim(), config.label, 'The component contains the expected label');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '"]').length, 1, 'The component contains the main field');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '_confirmation"]').length, 1, 'The component contains the confirmation field');

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
            widget: 'hidden',
            uri: 'foo',
            label: 'Foo'
        };
        var instance;

        assert.expect(23);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 2, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-widget .widget-label').length, 2, 'The component contains an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 2, 'The component contains an area for the field');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '"]').length, 1, 'The component contains the main field');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '_confirmation"]').length, 1, 'The component contains the confirmation field');

                assert.equal($container.find('.form-widget:visible').length, 2, 'The component is visible');
                assert.equal($container.find('.form-widget .widget-label:visible').length, 2, 'The label area is visible');
                assert.equal($container.find('.form-widget .widget-field:visible').length, 2, 'The field area is visible');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '"]:visible').length, 1, 'The main field is visible');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '_confirmation"]:visible').length, 1, 'The confirmation field is visible');

                instance.hide();
                assert.equal($container.find('.form-widget:visible').length, 0, 'The component is hidden');
                assert.equal($container.find('.form-widget .widget-label:visible').length, 0, 'The label area is hidden');
                assert.equal($container.find('.form-widget .widget-field:visible').length, 0, 'The field area is hidden');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '"]:visible').length, 0, 'The main field is hidden');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '_confirmation"]:visible').length, 0, 'The confirmation field is hidden');

                instance.show();
                assert.equal($container.find('.form-widget:visible').length, 2, 'The component is visible again');
                assert.equal($container.find('.form-widget .widget-label:visible').length, 2, 'The label area is visible again');
                assert.equal($container.find('.form-widget .widget-field:visible').length, 2, 'The field area is visible again');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '"]:visible').length, 1, 'The main field is visible again');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '_confirmation"]:visible').length, 1, 'The confirmation field is visible again');

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
            widget: 'hidden',
            uri: 'foo',
            label: 'Foo'
        };
        var instance;

        assert.expect(12);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 2, 'The container contains an element');
                        assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-widget .widget-label').length, 2, 'The component contains an area for the label');
                        assert.equal($container.find('.form-widget .widget-field').length, 2, 'The component contains an area for the field');
                        assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '"]:enabled').length, 1, 'The main field is enabled');
                        assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '_confirmation"]:enabled').length, 1, 'The confirmation field is enabled');
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .after('disable.test', function () {
                                    assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '"]:enabled').length, 0, 'The main field is disabled');
                                    assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '_confirmation"]:enabled').length, 0, 'The confirmation field is disabled');
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
                                    assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '"]:enabled').length, 1, 'The main field is enabled again');
                                    assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '_confirmation"]:enabled').length, 1, 'The confirmation field is enabled again');
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

        instance = widgetFactory($container, {widget: 'hidden', uri: 'foo'})
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 2, 'The container contains an element');
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
            widget: 'hidden',
            uri: 'foo'
        };
        var instance;

        assert.expect(5);

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getUri(), config.uri, 'The expected uri is returned');
                assert.deepEqual(this.getValue(), {
                    confirmation: '',
                    value: ''
                }, 'The expected value is returned');
                assert.equal(this.getWidgetElement(), null, 'There is no form element yet');
            })
            .on('ready', function () {
                assert.ok(this.getWidgetElement().is('input'), 'The expected form element is returned');
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
        var config = {
            widget: 'hidden',
            uri: 'foo'
        };
        var instance;

        assert.expect(14);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 2, 'The container contains an element');
                        assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-widget .widget-label').length, 2, 'The component contains an area for the label');
                        assert.equal($container.find('.form-widget .widget-field').length, 2, 'The component contains an area for the field');
                        assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '"]').length, 1, 'The component contains the main field');
                        assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '_confirmation"]').length, 1, 'The component contains the confirmation field');
                        assert.deepEqual(instance.getValue(), {
                            confirmation: '',
                            value: ''
                        }, 'Empty value');

                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .on('change.test', function (value, uri) {
                                    assert.equal(uri, 'foo', 'The change event has been triggered');
                                    assert.deepEqual(value, {
                                        confirmation: 'yes',
                                        value: 'yes'
                                    }, 'The expected value is there');
                                    resolve();
                                })
                                .setValue('yes');
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            assert.deepEqual(instance.getValue(), {
                                confirmation: 'yes',
                                value: 'yes'
                            }, 'The value is set');
                            instance
                                .off('.test')
                                .on('change.test', function (value, uri) {
                                    assert.equal(uri, 'foo', 'The change event has been triggered');
                                    assert.deepEqual(value, {
                                        confirmation: 'yes',
                                        value: 'no'
                                    }, 'The expected value is there');
                                    resolve();
                                });

                            $container.find('.form-widget .widget-field [name="' + config.uri + '"]').val('no').change();
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
        var config = {
            widget: 'hidden',
            uri: 'foo',
            value: 'yes'
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
                        assert.equal($container.children().length, 2, 'The container contains an element');
                        assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-widget .widget-label').length, 2, 'The component contains an area for the label');
                        assert.equal($container.find('.form-widget .widget-field').length, 2, 'The component contains an area for the field');
                        assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '"]').length, 1, 'The component contains the main field');
                        assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '_confirmation"]').length, 1, 'The component contains the confirmation field');
                        assert.deepEqual(instance.getValue(), {
                            confirmation: '',
                            value: ''
                        }, 'Init value');

                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .on('change.test', function (value, uri) {
                                    assert.equal(uri, 'foo', 'The change event has been triggered');
                                    assert.deepEqual(value, {
                                        confirmation: 'yes',
                                        value: 'yes'
                                    }, 'The expected value is there');
                                    resolve();
                                })
                                .setValue('yes');
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

    QUnit.test('serialize', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-serialize');
        var config = {
            widget: 'hidden',
            uri: 'foo'
        };
        var instance;

        assert.expect(10);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 2, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-widget .widget-label').length, 2, 'The component contains an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 2, 'The component contains an area for the field');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '"]').length, 1, 'The component contains the main field');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '_confirmation"]').length, 1, 'The component contains the confirmation field');

                assert.deepEqual(instance.serialize(), {name: 'foo', value: ''}, 'Empty value');
                instance.setValue('yes');
                assert.deepEqual(instance.serialize(), {name: 'foo', value: 'yes'}, 'New value');

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

    QUnit.test('validate', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-validate');
        var config = {
            widget: 'hidden',
            required: true,
            uri: 'foo'
        };
        var instance;

        assert.expect(10);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 2, 'The container contains an element');
                assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-widget .widget-label').length, 2, 'The component contains an area for the label');
                assert.equal($container.find('.form-widget .widget-field').length, 2, 'The component contains an area for the field');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '"]').length, 1, 'The component contains the main field');
                assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '_confirmation"]').length, 1, 'The component contains the confirmation field');

                instance.validate()
                    .then(function () {
                        assert.ok(false, 'The form should not be valid');
                    })
                    .catch(function () {
                        assert.ok(true, 'The form has been rejected');
                    })
                    .then(function () {
                        instance.getValidator().removeValidation('required');
                        return instance.validate()
                            .then(function () {
                                assert.ok(true, 'The field is valid');
                            })
                            .catch(function () {
                                assert.ok(false, 'The form should be valid');
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
        var config = {
            widget: 'hidden',
            uri: 'foo',
            value: 'no'
        };
        var instance;

        assert.expect(12);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = widgetFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 2, 'The container contains an element');
                        assert.equal($container.children().is('.form-widget'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-widget .widget-label').length, 2, 'The component contains an area for the label');
                        assert.equal($container.find('.form-widget .widget-field').length, 2, 'The component contains an area for the field');
                        assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '"]').length, 1, 'The component contains the main field');
                        assert.equal($container.find('.form-widget .widget-field [name="' + config.uri + '_confirmation"]').length, 1, 'The component contains the confirmation field');
                        instance.setValue('yes');
                        assert.deepEqual(instance.getValue(), {
                            confirmation: 'yes',
                            value: 'yes'
                        }, 'Init value');

                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .on('change.test', function (value, uri) {
                                    assert.equal(uri, 'foo', 'The change event has been triggered');
                                    assert.deepEqual(value, {
                                        confirmation: '',
                                        value: ''
                                    }, 'The expected value is there');
                                    resolve();
                                })
                                .reset();
                        });
                    })
                    .then(function () {
                        assert.deepEqual(instance.getValue(), {
                            confirmation: '',
                            value: ''
                        }, 'The value has been reset');
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
            widget: 'hidden',
            uri: 'foo',
            label: 'Test'
        });

        assert.expect(3);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 2, 'The container contains an element');
                ready();
            })
            .on('change', function (value, uri) {
                this.validate();
                $outputChange.val('value of [' + uri + '] changed to "' + value + '"\n' + $outputChange.val());
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
