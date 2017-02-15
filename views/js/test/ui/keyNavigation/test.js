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
    'ui/keyNavigation/domNavigableElement',
    'ui/keyNavigation/groupNavigableElement',
], function($, _, keyNavigator, domNavigableElement, groupNavigableElement){
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
        var $container = $('#qunit-fixture');
        var $navigables = $container.find('.nav');
        var navigables = domNavigableElement.createFromJqueryContainer($navigables);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            id : 'bottom-toolbar',
            elements : navigables,
            default : navigables.length - 1
        }).on('activate', function(cursor){
            QUnit.start();
            assert.ok(true, 'activated');
            assert.equal(cursor.position, 2, 'activated position is ok');
            assert.ok(cursor.navigable.getElement() instanceof $, 'navigable element in cursor');
            assert.equal(cursor.navigable.getElement().data('id'), 'C', 'navigable element in cursor is correct');
        });

        navigator.focus();
        assert.equal($(document.activeElement).data('id'), 'C', 'focus on last');
        $(document.activeElement).trigger($.Event('keyup',{keyCode: 13}));
    });

    QUnit.asyncTest('navigate with API', function(assert){
        var navigator;
        var $container = $('#qunit-fixture');
        var $navigables = $container.find('.nav');
        var navigables = domNavigableElement.createFromJqueryContainer($navigables);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            id : 'bottom-toolbar',
            replace : true,
            group : $container,
            elements : navigables,
            default : navigables.length - 1
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
        assert.equal($(document.activeElement).data('id'), 'C', 'focus on last');
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
        var $container = $('#qunit-fixture');
        var $navigables = $container.find('.nav');
        var navigables = domNavigableElement.createFromJqueryContainer($navigables);

        QUnit.expect(16);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            id : 'bottom-toolbar',
            replace : true,
            group : $container,
            elements : navigables,
            default : navigables.length - 1
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
        assert.equal($(document.activeElement).data('id'), 'C', 'focus on last');

        $(document.activeElement).trigger($.Event('keydown',{keyCode: 40}));//down
        assert.equal($(document.activeElement).data('id'), 'C', 'stay on last');

        $(document.activeElement).trigger($.Event('keydown',{keyCode: 38}));//up
        assert.equal($(document.activeElement).data('id'), 'B', 'focus on second');

        $(document.activeElement).trigger($.Event('keydown',{keyCode: 37}));//left
        assert.equal($(document.activeElement).data('id'), 'A', 'focus on first');

        $(document.activeElement).trigger($.Event('keydown',{keyCode: 38}));//up
        assert.equal($(document.activeElement).data('id'), 'A', 'stay on first');

        $(document.activeElement).trigger($.Event('keydown',{keyCode: 39}));//right
        assert.equal($(document.activeElement).data('id'), 'B', 'focus on second');

        $(document.activeElement).trigger($.Event('keyup',{keyCode: 13}));//enter

    });

    QUnit.asyncTest('loop', function(assert){
        var navigator;
        var $container = $('#qunit-fixture');
        var $navigables = $container.find('.nav');
        var navigables = domNavigableElement.createFromJqueryContainer($navigables);

        QUnit.expect(10);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            id : 'bottom-toolbar',
            replace : true,
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
        var $container = $('#qunit-fixture');
        var $navigables = $container.find('.nav');
        var navigables = domNavigableElement.createFromJqueryContainer($navigables);

        QUnit.expect(9);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            id : 'bottom-toolbar',
            replace : true,
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
        var $container = $('#qunit-fixture');
        var $navigables = $container.find('.nav');
        var navigables = domNavigableElement.createFromJqueryContainer($navigables);

        QUnit.expect(9);

        assert.equal(navigables.length, 3, 'navigable element created');

        navigator = keyNavigator({
            id : 'bottom-toolbar',
            replace : true,
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

    QUnit.module('Group navigable element');



});
