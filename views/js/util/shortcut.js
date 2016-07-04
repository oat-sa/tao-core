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
 * Helper allowing to register shortcuts on the whole page.
 *
 * You may register keyboard and mouse shortcuts, like:
 *
 * ```
 * Ctrl+C
 * Shift+leftMouseClick
 * ```
 *
 * **Known limitations:**
 * Due to browser implementation, some shortcuts may not work.
 * For instance on a french keyboard layout, the shortcut "Shift+;" wont work as the browser
 * will return the result of the uppercase key that is "Shift+." in this case.
 * For alphanumeric keys the issue is prevented (this is the more needed feature).
 *
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'lodash'
], function (_) {
    'use strict';

    /**
     * Indicates a listener is installed for keyboard events
     * @type {Boolean}
     */
    var keyboardIsRegistered = false;

    /**
     * Indicates a listener is installed for mouse click events
     * @type {Boolean}
     */
    var mouseClickIsRegistered = false;

    /**
     * Indicates a listener is installed for mouse wheel events
     * @type {Boolean}
     */
    var mouseWheelIsRegistered = false;

    /**
     * Number of registered shortcuts involving the keyboard
     * @type {Number}
     */
    var keyboardCount = 0;

    /**
     * Number of registered shortcuts involving the mouse clicks
     * @type {Number}
     */
    var mouseClickCount = 0;

    /**
     * Number of registered shortcuts involving the mouse wheel
     * @type {Number}
     */
    var mouseWheelCount = 0;

    /**
     * Default options applied to each shortcut
     * @type {Object}
     */
    var defaultOptions = {
        propagate: false,
        prevent: true
    };

    /**
     * List of registered shortcuts (use the normalized name as a key)
     * @type {Object}
     */
    var registeredShortcuts = {};

    /**
     * Translation map from name of modifiers to event property
     * @type {Object}
     */
    var modifiers = {
        ctrl: 'ctrlKey',
        alt: 'altKey',
        option: 'altKey',
        shift: 'shiftKey',
        meta: 'metaKey',
        cmd: 'metaKey',
        win: 'metaKey'
    };

    /**
     * Translation map from normalized name of keys
     * @type {Object}
     */
    var translateKeys = {
        'escape': 'esc',
        'arrowdown': 'down',
        'arrowleft': 'left',
        'arrowright': 'right',
        'arrowup': 'up'
    };

    /**
     * List of special keys with their codes
     * @type {Object}
     */
    var specialKeys = {
        8: 'backspace',
        9: 'tab',
        13: 'enter',
        19: 'pause',
        20: 'capslock',
        27: 'esc',
        32: 'space',
        33: 'pageup',
        34: 'pagedown',
        35: 'end',
        36: 'home',
        37: 'left',
        38: 'up',
        39: 'right',
        40: 'down',
        45: 'insert',
        46: 'delete',
        91: 'meta',
        112: 'f1',
        113: 'f2',
        114: 'f3',
        115: 'f4',
        116: 'f5',
        117: 'f6',
        118: 'f7',
        119: 'f8',
        120: 'f9',
        121: 'f10',
        122: 'f11',
        123: 'f12',
        145: 'scrolllock',
        144: 'numlock'
    };

    /**
     * Registers an event handler on a particular element
     * @param {Element} target
     * @param {String} eventName
     * @param {Function} listener
     */
    function registerEvent(target, eventName, listener) {
        if (target.addEventListener) {
            target.addEventListener(eventName, listener, false);
        } else if (target.attachEvent) {
            target.attachEvent('on' + eventName, listener);
        } else {
            target['on' + eventName] = listener;
        }
    }

    /**
     * Removes an event handler from a particular element
     * @param {Element} target
     * @param {String} eventName
     * @param {Function} listener
     */
    function unregisterEvent(target, eventName, listener) {
        if (target.removeEventListener) {
            target.removeEventListener(eventName, listener, false);
        } else if (target.detachEvent) {
            target.detachEvent('on' + eventName, listener);
        } else {
            target['on' + eventName] = null;
        }
    }

    /**
     * Registers a listener for the keyboard shortcuts
     */
    function registerKeyboard() {
        if (!keyboardIsRegistered) {
            registerEvent(window, 'keydown', onKeyboard);
            keyboardIsRegistered = true;
        }
    }

    /**
     * Removes the listener of the keyboard shortcuts
     */
    function unregisterKeyboard() {
        if (keyboardIsRegistered) {
            unregisterEvent(window, 'keydown', onKeyboard);
            keyboardIsRegistered = false;
        }
    }

    /**
     * Registers a listener for the mouse click shortcuts
     */
    function registerMouseClick() {
        if (!mouseClickIsRegistered) {
            registerEvent(window, 'click', onMouseClick);
            mouseClickIsRegistered = true;
        }
    }

    /**
     * Removes the listener of the mouse click shortcuts
     */
    function unregisterMouseClick() {
        if (mouseClickIsRegistered) {
            unregisterEvent(window, 'click', onMouseClick);
            mouseClickIsRegistered = false;
        }
    }

    /**
     * Registers a listener for the mouse wheel shortcuts
     */
    function registerMouseWheel() {
        if (!mouseWheelIsRegistered) {
            registerEvent(window, 'wheel', onMouseWheel);
            mouseWheelIsRegistered = true;
        }
    }

    /**
     * Removes the listener of the mouse wheel shortcuts
     */
    function unregisterMouseWheel() {
        if (mouseWheelIsRegistered) {
            unregisterEvent(window, 'wheel', onMouseWheel);
            mouseWheelIsRegistered = false;
        }
    }

    /**
     * Reacts to a keyboard event
     * @param {KeyboardEvent} event
     */
    function onKeyboard(event) {
        processShortcut(event, {
            keyboardInvolved: true,
            ctrlKey: event.ctrlKey,
            altKey: event.altKey,
            shiftKey: event.shiftKey,
            metaKey: event.metaKey,
            key: getActualKey(event)
        });
    }

    /**
     * Reacts to a mouse click event
     * @param {MouseEvent} event
     */
    function onMouseClick(event) {
        processShortcut(event, _.merge({
            mouseClickInvolved: true,
            ctrlKey: event.ctrlKey,
            altKey: event.altKey,
            shiftKey: event.shiftKey,
            metaKey: event.metaKey
        }, getActualButton(event)));
    }

    /**
     * Reacts to a mouse wheel event
     * @param {WheelEvent} event
     */
    function onMouseWheel(event) {
        processShortcut(event, _.merge({
            mouseClickInvolved: true,
            ctrlKey: event.ctrlKey,
            altKey: event.altKey,
            shiftKey: event.shiftKey,
            metaKey: event.metaKey
        }, getActualScroll(event)));
    }

    /**
     * Process a shortcut based on its descriptor
     * @param {Event} event
     * @param {Object} descriptor
     */
    function processShortcut(event, descriptor) {
        var keystroke = normalizeShortcut(descriptor);
        var parsedShortcut = registeredShortcuts[keystroke];

        if (parsedShortcut) {
            if (!parsedShortcut.options.propagate) {
                event.stopPropagation();
            }
            if (parsedShortcut.options.prevent) {
                event.preventDefault();
            }
            parsedShortcut.handler(event, keystroke);
        }
    }

    /**
     * Gets the actual input key
     * @param {KeyboardEvent} event
     * @returns {String}
     */
    function getActualKey(event) {
        // Get the code of the key, used to identify special keys on browser that does not support the full KeyboardEvent API
        var code = event.which || event.keyCode;
        var character = code >= 32 ? String.fromCharCode(code).toLowerCase() : '';

        // Get the name of the key on browser that have a good support of the KeyboardEvent API
        var key = event.key && event.key.toLowerCase();

        // If the browser supports the KeyboardEvent API it may provide the result of the shortcut instead of the actual key.
        // For instance on Mac if you input "Alt+V" the key property will contain "◊"
        var keyName = event.code && event.code.toLowerCase();
        if (keyName) {
            if (keyName.indexOf('key') === 0) {
                // fix the result key only if the actual key name is not alpha (diff due to local layout)
                if (key < 'a' || key > 'z') {
                    if (character >= 'a' && character <= 'z') {
                        key = character;
                    }
                }
            } else if (keyName.indexOf('digit') === 0) {
                key = keyName.substr(5);
            }
        }

        return key || specialKeys[code] || character;
    }

    /**
     * Gets the pressed buttons
     * @param {MouseEvent} event
     * @return {Object}
     */
    function getActualButton(event) {
        var buttons = {
            clickLeft: false,
            clickRight: false,
            clickMiddle: false,
            clickBack: false,
            clickForward: false
        };

        if (event.buttons) {
            buttons.clickLeft = !!(event.buttons & 1);
            buttons.clickRight = !!(event.buttons & 2);
            buttons.clickMiddle = !!(event.buttons & 4);
            buttons.clickBack = !!(event.buttons & 8);
            buttons.clickForward = !!(event.buttons & 16);
        } else {
            switch (event.button) {
                case 0:
                    buttons.clickLeft = true;
                    break;

                case 1:
                    buttons.clickMiddle = true;
                    break;

                case 2:
                    buttons.clickRight = true;
                    break;

                case 3:
                    buttons.clickBack = true;
                    break;

                case 4:
                    buttons.clickForward = true;
                    break;
            }
        }

        return buttons;
    }

    /**
     * Gets the scroll direction
     * @param {WheelEvent} event
     * @return {Object}
     */
    function getActualScroll(event) {
        return {
            scrollUp: event.deltaY < 0,
            scrollDown: event.deltaY > 0
        }
    }

    /**
     * Gets a normalized name from a shortcut descriptor
     * @param {Object} descriptor
     * @returns {String}
     */
    function normalizeShortcut(descriptor) {
        var key = translateKeys[descriptor.key] || descriptor.key;
        var keystroke = [];

        if (descriptor.ctrlKey) {
            keystroke.push('control');
        }
        if (descriptor.altKey) {
            keystroke.push('alt');
        }
        if (descriptor.shiftKey) {
            keystroke.push('shift');
        }
        if (descriptor.metaKey) {
            keystroke.push('meta');
        }

        if (descriptor.scrollDown) {
            keystroke.push('scrollDown');
        }
        if (descriptor.scrollUp) {
            keystroke.push('scrollUp');
        }

        if (descriptor.clickLeft) {
            keystroke.push('clickLeft');
        }
        if (descriptor.clickRight) {
            keystroke.push('clickRight');
        }
        if (descriptor.clickMiddle) {
            keystroke.push('clickMiddle');
        }
        if (descriptor.clickBack) {
            keystroke.push('clickBack');
        }
        if (descriptor.clickForward) {
            keystroke.push('clickForward');
        }

        if (key && keystroke.indexOf(key) < 0) {
            keystroke.push(key);
        }

        return keystroke.join('+');
    }

    /**
     * Parses a shortcut and return a descriptor
     * @param {String} shortcut
     * @returns {Object}
     */
    function parseShortcut(shortcut) {
        var normalized = String(shortcut).trim().toLowerCase();
        var parts = normalized.split('+');
        var parsedShortcut = {
            keyboardInvolved: false,
            mouseClickInvolved: false,
            mouseWheelInvolved: false,
            ctrlKey: false,
            altKey: false,
            shiftKey: false,
            metaKey: false,
            key: null,
            scrollUp: null,
            scrollDown: null,
            clickLeft: null,
            clickRight: null,
            clickMiddle: null,
            clickBack: null,
            clickForward: null
        };

        _.forEach(parts, function (part) {
            if (modifiers[part]) {
                parsedShortcut[modifiers[part]] = true;
            } else if (part.indexOf('mouse') >= 0) {
                if (parsedShortcut.keyboardInvolved) {
                    throw new Error('A shortcut cannot involve both mouse and regular keys!');
                }

                if (part.indexOf('scroll') >= 0) {
                    parsedShortcut.mouseWheelInvolved = true;
                    parsedShortcut.scrollUp = part.indexOf('up') >= 0;
                    parsedShortcut.scrollDown = part.indexOf('down') >= 0;
                }

                if (part.indexOf('click') >= 0) {
                    parsedShortcut.mouseClickInvolved = true;
                    parsedShortcut.clickLeft = part.indexOf('left') >= 0;
                    parsedShortcut.clickRight = part.indexOf('right') >= 0;
                    parsedShortcut.clickMiddle = part.indexOf('middle') >= 0;
                    parsedShortcut.clickBack = part.indexOf('back') >= 0;
                    parsedShortcut.clickForward = part.indexOf('forward') >= 0;
                }
            } else {
                if (parsedShortcut.mouseClickInvolved || parsedShortcut.mouseWheelInvolved) {
                    throw new Error('A shortcut cannot involve both mouse and regular keys!');
                }

                parsedShortcut.keyboardInvolved = true;
                parsedShortcut.key = part;
            }
        });

        return parsedShortcut;
    }

    /**
     * The helper contains static methods to add/remove shortcuts on the whole page
     * @type {shortcut}
     */
    return {
        /**
         * Registers a new shortcut
         * @param {String} shortcut
         * @param {Function} handler
         * @param {Object} [options]
         * @param {Boolean} [options.propagate] - Allow the event to be propagated after caught (default: false)
         * @param {Boolean} [options.prevent] - Prevent the default behavior of the shortcut (default: true)
         * @returns {shortcut} this
         */
        add: function add(shortcut, handler, options) {
            var parsedShortcut = parseShortcut(shortcut);
            var keystroke = normalizeShortcut(parsedShortcut);

            // register the shortcut using its normalized name
            parsedShortcut.handler = handler;
            parsedShortcut.options = _.defaults(_.clone(options || {}), defaultOptions);
            registeredShortcuts[keystroke] = parsedShortcut;

            // activate the right event listener
            if (parsedShortcut.keyboardInvolved) {
                keyboardCount++;
                registerKeyboard();
            }
            if (parsedShortcut.mouseClickInvolved) {
                mouseClickCount++;
                registerMouseClick();
            }
            if (parsedShortcut.mouseWheelInvolved) {
                mouseWheelCount++;
                registerMouseWheel();
            }

            return this;
        },

        /**
         * Removes a shortcut
         * @param {String} shortcut
         * @returns {shortcut} this
         */
        remove: function remove(shortcut) {
            var parsedShortcut = parseShortcut(shortcut);
            var keystroke = normalizeShortcut(parsedShortcut);

            // retrieve the actual registered shortcut and remove it
            parsedShortcut = registeredShortcuts[keystroke];
            registeredShortcuts[keystroke] = null;

            // remove the related event listener if no more shortcuts are registered
            if (parsedShortcut) {
                if (parsedShortcut.keyboardInvolved) {
                    keyboardCount--;
                    if (keyboardCount <= 0) {
                        unregisterKeyboard();
                        keyboardCount = 0;
                    }
                }
                if (parsedShortcut.mouseClickInvolved) {
                    mouseClickCount--;
                    if (mouseClickCount <= 0) {
                        unregisterMouseClick();
                        mouseClickCount = 0;
                    }
                }
                if (parsedShortcut.mouseWheelInvolved) {
                    mouseWheelCount--;
                    if (mouseWheelCount <= 0) {
                        unregisterMouseWheel();
                        mouseWheelCount = 0;
                    }
                }
            }

            return this;
        },

        /**
         * Checks if a particular shortcut is already registered
         * @param {String} shortcut
         * @returns {Boolean}
         */
        exists: function exists(shortcut) {
            var parsedShortcut = parseShortcut(shortcut);
            var keystroke = normalizeShortcut(parsedShortcut);
            return !!registeredShortcuts[keystroke];
        },

        /**
         * Removes all registered shortcuts
         * @returns {shortcut} this
         */
        clear: function clear() {
            unregisterKeyboard();
            unregisterMouseClick();
            unregisterMouseWheel();

            registeredShortcuts = {};
            keyboardCount = 0;
            mouseClickCount = 0;
            mouseWheelCount = 0;

            return this;
        }
    };
});
