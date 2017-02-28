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
 *
 *
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'ui/component',
    'ui/transformer'
], function (_, componentFactory, transformer) {
    'use strict';

    var defaultConfig = {
        x: 0,
        y: 0,
        position: 'absolute'
    };

    var placeableComponent = {
        /**
         * Center the container inside its parent container
         * @returns {movableComponent} chains
         *
         * @fires movableComponent#center
         */
        center: function place() {
            var $container = this.getContainer(),
                $element = this.getElement(),
                position;

            if (this.is('rendered') && !this.is('disabled')) {
                if ($container.length) {
                    this.moveTo(
                        $container.width() / 2 - $element.width() / 2,
                        $container.height() / 2 - $element.height() / 2
                    );
                    position = this.getPosition();

                    /**
                     * @event movableComponent#center the component has been centered
                     * @param {Number} x
                     * @param {Number} y
                     */
                    this.trigger('center', position.x, position.y);
                }
            }
            return this;
        },

        /**
         * Moves the component by the given offset
         * @param {Number} xOffset
         * @param {Number} yOffset
         * @returns {movableComponent} chains
         *
         * @fires movableComponent#move
         */
        moveBy: function moveBy(xOffset, yOffset) {
            var $element = this.getElement(),
                newX,
                newY;

            if (this.is('rendered') && !this.is('disabled')) {
                transformer.translate($element, xOffset, yOffset);

                newX = this.config.x + xOffset;
                newY = this.config.y + yOffset;

                $element.data('x', newX);
                $element.data('y', newY);

                /**
                 * @event movableComponent#move the component has moved
                 * @param {Number} x - the new x position
                 * @param {Number} y - the new y position
                 */
                this.trigger('move', newX, newY);
            }
            return this;
        },

        /**
         * Moves the component to the given position
         * @param {Number} x
         * @param {Number} y
         * @returns {movableComponent} chains
         *
         * @fires movableComponent#move
         */
        moveTo: function moveTo(x, y) {
            var $element = this.getElement(),
                xOffset,
                yOffset;

            if (this.is('rendered') && !this.is('disabled')) {
                xOffset = x - this.config.x;
                yOffset = y - this.config.y;

                transformer.translate($element, xOffset, yOffset);

                $element.data('x', x);
                $element.data('y', y);

                /**
                 * @event movableComponent#move the component has moved
                 * @param {Number} x - the new x position
                 * @param {Number} y - the new y position
                 */
                this.trigger('move', x, y);
            }
            return this;
        },

        resetPosition: function resetPosition() {
            var $element = this.getElement();

            if (this.is('rendered')) {
                $element.css({
                    top: this.config.y,
                    left: this.config.x,
                    position: 'absolute'
                });

                $element.data('x', this.config.x);
                $element.data('y', this.config.y);

                transformer.reset($element);

                /**
                 * @event movableComponent#move the component has moved
                 * @param {Number} x - the new x position
                 * @param {Number} y - the new y position
                 */
                this.trigger('move', this.config.x, this.config.y);
            }
        },

        /**
         * Gets the actual position of the component inside its container, with respect to the possible translation
         * @returns {Object}
         */
        getPosition: function getPosition() {
            var $element;
            var position = {
                x: this.config.x,
                y: this.config.y
            };

            if (this.is('rendered')) {
                $element = this.getElement();
                position = {
                    x: $element.data('x') || 0,
                    y: $element.data('y') || 0
                };
            }
            return position;

        }
    };

    return function makePlaceable(component) {
        _.assign(component, placeableComponent);

        return component
            .on('init', function() {
                this.config.x = this.config.x || defaultConfig.x;
                this.config.y = this.config.y || defaultConfig.y;
            })
            .on('render', function() {
                var $element = this.getElement();

                $element.css({
                    position: 'absolute'
                });

                this.resetPosition();
            });
    };

});
