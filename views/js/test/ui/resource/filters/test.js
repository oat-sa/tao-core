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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

/**
 * Test the module ui/resource/list
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'ui/resource/filters',
    'json!test/ui/resource/filters/properties.json'
], function($, filtersFactory, propertiesData) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof filtersFactory, 'function', "The filtersFactory module exposes a function");
        assert.equal(typeof filtersFactory(), 'object', "The filtersFactory produces an object");
        assert.notStrictEqual(filtersFactory(), filtersFactory(), "The filtersFactory provides a different object on each call");
    });

    QUnit.cases([
        { title : 'init' },
        { title : 'destroy' },
        { title : 'render' },
        { title : 'show' },
        { title : 'hide' },
        { title : 'enable' },
        { title : 'disable' },
        { title : 'is' },
        { title : 'setState' },
        { title : 'getContainer' },
        { title : 'getElement' },
        { title : 'getTemplate' },
        { title : 'setTemplate' },
    ]).test('Component API ', function(data, assert) {
        var instance = filtersFactory();
        assert.equal(typeof instance[data.title], 'function', 'The filters exposes the component method "' + data.title);
    });

    QUnit.cases([
        { title : 'on' },
        { title : 'off' },
        { title : 'trigger' },
        { title : 'before' },
        { title : 'after' },
    ]).test('Eventifier API ', function(data, assert) {
        var instance = filtersFactory();
        assert.equal(typeof instance[data.title], 'function', 'The filters exposes the eventifier method "' + data.title);
    });

    QUnit.cases([
    ]).test('Instance API ', function(data, assert) {
        var instance = filtersFactory();
        assert.equal(typeof instance[data.title], 'function', 'The resourceList exposes the method "' + data.title);
    });


    QUnit.module('Behavior');

    QUnit.asyncTest('Lifecycle', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(2);

        filtersFactory($container, {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            data : propertiesData
        })
        .on('init', function(){
            assert.ok( !this.is('rendered'), 'The component is not yet rendered');
        })
        .on('render', function(){

            assert.ok(this.is('rendered'), 'The component is now rendered');

            this.destroy();
        })
        .on('destroy', function(){

            QUnit.start();
        });
    });
/*
    QUnit.asyncTest('Rendering', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(8);

        assert.equal($('.resource-list', $container).length, 0, 'No resource list in the container');

        filtersFactory($container, {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            data : propertiesData
        })
        .on('render', function(){

            var $element = this.getElement();

            assert.equal($('.resource-list', $container).length, 1, 'The component has been inserted');
            assert.equal($('.resource-list', $container)[0], $element[0], 'The component element is correct');

            assert.equal($('li', $element).length, 25, 'The list has 25 nodes');
            assert.equal($('li:first-child', $element).data('uri'), 'http://bertao/tao.rdf#i1491898771637894', 'The 1st list item has the correct URI');
            assert.equal($('li:first-child', $element).text().trim(), 'Maths test 1', 'The 1st list item has the correct text content');
            assert.equal($('li:last-child', $element).data('uri'), 'http://bertao/tao.rdf#i14918990131344188', 'The last list item has the correct URI');
            assert.equal($('li:last-child', $element).text().trim(), 'Demo item 1', 'The last list item has the correct text content');

            QUnit.start();
        });
    }); */



    QUnit.module('Visual');

    QUnit.asyncTest('playground', function(assert) {

        var container = document.getElementById('visual');

        QUnit.expect(1);

        filtersFactory(container, {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            data : propertiesData
        })
        .on('render', function(){
            assert.ok(true);
            QUnit.start();
        });
    });
});
