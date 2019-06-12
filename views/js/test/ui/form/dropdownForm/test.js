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
    'ui/form/dropdownForm',
    'ui/form/widget/widget',
    'ui/form/widget/definitions'
], function (
    $,
    _,
    dropdownFormFactory,
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
            return dropdownFormFactory('#fixture-api')
                .on('ready', function () {
                    this.destroy();
                });
        }

        assert.expect(3);

        assert.equal(typeof dropdownFormFactory, 'function', 'The module exposes a function');
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
        var instance = dropdownFormFactory('#fixture-api')
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
        var instance = dropdownFormFactory('#fixture-api')
            .on('ready', function () {
                this.destroy();
            });
        assert.expect(1);
        assert.equal(typeof instance[data.title], 'function', 'The instance exposes a "' + data.title + '" function');
    });

    QUnit.cases.init([
        {title: 'getForm'},
        {title: 'setFormWidgets'},
        {title: 'getFormValues'},
        {title: 'setFormValues'},
        {title: 'openForm'},
        {title: 'closeForm'}
    ]).test('dropdownForm API ', function (data, assert) {
        var instance = dropdownFormFactory('#fixture-api')
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
        var instance = dropdownFormFactory($container);

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
                id: 'foo',
                label: 'Foo'
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
                id: 'foo',
                label: 'Foo'
            }],
            values: {
                text: 'foo 3'
            }
        }
    }]).test('render', function (data, assert) {
        var ready = assert.async();
        var $container = $('#fixture-render');
        var widgets = data.config && data.config.widgets;
        var buttons = [{
            id: 'submit'
        }].concat(data.config && data.config.buttons || []);
        var instance;

        assert.expect(13 + _.size(widgets) + _.size(buttons));

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = dropdownFormFactory($container, data.config);

        assert.equal(instance.getForm(), null, 'The form does not exist');

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.dropdown-form'), true, 'The container contains the expected element');
                assert.equal($container.find('.dropdown-form .trigger-button').length, 1, 'The component contains an area for the trigger button');
                assert.equal($container.find('.dropdown-form .form-panel').length, 1, 'The component contains an area for the form');

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

                assert.deepEqual(instance.getFormValues(), data.config && data.config.values || {}, 'The component has set the form values');

                assert.notEqual(instance.getForm(), null, 'The form is accessible');

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
        var instance;

        assert.expect(9);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = dropdownFormFactory($container);
        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.dropdown-form'), true, 'The container contains the expected element');
                assert.equal($container.find('.dropdown-form [data-control="trigger"]').length, 1, 'The component contains the trigger button');
                assert.equal($container.find('.dropdown-form [data-control="submit"]').length, 1, 'The component contains the submit button');
                assert.equal($container.find('.dropdown-form [data-control="trigger"]:visible').length, 1, 'The trigger button of is visible');

                instance.hide();
                assert.equal($container.find('.dropdown-form [data-control="trigger"]:visible').length, 0, 'The trigger button of is hidden');

                instance.show();
                assert.equal($container.find('.dropdown-form [data-control="trigger"]:visible').length, 1, 'The trigger button of is visible again');

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

        assert.expect(12);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = dropdownFormFactory($container);
        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function() {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.dropdown-form'), true, 'The container contains the expected element');
                        assert.equal($container.find('.dropdown-form [data-control="trigger"]').length, 1, 'The component contains the trigger button');
                        assert.equal($container.find('.dropdown-form [data-control="submit"]').length, 1, 'The component contains the submit button');
                        assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The form is hidden');

                        instance.openForm();
                        assert.equal($container.find('.dropdown-form form:visible').length, 1, 'The form is visible');
                        assert.equal($container.find('.dropdown-form [data-control="trigger"]:enabled').length, 1, 'The trigger button of is enabled');
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            instance
                                .off('.test')
                                .after('disable.test', function() {
                                    assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The form is hidden');
                                    assert.equal($container.find('.dropdown-form [data-control="trigger"]:enabled').length, 0, 'The trigger button of is disabled');
                                    resolve();
                                })
                                .disable();
                        });
                    })
                    .then(function() {
                        return new Promise(function(resolve) {
                            instance
                                .off('.test')
                                .after('enable.test', function() {
                                    assert.equal($container.find('.dropdown-form [data-control="trigger"]:enabled').length, 1, 'The trigger button of is enabled again');
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
                    .then(function() {
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

        instance = dropdownFormFactory($container);
        instance
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
        title: 'default'
    }, {
        title: 'config widgets',
        config: {
            widgets: [{
                widget: 'text',
                uri: 'title',
                label: 'Title'
            }]
        }
    }, {
        title: 'set widgets',
        widgets: [{
            widget: 'text',
            uri: 'secret',
            value: 'foo'
        }, {
            widget: 'text',
            uri: 'title',
            label: 'Title'
        }]
    }, {
        title: 'replace widgets',
        config: {
            widgets: [{
                widget: 'text',
                uri: 'title',
                label: 'Title'
            }]
        },
        widgets: [{
            widget: 'text',
            uri: 'secret',
            value: 'foo'
        }]
    }]).test('widgets', function (data, assert) {
        var ready = assert.async();
        var $container = $('#fixture-widgets');
        var instance;

        assert.expect(10 + _.size(data.config && data.config.widgets) + _.size(data.widgets));

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = dropdownFormFactory($container, data.config);

        instance
            .setFormWidgets([{
                widget: 'text',
                uri: 'foo',
                label: 'Foo'
            }])
            .then(function() {
                assert.ok(false, 'The process should fail');
            })
            .catch(function() {
                assert.ok(true, 'Cannot add an element if the form is not yet rendered');
            });

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.dropdown-form'), true, 'The container contains the expected element');
                assert.equal($container.find('.dropdown-form [data-control="trigger"]').length, 1, 'The component contains the trigger button');
                assert.equal($container.find('.dropdown-form [data-control="submit"]').length, 1, 'The component contains the submit button');
                assert.equal($container.find('.dropdown-form fieldset').length, 1, 'The component contains a place for the widgets');

                assert.equal($container.find('.dropdown-form fieldset').children().length, _.size(data.config && data.config.widgets), 'The initial widgets are rendered');

                _.forEach(data.config && data.config.widgets, function(widget) {
                    assert.equal($container.find('.dropdown-form fieldset [name="' + widget.uri + '"]').first().length, 1, 'The widget ' + widget.uri + ' has been rendered');
                });

                instance.setFormWidgets(data.widgets)
                    .then(function() {
                        assert.equal($container.find('.dropdown-form fieldset').children().length, _.size(data.widgets), 'The new widgets are rendered');

                        _.forEach(data.widgets, function (widget) {
                            assert.equal($container.find('.dropdown-form fieldset [name="' + widget.uri + '"]').first().length, 1, 'The widget ' + widget.uri + ' has been rendered');
                        });
                    })
                    .catch(function (err) {
                        assert.ok(false, 'The operation should not fail!');
                        assert.pushResult({
                            result: false,
                            message: err
                        });
                    })
                    .then(function() {
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

    QUnit.test('values', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-values');
        var instance;

        assert.expect(21);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = dropdownFormFactory($container)
            .on('init', function () {
                assert.deepEqual(instance.getFormValues(), {}, 'There is no values');
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                Promise.resolve()
                    .then(function () {
                        assert.equal($container.children().length, 1, 'The container contains an element');
                        assert.equal($container.children().is('.dropdown-form'), true, 'The container contains the expected element');
                        assert.equal($container.find('.form-component fieldset').length, 1, 'The component contains an area for the form widgets');
                        assert.equal($container.find('.form-component .form-actions').length, 1, 'The component contains an area for the buttons');
                        assert.equal($container.find('.form-component fieldset').children().length, 0, 'The component does not contains any form widgets');
                        assert.deepEqual(instance.getFormValues(), {}, 'There is no values');
                        assert.equal(instance.getForm().getValue('foo'), '', 'The widget foo is unknown and has no value');

                        return instance.setFormWidgets([{
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
                        assert.deepEqual(instance.getFormValues(), {
                            text: '',
                            foo: ''
                        }, 'Empty values');

                        instance.getForm().setValue('text', 'test');
                        assert.equal(instance.getForm().getValue('text'), 'test', 'The text widget is filled');
                        assert.deepEqual(instance.getFormValues(), {
                            text: 'test',
                            foo: ''
                        }, 'Expected values');

                        $container.find('.form-component fieldset [name="foo"]').val('top').change();
                    })
                    .then(function () {
                        assert.equal(instance.getForm().getValue('foo'), 'top', 'The foo widget is filled');
                        assert.deepEqual(instance.getFormValues(), {
                            text: 'test',
                            foo: 'top'
                        }, 'Expected values');

                        instance.setFormValues({
                            text: 'top',
                            foo: 'bar'
                        });
                        assert.equal(instance.getForm().getValue('text'), 'top', 'The text widget is filled');
                        assert.equal(instance.getForm().getValue('foo'), 'bar', 'The foo widget is filled');
                        assert.deepEqual(instance.getFormValues(), {
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

    QUnit.cases.init([{
        title: 'default'
    }, {
        title: 'default open',
        open: true
    }]).test('open/close form', function (data, assert) {
        var ready = assert.async();
        var $container = $('#fixture-open');
        var instance;

        assert.expect(12);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = dropdownFormFactory($container);
        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');

                if (data.open) {
                    instance.openForm();
                    assert.equal(instance.is('open'), true, 'The form is opened by default');
                } else {
                    assert.equal(instance.is('open'), false, 'The form is closed by default');
                }
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.dropdown-form'), true, 'The container contains the expected element');
                assert.equal($container.find('.dropdown-form [data-control="trigger"]').length, 1, 'The component contains the trigger button');
                assert.equal($container.find('.dropdown-form [data-control="submit"]').length, 1, 'The component contains the submit button');

                if (data.open) {
                    assert.equal($container.find('.dropdown-form form:visible').length, 1, 'The form is visible');
                } else {
                    assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The form is hidden');
                }

                Promise.resolve()
                    .then(function() {
                        return new Promise(function(resolve) {
                            instance
                                .off('.test')
                                .on('open.test', function() {
                                    assert.equal($container.find('.dropdown-form form:visible').length, 1, 'The open event has been emitted');
                                    resolve();
                                })
                                .openForm();
                        });
                    })
                    .then(function() {
                        assert.equal($container.find('.dropdown-form form:visible').length, 1, 'The form is visible');
                        return new Promise(function(resolve) {
                            instance
                                .off('.test')
                                .on('close.test', function() {
                                    assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The close event has been emitted');
                                    resolve();
                                })
                                .closeForm();
                        });
                    })
                    .then(function() {
                        assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The form is hidden again');
                    })
                    .catch(function (err) {
                        assert.ok(false, 'The operation should not fail!');
                        assert.pushResult({
                            result: false,
                            message: err
                        });
                    })
                    .then(function() {
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
        title: 'left',
        left: true
    }, {
        title: 'right',
        left: false
    }]).test('open position', function (data, assert) {
        var ready = assert.async();
        var $container = $('#fixture-open');
        var instance;

        assert.expect(15);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = dropdownFormFactory($container);
        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.dropdown-form'), true, 'The container contains the expected element');
                assert.equal($container.find('.dropdown-form [data-control="trigger"]').length, 1, 'The component contains the trigger button');
                assert.equal($container.find('.dropdown-form [data-control="submit"]').length, 1, 'The component contains the submit button');
                assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The form is hidden');

                $container.css({
                    position: 'relative',
                    width: '800px'
                });
                instance.getElement().css({
                    position: 'relative',
                    width: '80px',
                    left: data.left ? '0' : '600px'
                });

                Promise.resolve()
                    .then(function() {
                        return new Promise(function(resolve) {
                            instance
                                .off('.test')
                                .on('open.test', function() {
                                    assert.equal($container.find('.dropdown-form form:visible').length, 1, 'The open event has been emitted');
                                    resolve();
                                })
                                .openForm();
                        });
                    })
                    .then(function() {
                        assert.equal($container.find('.dropdown-form form:visible').length, 1, 'The form is visible');
                        assert.equal($container.find('.dropdown-form').is('.open-on-left'), data.left, 'The form is open on the left');
                        assert.equal($container.find('.dropdown-form').is('.open-on-right'), !data.left, 'The form is open on the right');
                        assert.equal(instance.is('open-on-left'), data.left, 'The state is open on the left');
                        assert.equal(instance.is('open-on-right'), !data.left, 'The state is open on the right');

                        return new Promise(function(resolve) {
                            instance
                                .off('.test')
                                .on('close.test', function() {
                                    assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The close event has been emitted');
                                    resolve();
                                })
                                .closeForm();
                        });
                    })
                    .then(function() {
                        assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The form is hidden again');
                    })
                    .catch(function (err) {
                        assert.ok(false, 'The operation should not fail!');
                        assert.pushResult({
                            result: false,
                            message: err
                        });
                    })
                    .then(function() {
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

    QUnit.test('trigger dropdown', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-trigger');
        var instance = dropdownFormFactory($container);

        assert.expect(11);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.dropdown-form'), true, 'The container contains the expected element');
                assert.equal($container.find('.dropdown-form [data-control="trigger"]').length, 1, 'The component contains the trigger button');
                assert.equal($container.find('.dropdown-form [data-control="submit"]').length, 1, 'The component contains the submit button');

                assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The form is hidden');

                Promise.resolve()
                    .then(function() {
                        return new Promise(function(resolve) {
                            instance
                                .off('.test')
                                .on('open.test', function() {
                                    assert.equal($container.find('.dropdown-form form:visible').length, 1, 'The open event has been emitted');
                                    resolve();
                                });

                            $container.find('.dropdown-form [data-control="trigger"]').click();
                        });
                    })
                    .then(function() {
                        assert.equal($container.find('.dropdown-form form:visible').length, 1, 'The form is visible');
                        return new Promise(function(resolve) {
                            instance
                                .off('.test')
                                .on('close.test', function() {
                                    assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The close event has been emitted');
                                    resolve();
                                });

                            $container.find('.dropdown-form [data-control="trigger"]').click();
                        });
                    })
                    .then(function() {
                        assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The form is hidden again');
                    })
                    .catch(function (err) {
                        assert.ok(false, 'The operation should not fail!');
                        assert.pushResult({
                            result: false,
                            message: err
                        });
                    })
                    .then(function() {
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

    QUnit.test('submit', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-submit');
        var instance = dropdownFormFactory($container, {
            widgets: [{
                widget: 'text',
                uri: 'foo',
                label: 'Foo',
                value: 'bar'
            }]
        });

        assert.expect(12);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.dropdown-form'), true, 'The container contains the expected element');
                assert.equal($container.find('.dropdown-form [data-control="trigger"]').length, 1, 'The component contains the trigger button');
                assert.equal($container.find('.dropdown-form [data-control="submit"]').length, 1, 'The component contains the submit button');

                assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The form is hidden');

                Promise.resolve()
                    .then(function() {
                        return new Promise(function(resolve) {
                            instance
                                .off('.test')
                                .on('open.test', function() {
                                    assert.equal($container.find('.dropdown-form form:visible').length, 1, 'The open event has been emitted');
                                    resolve();
                                });

                            $container.find('.dropdown-form [data-control="trigger"]').click();
                        });
                    })
                    .then(function() {
                        instance.off('.test');
                        assert.equal($container.find('.dropdown-form form:visible').length, 1, 'The form is visible');
                        _.defer(function() {
                            $container.find('.dropdown-form [data-control="submit"]').click();
                        });
                        return Promise.all([
                            new Promise(function(resolve) {
                                instance
                                    .on('close.test', function() {
                                        assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The close event has been emitted');
                                        resolve();
                                    });
                            }),
                            new Promise(function(resolve) {
                                instance
                                    .on('submit.test', function(values) {
                                        assert.deepEqual(values, [{
                                            name: 'foo',
                                            value: 'bar'
                                        }], 'The values are submitted');
                                        resolve();
                                    });
                            })
                        ]);
                    })
                    .then(function() {
                        assert.equal($container.find('.dropdown-form form:visible').length, 0, 'The form is hidden again');
                    })
                    .catch(function (err) {
                        assert.ok(false, 'The operation should not fail!');
                        assert.pushResult({
                            result: false,
                            message: err
                        });
                    })
                    .then(function() {
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

    QUnit.test('change', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-change');
        var config = {
            widgets: [{
                widget: 'text',
                uri: 'secret',
                value: 'foo'
            }, {
                widget: 'text',
                uri: 'title',
                label: 'Title'
            }]
        };
        var instance;

        function testElement(expectedUri, expectedValue) {
            _.defer(function() {
                $container.find('.dropdown-form fieldset [name="' + expectedUri + '"]').val(expectedValue).change();
            });
            return new Promise(function(resolve) {
                instance
                    .off('.test')
                    .on('change.test', function(uri, value) {
                        assert.equal(uri, expectedUri, 'The change event is emitted for ' + expectedUri);
                        assert.equal(value, expectedValue, 'The change event comes with the value ' + expectedValue);
                        resolve();
                    });
            });
        }

        assert.expect(13);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = dropdownFormFactory($container, config);
        instance
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.dropdown-form'), true, 'The container contains the expected element');
                assert.equal($container.find('.dropdown-form [data-control="trigger"]').length, 1, 'The component contains the trigger button');
                assert.equal($container.find('.dropdown-form [data-control="submit"]').length, 1, 'The component contains the submit button');
                assert.equal($container.find('.dropdown-form fieldset').length, 1, 'The component contains a place for the widgets');
                assert.equal($container.find('.dropdown-form fieldset').children().length, _.size(config.widgets), 'The initial widgets are rendered');

                instance.openForm();
                assert.equal($container.find('.dropdown-form form:visible').length, 1, 'The form is visible');

                Promise.resolve()
                    .then(function() {
                        return testElement('secret', 'password');
                    })
                    .then(function() {
                        return testElement('title', 'foo bar');
                    })
                    .catch(function (err) {
                        assert.ok(false, 'The operation should not fail!');
                        assert.pushResult({
                            result: false,
                            message: err
                        });
                    })
                    .then(function() {
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
/**/
    QUnit.module('Visual');

    QUnit.test('Visual test', function (assert) {
        var ready = assert.async();
        var $container = $('#visual-test .toolbar');
        var $outputChange = $('#visual-test .change-output');
        var $outputSubmit = $('#visual-test .submit-output');

        function getInstance(config) {
            return new Promise(function (resolve, reject) {
                dropdownFormFactory($container, config)
                    .on('init', function () {
                        assert.ok(true, 'The instance has been initialized');
                    })
                    .on('ready', function () {
                        assert.ok(true, 'The instance has been created');
                        resolve(this);
                    })
                    .on('error', reject)
                    .on('change', function (uri, value) {
                        $outputChange.val('value of [' + uri + '] changed to "' + value + '"\n' + $outputChange.val());
                    })
                    .on('open', function () {
                        $outputChange.val('');
                        $outputSubmit.val('');
                    })
                    .on('submit', function (values) {
                        $outputSubmit.val('Submitted values:\n' + JSON.stringify(values, null, 2));
                    });
            })
        }

        assert.expect(6);

        assert.equal($container.children().length, 0, 'The container is empty');

        Promise.all([
            getInstance({
                triggerLabel: 'Validate',
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
                    uri: 'publish',
                    label: 'Publish',
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
                }]
            }),
            getInstance({
                triggerLabel: 'Comment',
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
                    widget: widgetDefinitions.CHECKBOX,
                    uri: 'publish',
                    label: 'Publish',
                    range: [{
                        uri: 'yes',
                        label: 'Yes'
                    }, {
                        uri: 'no',
                        label: 'No'
                    }]
                }]
            })
        ])
            .then(function () {
                assert.equal($container.children().length, 2, 'The container contains the expected widgets');
                ready();
            })
            .catch(function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });
});
