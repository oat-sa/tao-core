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
    'ui/transformer'
], function (_, interact, component, transformer) {
    'use strict';

    /**
     * Some default values
     * @type {Object}
     */
    var defaultConfig = {
        x: 0,
        y: 0,
        width: 250,
        height: 100,
        minWidth: 75,
        minHeight: 25
    };

    /**
     * Defines a movableComponent
     * @typedef {Object} movableComponent
     */
    var movableComponent = {

        /**
         * Place the container against the parent container (at the center/middle)
         * @returns {movableComponent} chains
         */
        place: function place() {
            var $container = this.getContainer();
            var $element = this.getElement();
            if (this.is('rendered') && !this.is('disabled')) {
                if ($container.length) {
                    $element.css({
                        left: $container.width() / 2 - $element.width() / 2,
                        top: $container.height() / 2 - $element.height() / 2
                    });
                }
            }
            return this;
        },

        /**
         * Moves the mask to the given position
         * @param {Number} x - the new x position
         * @param {Number} y - the new y position
         * @returns {movableComponent} chains
         *
         * @fires movableComponent#move
         */
        moveTo: function moveTo(x, y) {
            var $element = this.getElement();
            if (this.is('rendered') && !this.is('disabled')) {
                this.config.x = this.config.x + x;
                this.config.y = this.config.y + y;

                transformer.translate($element, this.config.x, this.config.y);

                /**
                 * @event movableComponent#move the component has moved
                 * @param {Number} x - the new x position
                 * @param {Number} y - the new y position
                 */
                this.trigger('move', this.config.x, this.config.y);
            }
            return this;
        },

        /**
         * Resize the mask (minimum constraints applies)
         * @param {Number} width - the new width
         * @param {Number} height - the new height
         * @returns {movableComponent} chains
         *
         * @fires movableComponent#resize
         */
        resize: function resize(width, height) {
            if (this.is('rendered') && !this.is('disabled')) {
                if (this.config.maxWidth) {
                    width = Math.min(width, this.config.maxWidth);
                }
                if (this.config.maxHeight) {
                    height = Math.min(height, this.config.maxHeight);
                }
                this.setSize(
                    Math.max(width, this.config.minWidth),
                    Math.max(height, this.config.minHeight)
                );

                /**
                 * @event movableComponent#resize the component has been resized
                 * @param {Number} width - the new width
                 * @param {Number} height - the new height
                 */
                this.trigger('resize', this.config.width, this.config.height);
            }
            return this;
        },

        /**
         * Gets the actual position of the component inside its container, with respect to the possible translation
         * @returns {Object}
         */
        getPosition: function getPosition() {
            var $element;
            var position = {
                top: this.config.y,
                left: this.config.x
            };

            if (this.is('rendered')) {
                $element = this.getElement();
                position.top += parseFloat($element.css('top'));
                position.left += parseFloat($element.css('left'));
            }

            return position;
        }
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
     * @param {Number} [defaults.x] - the initial position top absolute to the windows
     * @param {Number} [defaults.y] - the initial position left absolute to the windows
     * @returns {movableComponent} the component (uninitialized)
     */
    function movableComponentFactory(specs, defaults) {

        defaults = _.defaults(defaults || {}, defaultConfig);
        specs = _.defaults(specs || {}, movableComponent);

        return component(specs, defaults).on('render', function () {
            var self = this;
            var $element = this.getElement();
            var element = $element[0];
            var $container = this.getContainer();
            var container = $container[0];

            this.setSize(this.config.width, this.config.height)
                .place();
            if (this.config.x !== 0 || this.config.y !== 0) {
                this.moveTo(0, 0);
            }

            interact(element)
                .draggable({
                    autoScroll: true,
                    restrict: {
                        restriction: container,
                        elementRect: {left: 0, right: 1, top: 0, bottom: 1}
                    },
                    onmove: function onMove(event) {
                        self.moveTo(event.dx, event.dy);
                    }
                })
                .resizable({
                    autoScroll: true,
                    restrict: {
                        restriction: container
                    },
                    edges: {left: true, right: true, bottom: true, top: true}
                })
                .on('resizemove', function (event) {
                    self.resize(event.rect.width, event.rect.height);
                    self.moveTo(event.deltaRect.left, event.deltaRect.top);
                })
                .on('dragstart', function () {
                    self.setState('moving', true);
                })
                .on('dragend', function () {
                    self.setState('moving', false);
                })
                .on('resizestart', function () {
                    self.setState('sizing', true);
                })
                .on('resizeend', function () {
                    self.setState('sizing', false);
                });
        });
    }

    return movableComponentFactory;
});
