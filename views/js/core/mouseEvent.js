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
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([], function () {
    'use strict';

    var dispatchEvent;
    var allowedEvents = [
        'click',
        'contextmenu',
        'dblclick',
        'mousedown',
        'mouseenter',
        'mouseleave',
        'mousemove',
        'mouseout',
        'mouseover',
        'mouseup',
        'show'
    ];

    /**
     * Creates an event (requires IE > 9)
     * @function createEvent
     * @param {String} eventName
     * @param {*} options
     */
    var createEvent = function createEvent(eventName, eventOptions) {
        var event;
        try {
            event = new MouseEvent(eventName, eventOptions);
        } catch (e) {
            event = document.createEvent('MouseEvents');
            event.initMouseEvent(
                eventName,
                eventOptions.bubbles     || false,
                eventOptions.cancelable  || false,
                eventOptions.view        || null,
                eventOptions.detail      || 0,
                eventOptions.screenX     || 0,
                eventOptions.screenY     || 0,
                eventOptions.clientX     || 0,
                eventOptions.clientY     || 0,
                eventOptions.ctrlKey     || false,
                eventOptions.altKey      || false,
                eventOptions.shiftKey    || false,
                eventOptions.metaKey     || false,
                eventOptions.button      || 0,
                eventOptions.relatedTarget || null
            );
        }
        return event;
    };

    /**
     * Dispatches an event
     * @function dispatchEvent
     * @param {HTMLElement} element
     * @param {String} eventName
     * @param {Event} event
     * @return {Boolean} Returns `true` if the event has been dispatched
     */
    if (document.dispatchEvent) {
        dispatchEvent = function dispatchEventUsingDispatchEvent(element, eventName, event) {
            if (element) {
                element.dispatchEvent(event);
                return true;
            }
            return false;
        };
    } else if (document.fireEvent) {
        dispatchEvent = function dispatchEventUsingFireEvent(element, eventName, event) {
            if (element) {
                element.fireEvent('on' + eventName, event);
                return true;
            }
            return false;
        };
    } else {
        dispatchEvent = function dispatchEventDummy() {
            return false;
        };
    }


    /**
     * Triggers a mouse event using native methods
     * @param {HTMLElement} element
     * @param {String} eventName
     * @param {*} eventOptions
     * @returns {Boolean} Returns true if the event has been successfully triggered
     */
    var triggerMouseEvent = function triggerMouseEvent(element, eventName, eventOptions) {
        if (allowedEvents.indexOf(eventName) === -1) {
            return false;
        }
        var event = createEvent(eventName, eventOptions);
        return dispatchEvent(element, eventName, event);
    };

    return triggerMouseEvent;
});
