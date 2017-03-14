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
define([
    'jquery',
    'lodash',
    'ui/keyNavigation/navigator',
    'ui/keyNavigation/navigableDomElement',
    'ui/keyNavigation/navigableGroupElement',
    'lib/simulator/jquery.simulate'
], function($, _, keyNavigator, navigableDomElement, navigableGroupElement){
    'use strict';

    var pluginApi = [
        { name : 'on', title : 'on' },
        { name : 'off', title : 'off' },
        { name : 'trigger', title : 'trigger' },
        { name : 'getId', title : 'getId' },
        { name : 'getGroup', title : 'getGroup' },
        { name : 'next', title : 'next' },
        { name : 'previous', title : 'previous' },
        { name : 'activate', title : 'activate' },
        { name : 'goto', title : 'goto' },
        { name : 'focus', title : 'focus' },
        { name : 'focusPosition', title : 'focusPosition' },
        { name : 'destroy', title : 'destroy' },
        { name : 'blur', title : 'blur' }
    ];

    QUnit.module('API');

    QUnit.test('factory', function(assert) {
        var navigator = keyNavigator();
        assert.equal(typeof keyNavigator, 'function', "The module exposes a function");
        assert.equal(typeof navigator, 'object', 'The factory creates an object');
        assert.notDeepEqual(navigator, keyNavigator(), 'The factory creates new objects');
    });

    QUnit
        .cases(pluginApi)
        .test('component method ', function(data, assert) {
            var navigator = keyNavigator();
            QUnit.expect(1);
            assert.equal(typeof navigator[data.name], 'function', 'The navigator exposes a "' + data.name + '" function');
        });


    QUnit.module('Dom navigable element');

    QUnit.asyncTest('activate', function(assert){
        var navigator;
        var $container = $('#qunit-fixture .nav-1');
        var $navigables = $container.find('.nav');
        var navigables = navigableDomElement.createFromDoms($navigables);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            elements : navigables,
            defaultPosition : navigables.length - 1
        }).on('activate', function(cursor){
            QUnit.start();
            assert.ok(true, 'activated');
            assert.equal(cursor.position, 2, 'activated position is ok');
            assert.ok(cursor.navigable.getElement() instanceof $, 'navigable element in cursor');
            assert.equal(cursor.navigable.getElement().data('id'), 'C', 'navigable element in cursor is correct');
        });

        navigator.focus();
        assert.equal($(document.activeElement).data('id'), 'C', 'focus on last');
        $(document.activeElement).simulate('keydown', {keyCode: 13});//enter
    });

    QUnit.asyncTest('navigate with API', function(assert){
        var navigator;
        var $container = $('#qunit-fixture .nav-1');
        var $navigables = $container.find('.nav');
        var navigables = navigableDomElement.createFromDoms($navigables);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            id : 'bottom-toolbar',
            replace : true,
            group : $container,
            elements : navigables,
            defaultPosition : navigables.length - 1
        }).on('right down', function(){
            this.next();
        }).on('left up', function(){
            this.previous();
        }).on('activate', function(cursor){
            QUnit.start();
            assert.ok(true, 'activated');
            assert.equal(cursor.position, 0, 'activated position is ok');
            assert.ok(cursor.navigable.getElement() instanceof $, 'navigable element in cursor');
            assert.equal(cursor.navigable.getElement().data('id'), 'A', 'navigable element in cursor is correct');
        });

        navigator.focus();
        assert.equal($(document.activeElement).data('id'), 'C', 'default focus on last');
        navigator.next();
        assert.equal($(document.activeElement).data('id'), 'C', 'stay on last');
        navigator.previous();
        assert.equal($(document.activeElement).data('id'), 'B', 'focus on second');
        navigator.previous();
        assert.equal($(document.activeElement).data('id'), 'A', 'focus on first');
        navigator.previous();
        assert.equal($(document.activeElement).data('id'), 'A', 'stay on first');
        navigator.activate();

    });

    QUnit.asyncTest('navigate with keyboard', function(assert){
        var navigator;
        var $container = $('#qunit-fixture .nav-1');
        var $navigables = $container.find('.nav');
        var navigables = navigableDomElement.createFromDoms($navigables);

        QUnit.expect(16);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            elements : navigables,
            defaultPosition : navigables.length - 1
        }).on('right down', function(){
            this.next();
            assert.ok(true, 'go next');
        }).on('left up', function(){
            this.previous();
            assert.ok(true, 'go previous');
        }).on('activate', function(cursor){
            QUnit.start();
            assert.ok(true, 'activated');
            assert.equal(cursor.position, 1, 'activated position is ok');
            assert.ok(cursor.navigable.getElement() instanceof $, 'navigable element in cursor');
            assert.equal(cursor.navigable.getElement().data('id'), 'B', 'navigable element in cursor is correct');
        });

        navigator.focus();
        assert.equal($(document.activeElement).data('id'), 'C', 'default focus on last');

        $(document.activeElement).simulate('keydown', {keyCode: 40});//down
        assert.equal($(document.activeElement).data('id'), 'C', 'stay on last');

        $(document.activeElement).simulate('keydown', {keyCode: 38});//up
        assert.equal($(document.activeElement).data('id'), 'B', 'focus on second');

        $(document.activeElement).simulate('keydown', {keyCode: 37});//left
        assert.equal($(document.activeElement).data('id'), 'A', 'focus on first');

        $(document.activeElement).simulate('keydown', {keyCode: 38});//up
        assert.equal($(document.activeElement).data('id'), 'A', 'stay on first');

        $(document.activeElement).simulate('keydown', {keyCode: 39});//right
        assert.equal($(document.activeElement).data('id'), 'B', 'focus on second');

        $(document.activeElement).simulate('keydown', {keyCode: 13});//enter

    });

    QUnit.test('isFocused', function(assert){
        var navigator;
        var $container = $('#qunit-fixture .nav-1');
        var $navigables = $container.find('.nav');
        var navigables = navigableDomElement.createFromDoms($navigables);

        QUnit.expect(4);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({elements : navigables});

        assert.ok(!navigator.isFocused(), 'the navigator is not on focus');
        navigator.focus();
        assert.ok(navigator.isFocused(), 'the navigator is now on focus');
        navigator.blur();
        assert.ok(!navigator.isFocused(), 'the navigator is now blurred');
    });

    QUnit.asyncTest('loop', function(assert){
        var navigator;
        var $container = $('#qunit-fixture .nav-1');
        var $navigables = $container.find('.nav');
        var navigables = navigableDomElement.createFromDoms($navigables);

        QUnit.expect(10);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            loop : true,
            elements : navigables
        }).on('right down', function(){
            this.next();
        }).on('left up', function(){
            this.previous();
        }).on('activate', function(cursor){
            QUnit.start();
            assert.ok(true, 'activated');
            assert.equal(cursor.position, 2, 'activated position is ok');
            assert.ok(cursor.navigable.getElement() instanceof $, 'navigable element in cursor');
            assert.equal(cursor.navigable.getElement().data('id'), 'C', 'navigable element in cursor is correct');
        });

        navigator.focus();
        assert.equal($(document.activeElement).data('id'), 'A', 'focus on first');

        navigator.next();
        assert.equal($(document.activeElement).data('id'), 'B', 'focus on second');

        navigator.next();
        assert.equal($(document.activeElement).data('id'), 'C', 'focus on last');

        navigator.next();
        assert.equal($(document.activeElement).data('id'), 'A', 'loop to first');

        navigator.previous();
        assert.equal($(document.activeElement).data('id'), 'C', 'loop to last');

        navigator.activate();

    });

    QUnit.asyncTest('keep state off', function(assert){
        var navigator;
        var $container = $('#qunit-fixture .nav-1');
        var $navigables = $container.find('.nav');
        var navigables = navigableDomElement.createFromDoms($navigables);

        QUnit.expect(9);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            elements : navigables
        }).on('right down', function(){
            this.next();
        }).on('left up', function(){
            this.previous();
        }).on('activate', function(cursor){
            QUnit.start();
            assert.ok(true, 'activated');
            assert.equal(cursor.position, 0, 'activated position is ok');
            assert.ok(cursor.navigable.getElement() instanceof $, 'navigable element in cursor');
            assert.equal(cursor.navigable.getElement().data('id'), 'A', 'navigable element in cursor is correct');
        });

        navigator.focus();
        assert.equal($(document.activeElement).data('id'), 'A', 'focus on first');

        navigator.next();
        assert.equal($(document.activeElement).data('id'), 'B', 'focus on second');

        $(document.activeElement).blur();
        assert.equal(document.activeElement, $('body').get(0), 'focus out');

        navigator.focus();
        assert.equal($(document.activeElement).data('id'), 'A', 'focus on a a navigator with keep state on should reset the cursor');

        navigator.activate();
    });

    QUnit.asyncTest('keep state on', function(assert){
        var navigator;
        var $container = $('#qunit-fixture .nav-1');
        var $navigables = $container.find('.nav');
        var navigables = navigableDomElement.createFromDoms($navigables);

        QUnit.expect(9);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            keepState : true,
            elements : navigables
        }).on('right down', function(){
            this.next();
        }).on('left up', function(){
            this.previous();
        }).on('activate', function(cursor){
            QUnit.start();
            assert.ok(true, 'activated');
            assert.equal(cursor.position, 1, 'activated position is ok');
            assert.ok(cursor.navigable.getElement() instanceof $, 'navigable element in cursor');
            assert.equal(cursor.navigable.getElement().data('id'), 'B', 'navigable element in cursor is correct');
        });

        navigator.focus();
        assert.equal($(document.activeElement).data('id'), 'A', 'focus on first');

        navigator.next();
        assert.equal($(document.activeElement).data('id'), 'B', 'focus on second');

        $(document.activeElement).blur();
        assert.equal(document.activeElement, $('body').get(0), 'focus out');

        navigator.focus();
        assert.equal($(document.activeElement).data('id'), 'B', 'focus on a a navigator with keep state on should restore the cursor in memory');

        navigator.activate();
    });

    QUnit.asyncTest('activate with space', function(assert){
        var navigator;
        var $container = $('#qunit-fixture .nav-2');
        var $navigables = $container.find('.nav');
        var navigables = navigableDomElement.createFromDoms($navigables);

        var $textarea  = $('textarea', $container);
        QUnit.expect(7);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            keepState : true,
            elements : navigables
        })
        .on('right', function(){
            this.next();
        })
        .on('activate', function(cursor){
            assert.equal(cursor.position, 2, 'activated position is ok');
            assert.equal(cursor.navigable.getElement().data('id'), 'C', 'navigable element in cursor is correct');

            assert.equal($textarea.length, 1, 'The textarea element exists');

            this.on('blur', function(){
                assert.ok(false, 'Hitting the space key should not blur the active element');
            });

            $textarea.simulate('keydown', {keyCode: 32});//space-> should not blur
            $textarea.simulate('keyup', {keyCode: 32});//space

            setTimeout(function(){
                QUnit.start();
            }, 10);
        });

        navigator.focus();
        assert.equal($(document.activeElement).data('id'), 'A', 'focus on first');

        $(document.activeElement).simulate('keydown', {keyCode: 39});//right
        assert.equal($(document.activeElement).data('id'), 'B', 'focus on second');

        $(document.activeElement).simulate('keydown', {keyCode: 39});//right
        assert.equal($(document.activeElement).data('id'), 'C', 'focus on third');

        $(document.activeElement).simulate('keyup', {keyCode: 32});//space -> activate
    });


    QUnit.module('Group navigable element');

    QUnit.asyncTest('navigate between navigable areas', function(assert){
        var navigator;
        var $container = $('#qunit-fixture');
        var navigableAreas = [
            keyNavigator({
                id : 'A',
                replace : true,
                elements : navigableDomElement.createFromDoms($container.find('[data-id=A]')),
                group : $container.find('[data-id=A]')
            }),
            keyNavigator({
                id : 'B',
                replace : true,
                elements : navigableDomElement.createFromDoms($container.find('[data-id=B]')),
                group : $container.find('[data-id=B]')
            }),
            keyNavigator({
                id : 'C',
                replace : true,
                elements : navigableDomElement.createFromDoms($container.find('[data-id=C]')),
                group : $container.find('[data-id=C]')
            })
        ];

        var navigables = navigableGroupElement.createFromNavigators(navigableAreas);

        QUnit.expect(8);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            elements : navigables
        }).on('right down', function(){
            this.next();
        }).on('left up', function(){
            this.previous();
        }).on('activate', function(cursor){
            QUnit.start();
            assert.ok(true, 'activated');
            assert.equal(cursor.position, 2, 'activated position is ok');
            assert.ok(cursor.navigable.getElement() instanceof $, 'navigable element in cursor');
            assert.equal(cursor.navigable.getElement().data('id'), 'C', 'navigable element in cursor is correct');
        });

        navigator.focus();
        assert.equal($(document.activeElement).data('id'), 'A', 'focus on first');

        navigator.next();
        assert.equal($(document.activeElement).data('id'), 'B', 'focus on second');

        navigator.next();
        assert.equal($(document.activeElement).data('id'), 'C', 'focus on last');

        navigator.activate();
    });

});
