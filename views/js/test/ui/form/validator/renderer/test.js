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
    'ui/form/validator/renderer'
], function (
    $,
    _,
    validatorRendererFactory
) {
    'use strict';

    QUnit.module('Factory');

    QUnit.test('module', function (assert) {
        function getInstance() {
            return validatorRendererFactory('#fixture-api')
                .on('ready', function () {
                    this.destroy();
                });
        }

        assert.expect(3);

        assert.equal(typeof validatorRendererFactory, 'function', 'The module exposes a function');
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
        var instance = validatorRendererFactory('#fixture-api')
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
        var instance = validatorRendererFactory('#fixture-api')
            .on('ready', function () {
                this.destroy();
            });
        assert.expect(1);
        assert.equal(typeof instance[data.title], 'function', 'The instance exposes a "' + data.title + '" function');
    });

    QUnit.cases.init([
        {title: 'clear'},
        {title: 'display'}
    ]).test('component API ', function (data, assert) {
        var instance = validatorRendererFactory('#fixture-api')
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
        var instance = validatorRendererFactory($container);

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

    QUnit.test('render empty', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-render');
        var config = {};
        var instance;

        assert.expect(5);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = validatorRendererFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-validator'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-validator .validation-error').length, 0, 'The component is empty');

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

    QUnit.test('render single message', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-render');
        var config = {
            messages: 'Foo'
        };
        var instance;

        assert.expect(5);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = validatorRendererFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-validator'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-validator .validation-error').length, 1, 'The component displays one message');

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

    QUnit.test('render messages', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-render');
        var config = {
            messages: [
                'Foo',
                'Bar'
            ]
        };
        var instance;

        assert.expect(5);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = validatorRendererFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-validator'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-validator .validation-error').length, 2, 'The component displays some messages');

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
            messages: [
                'Foo',
                'Bar'
            ]
        };
        var instance;

        assert.expect(11);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = validatorRendererFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-validator'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-validator .validation-error').length, 2, 'The component displays some messages');

                assert.equal($container.find('.form-validator:visible').length, 1, 'The component is visible');
                assert.equal($container.find('.form-validator .validation-error:visible').length, 2, 'The messages are visible');

                instance.hide();
                assert.equal($container.find('.form-validator:visible').length, 0, 'The component is hidden');
                assert.equal($container.find('.form-validator .validation-error:visible').length, 0, 'The messages are hidden');

                instance.show();
                assert.equal($container.find('.form-validator:visible').length, 1, 'The component is visible again');
                assert.equal($container.find('.form-validator .validation-error:visible').length, 2, 'The messages are visible again');

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

    QUnit.test('destroy', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-destroy');
        var instance;

        assert.expect(4);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = validatorRendererFactory($container, {widget: 'text', uri: 'foo'})
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

    QUnit.test('messages', function (assert) {
        var ready = assert.async();
        var $container = $('#fixture-messages');
        var config = {
            messages: [
                'Foo',
                'Bar'
            ]
        };
        var instance;

        assert.expect(8);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = validatorRendererFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
                assert.equal($container.children().is('.form-validator'), true, 'The container contains the expected element');
                assert.equal($container.find('.form-validator .validation-error').length, 2, 'The component displays some messages');

                instance.clear();
                assert.equal($container.find('.form-validator .validation-error').length, 0, 'The component is now empty');

                instance.display('foo');
                assert.equal($container.find('.form-validator .validation-error').length, 1, 'The component displays a message');

                instance.display(['first', 'second']);
                assert.equal($container.find('.form-validator .validation-error').length, 2, 'The component displays 2 messages');

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


    QUnit.module('Visual');

    QUnit.test('Visual test', function (assert) {
        var ready = assert.async();
        var $container = $('#visual-test');
        var config = {
            messages: [
                'This field is required',
                'The value should be comprised between 0 and 100'
            ]
        };
        var instance;

        assert.expect(3);

        assert.equal($container.children().length, 0, 'The container is empty');

        instance = validatorRendererFactory($container, config)
            .on('init', function () {
                assert.equal(this, instance, 'The instance has been initialized');
            })
            .on('ready', function () {
                assert.equal($container.children().length, 1, 'The container contains an element');
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
});
