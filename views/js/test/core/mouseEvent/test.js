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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery',
    'core/mouseEvent'
], function($, triggerMouseEvent){
    'use strict';

    QUnit.module('API');

    QUnit.test('mouseEvent api', function(assert){
        assert.ok(typeof triggerMouseEvent === 'function', "The mouseEvent module exposes a function");
    });


    QUnit.module('Events');

    QUnit.test('invalid event', function(assert){
        var element = document.getElementById('elem1');
        var eventName = 'custom';
        var eventOptions = {};

        assert.ok(! triggerMouseEvent(element, eventName, eventOptions), "returns false if event is invalid");
    });


    QUnit.asyncTest('jQuery', function(assert) {
        var element = $('#elem1');
        var eventName = 'click';
        var eventOptions = {
            bubbles: true,
            cancelable: true,
            screenX: 15,
            screenY: 25
        };

        element.on(eventName, function(event) {
            assert.ok(true, 'The event has been triggered');
            assert.strictEqual(event.target, element.get(0), 'The event has the right target');
            assert.strictEqual(event.type, eventName, 'The event has the right name');
            assert.strictEqual(event.bubbles, eventOptions.bubbles, 'The event has the right bubbles option');
            assert.strictEqual(event.cancelable, eventOptions.cancelable, 'The event has the right cancelable option');
            assert.strictEqual(event.screenX, eventOptions.screenX, 'The event has the right screenX option');
            assert.strictEqual(event.screenY, eventOptions.screenY, 'The event has the right screenY option');

            QUnit.start();
        });

        triggerMouseEvent(element.get(0), eventName, eventOptions);
    });


    QUnit.asyncTest('native', function(assert) {
        var element = document.getElementById('elem2');
        var eventName = 'dblclick';
        var eventOptions = {
            bubbles: true,
            cancelable: true,
            screenX: 15,
            screenY: 25
        };

        element.addEventListener(eventName, function(event) {
            assert.ok(true, 'The event has been triggered');
            assert.strictEqual(event.target, element, 'The event has the right target');
            assert.strictEqual(event.type, eventName, 'The event has the right name');
            assert.strictEqual(event.bubbles, eventOptions.bubbles, 'The event has the right bubbles option');
            assert.strictEqual(event.cancelable, eventOptions.cancelable, 'The event has the right cancelable option');
            assert.strictEqual(event.screenX, eventOptions.screenX, 'The event has the right screenX option');
            assert.strictEqual(event.screenY, eventOptions.screenY, 'The event has the right screenY option');

            QUnit.start();
        });

        triggerMouseEvent(element, eventName, eventOptions);
    });
});
