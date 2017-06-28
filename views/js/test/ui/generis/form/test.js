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
    'ui/generis/form/form',
    'json!test/ui/generis/data'
], function(
    $,
    _,
    generisFormFactory,
    generisData
) {
    'use strict';


    /**
     * Api
     */
    QUnit.module('Api');

    QUnit.test('module', 3, function (assert) {
        assert.equal(typeof generisFormFactory, 'function', 'The module exposes a function');
        assert.equal(typeof generisFormFactory(), 'object', 'The factory produces an object');
        assert.notStrictEqual(generisFormFactory(), generisFormFactory(), 'The factory provides a different object on each call');
    });

    QUnit
    .cases([
        { name: 'data',           title: 'data',           type: 'object' },
        { name: 'errors',         title: 'errors',         type: 'object' },
        { name: 'widgets',        title: 'widgets',        type: 'object' },
        { name: 'addWidget',      title: 'addWidget',      type: 'function' },
        { name: 'removeWidget',   title: 'removeWidget',   type: 'function' },
        { name: 'validate',       title: 'validate',       type: 'function' },
        { name: 'serializeArray', title: 'serializeArray', type: 'function' }
    ])
    .test('instance', function (data, assert) {
        var instance = generisFormFactory();
        assert.equal(typeof instance[data.name], data.type, 'The instance exposes a(n) "' + data.title + '" ' + data.type);
    });


    /**
     * Methods
     */
    QUnit.module('Methods');

    QUnit.test('addWidget', function (assert) {
        assert.ok(true);
    });

    QUnit.test('removeWidget', function (assert) {
        assert.ok(true);
    });

    QUnit.test('validate', function (assert) {
        assert.ok(true);
    });

    QUnit.test('serializeArray', function (assert) {
        assert.ok(true);
    });


    /**
     * Events
     */
    QUnit.module('Events');

    QUnit.test('submit', function (assert) {
        assert.ok(true);
    });


    /**
     * Visual Test
     */
    QUnit.module('Visual Test');

    QUnit.test('Display and play', function (assert) {
        generisFormFactory({ data: generisData })
        .on('render', function () {
            assert.ok(true);
        })
        .render('#display-and-play');
    });
});

