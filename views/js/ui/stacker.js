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
 * Helper to manage z-indexes within the same stacking context.
 * It can be used to ensure that a given element will be placed on top of the others.
 * It does not provide any way to define the stacking context, as there are many ways to do so,
 * with different implications on the rest of the layout. Prefer CSS for that.
 *
 * To share the same scope between modules, instanciate the component with the relevant scope id
 *
 * @example
 * stacker = stackerFactory('test-runner');
 *
 * // put on top
 * stacker.bringToFront($element);
 *
 * // put on top on mouse click
 * stacker.autoBringToFront($element);
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define(['jquery'], function($) {
    'use strict';

    var ns = '.stacker',
        indexes = {},
        increment = 10,
        zIndexStart = 1000,
        defaultScope = 'global';

    /**
     * Check that the given element is valid
     * @returns Boolean
     */
    function isElementValid($element) {
        return $element instanceof $ && $element.length;
    }

    /**
     * Intialise the scope if it does not exist yet
     */
    function initScope(scope) {
        if (! indexes[scope]) {
            indexes[scope] = zIndexStart;
        }
    }

    /**
     * Check if the given element z-index has already the maximum available value
     * @param {jQuery} $element
     * @param {String} scope
     * @returns {Boolean}
     */
    function isHighest($element, scope) {
        var elementIndex = parseInt($element.css('z-index'), 10);
        return elementIndex >= indexes[scope];
    }

    /**
     * @returns {Number} - the next available zIndex
     */
    function getNext(scope) {
        indexes[scope] += increment;
        return indexes[scope];
    }

    /**
     * @param {String} scope - an artificial context to scope the stacker
     * @returns {Object} - the stacker helper
     */
    return function stackerFactory(scope) {
        scope = scope || defaultScope;
        initScope(scope);

        return {
            /**
             * Set the z-index, on the given element, to the next available value
             * @param {jQuery} $element
             */
            bringToFront: function bringToFront($element) {
                if (isElementValid($element) && ! isHighest($element, scope)) {
                    $element.get(0).style.zIndex = getNext(scope);
                }
            },

            /**
             * Adds a mousedown listener on the given element, so it is automatically brought to front
             * as soon as the mouse click starts
             * @param {jQuery} $element
             */
            autoBringToFront: function autoBringToFront($element) {
                var self = this;

                if (isElementValid($element)) {
                    $element.off('mousedown' + ns);
                    $element.on('mousedown' + ns, function() {
                        self.bringToFront($element);
                    });
                }
            },

            /**
             * Reset the z-index of the given element
             * @param {jQuery} $element
             */
            reset: function reset($element) {
                if (isElementValid($element)) {
                    $element.get(0).style.zIndex = 'auto';
                }
            },


            /**
             * Returns index of the current scope
             */
            getCurrent: function getCurrent() {
                return indexes[scope];
            }
        };
    };
});