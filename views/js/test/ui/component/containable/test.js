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
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery',
    'ui/component',
    'ui/component/placeable',
    'ui/component/containable'
], function ($, componentFactory, makePlaceable, makeContainable) {
    'use strict';

    var fixtureContainer = '#qunit-fixture';

    QUnit.module('API');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.ok(typeof makeContainable === 'function', 'The module expose a function');
    });

    QUnit
        .cases([
            { title: 'containIn' }
        ])
        .test('component API', function(data, assert) {
            var component = makeContainable(componentFactory());

            QUnit.expect(1);
            assert.equal(typeof component[data.title], 'function', 'The component has the method ' + data.title);
        });

    QUnit.test('auto makes the component placeable', function(assert) {
        var component = makeContainable(componentFactory());
        QUnit.expect(1);
        assert.ok(makePlaceable.isPlaceable(component), 'created component is placeable');
    });

    QUnit.module('Containable Component');

    QUnit
        .cases([
            // component is 200 x 100 per CSS rule, container 500x500

            // overflow container
            { title: 'overflow right',   moveToX: 301,   moveToY: 100,   expectedX: 300,   expectedY: 100 },
            { title: 'overflow left',    moveToX: -1,    moveToY: 100,   expectedX: 0,     expectedY: 100 },
            { title: 'overflow top',     moveToX: 100,   moveToY: -1,    expectedX: 100,   expectedY: 0 },
            { title: 'overflow bottom',  moveToX: 100,   moveToY: 401,   expectedX: 100,   expectedY: 400 },

            // overflow padding
            { title: 'overflow right, padding',      moveToX: 291,   moveToY: 100,   expectedX: 290,   expectedY: 100,   padding: 10 },
            { title: 'overflow right, paddingRight', moveToX: 291,   moveToY: 100,   expectedX: 290,   expectedY: 100,   paddingRight: 10 },
            { title: 'overflow left, padding',       moveToX: 9,     moveToY: 100,   expectedX: 10,    expectedY: 100,   padding: 10 },
            { title: 'overflow left, paddingLeft',   moveToX: 9,     moveToY: 100,   expectedX: 10,    expectedY: 100,   paddingLeft: 10 },
            { title: 'overflow top, padding',        moveToX: 100,   moveToY: 9,     expectedX: 100,   expectedY: 10,    padding: 10 },
            { title: 'overflow top, paddingTop',     moveToX: 100,   moveToY: 9,     expectedX: 100,   expectedY: 10,    paddingTop: 10 },
            { title: 'overflow bottom, padding',     moveToX: 100,   moveToY: 391,   expectedX: 100,   expectedY: 390,   padding: 10 },
            { title: 'overflow bottom, paddingBottom', moveToX: 100, moveToY: 391,   expectedX: 100,   expectedY: 390,   paddingBottom: 10 }
        ])
        .asyncTest('automatically reposition the component if it overflow its container', function (data, assert) {
            var component = makeContainable(componentFactory()),
                $container = $(fixtureContainer);

            QUnit.expect(5);

            $container.css({
                width: '500px',
                height: '500px'
            });

            component
                .init()
                .render($container)
                .containIn($container, {
                    padding: data.padding,
                    paddingTop: data.paddingTop,
                    paddingRight: data.paddingRight,
                    paddingBottom: data.paddingBottom,
                    paddingLeft: data.paddingLeft
                })

                .on('contained', function(newX, newY) {
                    var componentPosition = this.getPosition();

                    assert.ok(true, 'contained event has been triggered');

                    assert.equal(componentPosition.x, data.expectedX, 'component x position has been contained');
                    assert.equal(componentPosition.y, data.expectedY, 'component y position has been contained');

                    assert.equal(newX, data.expectedX, 'contained event has the right x parameter');
                    assert.equal(newY, data.expectedY, 'contained event has the right y parameter');

                    QUnit.start();
                })

                .moveTo(data.moveToX, data.moveToY);
        });

    QUnit
        .cases([
            // component is 200 x 100 per CSS rule, container 500x500

            // position at edges
            { title: 'edge right',   moveToX: 300,   moveToY: 100,   expectedX: 300,   expectedY: 100 },
            { title: 'edge left',    moveToX: 0,     moveToY: 100,   expectedX: 0,     expectedY: 100 },
            { title: 'edge top',     moveToX: 100,   moveToY: 0,     expectedX: 100,   expectedY: 0 },
            { title: 'edge bottom',  moveToX: 100,   moveToY: 400,   expectedX: 100,   expectedY: 400 },

            // position at edges, with padding
            { title: 'edge right, padding',      moveToX: 290,   moveToY: 100,   expectedX: 290,   expectedY: 100,   padding: 10 },
            { title: 'edge left, padding',       moveToX: 10,    moveToY: 100,   expectedX: 10,    expectedY: 100,   padding: 10 },
            { title: 'edge right, paddingRight', moveToX: 290,   moveToY: 100,   expectedX: 290,   expectedY: 100,   paddingRight: 10 },
            { title: 'edge left, paddingLeft',   moveToX: 10,    moveToY: 100,   expectedX: 10,    expectedY: 100,   paddingLeft: 10 },
            { title: 'edge top, padding',        moveToX: 100,   moveToY: 10,    expectedX: 100,   expectedY: 10,    padding: 10 },
            { title: 'edge top, paddingTop',     moveToX: 100,   moveToY: 10,    expectedX: 100,   expectedY: 10,    paddingTop: 10 },
            { title: 'edge bottom, padding',     moveToX: 100,   moveToY: 390,   expectedX: 100,   expectedY: 390,   padding: 10 },
            { title: 'edge bottom, paddingBottom', moveToX: 100, moveToY: 390,   expectedX: 100,   expectedY: 390,   paddingBottom: 10 }
        ])
        .asyncTest('does not interfere with normal positioning if no overflow', function (data, assert) {
            var component = makeContainable(componentFactory()),
                $container = $(fixtureContainer);

            QUnit.expect(1);

            $container.css({
                width: '500px',
                height: '500px'
            });

            component
                .init()
                .render($container)
                .containIn($container, {
                    padding: data.padding,
                    paddingTop: data.paddingTop,
                    paddingRight: data.paddingRight,
                    paddingBottom: data.paddingBottom,
                    paddingLeft: data.paddingLeft
                })

                .on('contained', function() {
                    assert.ok(false, 'contained should not be triggered');
                    QUnit.start();
                })
                .on('move', function() {
                    assert.ok(true, 'move event has been triggered');
                    QUnit.start();
                })

                .moveTo(data.moveToX, data.moveToY);
        });

    QUnit.module('Visual test');

    QUnit.asyncTest('display and play', function (assert) {
        var component = makeContainable(componentFactory()),
            $container = $('#outside'),

            $xPos = $container.find('#xPos'),
            $yPos = $container.find('#yPos'),
            $padding = $container.find('#padding');

        QUnit.expect(1);

        component
            .on('render', function(){
                var self = this;

                $container.find('input').on('change', function(e) {
                    e.preventDefault();
                    self.containIn($container, { padding: $padding.val() })
                        .moveTo($xPos.val(), $yPos.val());
                });

                assert.ok(true);
                QUnit.start();
            })
            .init()
            .render($container)
            .setSize(200, 100)
            .containIn($container, { padding: $padding.val() })
            .moveTo($xPos.val(), $yPos.val());
    });

});