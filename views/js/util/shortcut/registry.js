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
    'jquery',
    'lodash',
    'util/namespace'
], function ($, _, namespaceHelper) {
    'use strict';

    /**
     * All shortcuts have a namespace, this one is the default
     */
    var defaultNs = '*';

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
     * @param {Element|Window} target
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
     * @param {Element|Window} target
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

        //return special key map first, if not fallback to one of the other key identification methods
        return specialKeys[code] || key || character;
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
        };
    }

    /**
     * Gets a normalized shortcut command from a shortcut descriptor
     * @param {Object} descriptor
     * @returns {String}
     */
    function normalizeCommand(descriptor) {
        var key = translateKeys[descriptor.key] || descriptor.key;
        var parts = [];

        if (descriptor.ctrlKey) {
            parts.push('control');
        }
        if (descriptor.altKey) {
            parts.push('alt');
        }
        if (descriptor.shiftKey) {
            parts.push('shift');
        }
        if (descriptor.metaKey) {
            parts.push('meta');
        }

        if (descriptor.scrollDown) {
            parts.push('scrollDown');
        }
        if (descriptor.scrollUp) {
            parts.push('scrollUp');
        }

        if (descriptor.clickLeft) {
            parts.push('clickLeft');
        }
        if (descriptor.clickRight) {
            parts.push('clickRight');
        }
        if (descriptor.clickMiddle) {
            parts.push('clickMiddle');
        }
        if (descriptor.clickBack) {
            parts.push('clickBack');
        }
        if (descriptor.clickForward) {
            parts.push('clickForward');
        }

        if (key && parts.indexOf(key) < 0) {
            parts.push(key);
        }

        return parts.join('+');
    }

    /**
     * Parses a shortcut command and return a descriptor
     * @param {String} shortcut
     * @returns {Object}
     */
    function parseCommand(shortcut) {
        var parts = namespaceHelper.getName(shortcut).split('+');
        var descriptor = {
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
                descriptor[modifiers[part]] = true;
            } else if (part.indexOf('mouse') >= 0) {
                if (descriptor.keyboardInvolved) {
                    throw new Error('A shortcut cannot involve both mouse and regular keys!');
                }

                if (part.indexOf('scroll') >= 0) {
                    descriptor.mouseWheelInvolved = true;
                    descriptor.scrollUp = part.indexOf('up') >= 0;
                    descriptor.scrollDown = part.indexOf('down') >= 0;
                }

                if (part.indexOf('click') >= 0) {
                    descriptor.mouseClickInvolved = true;
                    descriptor.clickLeft = part.indexOf('left') >= 0;
                    descriptor.clickRight = part.indexOf('right') >= 0;
                    descriptor.clickMiddle = part.indexOf('middle') >= 0;
                    descriptor.clickBack = part.indexOf('back') >= 0;
                    descriptor.clickForward = part.indexOf('forward') >= 0;
                }
            } else {
                if (descriptor.mouseClickInvolved || descriptor.mouseWheelInvolved) {
                    throw new Error('A shortcut cannot involve both mouse and regular keys!');
                }

                descriptor.keyboardInvolved = true;
                descriptor.key = part;
            }
        });

        return descriptor;
    }

    /**
     * Builds shortcuts registry that manages shortcuts attached to a DOM element
     *
     * @param {Element|Window} root - The root element from which listen to events
     * @param {Object} [defaultOptions] - Default options applied to each shortcut
     * @param {Boolean} [defaultOptions.propagate] - Allow the event to be propagated after caught
     * @param {Boolean} [defaultOptions.prevent] - Prevent the default behavior of the shortcut
     * @param {Boolean} [defaultOptions.avoidInput] - Prevent the shortcut to be caught inside an input field
     * @param {Boolean} [defaultOptions.allowIn] - Always allows the shortcut if the event source is in the scope of
     * the provided CSS class, even if the shortcut is triggered from an input field.
     * @returns {shortcut}
     */
    return function shortcutFactory(root, defaultOptions) {
        var keyboardIsRegistered = false;
        var mouseClickIsRegistered = false;
        var mouseWheelIsRegistered = false;

        var keyboardCount = 0;
        var mouseClickCount = 0;
        var mouseWheelCount = 0;

        var shortcuts = {};
        var handlers = {};
        var states = {};

        /**
         * Gets the handlers for a shortcut
         * @param {String} command - the shortcut command
         * @param {String} namespace - the shortcut namespace
         * @returns {Function[]} the handlers
         */
        function getHandlers(command, namespace) {
            handlers[namespace] = handlers[namespace] || {};
            handlers[namespace][command] = handlers[namespace][command] || [];
            return handlers[namespace][command];
        }

        /**
         * Gets all the handlers related to a particular command, not regarding the namespace
         * @param {String} command - the shortcut command
         * @returns {Function[]} the handlers
         */
        function getCommandHandlers(command) {
            return _.reduce(handlers, function (acc, nsHandlers) {
                if (nsHandlers[command]) {
                    acc = acc.concat(nsHandlers[command]);
                }
                return acc;
            }, []);
        }

        /**
         * Clears the handles attached to a shortcut
         * @param {String} command - the shortcut command
         * @param {String} namespace - the shortcut namespace
         */
        function clearHandlers(command, namespace) {
            if (namespace && !command) {
                handlers[namespace] = {};
            } else {
                _.forEach(handlers, function (nsHandlers, ns) {
                    if (nsHandlers[command] && (namespace === defaultNs || namespace === ns)) {
                        nsHandlers[command] = [];
                    }
                });
            }
        }

        /**
         * Assign options to a shortcut
         * @param {Object} descriptor
         * @param {Object} options
         */
        function setOptions(descriptor, options) {
            descriptor.options = _.defaults(_.merge(descriptor.options || {}, options), defaultOptions);
        }

        /**
         * Registers a listener for the keyboard shortcuts
         */
        function registerKeyboard() {
            if (!keyboardIsRegistered) {
                registerEvent(root, 'keydown', onKeyboard);
                keyboardIsRegistered = true;
            }

            keyboardCount++;
        }

        /**
         * Removes the listener of the keyboard shortcuts
         */
        function unregisterKeyboard() {
            keyboardCount--;

            if (keyboardCount <= 0) {
                keyboardCount = 0;

                if (keyboardIsRegistered) {
                    unregisterEvent(root, 'keydown', onKeyboard);
                    keyboardIsRegistered = false;
                }
            }
        }

        /**
         * Registers a listener for the mouse click shortcuts
         */
        function registerMouseClick() {
            if (!mouseClickIsRegistered) {
                registerEvent(root, 'click', onMouseClick);
                mouseClickIsRegistered = true;
            }

            mouseClickCount++;
        }

        /**
         * Removes the listener of the mouse click shortcuts
         */
        function unregisterMouseClick() {
            mouseClickCount--;

            if (mouseClickCount <= 0) {
                mouseClickCount = 0;

                if (mouseClickIsRegistered) {
                    unregisterEvent(root, 'click', onMouseClick);
                    mouseClickIsRegistered = false;
                }
            }
        }

        /**
         * Registers a listener for the mouse wheel shortcuts
         */
        function registerMouseWheel() {
            if (!mouseWheelIsRegistered) {
                registerEvent(root, 'wheel', onMouseWheel);
                mouseWheelIsRegistered = true;
            }

            mouseWheelCount++;
        }

        /**
         * Removes the listener of the mouse wheel shortcuts
         */
        function unregisterMouseWheel() {
            mouseWheelCount--;

            if (mouseWheelCount <= 0) {
                mouseWheelCount = 0;

                if (mouseWheelIsRegistered) {
                    unregisterEvent(root, 'wheel', onMouseWheel);
                    mouseWheelIsRegistered = false;
                }
            }
        }

        /**
         * Registers a command shortcut and activates the right event listener
         * @param {String} command
         * @param {Object} descriptor
         */
        function registerCommand(command, descriptor) {
            shortcuts[command] = descriptor;

            if (descriptor.keyboardInvolved) {
                registerKeyboard();
            }
            if (descriptor.mouseClickInvolved) {
                registerMouseClick();
            }
            if (descriptor.mouseWheelInvolved) {
                registerMouseWheel();
            }
        }

        /**
         * Unregisters a command shortcut and removes the related event listener if not used anymore
         * @param {String} command
         */
        function unregisterCommand(command) {
            var descriptor = shortcuts[command];
            shortcuts[command] = null;

            if (descriptor) {
                if (descriptor.keyboardInvolved) {
                    unregisterKeyboard();
                }
                if (descriptor.mouseClickInvolved) {
                    unregisterMouseClick();
                }
                if (descriptor.mouseWheelInvolved) {
                    unregisterMouseWheel();
                }
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
            var command = normalizeCommand(descriptor);
            var shortcut = shortcuts[command];
            var shortcutHandlers;
            var $target;

            if (shortcut && !states.disabled) {
                if (shortcut.options.avoidInput === true) {
                    $target = $(event.target);
                    if ($target.closest('[type="text"],textarea').length) {
                        if (!shortcut.options.allowIn || !$target.closest(shortcut.options.allowIn).length) {
                            return;
                        }
                    }
                }
                if (shortcut.options.propagate === false) {
                    event.stopPropagation();
                }
                if (shortcut.options.prevent === true) {
                    event.preventDefault();
                }

                shortcutHandlers = getCommandHandlers(command);

                if (shortcutHandlers) {
                    _.forEach(shortcutHandlers, function (handler) {
                        handler(event, command);
                    });
                }
            }
        }

        if (root.jquery) {
            root = root.get(0);
        }

        /**
         * Defines the registry that manages the shortcuts attached to the provided DOM root
         * @typedef {shortcut}
         */
        return {
            /**
             * Sets options for a particular shortcut.
             * If the shortcut does not already exists, create it
             * @param {String} shortcut
             * @param {Object} [options]
             * @param {Boolean} [options.propagate] - Allow the event to be propagated after caught
             * @param {Boolean} [options.prevent] - Prevent the default behavior of the shortcut
             * @param {Boolean} [options.avoidInput] - Prevent the shortcut to be caught inside an input field
             * @param {Boolean} [options.allowIn] - Always allows the shortcut if the event source is in the scope of
             * the provided CSS class, even if the shortcut is triggered from an input field.
             * @returns {shortcut} this
             */
            set: function set(shortcut, options) {
                _.forEach(namespaceHelper.split(shortcut, true), function (normalized) {
                    var descriptor = parseCommand(normalized);
                    var command = normalizeCommand(descriptor);

                    setOptions(descriptor, options);
                    registerCommand(command, descriptor);
                });

                return this;
            },

            /**
             * Registers a new shortcut
             * @param {String} shortcut
             * @param {Function} handler
             * @param {Object} [options]
             * @param {Boolean} [options.propagate] - Allow the event to be propagated after caught
             * @param {Boolean} [options.prevent] - Prevent the default behavior of the shortcut
             * @param {Boolean} [options.avoidInput] - Prevent the shortcut to be caught inside an input field
             * @param {Boolean} [options.allowIn] - Always allows the shortcut if the event source is in the scope of
             * the provided CSS class, even if the shortcut is triggered from an input field.
             * @returns {shortcut} this
             */
            add: function add(shortcut, handler, options) {
                if (_.isFunction(handler)) {
                    _.forEach(namespaceHelper.split(shortcut, true), function (normalized) {
                        var namespace = namespaceHelper.getNamespace(normalized, defaultNs);
                        var descriptor = parseCommand(normalized);
                        var command = normalizeCommand(descriptor);

                        setOptions(descriptor, options);
                        registerCommand(command, descriptor);
                        getHandlers(command, namespace).push(handler);
                    });
                }

                return this;
            },

            /**
             * Removes a shortcut
             * @param {String} shortcut
             * @returns {shortcut} this
             */
            remove: function remove(shortcut) {
                _.forEach(namespaceHelper.split(shortcut, true), function (normalized) {
                    var namespace = namespaceHelper.getNamespace(normalized, defaultNs);
                    var descriptor = parseCommand(normalized);
                    var command = normalizeCommand(descriptor);

                    clearHandlers(command, namespace);

                    if (!getCommandHandlers(command).length) {
                        unregisterCommand(command);
                    }
                });

                return this;
            },

            /**
             * Checks if a particular shortcut is already registered
             * @param {String} shortcut
             * @returns {Boolean}
             */
            exists: function exists(shortcut) {
                var normalized = String(shortcut).trim().toLowerCase();
                var namespace = namespaceHelper.getNamespace(normalized, defaultNs);
                var descriptor = parseCommand(normalized);
                var command = normalizeCommand(descriptor);
                var shortcutExists = false;

                if (shortcuts[command]) {
                    shortcutExists = namespace === defaultNs || !!getHandlers(command, namespace).length;
                } else if (!command){
                    shortcutExists = !_.isEmpty(handlers[namespace]);
                }

                return shortcutExists;
            },

            /**
             * Removes all registered shortcuts
             * @returns {shortcut} this
             */
            clear: function clear() {
                shortcuts = {};
                handlers = {};
                keyboardCount = 0;
                mouseClickCount = 0;
                mouseWheelCount = 0;

                unregisterKeyboard();
                unregisterMouseClick();
                unregisterMouseWheel();

                return this;
            },

            /**
             * Checks a particular state
             * @param {String} name
             * @returns {Boolean}
             */
            getState: function getState(name) {
                return !!states[name];
            },

            /**
             * Sets a particular state
             * @param {String} name
             * @param {Boolean} state
             * @returns {shortcut}
             */
            setState: function setState(name, state) {
                states[name] = !!state;
                return this;
            },

            /**
             * Enables the shortcuts to be listened
             * @returns {shortcut}
             */
            enable: function enable() {
                this.setState('disabled', false);
                return this;
            },

            /**
             * Prevents the shortcuts to be listened
             * @returns {shortcut}
             */
            disable: function disable() {
                this.setState('disabled', true);
                return this;
            }
        };
    };
});
