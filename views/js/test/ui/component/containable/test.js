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
            { title: 'contain right',   moveToX: 301,   moveToY: 100,   expectedX: 300,   expectedY: 100 },
            { title: 'contain left',    moveToX: -1,    moveToY: 100,   expectedX: 0,     expectedY: 100 },
            { title: 'contain top',     moveToX: 100,   moveToY: -1,    expectedX: 100,   expectedY: 0 },
            { title: 'contain bottom',  moveToX: 100,   moveToY: 401,   expectedX: 100,   expectedY: 400 },

            // with padding
            { title: 'contain right, padding',      moveToX: 301,   moveToY: 100,   expectedX: 290,   expectedY: 100,   padding: 10 },
            { title: 'contain right, paddingRight', moveToX: 301,   moveToY: 100,   expectedX: 290,   expectedY: 100,   paddingRight: 10 },
            { title: 'contain left, padding',       moveToX: -1,    moveToY: 100,   expectedX: 10,    expectedY: 100,   padding: 10 },
            { title: 'contain left, paddingLeft',   moveToX: -1,    moveToY: 100,   expectedX: 10,    expectedY: 100,   paddingLeft: 10 },
            { title: 'contain top, padding',        moveToX: 100,   moveToY: -1,    expectedX: 100,   expectedY: 10,    padding: 10 },
            { title: 'contain top, paddingTop',     moveToX: 100,   moveToY: -1,    expectedX: 100,   expectedY: 10,    paddingTop: 10 },
            { title: 'contain bottom, padding',     moveToX: 100,   moveToY: 401,   expectedX: 100,   expectedY: 390,   padding: 10 },
            { title: 'contain bottom, paddingBottom', moveToX: 100, moveToY: 401,   expectedX: 100,   expectedY: 390,   paddingBottom: 10 }
        ])
        .asyncTest('automatically reposition the component if it overflow its container', function (data, assert) {
            var component = makeContainable(componentFactory()),
                $container = $(fixtureContainer);

            QUnit.expect(3);

            $container.css({
                width: '500px',
                height: '500px'
            });

            component
                .init({
                    width: 200,
                    height: 100
                })
                .render($container)
                .containIn($container, {
                    padding: data.padding,
                    paddingTop: data.paddingTop,
                    paddingRight: data.paddingRight,
                    paddingBottom: data.paddingBottom,
                    paddingLeft: data.paddingLeft
                })

                .on('contained', function() {
                    var componentPosition = this.getPosition();

                    assert.ok(true, 'contained event has been triggered');
                    assert.equal(componentPosition.x, data.expectedX, 'component x position has been contained');
                    assert.equal(componentPosition.y, data.expectedY, 'component y position has been contained');

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