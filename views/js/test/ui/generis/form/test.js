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
    'ui/generis/form/form'
], function(
    $,
    _,
    generisFormFactory
) {
    'use strict';


    /**
     * Api
     */
    QUnit.module('Api');

    QUnit.test('module', 3, function (assert) {
        var obj1 = generisFormFactory({
            resource: {
                url: 'js/test/ui/generis/form/data/desc/success.json'
            }
        });
        var obj2 = generisFormFactory({
            resource: {
                url: 'js/test/ui/generis/form/data/desc/success.json'
            }
        });
        assert.equal(typeof generisFormFactory, 'function', 'The module exposes a function');
        assert.equal(typeof obj1, 'object', 'The factory produces an object');
        assert.notStrictEqual(obj1, obj2, 'The factory provides a different object on each call');
    });

    QUnit
    .cases([
        { name : 'get', title : 'get' },
        { name : 'set', title : 'set' },
        { name : 'validate', title : 'validate' }
    ])
    .test('instance', function (data, assert) {
        var instance = generisFormFactory({
            resource: {
                url: 'js/test/ui/generis/form/data/desc/success.json'
            }
        });
        assert.equal(typeof instance[data.name], 'function', 'The instance exposes a "' + data.title + '" function');
    });


    /**
     * Methods
     */
    QUnit.module('Methods');

    QUnit.test('initialization', function (assert) {
        generisFormFactory({
            resource: {
                url: 'js/test/ui/generis/form/data/desc/success.json'
            }
        })
        .on('init', function () {
            assert.ok(true, 'form is successfully initialized');
        })
        .init();
    });

    QUnit.asyncTest('get', function (assert) {
        generisFormFactory({
            resource: {
                url: 'js/test/ui/generis/form/data/desc/success.json'
            }
        })
        .on('load', function () {
            assert.equal(
                this.get('http://www.tao.lu/Ontologies/generis.rdf#userFirstName'),
                'Bertrand',
                'gets correct field value'
            );
            QUnit.start();
        })
        .init();
    });

    QUnit.asyncTest('set', function (assert) {
        generisFormFactory({
            resource: {
                url: 'js/test/ui/generis/form/data/desc/success.json'
            }
        })
        .on('load', function () {
            assert.equal(
                this.set('http://www.tao.lu/Ontologies/generis.rdf#userFirstName', 'Foo'),
                'Foo',
                'sets correct field value'
            );
            assert.equal(
                this.get('http://www.tao.lu/Ontologies/generis.rdf#userFirstName'),
                'Foo',
                'gets correct field value'
            );
            QUnit.start();
        })
        .init();
    });

    QUnit.test('validate', function (assert) {
        assert.ok(true);
    });

    QUnit.test('submit', function (assert) {
        assert.ok(true);
    });


    /**
     * Visual Test
     */
    QUnit.module('Visual Test');

    QUnit.test('Display and play', function (assert) {
        generisFormFactory({
            class: {
                uri: 'http://www.tao.lu/Ontologies/generis.rdf#User',
                label: 'User'
            },
            resource: {
                url: 'js/test/ui/generis/form/data/desc/success.json'
            },
            uri: 'http://taoplatform/data.rdf#i1489048120705064'
        })
        .on('render', function () {
            assert.ok(true);
        })
        .init({
            form: {
                action: 'js/test/ui/generis/form/data/edit/success.json'
            }
        })
        .render('#display-and-play');
    });
});

