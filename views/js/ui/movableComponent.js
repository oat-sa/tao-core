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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 */

/**
 * Creates a movable and resizable component
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'interact',
    'ui/component',
    'ui/component/draggable',
    'ui/component/resizable',
    'ui/component/stackable'
], function (_, interact, componentFactory, makeDraggable, makeResizable, makeStackable) {
    'use strict';

    /**
     * Some default values
     * @type {Object}
     */
    var defaultConfig = {
        initialX: 0,
        initialY: 0,
        width: 250,
        height: 100,
        minWidth: 75,
        minHeight: 25
    };

    /**
     * Creates a new movable component
     * @param {Object} specs The component API
     * @param {Object} defaults The default config assigned to all movable components
     * @param {jQuery|HTMLElement|String} [defaults.renderTo] - An optional container in which renders the component
     * @param {Boolean} [defaults.replace] - When the component is appended to its container, clears the place before
     * @param {Number} [defaults.width] - the initial width of the component
     * @param {Number} [defaults.height] - the intial height of the component
     * @param {Number} [defaults.minWidth] - the min width for resize
     * @param {Number} [defaults.minHeight] - the min height for resize
     * @param {Number} [defaults.maxWidth] - the max width for resize
     * @param {Number} [defaults.maxHeight] - the max height for resize
     * @param {Number} [defaults.initialX] - the initial position top absolute to the windows
     * @param {Number} [defaults.initialY] - the initial position left absolute to the windows
     * @param {String} [defaults.stackingScope] - in which scope to stack the component
     * @returns {Component} the component (uninitialized)
     */
    function movableComponentFactory(specs, defaults) {
        var component;

        defaults = _.defaults(defaults || {}, defaultConfig);

        component = componentFactory(specs, defaults);

        makeDraggable(component);
        makeResizable(component);
        makeStackable(component);

        component.on('render', function () {
            this.setSize(this.config.width, this.config.height)
                .center();
        });

        return component;
    }

    return movableComponentFactory;
});
