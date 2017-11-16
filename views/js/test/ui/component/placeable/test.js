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
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'ui/component',
    'ui/component/placeable',
    'ui/transformer'
], function ($, componentFactory, makePlaceable, transformer) {
    'use strict';

    var fixtureContainer = '#qunit-fixture';

    QUnit.module('API');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.ok(typeof makePlaceable === 'function', 'The module expose a function');
    });

    QUnit
        .cases([
            { title: 'center',          method: 'center' },
            { title: 'getPosition',     method: 'getPosition' },
            { title: 'moveTo',          method: 'moveTo' },
            { title: 'moveBy',          method: 'moveBy' },
            { title: 'resetPosition',   method: 'resetPosition' },
            { title: 'moveToX',         method: 'moveToX' },
            { title: 'moveToY',         method: 'moveToY' }
        ])
        .test('component API', function(data, assert) {
            var component = makePlaceable(componentFactory());

            QUnit.expect(1);
            assert.equal(typeof component[data.method], 'function', 'The component has the method ' + data.method);
        });

    QUnit.test('.isPlaceable()', function(assert) {
        var component = componentFactory();

        QUnit.expect(2);

        assert.ok(! makePlaceable.isPlaceable(component), 'returns false if component is not placeable');

        makePlaceable(component);

        assert.ok(makePlaceable.isPlaceable(component), 'returns true if component is placeable');
    });

    QUnit.module('component options');

    QUnit.test('pass options with makePlaceable()', function (assert) {
        var $container = $(fixtureContainer),
            component = makePlaceable(
                componentFactory(),
                { initialX: 50, initialY: 50 }
            ),
            position;

        component
            .init()
            .render($container);

        position = component.getElement().position();
        assert.equal(position.left, 50, 'initialX option has been used');
        assert.equal(position.top, 50, 'initialY option has been used');
    });

    QUnit.test('options in init overrides options passed in makePlaceable()', function (assert) {
        var $container = $(fixtureContainer),
            component = makePlaceable(
                componentFactory(),
                { initialX: 50, initialY: 50 }
            ),
            position;

        component
            .init({ initialX: 75, initialY: 75 })
            .render($container);

        position = component.getElement().position();
        assert.equal(position.left, 75, 'initialX option has been used');
        assert.equal(position.top, 75, 'initialY option has been used');
    });

    QUnit.module('Placeable component');

    QUnit.test('render the component with absolute positioning', function (assert) {
        var $container = $(fixtureContainer),
            component = makePlaceable(componentFactory())
                .init()
                .render($container),
            $element = component.getElement();

        QUnit.expect(1);

        assert.equal($element.css('position'), 'absolute', 'component has been rendered with absolute positioning');
    });

    QUnit.test('render component at the top/left of the container if no default position is specified', function(assert) {
        var component = makePlaceable(componentFactory()),
            $container = $(fixtureContainer).width(800).height(600),
            position,
            $element;

        QUnit.expect(8);

        component
            .init()
            .render($container);

        position = component.getPosition();
        assert.equal(position.x, 0, 'getPosition() returns the correct x value');
        assert.equal(position.y, 0, 'getPosition() returns the correct y value');

        position = component.getElement().position();
        assert.equal(position.left, 0, '$.position() returns the correct left value');
        assert.equal(position.top, 0, '$.position() returns the correct top value');

        $element = component.getElement();
        assert.equal($element.css('left'), '0px', 'component\'s element has the correct value for css property left');
        assert.equal($element.css('top'), '0px', 'component\'s element has the correct value for css property top');
        assert.equal(transformer.getTransformation($element).obj.translateX, 0, 'component\'s element has the right x translation');
        assert.equal(transformer.getTransformation($element).obj.translateY, 0, 'component\'s element has the the right y translation');
    });


    QUnit.test('render component into default position', function(assert) {
        var component = makePlaceable(componentFactory()),
            $container = $(fixtureContainer).width(800).height(600),
            position,
            $element;

        QUnit.expect(8);

        component
            .init({
                initialX: 100,
                initialY: 50
            })
            .render($container);

        position = component.getPosition();
        assert.equal(position.x, 100, 'getPosition() returns the correct x value');
        assert.equal(position.y, 50, 'getPosition() returns the correct y value');

        position = component.getElement().position();
        assert.equal(position.left, 100, '$.position() returns the correct left value');
        assert.equal(position.top, 50, '$.position() returns the correct top value');

        $element = component.getElement();
        assert.equal($element.css('left'), '100px', 'component\'s element has the correct value for css property left');
        assert.equal($element.css('top'), '50px', 'component\'s element has the correct value for css property top');
        assert.equal(transformer.getTransformation($element).obj.translateX, 0, 'component\'s element has the right x translation');
        assert.equal(transformer.getTransformation($element).obj.translateY, 0, 'component\'s element has the the right y translation');
    });

    QUnit.asyncTest('.center() - no default position', function (assert) {
        var component = makePlaceable(componentFactory()),
            $container = $(fixtureContainer).width(800).height(600),
            position,
            $element;

        QUnit.expect(11);

        component
            .on('center', function(x, y) {
                assert.ok(true, 'center event has been triggered');
                assert.equal(x, 160, 'correct x value is transmitted as an event parameter');
                assert.equal(y, 120, 'correct y value is transmitted as an event parameter');

                QUnit.start();
            })
            .init()
            .render($container);

        component.setSize(480, 360);
        component.center();

        position = component.getPosition();
        assert.equal(position.x, 160, 'component has the correct x');
        assert.equal(position.y, 120, 'component has the correct y');

        position = component.getElement().position();
        assert.equal(position.left, 160, '$.position() returns the correct left value');
        assert.equal(position.top, 120, '$.position() returns the correct top value');

        $element = component.getElement();
        assert.equal($element.css('left'), '0px', 'component\'s element has the correct value for css property left');
        assert.equal($element.css('top'), '0px', 'component\'s element has the correct value for css property top');
        assert.equal(transformer.getTransformation($element).obj.translateX, 160, 'component\'s element has the right x translation');
        assert.equal(transformer.getTransformation($element).obj.translateY, 120, 'component\'s element has the the right y translation');
    });

    QUnit.asyncTest('.center() - with default position', function (assert) {
        var component = makePlaceable(componentFactory()),
            $container = $(fixtureContainer).width(800).height(600),
            position,
            $element;

        QUnit.expect(11);

        component
            .on('center', function(x, y) {
                assert.ok(true, 'center event has been triggered');
                assert.equal(x, 160, 'correct x value is transmitted as an event parameter');
                assert.equal(y, 120, 'correct y value is transmitted as an event parameter');

                QUnit.start();
            })
            .init({
                initialX: 150,
                initialY: 170
            })
            .render($container);

        component.setSize(480, 360);
        component.center();

        position = component.getPosition();
        assert.equal(position.x, 160, 'component has the correct x');
        assert.equal(position.y, 120, 'component has the correct y');

        position = component.getElement().position();
        assert.equal(position.left, 160, '$.position() returns the correct left value');
        assert.equal(position.top, 120, '$.position() returns the correct top value');

        $element = component.getElement();
        assert.equal($element.css('left'), '150px', 'component\'s element has the correct value for css property left');
        assert.equal($element.css('top'), '170px', 'component\'s element has the correct value for css property top');
        assert.equal(transformer.getTransformation($element).obj.translateX, 10, 'component\'s element has the right x translation');
        assert.equal(transformer.getTransformation($element).obj.translateY, -50, 'component\'s element has the the right y translation');
    });

    QUnit.asyncTest('.moveBy() - no default position', function (assert) {
        var component = makePlaceable(componentFactory()),
            $container = $(fixtureContainer).width(800).height(600),
            position,
            $element,
            moveCounter = 0;

        QUnit.expect(13);

        component
            .on('move', function(x, y) {
                moveCounter++;

                if (moveCounter === 2) {
                    assert.ok(true, 'move event has been triggered');
                    assert.equal(x, 50, 'correct x value is transmitted as an event parameter');
                    assert.equal(y, 100, 'correct y value is transmitted as an event parameter');

                    QUnit.start();
                }
            })
            .init()
            .render($container);

        position = component.getPosition();
        assert.equal(position.x, 0, 'component has the correct x');
        assert.equal(position.y, 0, 'component has the correct y');

        component.moveBy(50, 100);

        position = component.getPosition();
        assert.equal(position.x, 50, 'component has the correct x');
        assert.equal(position.y, 100, 'component has the correct y');

        position = component.getElement().position();
        assert.equal(position.left, 50, '$.position() returns the correct left value');
        assert.equal(position.top, 100, '$.position() returns the correct top value');

        $element = component.getElement();
        assert.equal($element.css('left'), '0px', 'component\'s element has the correct value for css property left');
        assert.equal($element.css('top'), '0px', 'component\'s element has the correct value for css property top');
        assert.equal(transformer.getTransformation($element).obj.translateX, 50, 'component\'s element has the right x translation');
        assert.equal(transformer.getTransformation($element).obj.translateY, 100, 'component\'s element has the the right y translation');
    });

    QUnit.asyncTest('.moveBy() - with default position', function (assert) {
        var component = makePlaceable(componentFactory()),
            $container = $(fixtureContainer).width(800).height(600),
            position,
            $element,
            moveCounter = 0;

        QUnit.expect(13);

        component
            .on('move', function(x, y) {
                moveCounter++;

                if (moveCounter === 2) {
                    assert.ok(true, 'move event has been triggered');
                    assert.equal(x, 260, 'correct x value is transmitted as an event parameter');
                    assert.equal(y, 225, 'correct y value is transmitted as an event parameter');

                    QUnit.start();
                }
            })
            .init({
                initialX: 210,
                initialY: 125
            })
            .render($container);

        position = component.getPosition();
        assert.equal(position.x, 210, 'component has the correct x');
        assert.equal(position.y, 125, 'component has the correct y');

        component.moveBy(50, 100);

        position = component.getPosition();
        assert.equal(position.x, 260, 'component has the correct x');
        assert.equal(position.y, 225, 'component has the correct y');

        position = component.getElement().position();
        assert.equal(position.left, 260, '$.position() returns the correct left value');
        assert.equal(position.top, 225, '$.position() returns the correct top value');

        $element = component.getElement();
        assert.equal($element.css('left'), '210px', 'component\'s element has the correct value for css property left');
        assert.equal($element.css('top'), '125px', 'component\'s element has the correct value for css property top');
        assert.equal(transformer.getTransformation($element).obj.translateX, 50, 'component\'s element has the right x translation');
        assert.equal(transformer.getTransformation($element).obj.translateY, 100, 'component\'s element has the the right y translation');
    });

    QUnit.asyncTest('.moveBy() - multiple consecutive calls', function(assert) {
        var component = makePlaceable(componentFactory()),
            $container = $(fixtureContainer).width(800).height(600),
            position,
            $element,
            moveCounter = 0;

        QUnit.expect(23);

        component
            .on('move', function(x, y) {
                moveCounter++;

                if (moveCounter === 2) {
                    assert.ok(true, 'move event has been triggered');
                    assert.equal(x, 260, 'correct x value is transmitted as an event parameter');
                    assert.equal(y, 225, 'correct y value is transmitted as an event parameter');

                } else if (moveCounter === 3) {
                    assert.ok(true, 'move event has been triggered');
                    assert.equal(x, 150, 'correct x value is transmitted as an event parameter');
                    assert.equal(y, 150, 'correct y value is transmitted as an event parameter');

                } else if (moveCounter === 4) {
                    assert.ok(true, 'move event has been triggered');
                    assert.equal(x, 300, 'correct x value is transmitted as an event parameter');
                    assert.equal(y, 200, 'correct y value is transmitted as an event parameter');

                    QUnit.start();
                }
            })
            .init({
                initialX: 210,
                initialY: 125
            })
            .render($container);

        $element = component.getElement();

        position = component.getPosition();
        assert.equal(position.x, 210, 'component has the correct x');
        assert.equal(position.y, 125, 'component has the correct y');

        component.moveBy(50, 100);

        position = component.getPosition();
        assert.equal(position.x, 260, 'component has the correct x');
        assert.equal(position.y, 225, 'component has the correct y');

        assert.equal(transformer.getTransformation($element).obj.translateX, 50, 'component\'s element has the right x translation');
        assert.equal(transformer.getTransformation($element).obj.translateY, 100, 'component\'s element has the right y translation');

        component.moveBy(-110, -75);

        position = component.getPosition();
        assert.equal(position.x, 150, 'component has the correct x');
        assert.equal(position.y, 150, 'component has the correct y');

        assert.equal(transformer.getTransformation($element).obj.translateX, -60, 'component\'s element has the right x translation');
        assert.equal(transformer.getTransformation($element).obj.translateY, 25, 'component\'s element has the right y translation');

        component.moveBy(150, 50);

        position = component.getPosition();
        assert.equal(position.x, 300, 'component has the correct x');
        assert.equal(position.y, 200, 'component has the correct y');

        assert.equal(transformer.getTransformation($element).obj.translateX, 90, 'component\'s element has the right x translation');
        assert.equal(transformer.getTransformation($element).obj.translateY, 75, 'component\'s element has the right y translation');

    });

    QUnit.asyncTest('.moveTo() - no default position', function (assert) {
        var component = makePlaceable(componentFactory()),
            $container = $(fixtureContainer).width(800).height(600),
            position,
            $element,
            moveCounter = 0;

        QUnit.expect(13);

        component
            .on('move', function(x, y) {
                moveCounter++;

                if (moveCounter === 2) {
                    assert.ok(true, 'move event has been triggered');
                    assert.equal(x, 400, 'correct x value is transmitted as an event parameter');
                    assert.equal(y, 300, 'correct y value is transmitted as an event parameter');

                    QUnit.start();
                }
            })
            .init()
            .render($container);

        position = component.getPosition();
        assert.equal(position.x, 0, 'component has the correct x');
        assert.equal(position.y, 0, 'component has the correct y');

        component.moveTo(400, 300);

        position = component.getPosition();
        assert.equal(position.x, 400, 'component has the correct x');
        assert.equal(position.y, 300, 'component has the correct y');

        position = component.getElement().position();
        assert.equal(position.left, 400, '$.position() returns the correct left value');
        assert.equal(position.top, 300, '$.position() returns the correct top value');

        $element = component.getElement();
        assert.equal($element.css('left'), '0px', 'component\'s element has the correct value for css property left');
        assert.equal($element.css('top'), '0px', 'component\'s element has the correct value for css property top');
        assert.equal(transformer.getTransformation($element).obj.translateX, 400, 'component\'s element has the right x translation');
        assert.equal(transformer.getTransformation($element).obj.translateY, 300, 'component\'s element has the the right y translation');
    });

    QUnit.asyncTest('.moveTo() - with default position', function (assert) {
        var component = makePlaceable(componentFactory()),
            $container = $(fixtureContainer).width(800).height(600),
            position,
            $element,
            moveCounter = 0;

        QUnit.expect(13);

        component
            .on('move', function(x, y) {
                moveCounter++;

                if (moveCounter === 2) {
                    assert.ok(true, 'move event has been triggered');
                    assert.equal(x, 400, 'correct x value is transmitted as an event parameter');
                    assert.equal(y, 300, 'correct y value is transmitted as an event parameter');

                    QUnit.start();
                }
            })
            .init({
                initialX: 210,
                initialY: 425
            })
            .render($container);

        position = component.getPosition();
        assert.equal(position.x, 210, 'component has the correct x');
        assert.equal(position.y, 425, 'component has the correct y');

        component.moveTo(400, 300);

        position = component.getPosition();
        assert.equal(position.x, 400, 'component has the correct x');
        assert.equal(position.y, 300, 'component has the correct y');

        position = component.getElement().position();
        assert.equal(position.left, 400, '$.position() returns the correct left value');
        assert.equal(position.top, 300, '$.position() returns the correct top value');

        $element = component.getElement();
        assert.equal($element.css('left'), '210px', 'component\'s element has the correct value for css property left');
        assert.equal($element.css('top'), '425px', 'component\'s element has the correct value for css property top');
        assert.equal(transformer.getTransformation($element).obj.translateX, 190, 'component\'s element has the right x translation');
        assert.equal(transformer.getTransformation($element).obj.translateY, -125, 'component\'s element has the the right y translation');
    });

    QUnit.asyncTest('.moveToX(), .moveToY', function (assert) {
        var component = makePlaceable(componentFactory()),
            $container = $(fixtureContainer),
            moveCounter = 0,
            componentPosition;

        QUnit.expect(8);

        component
            .on('render', function() {
                componentPosition = this.getPosition();
                assert.equal(componentPosition.x, 0, 'component has been rendered at x = 0');
                assert.equal(componentPosition.y, 0, 'component has been rendered at y = 0');

                this.moveToX(200);
            })
            .on('move', function() {
                moveCounter++;

                if (moveCounter === 2) {
                    componentPosition = this.getPosition();

                    assert.ok(true, 'move event has been triggered');
                    assert.equal(componentPosition.x, 200, 'component has the correct x position');
                    assert.equal(componentPosition.y, 0, 'component y position has not moved');

                    this.moveToY(100);

                } else if(moveCounter === 3) {
                    componentPosition = this.getPosition();

                    assert.ok(true, 'move event has been triggered');
                    assert.equal(componentPosition.x, 200, 'component x position has not moved');
                    assert.equal(componentPosition.y, 100, 'component has the correct y position');

                    QUnit.start();
                }
            })
            .init({
                width: 100,
                height: 50
            })
            .render($container);
    });

    QUnit.asyncTest('.resetPosition() - no default position', function (assert) {
        var component = makePlaceable(componentFactory()),
            $container = $(fixtureContainer).width(800).height(600),
            position,
            $element,
            moveCounter = 0;

        QUnit.expect(13);

        component
            .on('move', function(x, y) {
                moveCounter++;

                if (moveCounter === 3) {
                    assert.ok(true, 'move event has been triggered');
                    assert.equal(x, 0, 'correct x value is transmitted as an event parameter');
                    assert.equal(y, 0, 'correct y value is transmitted as an event parameter');

                    QUnit.start();
                }
            })
            .init()
            .render($container);

        position = component.getPosition();
        assert.equal(position.x, 0, 'component has the correct x');
        assert.equal(position.y, 0, 'component has the correct y');

        component.moveTo(400, 300);

        position = component.getPosition();
        assert.equal(position.x, 400, 'component has the correct x');
        assert.equal(position.y, 300, 'component has the correct y');

        component.resetPosition();

        position = component.getPosition();
        assert.equal(position.x, 0, 'component has the correct x');
        assert.equal(position.y, 0, 'component has the correct y');

        $element = component.getElement();
        assert.equal($element.css('left'), '0px', 'component\'s element has the correct value for css property left');
        assert.equal($element.css('top'), '0px', 'component\'s element has the correct value for css property top');
        assert.equal(transformer.getTransformation($element).obj.translateX, 0, 'component\'s element has the right x translation');
        assert.equal(transformer.getTransformation($element).obj.translateY, 0, 'component\'s element has the the right y translation');
    });

    QUnit.asyncTest('.resetPosition() - with default position', function (assert) {
        var component = makePlaceable(componentFactory()),
            $container = $(fixtureContainer).width(800).height(600),
            position,
            $element,
            moveCounter = 0;

        QUnit.expect(13);

        component
            .on('move', function(x, y) {
                moveCounter++;

                if (moveCounter === 3) {
                    assert.ok(true, 'move event has been triggered');
                    assert.equal(x, 210, 'correct x value is transmitted as an event parameter');
                    assert.equal(y, 425, 'correct y value is transmitted as an event parameter');

                    QUnit.start();
                }
            })
            .init({
                initialX: 210,
                initialY: 425
            })
            .render($container);

        position = component.getPosition();
        assert.equal(position.x, 210, 'component has the correct x');
        assert.equal(position.y, 425, 'component has the correct y');

        component.moveTo(400, 300);

        position = component.getPosition();
        assert.equal(position.x, 400, 'component has the correct x');
        assert.equal(position.y, 300, 'component has the correct y');

        component.resetPosition();

        position = component.getPosition();
        assert.equal(position.x, 210, 'component has the correct x');
        assert.equal(position.y, 425, 'component has the correct y');

        $element = component.getElement();
        assert.equal($element.css('left'), '210px', 'component\'s element has the correct value for css property left');
        assert.equal($element.css('top'), '425px', 'component\'s element has the correct value for css property top');
        assert.equal(transformer.getTransformation($element).obj.translateX, 0, 'component\'s element has the right x translation');
        assert.equal(transformer.getTransformation($element).obj.translateY, 0, 'component\'s element has the the right y translation');
    });

});