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
 * Test the ui/resource/selectable module
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'ui/resource/selectable',
], function($, selectable) {
    'use strict';

    var noop = function(){};
    var componentMock = {
        init: noop,
        render: noop,
        on : noop,
        is: function(){
            return true;
        },
        trigger: noop,
        getElement : function(){
            return $('#qunit-fixture .component');
        }
    };
    var nodesMock = [
        { uri : "item-1", num: 1 },
        { uri : "item-2", num: 2 },
        { uri : "item-3", num: 3 },
    ];


    QUnit.module('API');

    QUnit.test('module', function(assert) {
        QUnit.expect(4);

        assert.equal(typeof selectable, 'function', "The selectable module exposes a function");

        assert.throws(function(){
            selectable();
        }, TypeError, 'The selectable expects a component');
        assert.throws(function(){
            selectable({
                foo : noop
            });
        }, TypeError, 'The selectable expects a component');

        assert.equal(typeof selectable(componentMock), 'object', "The selectable returns an object");
    });

    QUnit.cases([
        { title : 'getNodes' },
        { title : 'getNode' },
        { title : 'setNodes' },
        { title : 'addNode' },
        { title : 'removeNode' },
        { title : 'hasNode' },
        { title : 'getSelection' },
        { title : 'clearSelection' },
        { title : 'select' },
        { title : 'unselect' },
    ]).test('selectable methodh ', function(data, assert) {
        var instance = selectable(componentMock);
        assert.equal(typeof instance[data.title], 'function', 'The selectable instance exposes a "' + data.title + '" method');
    });

    QUnit.test('augments', function(assert) {
        var instance = selectable(componentMock);

        QUnit.expect(5);

        assert.equal(typeof instance.on, 'function', 'The selectable instance has the component method');
        assert.equal(typeof instance.trigger, 'function', 'The selectable instance has the component method');
        assert.equal(typeof instance.init, 'function', 'The selectable instance has the component method');
        assert.equal(typeof instance.render, 'function', 'The selectable instance has the component method');
        assert.equal(typeof instance.getElement, 'function', 'The selectable instance has the component method');
    });


    QUnit.module('Nodes');

    QUnit.test('accessors', function(assert) {
        var negativeNodesMock = {
            'item-1' : { uri : "item-1", num: -1},
            'item-3' : { uri : "item-3", num: -3 }
        };

        var instance = selectable(componentMock);
        QUnit.expect(15);

        assert.ok(! instance.hasNode('item-1'));
        assert.ok(! instance.hasNode('item-3'));

        assert.equal(instance.getNode('item-1'), false);
        assert.equal(instance.getNode('item-3'), false);

        instance.setNodes(nodesMock);

        assert.ok(instance.hasNode('item-1'));
        assert.ok(instance.hasNode('item-3'));

        assert.equal(instance.getNode('item-1'), nodesMock[0]);
        assert.equal(instance.getNode('item-3'), nodesMock[2]);

        assert.ok(! instance.hasNode('item-12'));
        instance.addNode('item-12', { uri: 'item-12', num: 12});
        assert.ok(instance.hasNode('item-12'));

        instance.removeNode('item-12');
        assert.ok(! instance.hasNode('item-12'));

        instance.removeNode('item-1');
        assert.ok(! instance.hasNode('item-1'));

        assert.deepEqual(instance.getNodes(), {
            'item-2' : nodesMock[1],
            'item-3' : nodesMock[2]
        });

        instance.setNodes(negativeNodesMock);

        assert.equal(instance.getNode('item-1'), negativeNodesMock['item-1']);
        assert.deepEqual(instance.getNodes(), negativeNodesMock);
    });


    QUnit.module('Selection');

    QUnit.test('selects dom', function(assert) {
        var instance;
        QUnit.expect(18);

        instance = selectable(componentMock);
        instance.setNodes(nodesMock);

        assert.equal($('[data-uri=item-1]').length, 1, 'The item-1 element exists');
        assert.ok(! $('[data-uri=item-1]').hasClass('selected'), 'The item-1 has not the selected class');
        assert.equal($('[data-uri=item-2]').length, 1, 'The item-2 element exists');
        assert.ok(! $('[data-uri=item-2]').hasClass('selected'), 'The item-2 has not the selected class');
        assert.equal($('[data-uri=item-3]').length, 1, 'The item-3 element exists');
        assert.ok(! $('[data-uri=item-3]').hasClass('selected'), 'The item-3 has not the selected class');

        instance.select(['item-1', 'item-3']);

        assert.ok( $('[data-uri=item-1]').hasClass('selected'), 'The item-1 has now the selected class');
        assert.ok(! $('[data-uri=item-2]').hasClass('selected'), 'The item-2 has not the selected class');
        assert.ok( $('[data-uri=item-3]').hasClass('selected'), 'The item-3 has now the selected class');

        instance.unselect(['item-3']);

        assert.ok( $('[data-uri=item-1]').hasClass('selected'), 'The item-1 has still the selected class');
        assert.ok(! $('[data-uri=item-2]').hasClass('selected'), 'The item-2 has not the selected class');
        assert.ok(! $('[data-uri=item-3]').hasClass('selected'), 'The item-3 has not the selected class anymore');

        instance.selectAll();

        assert.ok( $('[data-uri=item-1]').hasClass('selected'), 'The item-1 has the selected class');
        assert.ok( $('[data-uri=item-2]').hasClass('selected'), 'The item-2 has the selected class');
        assert.ok( $('[data-uri=item-3]').hasClass('selected'), 'The item-3 has the selected class');

        instance.clearSelection();

        assert.ok(! $('[data-uri=item-1]').hasClass('selected'), 'The item-1 has not the selected class anymore');
        assert.ok(! $('[data-uri=item-2]').hasClass('selected'), 'The item-2 has not the selected class anymore');
        assert.ok(! $('[data-uri=item-3]').hasClass('selected'), 'The item-3 has not the selected class anymore');
    });

    QUnit.test('selection', function(assert) {
        var instance;
        var selection;
        QUnit.expect(12);

        instance = selectable(componentMock);
        instance.setNodes(nodesMock);

        selection = instance.getSelection();

        assert.equal(typeof selection['item-1'], 'undefined', 'The item-1 node is not in the selection');
        assert.equal(typeof selection['item-2'], 'undefined', 'The item-2 node is not in the selection');
        assert.equal(typeof selection['item-3'], 'undefined', 'The item-3 node is not in the selection');

        instance.select(['item-1', 'item-3']);
        selection = instance.getSelection();

        assert.deepEqual(selection['item-1'],  nodesMock[0], 'The item-1 node is in the selection');
        assert.equal(typeof selection['item-2'], 'undefined', 'The item-2 node is not in the selection');
        assert.deepEqual(selection['item-3'],  nodesMock[2], 'The item-3 node is in the selection');

        instance.selectAll();

        assert.deepEqual(selection['item-1'],  nodesMock[0], 'The item-1 node is in the selection');
        assert.deepEqual(selection['item-2'],  nodesMock[1], 'The item-2 node is in the selection');
        assert.deepEqual(selection['item-3'],  nodesMock[2], 'The item-3 node is in the selection');

        instance.clearSelection();
        selection = instance.getSelection();

        assert.equal(typeof selection['item-1'], 'undefined', 'The item-1 node is not in the selection anymore');
        assert.equal(typeof selection['item-2'], 'undefined', 'The item-2 node is not in the selection');
        assert.equal(typeof selection['item-3'], 'undefined', 'The item-3 node is not in the selection anymore');
    });

    QUnit.test('select only', function(assert) {
        var instance;
        var selection;
        QUnit.expect(9);

        instance = selectable(componentMock);
        instance.setNodes(nodesMock);

        selection = instance.getSelection();

        assert.equal(typeof selection['item-1'], 'undefined', 'The item-1 node is not in the selection');
        assert.equal(typeof selection['item-2'], 'undefined', 'The item-2 node is not in the selection');
        assert.equal(typeof selection['item-3'], 'undefined', 'The item-3 node is not in the selection');

        instance.select('item-1');
        selection = instance.getSelection();

        assert.deepEqual(selection['item-1'],  nodesMock[0], 'The item-1 node is in the selection');
        assert.equal(typeof selection['item-2'], 'undefined', 'The item-2 node is not in the selection');
        assert.equal(typeof selection['item-3'], 'undefined', 'The item-3 node is not in the selection');

        instance.select(['item-2'], true);
        selection = instance.getSelection();

        assert.equal(typeof selection['item-1'], 'undefined', 'The item-1 node is not in the selection anymore');
        assert.deepEqual(selection['item-2'],  nodesMock[1], 'The item-2 node is in the selection');
        assert.equal(typeof selection['item-3'], 'undefined', 'The item-3 node is not in the selection');
    });

    QUnit.test('remove selected node', function(assert) {
        var instance;
        var selection;
        QUnit.expect(10);

        instance = selectable(componentMock);
        instance.setNodes(nodesMock);

        selection = instance.getSelection();

        assert.equal(typeof selection['item-1'], 'undefined', 'The item-1 node is not in the selection');
        assert.equal(typeof selection['item-2'], 'undefined', 'The item-2 node is not in the selection');
        assert.equal(typeof selection['item-3'], 'undefined', 'The item-3 node is not in the selection');

        instance.select(['item-1', 'item-3']);
        selection = instance.getSelection();

        assert.deepEqual(selection['item-1'],  nodesMock[0], 'The item-1 node is in the selection');
        assert.equal(typeof selection['item-2'], 'undefined', 'The item-2 node is not in the selection');
        assert.deepEqual(selection['item-3'],  nodesMock[2], 'The item-3 node is in the selection');

        instance.removeNode('item-1');
        assert.ok( ! instance.hasNode('item-1'), 'The node is removed');

        selection = instance.getSelection();

        assert.equal(typeof selection['item-1'], 'undefined', 'The item-1 node is not in the selection anymore');
        assert.equal(typeof selection['item-2'], 'undefined', 'The item-2 node is not in the selection');
        assert.deepEqual(selection['item-3'],  nodesMock[2], 'The item-3 node is in the selection');
    });
});
