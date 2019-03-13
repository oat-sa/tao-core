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
 * @author Anton Tsymuk <anton@taotesting.com>
 */
define([
    'jquery',
    'ui/pageSizeSelector'
], function ($, pageSizeSelector) {
    'use strict';

    var defaultSize = 500;
    var options = [
        { label: '100', value: 100 },
        { label: '500', value: 500 },
        { label: '1000', value: 1000 },
    ];

    QUnit.module('pageSizeSelector');

    QUnit.test('module', 3, function (assert) {
        assert.equal(typeof pageSizeSelector, 'function', "The dropdown module exposes a function");
        assert.equal(typeof pageSizeSelector(), 'object', "The dropdown factory produces an object");
        assert.notStrictEqual(pageSizeSelector(), pageSizeSelector(), "The dropdown factory provides a different object on each call");
    });

    QUnit
        .cases([
            { title: 'init' },
            { title: 'destroy' },
            { title: 'render' },
            { title: 'show' },
            { title: 'hide' },
            { title: 'trigger' },
            { title: 'on' },
            { title: 'off' },
            { title: 'getElement' },

            { title: 'setSelectedOption' },
        ])
        .test('instance API ', function (data, assert) {
            var instance = pageSizeSelector();
            assert.equal(typeof instance[data.title], 'function', 'The dropdown instance exposes a "' + data.title + '" function');
        });

    QUnit.test('render with default config', function (assert) {
        var instance = pageSizeSelector({
            renderTo: '#fixture-render',
        });

        assert.equal(typeof instance, 'object', "The dropdown instance is an object");
        assert.ok(instance.getElement() instanceof $, "The dropdown instance gets a DOM element");
        assert.equal(instance.getElement().length, 1, "The dropdown instance gets a single element");

        assert.equal(instance.getElement().find('option').length, 5, "The selector rendered with default options");
        assert.equal(instance.getElement().find('select').val(), '25', "The default page size option is selected");

        instance.destroy();
    });

    QUnit.test('render with custom config', function (assert) {
        var instance = pageSizeSelector({
            renderTo: '#fixture-render-with-config',
            options: options,
            defaultSize: defaultSize,
        });

        assert.equal(typeof instance, 'object', "The dropdown instance is an object");
        assert.ok(instance.getElement() instanceof $, "The dropdown instance gets a DOM element");
        assert.equal(instance.getElement().length, 1, "The dropdown instance gets a single element");

        assert.equal(instance.getElement().find('option').length, 3, "The selector rendered with default options");
        assert.equal(instance.getElement().find('select').val(), '500', "The default page size option is selected");

        instance.destroy();
    });

    QUnit.test('use first option as default if there is no option with defaultSize', function (assert) {
        var instance = pageSizeSelector({
            renderTo: '#fixture-default-option',
            defaultSize: 1000,
        });

        assert.equal(instance.getElement().find('select').val(), '25', "The default page size option is selected");

        instance.destroy();
    });

    QUnit.asyncTest('trigger change event', function (assert) {
        QUnit.expect(2);
        QUnit.stop(1);

        var instance = pageSizeSelector({
            renderTo: '#fixture-change-event'
        });
        var selectedValue = 200;
        var select2Instance = instance.getElement().find('select');

        instance.on('change', function () {
            assert.ok(true, "The component fires a specific event when a page option is selected");

            QUnit.start();
        });

        select2Instance.trigger('change');

        instance.destroy();
    });

    QUnit.asyncTest('trigger change event after render to notify about selected value', function (assert) {
        QUnit.expect(1);

        var instance = pageSizeSelector({
            renderTo: '#fixture-change-event-after-render'
        });

        instance.on('change', function () {
            assert.ok(true, "The component fires a specific event when a page option is selected");

            QUnit.start();
        });

        instance.destroy();
    });

    QUnit.test('playground', function(assert) {
        pageSizeSelector({
            renderTo: '#visual-test-default-config'
        });

        pageSizeSelector({
            renderTo: '#visual-test-custom-config',
            options: options,
            defaultSize: defaultSize,
        });

        assert.ok(true, 'started');
    });
});
