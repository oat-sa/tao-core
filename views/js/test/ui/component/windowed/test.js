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
    'lodash',
    'jquery',
    'ui/component',
    'ui/component/placeable',
    'ui/component/draggable',
    'ui/component/resizable',
    'ui/component/windowed'
], function (_, $, componentFactory, makePlaceable, makeDraggable, makeResizable, makeWindowed) {
    'use strict';

    var fixtureContainer = '#qunit-fixture';

    QUnit.module('API');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.ok(typeof makeWindowed === 'function', 'The module expose a function');
    });

    QUnit
        .cases([
            { title: '_renderControls' },
            { title: 'getBody' },
            { title: 'getControls' },
            { title: 'getTitle' },
            { title: 'addControl' },
            { title: 'addPresets' }
        ])
        .test('component API', function(data, assert) {
            var component = makeWindowed(componentFactory());

            QUnit.expect(1);
            assert.equal(typeof component[data.title], 'function', 'The component has the method ' + data.title);
        });

    QUnit.test('auto makes the component placeable', function(assert) {
        var component = makeWindowed(componentFactory());
        QUnit.expect(1);
        assert.ok(makePlaceable.isPlaceable(component), 'created component is placeable');
    });

    QUnit.module('Windowed component');

    QUnit.asyncTest('Markup', function(assert) {
        var component = makeWindowed(componentFactory()),
            $container = $(fixtureContainer);

        QUnit.expect(5);

        component
            .on('render', function() {
                var $title      = component.getTitle(),
                    $controls   = component.getControls(),
                    $body       = component.getBody();

                assert.equal($controls.length, 1,   'controls element has been found');
                assert.equal($body.length, 1,       'body element has been found');

                assert.equal($title.html(), 'test component', 'title has been rendered');
                assert.equal($controls.find('[data-control="closer"]').length, 1, 'closer element has been rendered');

                component.destroy();

                assert.equal($('.window-component').length, 0, 'component has been destroyed');

                QUnit.start();
            })
            .init({
                windowTitle: 'test component'
            })
            .render($container);
    });

    QUnit.module('Controls');

    QUnit
        .cases([
            { title: 'invalid id',              icon: 'icon', onclick: _.noop },
            { title: 'invalid id', id: {},      icon: 'icon', onclick: _.noop },
            { title: 'invalid id', id: 69,      icon: 'icon', onclick: _.noop },
            { title: 'invalid id', id: _.noop,  icon: 'icon', onclick: _.noop },
            { title: 'invalid id', id: '',      icon: 'icon', onclick: _.noop },

            { title: 'invalid icon', id: 'id',                    onclick: _.noop },
            { title: 'invalid icon', id: 'id',    icon: {},       onclick: _.noop },
            { title: 'invalid icon', id: 'id',    icon: 69,       onclick: _.noop },
            { title: 'invalid icon', id: 'id',    icon: _.noop,   onclick: _.noop },
            { title: 'invalid icon', id: 'id',    icon: '',       onclick: _.noop },

            { title: 'no onclick and no event', id: 'id', icon: 'icon' },

            { title: 'invalid onclick', id: 'id', icon: 'icon', onclick: {} },
            { title: 'invalid onclick', id: 'id', icon: 'icon', onclick: 69 },
            { title: 'invalid onclick', id: 'id', icon: 'icon', onclick: '' },
            { title: 'invalid onclick', id: 'id', icon: 'icon', onclick: 'onclick' },

            { title: 'invalid event', id: 'id', icon: 'icon', event: {} },
            { title: 'invalid event', id: 'id', icon: 'icon', event: 69 },
            { title: 'invalid event', id: 'id', icon: 'icon', event: '' },
            { title: 'invalid event', id: 'id', icon: 'icon', event: '  ' },
            { title: 'invalid event', id: 'id', icon: 'icon', event: _.noop }
        ])
        .test('add control api', function(data, assert) {
            var component = makeWindowed(componentFactory()),
                addControl = function() {
                    component.addControl({
                        id: data.id,
                        icon: data.icon,
                        onclick: data.onclick,
                        event: data.event
                    });
                };

            QUnit.expect(1);
            assert.throws(addControl, Error);
        });


    QUnit.asyncTest('allow to add controls with a specific order', function(assert) {
        var component = makeWindowed(componentFactory()),
            $container = $(fixtureContainer);

        QUnit.expect(7);

        component
            .on('render', function() {
                var $controls = component.getControls(),
                    $renderedControls,
                    $cross,
                    $fullscreen,
                    $restore;

                $renderedControls = $controls.find('button');

                assert.equal($renderedControls.length, 3, '3 buttons have been rendered');

                $fullscreen = $renderedControls.eq(0);
                $restore = $renderedControls.eq(1);
                $cross = $renderedControls.eq(2);

                assert.equal($fullscreen.data('control'), 'fullscreen', 'fullscreen control has been rendered first');
                assert.ok($fullscreen.hasClass('icon-resize'), 'fullscreen control has the correct class');

                assert.ok($restore.data('control'), 'restore', 'restore control has been rendered second');
                assert.ok($restore.hasClass('icon-undo'), 'restore control has been the correct class');

                assert.ok($cross.data('control'), 'cross', 'cross control has been rendered last');
                assert.ok($cross.hasClass('icon-result-nok'), 'cross control has the correct class');

                QUnit.start();
            })
            .init({
                windowTitle: 'test component',
                hasCloser: false
            })
            .addControl({
                id: 'cross',
                order: 300,
                icon: 'result-nok',
                onclick: _.noop
            })
            .addControl({
                id: 'fullscreen',
                order: 100,
                icon: 'resize',
                onclick: _.noop
            })
            .addControl({
                id: 'restore',
                order: 200,
                icon: 'undo',
                onclick: _.noop
            })
            .render($container);
    });


    QUnit.asyncTest('allow to pass a onclick listener to a control', function(assert) {
        var component = makeWindowed(componentFactory()),
            $container = $(fixtureContainer),
            state = {
                crossClicked: false,
                fullscreenClicked: false,
                restoreClicked: false
            };

        QUnit.expect(3);

        component
            .on('render', function() {
                var $controls   = component.getControls(),

                    $cross      = $controls.find('[data-control="cross"]'),
                    $fullscreen = $controls.find('[data-control="fullscreen"]'),
                    $restore    = $controls.find('[data-control="restore"]');

                $cross.click();
                $fullscreen.click();
                $restore.click();

                assert.ok(state.crossClicked, 'cross listener has been called');
                assert.ok(state.fullscreenClicked, 'fullscreen listener has been called');
                assert.ok(state.restoreClicked, 'restore listener has been called');

                QUnit.start();
            })
            .init({
                windowTitle: 'test component',
                hasCloser: false
            })
            .addControl({
                id: 'cross',
                icon: 'result-nok',
                onclick: function() {
                    state.crossClicked = true;
                }
            })
            .addControl({
                id: 'fullscreen',
                icon: 'resize',
                onclick: function() {
                    state.fullscreenClicked = true;
                }
            })
            .addControl({
                id: 'restore',
                icon: 'undo',
                onclick: function() {
                    state.restoreClicked = true;
                }
            })
            .render($container);
    });


    QUnit.asyncTest('allow to pass a event name to a control', function(assert) {
        var component = makeWindowed(componentFactory()),
            $container = $(fixtureContainer);

        QUnit.expect(3);

        component
            .on('render', function() {
                var $controls   = component.getControls(),

                    $cross      = $controls.find('[data-control="cross"]'),
                    $fullscreen = $controls.find('[data-control="fullscreen"]'),
                    $restore    = $controls.find('[data-control="restore"]');

                $cross.click();
                $fullscreen.click();
                $restore.click();
            })
            .init({
                windowTitle: 'test component',
                hasCloser: false
            })
            .addControl({
                id: 'cross',
                icon: 'result-nok',
                event: 'crossclick'
            })
            .addControl({
                id: 'fullscreen',
                icon: 'resize',
                event: 'fullscreenclick'
            })
            .addControl({
                id: 'restore',
                icon: 'undo',
                event: 'restoreclick'
            })
            .on('crossclick', function() {
                assert.ok(true, 'cross control event has been fired');
            })
            .on('fullscreenclick', function() {
                assert.ok(true, 'fullscreen control event has been fired');
            })
            .on('restoreclick', function() {
                assert.ok(true, 'restore control event has been fired');
                QUnit.start();
            })
            .render($container);
    });

    QUnit.asyncTest('Closer control', function(assert) {
        var component = makeWindowed(componentFactory()),
            $container = $(fixtureContainer);

        QUnit.expect(5);

        component
            .on('render', function() {
                var $controls = component.getControls(),
                    $closer = $controls.find('[data-control="closer"]');

                assert.equal(this.is('hidden'), false, 'component is visible');
                assert.equal($closer.length, 1, 'closer element has been found');
                assert.ok($closer.hasClass('icon-result-nok'), 'close control has the correct class');

                $closer.click();
            })
            .on('close', function() {
                assert.ok(true, 'close event has been triggered');
                assert.equal(this.is('hidden'), true, 'component has been hidden');

                QUnit.start();
            })
            .init()
            .render($container);
    });

    QUnit.asyncTest('Delete control', function(assert) {
        var component = makeWindowed(componentFactory({}, { hasBin: true })),
            $container = $(fixtureContainer);

        QUnit.expect(4);

        component
            .on('render', function() {
                var $controls = component.getControls(),
                    $bin = $controls.find('[data-control="bin"]');

                assert.equal(this.is('hidden'), false, 'component is visible');
                assert.equal($bin.length, 1, 'bin element has been found');
                assert.ok($bin.hasClass('icon-bin'), 'bin control has the correct class');

                $bin.click();
            })
            .on('delete', function() {
                assert.ok(true, 'delete event has been triggered');

                QUnit.start();
            })
            .init()
            .render($container);
    });


    QUnit.module('Visual test');

    QUnit.asyncTest('display and play', function (assert) {
        var component = componentFactory(),
            $container = $('#outside');

        QUnit.expect(1);

        makeWindowed(component);
        makeDraggable(component);
        makeResizable(component);

        component
            .on('render', function(){
                var $content = this.getBody();

                $content.append('My content');

                assert.ok(true);
                QUnit.start();
            })
            .init({
                minWidth: 150,
                maxWidth: 700,
                minHeight: 150,
                maxHeight: 450,
                initialX: 100,
                initialY: 50,
                windowTitle: 'My Windowed component',
                hasBin: true,
                hasCloser: true
            })
            .render($container)
            .setSize(500, 300);
    });

});