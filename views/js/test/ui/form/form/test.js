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
    'ui/form/form',
    'ui/form/widget/widget',
    'ui/form/widget/definitions'
], function (
    $,
    _,
    formFactory,
    widgetFactory,
    widgetDefinitions
) {
    'use strict';

    widgetFactory.registerProvider('text', {
        init: function init() {

        }
    });

    QUnit.module('Factory');

    QUnit.test('module', function (assert) {
        function getInstance() {
            return formFactory('#fixture-api')
                .on('ready', function () {
                    this.destroy();
                });
        }

        assert.expect(3);

        assert.equal(typeof formFactory, 'function', 'The module exposes a function');
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
        var instance = formFactory('#fixture-api')
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
        var instance = formFactory('#fixture-api')
            .on('ready', function () {
                this.destroy();
            });
        assert.expect(1);
        assert.equal(typeof instance[data.title], 'function', 'The instance exposes a "' + data.title + '" function');
    });

    QUnit.cases.init([
        {title: 'getFormAction'},
        {title: 'getFormMethod'},
        {title: 'getRanges'},
        {title: 'getTitle'},
        {title: 'setTitle'},
        {title: 'getWidget'},
        {title: 'addWidget'},
        {title: 'removeWidget'},
        {title: 'getWidgets'},
        {title: 'setWidgets'},
        {title: 'removeWidgets'},
        {title: 'getButton'},
        {title: 'addButton'},
        {title: 'removeButton'},
        {title: 'getButtons'},
        {title: 'setButtons'},
        {title: 'removeButtons'},
        {title: 'getValue'},
        {title: 'setValue'},
        {title: 'getValues'},
        {title: 'setValues'},
        {title: 'serialize'},
        {title: 'validate'},
        {title: 'submit'},
        {title: 'reset'}
    ]).test('component API ', function (data, assert) {
        var instance = formFactory('#fixture-api')
            .on('ready', function () {
                this.destroy();
            });
        assert.expect(1);
        assert.equal(typeof instance[data.title], 'function', 'The instance exposes a "' + data.title + '" function');
    });

    QUnit.module('Life cycle');

    QUnit.test('init', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-init');
        var instance;

        assert.expect(1);

        instance = formFactory($container)
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

    QUnit.cases.init([{
        title: 'default'
    }, {
        title: 'empty',
        config: {}
    }, {
        title: 'widgets',
        config: {
            widgets: [{
                widget: 'text',
                uri: 'text',
                label: 'Text'
            }],
            buttons: [{
                id: 'submit',
                label: 'Submit'
            }],
            values: {
                text: 'foo 1'
            }
        }
    }, {
        title: 'default widget',
        config: {
            widgets: [{
                uri: 'text',
                label: 'Text'
            }],
            buttons: [{
                id: 'submit',
                label: 'Submit'
            }],
            values: {
                text: 'foo 2'
            }
        }
    }]).test('render', function (data, assert) {
        var ready = assert.async();
        var $container = $('#fixture-render');
        var widgets = data.config && data.config.widgets;
        var buttons = data.config && data.config.buttons;
        var instance;

        assert.expect(9 + _.size(widgets) + _.size(buttons));

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, data.config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');

                assert.equal($container.find('.form-component fieldset').children().length, _.size(widgets), 'The component contains the expected amount of widgets');
                assert.equal($container.find('.form-component .form-actions').children().length, _.size(buttons), 'The component contains the expected amount of buttons');

                _.forEach(widgets, function (widget) {
                    assert.equal($container.find('.form-component fieldset [name="' + widget.uri + '"]').length, 1, 'The component contains the widget ' + widget.uri);
                });

                _.forEach(buttons, function (button) {
                    assert.equal($container.find('.form-component .form-actions [data-control="' + button.id + '"]').length, 1, 'The component contains the button ' + button.id);
                });

                assert.deepEqual(instance.getValues(), data.config && data.config.values || {}, 'The component has set the form values');

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

    QUnit.test('render error', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-render-error');
        var instance;

        assert.expect(10);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, {
            widgets: [{
                uri: 'text',
                label: 'Text'
            }],
            buttons: [{
                label: 'Submit'
            }],
            values: {
                text: 'foo'
            }
        });

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('error', function (err) {
                assert.ok(true, 'An error has been raised');
                assert.pushResult({
                    result: true,
                    message: err
                });
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                assert.equal($container.find('.form-component fieldset').children().length, 0, 'The component should not contain any form widgets');
                assert.equal($container.find('.form-component .form-actions').children().length, 0, 'The component should not contain any form buttons');

                this.destroy();
            })
            .on('destroy', function () {
                ready();
            });
    });

    QUnit.test('show', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-show');
        var instance;

        assert.expect(23);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, {
            widgets: [{
                widget: 'text',
                uri: 'text',
                label: 'Text'
            }],
            buttons: [{
                id: 'submit',
                label: 'Submit'
            }]
        })
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                assert.equal($container.find('.form-component fieldset [name="text"]').length, 1, 'The component contains a text widget');
                assert.equal($container.find('.form-component .form-actions [data-control="submit"]').length, 1, 'The component contains a button button');

                assert.equal($container.find('.form-component:visible').length, 1, 'The component is visible');
                assert.equal($container.find('.form-component fieldset:visible').length, 1, 'The form widgets area is visible');
                assert.equal($container.find('.form-component .form-actions:visible').length, 1, 'The buttons area is visible');
                assert.equal($container.find('.form-component fieldset [name="text"]:visible').length, 1, 'The text widget is visible');
                assert.equal($container.find('.form-component .form-actions [data-control="submit"]:visible').length, 1, 'The button button is visible');

                instance.hide();
                assert.equal($container.find('.form-component:visible').length, 0, 'The component is hidden');
                assert.equal($container.find('.form-component fieldset:visible').length, 0, 'The form widgets area is hidden');
                assert.equal($container.find('.form-component .form-actions:visible').length, 0, 'The buttons area is hidden');
                assert.equal($container.find('.form-component fieldset [name="text"]:visible').length, 0, 'The text widget is hidden');
                assert.equal($container.find('.form-component .form-actions [data-control="submit"]:visible').length, 0, 'The button button is hidden');

                instance.show();
                assert.equal($container.find('.form-component:visible').length, 1, 'The component is visible again');
                assert.equal($container.find('.form-component fieldset:visible').length, 1, 'The form widgets area is visible again');
                assert.equal($container.find('.form-component .form-actions:visible').length, 1, 'The buttons area is visible again');
                assert.equal($container.find('.form-component fieldset [name="text"]:visible').length, 1, 'The text widget is visible again');
                assert.equal($container.find('.form-component .form-actions [data-control="submit"]:visible').length, 1, 'The button button is visible again');

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
        var instance;

        assert.expect(14);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, {
            widgets: [{
                widget: 'text',
                uri: 'text',
                label: 'Text'
            }],
            buttons: [{
                id: 'submit',
                label: 'Submit'
            }]
        });

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                        assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                        assert.equal($container.find('.form-component fieldset [name="text"]').length, 1, 'The component contains a text widget');
                        assert.equal($container.find('.form-component .form-actions [data-control="submit"]').length, 1, 'The component contains a button button');

                        assert.equal($container.find('.form-component fieldset [name="text"]:enabled').length, 1, 'The text widget is enabled');
                        assert.equal($container.find('.form-component .form-actions [data-control="submit"]:enabled').length, 1, 'The button button is enabled');
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .after('disable.test', function () {
                                    assert.equal($container.find('.form-component fieldset [name="text"]:enabled').length, 0, 'The text widget is disabled');
                                    assert.equal($container.find('.form-component .form-actions [data-control="submit"]:enabled').length, 0, 'The button button is disabled');
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
                                    assert.equal($container.find('.form-component fieldset [name="text"]:enabled').length, 1, 'The text widget is enabled again');
                                    assert.equal($container.find('.form-component .form-actions [data-control="submit"]:enabled').length, 1, 'The button button is enabled again');
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

        instance = formFactory($container)
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

    QUnit.cases.init([{
        title: 'default',
        expected: {
            formAction: '#',
            formMethod: 'get',
            ranges: {}
        }
    }, {
        title: 'empty',
        config: {},
        expected: {
            formAction: '#',
            formMethod: 'get',
            ranges: {}
        }
    }, {
        title: 'defined',
        config: {
            formAction: '/foo/bar',
            formMethod: 'post',
            title: 'My Form',
            ranges: {
                foo: ['bar']
            }
        },
        expected: {
            formAction: '/foo/bar',
            formMethod: 'post',
            title: 'My Form',
            ranges: {
                foo: ['bar']
            }
        }
    }]).test('properties', function (data, assert) {
        var ready = assert.async();
        var $container = $('#fixture-properties');
        var instance;

        assert.expect(5);

        instance = formFactory($container, data.config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
                assert.equal(this.getFormAction(), data.expected.formAction, 'The expected formAction is returned');
                assert.equal(this.getFormMethod(), data.expected.formMethod, 'The expected formMethod is returned');
                assert.equal(this.getTitle(), data.expected.title, 'The expected title is returned');
                assert.deepEqual(this.getRanges(), data.expected.ranges, 'The expected ranges are returned');
            })
            .on('ready', function () {
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

    QUnit.cases.init([{
        title: 'default',
        visible: false,
        expectedTitle: 'foo1'
    }, {
        title: 'empty',
        visible: false,
        config: {},
        expectedTitle: 'foo2'
    }, {
        title: 'defined',
        visible: true,
        config: {
            title: 'Form'
        },
        expectedTitle: 'foo3'
    }]).test('title', function (data, assert) {
        var ready = assert.async();
        var $container = $('#fixture-title');
        var instance;

        assert.expect(11);

        instance = formFactory($container, data.config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.find('h2').length, 1, 'The title element is there');
                assert.equal($container.find('h2').is(':visible'), data.visible, 'The title element has the expected visibility');

                Promise.resolve()
                    .then(function () {
                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .after('titlechange.test', function (title) {
                                    assert.equal(title, data.expectedTitle, 'The event titlechange has been emitted with the expected parameter');
                                    assert.equal(instance.getTitle(), data.expectedTitle, 'The title has been changed');
                                    resolve();
                                })
                                .setTitle(data.expectedTitle);
                        });
                    })
                    .then(function () {
                        assert.equal($container.find('h2').text().trim(), data.expectedTitle, 'The title element contains the expected text');
                        assert.equal($container.find('h2').is(':visible'), true, 'The title element is visible');

                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .after('titlechange.test', function (title) {
                                    assert.equal(title, '', 'The event titlechange has been emitted with the expected parameter');
                                    assert.equal(instance.getTitle(), '', 'The title has been changed');
                                    resolve();
                                })
                                .setTitle('');
                        });
                    })
                    .then(function () {
                        assert.equal($container.find('h2').text().trim(), '', 'The title element contains the expected text');
                        assert.equal($container.find('h2').is(':visible'), false, 'The title element is hidden');
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

    QUnit.cases.init([{
        title: 'undefined'
    }, {
        title: 'missing name',
        widget: {value: 'test'}
    }, {
        title: 'empty name',
        widget: {uri: ''}
    }]).test('add widget error ', function (data, assert) {
        var ready = assert.async();
        var $container = $('#fixture-widget-error');
        var instance;

        assert.expect(2);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container)
            .on('ready', function () {
                instance.addWidget(data.widget)
                    .then(function () {
                        assert.ok(false, 'The process should fail');
                    })
                    .catch(function () {
                        assert.ok(true, 'Cannot add an invalid widget');
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

    QUnit.test('add widget before init', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-widget-add-before');
        var ranges = {
            range1: ['single'],
            range2: ['multi']
        };
        var instance;

        assert.expect(15);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, {
            widgets: [{
                widget: 'text',
                uri: 'text',
                label: 'Text',
                range: 'range1'
            }],
            ranges: ranges
        });

        instance
            .addWidget({
                widget: 'text',
                uri: 'foo',
                label: 'Foo',
                range: 'range2'
            })
            .then(function (widget) {
                assert.equal(widget, instance.getWidget('foo'), 'The widget is provided');
                assert.notEqual(instance.getWidget('foo'), null, 'The widget foo now exists');
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                assert.equal($container.find('.form-component fieldset').children().length, 2, 'The component contains the expected initial amount of widgets');
                assert.equal($container.find('.form-component fieldset [name="text"]').length, 1, 'The component contains the widget text');
                assert.equal($container.find('.form-component fieldset [name="foo"]').length, 1, 'The component contains the widget foo');
                assert.notEqual(instance.getWidget('text'), null, 'The widget text exists');
                assert.notEqual(instance.getWidget('foo'), null, 'The widget foo exists');
                assert.equal(instance.getWidget('text').getConfig().range, ranges.range1, 'The widget text got the expected range');
                assert.equal(instance.getWidget('foo').getConfig().range, ranges.range2, 'The widget foo got the expected range');
                instance.destroy();
            })
            .catch(function () {
                assert.ok(false, 'The add of widget should not fail');
                instance.destroy();
            });

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
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

    QUnit.test('add widget after init', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-widget-add-after');
        var instance;

        assert.expect(21);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, {
            widgets: [{
                widget: 'text',
                uri: 'text',
                label: 'Text'
            }]
        });

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                return Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                        assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                        assert.equal($container.find('.form-component fieldset').children().length, 1, 'The component contains the expected initial amount of widgets');
                        assert.equal($container.find('.form-component fieldset [name="text"]').length, 1, 'The component contains the widget text');
                        assert.equal($container.find('.form-component fieldset [name="foo"]').length, 0, 'The component does not contain yet the widget foo');
                        assert.equal(instance.getWidget('foo'), null, 'The widget does not exist');

                        return Promise.all([
                            new Promise(function (resolve) {
                                instance
                                    .off('.test')
                                    .on('widgetadd.test', function (uri, widget) {
                                        assert.equal(uri, 'foo', 'The widgetadd event has been triggered');
                                        assert.notEqual(widget, null, 'The widget is provided');
                                        assert.equal(typeof widget, 'object', 'The widget is an object');
                                        resolve();
                                    });
                            }),
                            instance
                                .addWidget({
                                    widget: 'text',
                                    uri: 'foo',
                                    label: 'Foo'
                                })
                                .then(function (widget) {
                                    assert.equal(widget, instance.getWidget('foo'), 'The widget is provided');
                                    assert.notEqual(instance.getWidget('foo'), null, 'The widget foo now exists');
                                    assert.equal($container.find('.form-component fieldset [name="foo"]').length, 1, 'The component now contains the widget foo');
                                    assert.equal($container.find('.form-component fieldset').children().length, 2, 'The component now contains 2 form widgets');
                                })
                        ]);
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            assert.notEqual(instance.getWidget('text'), null, 'The widget text exists');
                            instance
                                .off('.test')
                                .on('widgetremove.test', function (uri) {
                                    assert.equal(uri, 'text', 'The widgetremove event has been triggered');
                                    assert.equal($container.find('.form-component fieldset [name="text"]').length, 0, 'The component does not contain the widget text anymore');
                                    assert.equal(instance.getWidget('text'), null, 'The widget text does not exist anymore');
                                    resolve();
                                })
                                .removeWidget('text');
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

    QUnit.test('set widgets before init', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-widget-set-before');
        var instance;

        assert.expect(14);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, {
            widgets: [{
                widget: 'text',
                uri: 'text',
                label: 'Text'
            }]
        });

        instance
            .setWidgets([{
                widget: 'text',
                uri: 'foo',
                label: 'Foo'
            }])
            .then(function (widgets) {
                var widget = widgets[0];
                assert.equal(widgets.length, 1, 'The expected amount of widget is set');
                assert.equal(widget, instance.getWidget('foo'), 'The widget is provided');
                assert.notEqual(instance.getWidget('foo'), null, 'The widget foo now exists');
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                assert.equal($container.find('.form-component fieldset').children().length, 2, 'The component contains the expected initial amount of widgets');
                assert.equal($container.find('.form-component fieldset [name="text"]').length, 1, 'The component contains the widget text');
                assert.equal($container.find('.form-component fieldset [name="foo"]').length, 1, 'The component contains the widget foo');
                assert.notEqual(instance.getWidget('text'), null, 'The widget text exists');
                assert.notEqual(instance.getWidget('foo'), null, 'The widget foo exists');
                instance.destroy();
            })
            .catch(function () {
                assert.ok(false, 'The add of widget should not fail');
                instance.destroy();
            });

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
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

    QUnit.test('set widgets after init', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-widget-set-after');
        var instance;

        assert.expect(28);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, {
            widgets: [{
                widget: 'text',
                uri: 'text',
                label: 'Text'
            }]
        });

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        var widgets = instance.getWidgets();
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                        assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                        assert.equal($container.find('.form-component fieldset').children().length, 1, 'The component contains the expected initial amount of widgets');
                        assert.equal($container.find('.form-component fieldset [name="text"]').length, 1, 'The component contains the widget text');

                        assert.equal(_.size(widgets), 1, 'The expected amount of widgets is returned');
                        assert.notEqual(widgets.text, null, 'The widget text is returned');
                        assert.equal(typeof widgets.text, 'object', 'The widget text is an object');
                    })
                    .then(function () {
                        return Promise.all([
                            new Promise(function (resolve) {
                                assert.equal($container.find('.form-component fieldset [name="foo"]').length, 0, 'The component does not contain yet the widget foo');
                                assert.equal(instance.getWidget('foo'), null, 'The widget foo does not exist');
                                assert.notEqual(instance.getWidget('text'), null, 'The widget text exists');
                                instance
                                    .off('.test')
                                    .on('widgetremove.test', function (uri) {
                                        assert.equal(uri, 'text', 'The widgetremove event has been triggered');
                                        assert.equal($container.find('.form-component fieldset [name="text"]').length, 0, 'The component does not contain the widget text anymore');
                                        assert.equal(instance.getWidget('text'), null, 'The widget text does not exist anymore');
                                    })
                                    .on('widgetadd.test', function (uri, widget) {
                                        assert.equal(uri, 'foo', 'The widgetadd event has been triggered');
                                        assert.equal(typeof widget, 'object', 'The widget is provided');
                                        resolve();
                                    });
                            }),
                            instance
                                .setWidgets([{
                                    widget: 'text',
                                    uri: 'foo',
                                    label: 'Foo'
                                }])
                                .then(function () {
                                    assert.notEqual(instance.getWidget('foo'), null, 'The widget foo now exists');
                                    assert.equal($container.find('.form-component fieldset [name="foo"]').length, 1, 'The component now contains the widget foo');
                                    assert.equal($container.find('.form-component fieldset').children().length, 1, 'The component contains 1 form widget');
                                })
                        ]);
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            assert.notEqual(instance.getWidget('foo'), null, 'The widget foo exists');
                            instance
                                .off('.test')
                                .on('widgetremove.test', function (uri) {
                                    assert.equal(uri, 'foo', 'The widgetremove event has been triggered');
                                    assert.equal($container.find('.form-component fieldset [name="foo"]').length, 0, 'The component does not contain the widget foo anymore');
                                    assert.equal(instance.getWidget('foo'), null, 'The widget foo does not exist anymore');
                                    assert.equal($container.find('.form-component fieldset').children().length, 0, 'The component does not contains form widgets anymore');
                                    assert.deepEqual(instance.getWidgets(), {}, 'The list of form widgets is empty');
                                    resolve();
                                })
                                .removeWidgets();
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

    QUnit.cases.init([{
        title: 'undefined'
    }, {
        title: 'missing id',
        button: {label: 'test'}
    }, {
        title: 'empty id',
        label: {id: ''}
    }]).test('add button error ', function (data, assert) {
        var ready = assert.async();
        var $container = $('#fixture-button-error');
        var instance;

        assert.expect(2);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container)
            .on('ready', function () {
                instance.addButton(data.button)
                    .then(function () {
                        assert.ok(false, 'The process should fail');
                    })
                    .catch(function () {
                        assert.ok(true, 'Cannot add an invalid button');
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

    QUnit.test('add button before init', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-button-add-before');
        var instance;

        assert.expect(13);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, {
            buttons: [{
                id: 'text',
                label: 'Text'
            }]
        });

        instance
            .addButton({
                id: 'foo',
                label: 'Foo'
            })
            .then(function (button) {
                assert.equal(button, instance.getButton('foo'), 'The button is provided');
                assert.notEqual(instance.getButton('foo'), null, 'The button foo now exists');
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                assert.equal($container.find('.form-component .form-actions').children().length, 2, 'The component contains the expected initial amount of buttons');
                assert.equal($container.find('.form-component .form-actions [data-control="text"]').length, 1, 'The component contains the button text');
                assert.equal($container.find('.form-component .form-actions [data-control="foo"]').length, 1, 'The component contains the button foo');
                assert.notEqual(instance.getButton('text'), null, 'The button text exists');
                assert.notEqual(instance.getButton('foo'), null, 'The button foo exists');
                instance.destroy();
            })
            .catch(function () {
                assert.ok(false, 'The add of button should not fail');
                instance.destroy();
            });

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
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

    QUnit.test('add button after init', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-button-add-after');
        var instance;

        assert.expect(21);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, {
            buttons: [{
                id: 'text',
                label: 'Text'
            }]
        });

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                return Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form buttons');
                        assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                        assert.equal($container.find('.form-component .form-actions').children().length, 1, 'The component contains the expected initial amount of buttons');
                        assert.equal($container.find('.form-component .form-actions [data-control="text"]').length, 1, 'The component contains the button text');
                        assert.equal($container.find('.form-component .form-actions [data-control="foo"]').length, 0, 'The component does not contain yet the button foo');
                        assert.equal(instance.getButton('foo'), null, 'The button does not exist');

                        return Promise.all([
                            new Promise(function (resolve) {
                                instance
                                    .off('.test')
                                    .on('buttonadd.test', function (id, button) {
                                        assert.equal(id, 'foo', 'The buttonadd event has been triggered');
                                        assert.notEqual(button, null, 'The button is provided');
                                        assert.equal(typeof button, 'object', 'The button is an object');
                                        resolve();
                                    });
                            }),
                            instance
                                .addButton({
                                    id: 'foo',
                                    label: 'Foo'
                                })
                                .then(function (button) {
                                    assert.equal(button, instance.getButton('foo'), 'The button is provided');
                                    assert.notEqual(instance.getButton('foo'), null, 'The button foo now exists');
                                    assert.equal($container.find('.form-component .form-actions [data-control="foo"]').length, 1, 'The component now contains the button foo');
                                    assert.equal($container.find('.form-component .form-actions').children().length, 2, 'The component now contains 2 form buttons');
                                })
                        ]);
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            assert.notEqual(instance.getButton('text'), null, 'The button text exists');
                            instance
                                .off('.test')
                                .on('buttonremove.test', function (id) {
                                    assert.equal(id, 'text', 'The buttonremove event has been triggered');
                                    assert.equal($container.find('.form-component .form-actions [data-control="text"]').length, 0, 'The component does not contain the button text anymore');
                                    assert.equal(instance.getButton('text'), null, 'The button text does not exist anymore');
                                    resolve();
                                })
                                .removeButton('text');
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

    QUnit.test('set buttons before init', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-button-set-before');
        var instance;

        assert.expect(14);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, {
            buttons: [{
                id: 'text',
                label: 'Text'
            }]
        });

        instance
            .setButtons([{
                id: 'foo',
                label: 'Foo'
            }])
            .then(function (buttons) {
                var button = buttons[0];
                assert.equal(buttons.length, 1, 'The expected amount of button is set');
                assert.equal(button, instance.getButton('foo'), 'The button is provided');
                assert.notEqual(instance.getButton('foo'), null, 'The button foo now exists');
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form buttons');
                assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                assert.equal($container.find('.form-component .form-actions').children().length, 2, 'The component contains the expected initial amount of buttons');
                assert.equal($container.find('.form-component .form-actions [data-control="text"]').length, 1, 'The component contains the button text');
                assert.equal($container.find('.form-component .form-actions [data-control="foo"]').length, 1, 'The component contains the button foo');
                assert.notEqual(instance.getButton('text'), null, 'The button text exists');
                assert.notEqual(instance.getButton('foo'), null, 'The button foo exists');
                instance.destroy();
            })
            .catch(function () {
                assert.ok(false, 'The add of button should not fail');
                instance.destroy();
            });

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
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

    QUnit.test('set buttons after init', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-button-set-after');
        var instance;

        assert.expect(28);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, {
            buttons: [{
                id: 'text',
                label: 'Text'
            }]
        });

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        var buttons = instance.getButtons();
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form buttons');
                        assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                        assert.equal($container.find('.form-component .form-actions').children().length, 1, 'The component contains the expected initial amount of buttons');
                        assert.equal($container.find('.form-component .form-actions [data-control="text"]').length, 1, 'The component contains the button text');

                        assert.equal(_.size(buttons), 1, 'The expected amount of buttons is returned');
                        assert.notEqual(buttons.text, null, 'The button text is returned');
                        assert.equal(typeof buttons.text, 'object', 'The button text is an object');
                    })
                    .then(function () {
                        return Promise.all([
                            new Promise(function (resolve) {
                                assert.equal($container.find('.form-component .form-actions [data-control="foo"]').length, 0, 'The component does not contain yet the button foo');
                                assert.equal(instance.getButton('foo'), null, 'The button foo does not exist');
                                assert.notEqual(instance.getButton('text'), null, 'The button text exists');
                                instance
                                    .off('.test')
                                    .on('buttonremove.test', function (id) {
                                        assert.equal(id, 'text', 'The buttonremove event has been triggered');
                                        assert.equal($container.find('.form-component .form-actions [data-control="text"]').length, 0, 'The component does not contain the button text anymore');
                                        assert.equal(instance.getButton('text'), null, 'The button text does not exist anymore');
                                    })
                                    .on('buttonadd.test', function (id, button) {
                                        assert.equal(id, 'foo', 'The buttonadd event has been triggered');
                                        assert.equal(typeof button, 'object', 'The button is provided');
                                        resolve();
                                    });
                            }),
                            instance
                                .setButtons([{
                                    id: 'foo',
                                    label: 'Foo'
                                }])
                                .then(function () {
                                    assert.notEqual(instance.getButton('foo'), null, 'The button foo now exists');
                                    assert.equal($container.find('.form-component .form-actions [data-control="foo"]').length, 1, 'The component now contains the button foo');
                                    assert.equal($container.find('.form-component .form-actions').children().length, 1, 'The component contains 1 form button');
                                })
                        ]);
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            assert.notEqual(instance.getButton('foo'), null, 'The button foo exists');
                            instance
                                .off('.test')
                                .on('buttonremove.test', function (id) {
                                    assert.equal(id, 'foo', 'The buttonremove event has been triggered');
                                    assert.equal($container.find('.form-component .form-actions [data-control="foo"]').length, 0, 'The component does not contain the button foo anymore');
                                    assert.equal(instance.getButton('foo'), null, 'The button foo does not exist anymore');
                                    assert.equal($container.find('.form-component .form-actions').children().length, 0, 'The component does not contains form buttons anymore');
                                    assert.deepEqual(instance.getButtons(), {}, 'The list of form buttons is empty');
                                    resolve();
                                })
                                .removeButtons();
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

    QUnit.test('click on button', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-button-click');
        var instance;

        assert.expect(16);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, {
            buttons: [{
                id: 'submit',
                label: 'Submit'
            }]
        });

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                        assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                        assert.equal($container.find('.form-component .form-actions').children().length, 1, 'The component contains the expected initial amount of buttons');
                        assert.equal($container.find('.form-component .form-actions [data-control="submit"]').length, 1, 'The component contains the button submit');
                        assert.equal($container.find('.form-component .form-actions [data-control="foo"]').length, 0, 'The component does not contain yet the button foo');
                        assert.equal(instance.getButton('foo'), null, 'The button foo does not exist');
                        return instance.addButton({
                            id: 'foo',
                            label: 'Foo'
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            assert.notEqual(instance.getButton('submit'), null, 'The button submit exists');
                            instance
                                .off('.test')
                                .on('button.test', function (id) {
                                    assert.equal(id, 'submit', 'The button event has been triggered');
                                })
                                .on('button-submit.test', function () {
                                    assert.ok(true, 'The button-submit event has been triggered');
                                    resolve();
                                });
                            $container.find('.form-component .form-actions [data-control="submit"]').click();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            assert.notEqual(instance.getButton('foo'), null, 'The button foo exists');
                            instance
                                .off('.test')
                                .on('button.test', function (id) {
                                    assert.equal(id, 'foo', 'The button event has been triggered');
                                })
                                .on('button-foo.test', function () {
                                    assert.ok(true, 'The button-foo event has been triggered');
                                    resolve();
                                });
                            $container.find('.form-component .form-actions [data-control="foo"]').click();
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

    QUnit.test('change notification', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-change');
        var instance;

        assert.expect(22);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container, {
            widgets: [{
                widget: 'text',
                uri: 'text',
                label: 'Text'
            }, {
                widget: 'text',
                uri: 'foo',
                label: 'Foo'
            }]
        });

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                        assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                        assert.equal($container.find('.form-component fieldset').children().length, 2, 'The component contains 2 form widgets');
                        assert.equal($container.find('.form-component fieldset [name="text"]').length, 1, 'The component contains the widget text');
                        assert.equal($container.find('.form-component fieldset [name="foo"]').length, 1, 'The component contains the widget foo');
                        assert.deepEqual(instance.getValues(), {
                            text: '',
                            foo: ''
                        }, 'Empty values');

                        instance.off('.test');
                        return Promise.all([
                            new Promise(function (resolve) {
                                instance
                                    .on('change-text.test', function (value) {
                                        assert.equal(value, 'test', 'The expected value is there');
                                        resolve();
                                    });
                            }),
                            new Promise(function (resolve) {
                                instance
                                    .on('change.test', function (uri, value) {
                                        assert.equal(uri, 'text', 'The change event has been triggered');
                                        assert.equal(value, 'test', 'The expected value is there');
                                        resolve();
                                    })
                                    .setValue('text', 'test');
                            })
                        ]);
                    })
                    .then(function () {
                        instance.off('.test');
                        return Promise.all([
                            new Promise(function (resolve) {
                                assert.equal(instance.getValue('foo'), '', 'The foo widget is empty');
                                instance
                                    .on('change-foo.test', function (value) {
                                        assert.equal(value, 'top', 'The expected value is there');
                                        resolve();
                                    });
                            }),
                            new Promise(function (resolve) {
                                assert.equal(instance.getValue('foo'), '', 'The foo widget is empty');
                                instance
                                    .on('change.test', function (uri, value) {
                                        assert.equal(uri, 'foo', 'The change event has been triggered');
                                        assert.equal(value, 'top', 'The expected value is there');
                                        resolve();
                                    });

                                $container.find('.form-component fieldset [name="foo"]').val('top').change();
                            })
                        ]);
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            var count = 0;
                            instance
                                .off('.test')
                                .on('change.test', function (uri, value) {
                                    assert.ok(true, 'The change event has been triggered');
                                    if (uri === 'text') {
                                        assert.equal(value, 'top', 'The expected value is there');
                                    } else if (uri === 'foo') {
                                        assert.equal(value, 'bar', 'The expected value is there');
                                    } else {
                                        assert.ok(false, 'The expected value is not there');
                                    }

                                    if (++count >= 2) {
                                        resolve();
                                    }
                                })
                                .setValues({
                                    text: 'top',
                                    foo: 'bar'
                                });
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
        var $container = $('#fixture-values');
        var instance;

        assert.expect(20);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                        assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                        assert.equal($container.find('.form-component fieldset').children().length, 0, 'The component does not contains any form widgets');
                        assert.deepEqual(instance.getValues(), {}, 'There is no values');
                        assert.equal(instance.getValue('foo'), '', 'The widget foo is unknown and has no value');

                        return instance.setWidgets([{
                            widget: 'text',
                            uri: 'text',
                            label: 'Text'
                        }, {
                            widget: 'text',
                            uri: 'foo',
                            label: 'Foo'
                        }]);
                    })
                    .then(function () {
                        assert.equal($container.find('.form-component fieldset').children().length, 2, 'The component now contains 2 form widgets');
                        assert.equal($container.find('.form-component fieldset [name="text"]').length, 1, 'The component contains the widget text');
                        assert.equal($container.find('.form-component fieldset [name="foo"]').length, 1, 'The component contains the widget foo');
                        assert.deepEqual(instance.getValues(), {
                            text: '',
                            foo: ''
                        }, 'Empty values');

                        instance.setValue('text', 'test');
                        assert.equal(instance.getValue('text'), 'test', 'The text widget is filled');
                        assert.deepEqual(instance.getValues(), {
                            text: 'test',
                            foo: ''
                        }, 'Expected values');

                        $container.find('.form-component fieldset [name="foo"]').val('top').change();
                    })
                    .then(function () {
                        assert.equal(instance.getValue('foo'), 'top', 'The foo widget is filled');
                        assert.deepEqual(instance.getValues(), {
                            text: 'test',
                            foo: 'top'
                        }, 'Expected values');

                        instance.setValues({
                            text: 'top',
                            foo: 'bar'
                        });
                        assert.equal(instance.getValue('text'), 'top', 'The text widget is filled');
                        assert.equal(instance.getValue('foo'), 'bar', 'The foo widget is filled');
                        assert.deepEqual(instance.getValues(), {
                            text: 'top',
                            foo: 'bar'
                        }, 'Expected values');
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

        assert.expect(20);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = formFactory($container)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                        assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                        assert.equal($container.find('.form-component fieldset').children().length, 0, 'The component does not contains any form widgets');
                        assert.deepEqual(instance.serialize(), [], 'There is no values');
                        assert.equal(instance.getValue('foo'), '', 'The widget foo is unknown and has no value');

                        return instance.setWidgets([{
                            widget: 'text',
                            uri: 'text',
                            label: 'Text'
                        }, {
                            widget: 'text',
                            uri: 'foo',
                            label: 'Foo'
                        }]);
                    })
                    .then(function () {
                        assert.equal($container.find('.form-component fieldset').children().length, 2, 'The component now contains 2 form widgets');
                        assert.equal($container.find('.form-component fieldset [name="text"]').length, 1, 'The component contains the widget text');
                        assert.equal($container.find('.form-component fieldset [name="foo"]').length, 1, 'The component contains the widget foo');
                        assert.deepEqual(instance.serialize(), [{
                            name: 'text',
                            value: ''
                        }, {
                            name: 'foo',
                            value: ''
                        }], 'Empty values');

                        instance.setValue('text', 'test');
                        assert.equal(instance.getValue('text'), 'test', 'The text widget is filled');
                        assert.deepEqual(instance.serialize(), [{
                            name: 'text',
                            value: 'test'
                        }, {
                            name: 'foo',
                            value: ''
                        }], 'Expected values');

                        $container.find('.form-component fieldset [name="foo"]').val('top').change();
                    })
                    .then(function () {
                        assert.equal(instance.getValue('foo'), 'top', 'The foo widget is filled');
                        assert.deepEqual(instance.serialize(), [{
                            name: 'text',
                            value: 'test'
                        }, {
                            name: 'foo',
                            value: 'top'
                        }], 'Expected values');

                        instance.setValues({
                            text: 'top',
                            foo: 'bar'
                        });
                    })
                    .then(function () {
                        assert.equal(instance.getValue('text'), 'top', 'The text widget is filled');
                        assert.equal(instance.getValue('foo'), 'bar', 'The foo widget is filled');
                        assert.deepEqual(instance.serialize(), [{
                            name: 'text',
                            value: 'top'
                        }, {
                            name: 'foo',
                            value: 'bar'
                        }], 'Expected values');
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

    QUnit.test('validate', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-validate');
        var instance = formFactory($container);

        assert.expect(10);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');

                instance.validate()
                    .then(function () {
                        assert.ok(true, 'The form is valid');
                    })
                    .catch(function () {
                        assert.ok(false, 'The form should be valid');
                    })
                    .then(function () {
                        return instance.setWidgets([{
                            widget: 'text',
                            uri: 'foo',
                            label: 'Foo'
                        }]);
                    })
                    .then(function () {
                        return instance.validate()
                            .then(function () {
                                assert.ok(!instance.is('invalid'), 'The form is valid');
                            })
                            .catch(function () {
                                assert.ok(!instance.is('invalid'), 'The form should be valid');
                            });
                    })
                    .then(function () {
                        instance.getWidget('foo')
                            .setValidator({
                                id: 'required',
                                message: 'Oops!',
                                predicate: function() {
                                    return false;
                                }
                            })
                            .setValue('');
                        return instance.validate()
                            .then(function () {
                                assert.ok(instance.is('invalid'), 'The form should not be valid');
                            })
                            .catch(function (reason) {
                                assert.ok(instance.is('invalid'), 'The form has been rejected');
                                assert.deepEqual(reason, [{
                                    uri: 'foo',
                                    messages: ['Oops!']
                                }], 'The expected reason has been received');
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

    QUnit.test('submit', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-submit');
        var instance = formFactory($container);

        assert.expect(13);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');

                Promise.resolve()
                    .then(function () {
                        instance.off('.test');
                        return new Promise(function (resolve) {
                            instance
                                .on('invalid.test', function () {
                                    assert.ok(false, 'The invalid event should not be emitted');
                                    resolve();
                                })
                                .on('submit.test', function (values) {
                                    assert.ok(true, 'The submit event has been emitted');
                                    assert.deepEqual(values, [], 'The list of values is empty');
                                    resolve();
                                })
                                .submit();
                        });
                    })
                    .then(function () {
                        return instance.setWidgets([{
                            widget: 'text',
                            uri: 'foo',
                            label: 'Foo'
                        }]);
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .setValues({
                                    foo: 'bar'
                                })
                                .on('invalid.test', function () {
                                    assert.ok(false, 'The invalid event should not be emitted');
                                    resolve();
                                })
                                .on('submit.test', function (values) {
                                    assert.ok(true, 'The submit event has been emitted');
                                    assert.deepEqual(values, [{
                                        name: 'foo',
                                        value: 'bar'
                                    }], 'The list of values is provided');
                                    resolve();
                                })
                                .submit();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .setValues({
                                    foo: 'bar'
                                })
                                .on('invalid.test', function () {
                                    assert.ok(false, 'The invalid event should not be emitted');
                                    resolve();
                                })
                                .on('submit.test', function (values) {
                                    assert.ok(true, 'The submit event has been emitted');
                                    assert.deepEqual(values, [{
                                        name: 'foo',
                                        value: 'bar'
                                    }], 'The list of values is provided');
                                    resolve();
                                });
                            $container.find('form').submit();
                        });
                    })
                    .then(function () {
                        return new Promise(function (resolve) {
                            instance.getWidget('foo').validate = function () {
                                return Promise.reject(false);
                            };
                            instance
                                .off('.test')
                                .on('submit.test', function () {
                                    assert.ok(false, 'The submit event should not be emitted');
                                    resolve();
                                })
                                .on('invalid.test', function () {
                                    assert.ok(true, 'The invalid event has been emitted');
                                    resolve();
                                })
                                .submit();
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

    QUnit.test('reset', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-reset');
        var instance = formFactory($container);

        assert.expect(14);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-component'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');

                Promise.resolve()
                    .then(function () {
                        instance.off('.test');
                        return new Promise(function (resolve) {
                            instance
                                .on('invalid.test', function () {
                                    assert.ok(false, 'The invalid event should not be emitted');
                                    resolve();
                                })
                                .on('reset.test', function () {
                                    assert.ok(true, 'The reset event has been emitted');
                                    assert.deepEqual(this.getValues(), {}, 'The list of values is empty');
                                    resolve();
                                })
                                .reset();
                        });
                    })
                    .then(function () {
                        return instance.setWidgets([{
                            widget: 'text',
                            uri: 'foo',
                            label: 'Foo'
                        }]);
                    })
                    .then(function () {
                        instance.setValues({
                            foo: 'bar'
                        });
                        assert.deepEqual(instance.getValues(), {
                            foo: 'bar'
                        }, 'The values are set');
                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .on('invalid.test', function () {
                                    assert.ok(false, 'The invalid event should not be emitted');
                                    resolve();
                                })
                                .on('reset.test', function () {
                                    assert.ok(true, 'The reset event has been emitted');
                                    assert.deepEqual(instance.getValues(), {
                                        foo: ''
                                    }, 'The list of values is reset');
                                    resolve();
                                })
                                .reset();
                        });
                    })
                    .then(function () {
                        instance.setValues({
                            foo: 'bar'
                        });
                        assert.deepEqual(instance.getValues(), {
                            foo: 'bar'
                        }, 'The values are set');
                        return new Promise(function (resolve) {
                            instance
                                .off('.test')
                                .on('invalid.test', function () {
                                    assert.ok(false, 'The invalid event should not be emitted');
                                    resolve();
                                })
                                .on('reset.test', function () {
                                    assert.ok(true, 'The reset event has been emitted');
                                    assert.deepEqual(instance.getValues(), {
                                        foo: ''
                                    }, 'The list of values is reset');
                                    resolve();
                                });
                            $container.find('form').get(0).reset();
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

    QUnit.module('Visual');

    QUnit.test('Visual test', function (assert) {
        var ready = assert.async();
        var $container = $('#visual-test .test');
        var $outputChange = $('#visual-test .change-output');
        var $outputSubmit = $('#visual-test .submit-output');
        var instance = formFactory($container, {
            widgets: [{
                widget: widgetDefinitions.TEXTBOX,
                uri: 'subject',
                label: 'Subject',
                required: true
            }, {
                widget: widgetDefinitions.TEXTAREA,
                uri: 'text',
                label: 'Text',
                required: true
            }, {
                widget: widgetDefinitions.COMBOBOX,
                uri: 'category',
                label: 'Category',
                required: true,
                range: [{
                    uri: 'comment',
                    label: 'Comment'
                }, {
                    uri: 'appprove',
                    label: 'Approve'
                }, {
                    uri: 'request',
                    label: 'Request changes'
                }]
            }, {
                widget: widgetDefinitions.CHECKBOX,
                uri: 'publish',
                label: 'Publish',
                required: true,
                range: [{
                    uri: 'yes',
                    label: 'Yes'
                }, {
                    uri: 'no',
                    label: 'No'
                }]
            }, {
                widget: widgetDefinitions.HIDDENBOX,
                uri: 'password',
                label: 'Password'
            }],
            buttons: [{
                id: 'clear',
                label: 'Clear'
            }, {
                id: 'reset',
                type: 'warning',
                icon: 'reset',
                label: 'Reset'
            }, {
                type: 'info',
                icon: 'save',
                id: 'submit',
                label: 'Submit'
            }]
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
            .on('change', function (uri, value) {
                $outputChange.val('value of [' + uri + '] changed to "' + value + '"\n' + $outputChange.val());
            })
            .on('button-reset', function () {
                this.reset();
            })
            .on('button-submit', function () {
                this.submit();
            })
            .on('reset button-clear', function () {
                $outputChange.val('');
                $outputSubmit.val('');
            })
            .on('submit', function (values) {
                $outputSubmit.val('Submitted values:\n' + JSON.stringify(values, null, 2));
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
