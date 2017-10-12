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
    'ui/resource/list',
    'json!test/ui/resource/list/nodes.json'
], function($, resourceListFactory, nodesData) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof resourceListFactory, 'function', "The resourceListFactory module exposes a function");
        assert.equal(typeof resourceListFactory(), 'object', "The resourceListFactory produces an object");
        assert.notStrictEqual(resourceListFactory(), resourceListFactory(), "The resourceListFactory provides a different object on each call");
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
        var instance = resourceListFactory();
        assert.equal(typeof instance[data.title], 'function', 'The resourceList exposes the component method "' + data.title);
    });

    QUnit.cases([
        { title : 'on' },
        { title : 'off' },
        { title : 'trigger' },
        { title : 'before' },
        { title : 'after' },
    ]).test('Eventifier API ', function(data, assert) {
        var instance = resourceListFactory();
        assert.equal(typeof instance[data.title], 'function', 'The resourceList exposes the eventifier method "' + data.title);
    });

    QUnit.cases([
        { title : 'query' },
        { title : 'update' },
    ]).test('Instance API ', function(data, assert) {
        var instance = resourceListFactory();
        assert.equal(typeof instance[data.title], 'function', 'The resourceList exposes the method "' + data.title);
    });


    QUnit.module('Behavior');

    QUnit.asyncTest('Lifecycle', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(2);

        resourceListFactory($container, {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            nodes : nodesData
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

    QUnit.asyncTest('Rendering', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(8);

        assert.equal($('.resource-list', $container).length, 0, 'No resource list in the container');

        resourceListFactory($container, {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            nodes : nodesData
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
    });

    QUnit.asyncTest('query/update', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(3);

        resourceListFactory($container, {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item'
        })
        .on('query', function(params){

            assert.equal(params.classUri, 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item', 'The query has the correct class URI');
            assert.equal($('li', this.getElement()).length, 0, 'The list contains no nodes');

            this.update(nodesData, params);
        })
        .on('update', function(){

            assert.equal($('li', this.getElement()).length, 25, 'The list has been updated');

            QUnit.start();
        });
    });

    QUnit.asyncTest('select nodes', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(10);

        resourceListFactory($container, {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            nodes : nodesData
        })
        .on('render', function(){

            var selection = this.getSelection();
            var $node1 = $('[data-uri="http://bertao/tao.rdf#i14918988138981105"]', this.getElement());
            var $node2 = $('[data-uri="http://bertao/tao.rdf#i14918988538969120"]', this.getElement());

            assert.equal($node1.length, 1, 'The node1 exists');
            assert.ok(! $node1.hasClass('selected'), 'The node1 is not selected');
            assert.equal(typeof selection['http://bertao/tao.rdf#i1491898801542197'], 'undefined', 'The selection does not contain the node1');

            assert.equal($node2.length, 1, 'The node1 exists');
            assert.ok(! $node2.hasClass('selected'), 'The node1 is not selected');
            assert.equal(typeof selection['http://bertao/tao.rdf#i14918988061562101'], 'undefined', 'The selection does not contain the noder2');

            $node1.click();
            $node2.click();

            selection = this.getSelection();

            assert.ok($node1.hasClass('selected'), 'The node1 is now selected');
            assert.equal(typeof selection['http://bertao/tao.rdf#i14918988138981105'], 'object', 'The selection contains the node1');
            assert.ok($node2.hasClass('selected'), 'The node2 is now selected');
            assert.equal(typeof selection['http://bertao/tao.rdf#i14918988538969120'], 'object', 'The selection contains the node2');

            QUnit.start();
        });
    });

    QUnit.asyncTest('unique selection', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(14);

        resourceListFactory($container, {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            nodes : nodesData,
            multiple: false
        })
        .on('render', function(){

            var selection = this.getSelection();
            var $node1 = $('[data-uri="http://bertao/tao.rdf#i14918988138981105"]', this.getElement());
            var $node2 = $('[data-uri="http://bertao/tao.rdf#i14918988538969120"]', this.getElement());

            assert.equal($node1.length, 1, 'The node1 exists');
            assert.ok(! $node1.hasClass('selected'), 'The node1 is not selected');
            assert.equal(typeof selection['http://bertao/tao.rdf#i14918988138981105'], 'undefined', 'The selection does not contain the node1');

            assert.equal($node2.length, 1, 'The node1 exists');
            assert.ok(! $node2.hasClass('selected'), 'The node1 is not selected');
            assert.equal(typeof selection['http://bertao/tao.rdf#i14918988538969120'], 'undefined', 'The selection does not contain the noder2');

            $node1.click();

            selection = this.getSelection();

            assert.ok($node1.hasClass('selected'), 'The node1 is now selected');
            assert.equal(typeof selection['http://bertao/tao.rdf#i14918988138981105'], 'object', 'The selection contains the node1');
            assert.ok(! $node2.hasClass('selected'), 'The node1 is not selected');
            assert.equal(typeof selection['http://bertao/tao.rdf#i14918988538969120'], 'undefined', 'The selection does not contain the noder2');

            $node2.click();

            selection = this.getSelection();

            assert.ok(! $node1.hasClass('selected'), 'The node1 is not selected anymore');
            assert.equal(typeof selection['http://bertao/tao.rdf#i14918988138981105'], 'undefined', 'The selection does not contain the node1 anymore');
            assert.ok($node2.hasClass('selected'), 'The node2 is now selected');
            assert.equal(typeof selection['http://bertao/tao.rdf#i14918988538969120'], 'object', 'The selection contains the node2');

            QUnit.start();
        });
    });


    QUnit.module('Visual');

    QUnit.asyncTest('playground', function(assert) {

        var container = document.getElementById('visual');
        var config = {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            nodes : nodesData,
            multiple: true
        };

        QUnit.expect(1);

        resourceListFactory(container, config)
            .on('render', function(){
                assert.ok(true);
                QUnit.start();
            });
    });
});
