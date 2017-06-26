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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */

define([
    'jquery',
    'lodash',
    'ui/generis/widget/textBox/textBox',
], function(
    $,
    _,
    generisWidgetTextBoxFactory
) {
    'use strict';


    /**
     * Api
     */
    QUnit.module('Api');

    QUnit.test('module', 3, function (assert) {
        assert.equal(typeof generisWidgetTextBoxFactory, 'function', 'The module exposes a function');
        assert.equal(typeof generisWidgetTextBoxFactory(), 'object', 'The factory produces an object');
        assert.notStrictEqual(generisWidgetTextBoxFactory(), generisWidgetTextBoxFactory(), 'The factory provides a different object on each call');
    });

    QUnit
    .cases([
        { name: 'get',          title: 'get',          type: 'function' },
        { name: 'set',          title: 'set',          type: 'function' },
        { name: 'validator',    title: 'validator',    type: 'object'   },
        { name: 'setValidator', title: 'setValidator', type: 'function' },
        { name: 'validate',     title: 'validate',     type: 'function' },
        { name: 'serialize',    title: 'serialize',    type: 'function' }
    ])
    .test('instance', function (data, assert) {
        var instance = generisWidgetTextBoxFactory();
        assert.equal(typeof instance[data.name], data.type, 'The instance exposes a(n) "' + data.title + '" ' + data.type);
    });


    /**
     * Methods
     */
    QUnit.module('Methods');

    QUnit.test('initialization', function (assert) {
        generisWidgetTextBoxFactory()
        .on('init', function () {
            assert.ok(true);
        })
        .init();
    });

    QUnit.test('get', function (assert) {
        generisWidgetTextBoxFactory()
        .on('init', function () {
            assert.equal(this.get(), 'foobar', 'returns correct value');
        })
        .init({
            uri: 'foo#bar',
            value: 'foobar'
        });
    });

    QUnit.test('set', function (assert) {
        generisWidgetTextBoxFactory()
        .on('init', function () {
            assert.equal(this.set('baz'), 'baz', 'returns updated value');
            assert.equal(this.get(), 'baz', 'updates value');
        })
        .init({
            uri: 'foo#bar',
            value: 'foobar'
        });
    });

    QUnit.test('setValidator', function (assert) {
        assert.ok(true);
    });

    QUnit.test('validate', function (assert) {
        assert.ok(true);
    });

    QUnit.test('serialize', function (assert) {
        var obj = {
            uri: 'foo#bar',
            value: 'foobar'
        };

        generisWidgetTextBoxFactory()
        .on('init', function () {
            var serialized = this.serialize();
            assert.equal(serialized.name, obj.uri, 'name property is correct');
            assert.equal(serialized.value, obj.value, 'value property is correct');
        })
        .init(obj);
    });


    /**
     * Events
     */
    QUnit.module('Events');

    QUnit.test('change & blur', function (assert) {
        assert.ok(true, 'on(\'change blur\')');
    });


    /**
     * Visual Test
     */
    QUnit.module('Visual Test');

    QUnit.test('Display and play', function (assert) {
        var tb1 = generisWidgetTextBoxFactory()
        .setValidator({
            validations: [{
                predicate: /world/i,
                message: 'It is \'WORLD\''
            }]
        })
        .on('render', function () {
            assert.ok(true);
        })
        .init({
            label: 'Hello',
            required: true,
            value: 'World',
            uri: 'taoplatform#helloWorld'
        })
        .render('#display-and-play > form > fieldset');

        var tb2 = generisWidgetTextBoxFactory()
        .setValidator({
            validations: [{
                predicate: /\S+/,
                message: 'Must contain something...'
            }]
        })
        .on('render', function () {
            assert.ok(true);
        })
        .init({
            label: 'Select a foo...',
            required: true,
            value: 'bar',
            uri: 'taoplatform#fooList'
        })
        .render('#display-and-play > form > fieldset');

        $('#validate').on('click', function (e) {
            e.preventDefault();

            console.log(tb1.serialize(), tb2.serialize());

            return false;
        });
    });
});

