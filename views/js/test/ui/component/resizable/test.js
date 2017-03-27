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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */
/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery',
    'ui/component',
    'ui/component/placeable',
    'ui/component/draggable',
    'ui/component/resizable'
], function ($, componentFactory, makePlaceable, makeDraggable, makeResizable) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.ok(typeof makeResizable === 'function', 'The module expose a function');
    });

    QUnit
        .cases([
            { title: 'resizeTo',          method: 'resizeTo' },
            { title: '_getCappedValue',   method: '_getCappedValue' }
        ])
        .test('component API', function(data, assert) {
            var component = makeResizable(componentFactory());

            QUnit.expect(1);
            assert.equal(typeof component[data.method], 'function', 'The component has the method ' + data.method);
        });

    QUnit.test('auto makes the component placeable', function(assert) {
        var component = makeDraggable(componentFactory());
        QUnit.expect(1);
        assert.ok(makePlaceable.isPlaceable(component), 'created component is placeable');
    });

    QUnit.module('Helpers functions');

    QUnit
        .cases([
            { title: 'no constrains',           expected: 100, input: 100 },
            { title: 'no constrains',           expected: 100, input: 100,  min: null,  max: null },
            { title: 'unused min constrain',    expected: 100, input: 100,  min: 50 },
            { title: 'applied min constrain',   expected: 100, input: 50,   min: 100 },
            { title: 'unused max constrain',    expected: 100, input: 100,              max: 200 },
            { title: 'applied max constrain',   expected: 100, input: 200,              max: 100 },
            { title: 'min/max, unused',         expected: 100, input: 100,  min: 50,    max: 200 },
            { title: 'min/max, applied min',    expected: 50,  input: 25,   min: 50,    max: 200 },
            { title: 'min/max, applied max',    expected: 200, input: 250,  min: 50,    max: 200 },
            { title: 'min/max, all negative',   expected: -50, input: -50,  min: -100,  max: -25 },
            { title: 'min/max, all negative',   expected: -50, input: -100, min: -50,   max: -25 },
            { title: 'min/max, all negative',   expected: -25, input: -10,  min: -50,   max: -25 },
            { title: 'min/max, 0 max',          expected: 0,   input: 100,  min: -100,  max: 0 },
            { title: 'min/max, 0 min',          expected: 0,   input: -100, min: 0,     max: 100 }

        ])
        .test('._getCappedValue()', function(data, assert) {
            var component = makeResizable(componentFactory());

            QUnit.expect(1);
            assert.equal(data.expected, component._getCappedValue(data.input, data.min, data.max));
        });

    QUnit.module('.resizeTo()');

    QUnit
        .cases([

            // resizing from right and/or bottom edges, no moving required
            {   title: 'normal resize',
                resizeW: 400,   resizeH: 350,   fromLeft: false,    fromTop: false,
                newW: 400,      newH: 350,      newX: 150,          newY: 150           },
            {   title: 'minWidth limit',
                resizeW: 250,   resizeH: 350,   fromLeft: false,    fromTop: false,
                newW: 300,      newH: 350,      newX: 150,          newY: 150           },
            {   title: 'maxWidth limit',
                resizeW: 750,   resizeH: 350,   fromLeft: false,    fromTop: false,
                newW: 620,      newH: 350,      newX: 150,          newY: 150           },
            {   title: 'minHeight limit',
                resizeW: 400,   resizeH: 150,   fromLeft: false,    fromTop: false,
                newW: 400,      newH: 200,      newX: 150,          newY: 150           },
            {   title: 'maxHeight limit',
                resizeW: 400,   resizeH: 450,   fromLeft: false,    fromTop: false,
                newW: 400,      newH: 400,      newX: 150,          newY: 150           },

            // resizing from left edge, moving required
            {   title: 'shrink from left',
                resizeW: 400,   resizeH: 300,   fromLeft: true,     fromTop: false,
                newW: 400,      newH: 300,      newX: 250,          newY: 150           },
            {   title: 'shrink from left, limited',
                resizeW: 200,   resizeH: 300,   fromLeft: true,     fromTop: false,
                newW: 300,      newH: 300,      newX: 350,          newY: 150           },
            {   title: 'expand from left',
                resizeW: 600,   resizeH: 300,   fromLeft: true,     fromTop: false,
                newW: 600,      newH: 300,      newX: 50,           newY: 150           },
            {   title: 'expand from left, limited',
                resizeW: 630,   resizeH: 300,   fromLeft: true,     fromTop: false,
                newW: 620,      newH: 300,      newX: 30,           newY: 150           },

            // resizing from left top, moving required
            {   title: 'shrink from top',
                resizeW: 500,   resizeH: 250,   fromLeft: false,     fromTop: true,
                newW: 500,      newH: 250,      newX: 150,          newY: 200           },
            {   title: 'shrink from top, limited',
                resizeW: 500,   resizeH: 150,   fromLeft: false,     fromTop: true,
                newW: 500,      newH: 200,      newX: 150,          newY: 250           },
            {   title: 'expand from top',
                resizeW: 500,   resizeH: 350,   fromLeft: false,     fromTop: true,
                newW: 500,      newH: 350,      newX: 150,          newY: 100           },
            {   title: 'expand from top, limited',
                resizeW: 500,   resizeH: 450,   fromLeft: false,     fromTop: true,
                newW: 500,      newH: 400,      newX: 150,          newY: 50            },

            // resizing from left and top edge, moving required
            {   title: 'shrink from left & top',
                resizeW: 400,   resizeH: 250,   fromLeft: true,     fromTop: true,
                newW: 400,      newH: 250,      newX: 250,          newY: 200           },
            {   title: 'shrink from left & top, limited',
                resizeW: 200,   resizeH: 150,   fromLeft: true,     fromTop: true,
                newW: 300,      newH: 200,      newX: 350,          newY: 250           },
            {   title: 'expand from left & top',
                resizeW: 600,   resizeH: 350,   fromLeft: true,     fromTop: true,
                newW: 600,      newH: 350,      newX: 50,           newY: 100           },
            {   title: 'expand from left & top, limited',
                resizeW: 630,   resizeH: 450,   fromLeft: true,     fromTop: true,
                newW: 620,      newH: 400,      newX: 30,           newY: 50            }
        ])
        .asyncTest('.resizeTo()', function (data, assert) {
            var $container = $('#qunit-fixture').width(800).height(600),
                component = makeResizable(componentFactory()),
                $element,
                position;

            QUnit.expect(20);

            component
                .on('beforeresize', function(width, height, fromLeft, fromTop) {
                    assert.ok(true, 'beforeresize event has been triggered');
                    assert.equal(width, data.resizeW, 'correct width has been passed as an event parameter');
                    assert.equal(height, data.resizeH, 'correct height has been passed as an event parameter');
                    assert.equal(fromLeft, data.fromLeft, 'correct value for resizeFromLeft has been passed as an event parameter');
                    assert.equal(fromTop, data.fromTop, 'correct value for resizeFromTop has been passed as an event parameter');
                })
                .on('resize', function(width, height, fromLeft, fromTop, x, y) {
                    assert.ok(true, 'resize event has been triggered');
                    assert.equal(width, data.newW, 'correct width has been passed as an event parameter');
                    assert.equal(height, data.newH, 'correct height has been passed as an event parameter');
                    assert.equal(fromLeft, data.fromLeft, 'correct value for resizeFromLeft has been passed as an event parameter');
                    assert.equal(fromTop, data.fromTop, 'correct value for resizeFromTop has been passed as an event parameter');
                    assert.equal(x, data.newX, 'correct x has been passed as an event parameter');
                    assert.equal(y, data.newY, 'correct y has been passed as an event parameter');

                    QUnit.start();
                })
                .init({
                    initialX: 150,
                    initialY: 150,
                    minWidth: 300,
                    maxWidth: 620,
                    minHeight: 200,
                    maxHeight: 400
                })
                .render($container)
                .setSize(500, 300);

            $element = component.getElement();
            position = component.getPosition();

            assert.equal(position.x, 150, 'component starts at the correct X');
            assert.equal(position.y, 150, 'component starts at the correct Y');
            assert.equal($element.width(), 500, 'component starts at the correct width');
            assert.equal($element.height(), 300, 'component starts at the correct height');

            component.resizeTo(
                data.resizeW,
                data.resizeH,
                data.fromLeft,
                data.fromTop
            );

            $element = component.getElement();
            position = component.getPosition();

            assert.equal(position.x, data.newX, 'component has the correct X');
            assert.equal(position.y, data.newY, 'component has the correct Y');
            assert.equal($element.width(), data.newW, 'component has the correct width');
            assert.equal($element.height(), data.newH, 'component has the correct height');
        });


    QUnit.module('Visual test');

    QUnit.asyncTest('display and play', function (assert) {
        var component = componentFactory(),
            $container = $('#outside');

        QUnit.expect(1);

        makeDraggable(component);
        makeResizable(component);

        component
            .on('render', function(){
                assert.ok(true);
                QUnit.start();
            })
            .init({
                minWidth: 300,
                maxWidth: 700,
                minHeight: 150,
                maxHeight: 450
            })
            .render($container)
            .setSize(500, 300)
            .center();
    });
});