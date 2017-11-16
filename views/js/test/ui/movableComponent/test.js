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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */

define([
    'jquery',
    'ui/movableComponent',
    'ui/component/placeable'
], function($, movableComponentFactory, makePlaceable) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert) {
        QUnit.expect(1);
        assert.equal(typeof movableComponentFactory, 'function', "The module exposes a function");
    });

    QUnit.test('factory', function(assert) {
        QUnit.expect(2);
        assert.equal(typeof movableComponentFactory(), 'object', "The factory creates an object");
        assert.notDeepEqual(movableComponentFactory(), movableComponentFactory(), "The factory creates a new object");
    });

    QUnit.cases([
        {name : 'init',         title : 'init'},
        {name : 'destroy',      title : 'destroy'},
        {name : 'render',       title : 'render'},
        {name : 'show',         title : 'show'},
        {name : 'hide',         title : 'hide'},
        {name : 'enable',       title : 'enable'},
        {name : 'disable',      title : 'disable'},
        {name : 'is',           title : 'is'},
        {name : 'setState',     title : 'setState'},
        {name : 'getContainer', title : 'getContainer'},
        {name : 'getElement',   title : 'getElement'},
        {name : 'getTemplate',  title : 'getTemplate'},
        {name : 'setTemplate',  title : 'setTemplate'},
        {name : 'resizeTo',     title : 'resizeTo'}
    ])
    .test('component API contains ', function(data, assert) {
        var component = movableComponentFactory();
        QUnit.expect(1);
        assert.equal(typeof component[data.name], 'function', 'The component has the method ' + data.name);
    });

    QUnit.test('component is placeable', function(assert) {
        var component = makePlaceable(movableComponentFactory());
        QUnit.expect(1);
        assert.ok(makePlaceable.isPlaceable(component), 'created component is placeable');
    });


    QUnit.module('Behavior');

    QUnit.asyncTest('DOM', function(assert) {
        var $container = $('#qunit-fixture');
        var component = movableComponentFactory();

        QUnit.expect(6);

        assert.equal($container.length, 1, 'The container exists');
        assert.equal($container.children().length, 0, 'The container is empty');

        assert.equal(typeof component, 'object', 'The component has been created');

        component
            .on('render', function() {
                var $element = $('.component', $container);
                assert.equal($element.length, 1, 'The component has been attached to the container');
                assert.ok($element.hasClass('rendered'), 'The component has the rendered class');
                assert.deepEqual($element[0], this.getElement()[0], 'The found element match the one bound to the component');

                QUnit.start();
            })
            .init({})
            .render($container);
    });

    QUnit.module('Visual');

    QUnit.asyncTest('visual test', function(assert) {
        var $container = $('#outside');

        QUnit.expect(1);

        movableComponentFactory()
            .on('render', function(){
                assert.ok(true);
                QUnit.start();
            })
            .init({
                x : 0,
                y : 0,
                width: 300,
                height: 200
            })
            .render($container);
    });
});
