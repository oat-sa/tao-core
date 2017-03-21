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
    'ui/hider',
    'ui/stacker',
    'ui/component',
    'ui/component/stackable'
], function ($, hider, stackerFactory, componentFactory, makeStackable) {
    'use strict';

    var fixtureContainer = '#qunit-fixture',
        stackingScope = 'myScope',
        stacker = stackerFactory(stackingScope);

    QUnit.module('plugin');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.ok(typeof makeStackable === 'function', 'The module expose a function');
    });

    QUnit
        .cases([
            { 'title': 'bringToFront' }
        ])
        .test('stackable component API', function(data, assert) {
            var component = makeStackable(componentFactory());

            QUnit.expect(1);
            assert.ok(typeof component[data.title] === 'function', 'component has a ' + data.title + ' method');
        });

    QUnit.module('Regular component');

    QUnit.test('does not provide any z-index behavior', function (assert) {
        var $container = $(fixtureContainer),
            component = componentFactory()
                .init()
                .render($container)
                .show(),
            $element = component.getElement();

        QUnit.expect(3);

        assert.ok(! hider.isHidden($element), 'component is visible');
        assert.equal($element.get(0).style.zIndex, '', 'component has no z-index');

        $element.trigger('click');
        assert.equal($element.get(0).style.zIndex, '', 'component has still no z-index');
    });

    QUnit.module('Stackable component');

    QUnit.test('bring component to front on .bringToFront()', function(assert) {
        var $container = $(fixtureContainer),
            component = makeStackable(componentFactory(), { stackingScope: stackingScope })
                .init()
                .render($container),
            $element = component.getElement();

        QUnit.expect(4);

        assert.ok(! hider.isHidden($element), 'component is visible');
        assert.equal($element.get(0).style.zIndex, stacker.getCurrent(), 'component has been brought to the front');

        // put another element on top of the component
        stacker.bringToFront($('div'));
        assert.notEqual($element.get(0).style.zIndex, stacker.getCurrent(), 'component is not on the front anymore');

        component.bringToFront();
        assert.equal($element.get(0).style.zIndex, stacker.getCurrent(), 'component has been brought back on the front');
    });

    QUnit.test('bring component to front on .render()', function(assert) {
        var $container = $(fixtureContainer),
            component = makeStackable(componentFactory(), { stackingScope: stackingScope })
                .init()
                .render($container),
            $element = component.getElement();

        QUnit.expect(2);

        assert.ok(! hider.isHidden($element), 'component is visible');
        assert.equal($element.get(0).style.zIndex, stacker.getCurrent(), 'component has the latest z-index');
    });

    QUnit.test('bring component to front on .show()', function(assert) {
        var $container = $(fixtureContainer),
            component = makeStackable(componentFactory(), { stackingScope: stackingScope })
                .init()
                .render($container),
            $element = component.getElement();

        QUnit.expect(4);

        assert.ok(! hider.isHidden($element), 'component is visible');
        assert.equal($element.get(0).style.zIndex, stacker.getCurrent(), 'component has been brought to the front');

        // put another element on top of the component
        stacker.bringToFront($('div'));
        assert.notEqual($element.get(0).style.zIndex, stacker.getCurrent(), 'component is not on the front anymore');

        component.show();
        assert.equal($element.get(0).style.zIndex, stacker.getCurrent(), 'component has been brought back on the front');
    });

    QUnit.test('bring component to front on mousedown', function(assert) {
        var $container = $(fixtureContainer),
            component = makeStackable(componentFactory(), { stackingScope: stackingScope })
                .init()
                .render($container),
            $element = component.getElement();

        QUnit.expect(4);

        assert.ok(! hider.isHidden($element), 'component is visible');
        assert.equal($element.get(0).style.zIndex, stacker.getCurrent(), 'component has been brought to the front');

        // put another element on top of the component
        stacker.bringToFront($('div'));
        assert.notEqual($element.get(0).style.zIndex, stacker.getCurrent(), 'component is not on the front anymore');

        $element.trigger('mousedown');
        assert.equal($element.get(0).style.zIndex, stacker.getCurrent(), 'component has been brought back on the front');
    });
});