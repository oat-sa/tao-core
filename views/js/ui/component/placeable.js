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
 * Here's how it works:
 * - default position is set by css properties top/left
 * - any movement is achieved with css transform properties translateX/Y
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

        _translate: function _translate(xOffset, yOffset) {
            var $element = this.getElement(),
                newX,
                newY;

            transformer.translate($element, xOffset, yOffset);

            // retrieving current translate values is a costly process (see ui/transformer & ui/unmatrix)
            // thus, we store them as custom attributes for later use, and especially when a relative transform will be needed (eg, .moveBy())
            $element.data('translateX', xOffset);
            $element.data('translateY', yOffset);

            // we also save current coordinates instead so we don't need to compute them each time they are needed
            newX = xOffset + this.config.x;
            newY = yOffset + this.config.y;
            $element.data('x', newX);
            $element.data('y', newY);

            /**
             * @event Placeable#move - the component has moved
             * @param {Number} x - the new x position
             * @param {Number} y - the new y position
             */
            this.trigger('move', newX, newY);

        },

        /**
         * Center the container inside its parent container
         * @returns {movableComponent} chains
         *
         * @fires movableComponent#center
         */
        center: function place() {
            var $container = this.getContainer(),
                $element = this.getElement(),
                centerX,
                centerY;

            if (this.is('rendered') && !this.is('disabled')) {
                if ($container.length) {
                    centerX = $container.width() / 2 - $element.width() / 2;
                    centerY = $container.height() / 2 - $element.height() / 2;

                    this.moveTo(centerX, centerY);

                    /**
                     * @event movableComponent#center the component has been centered
                     * @param {Number} x
                     * @param {Number} y
                     */
                    this.trigger('center', centerX, centerY);
                }
            }
            return this;
        },

        /**
         * Moves the component by the given offset, which is relative to the current position
         * @param {Number} xOffsetRelative
         * @param {Number} yOffsetRelative
         * @returns {movableComponent} chains
         *
         * @fires movableComponent#move
         */
        moveBy: function moveBy(xOffsetRelative, yOffsetRelative) {
            var $element = this.getElement(),
                xOffsetAbsolute,
                yOffsetAbsolute;

            if (this.is('rendered') && !this.is('disabled')) {
                xOffsetAbsolute = $element.data('translateX') + xOffsetRelative;
                yOffsetAbsolute = $element.data('translateY') + yOffsetRelative;

                this._translate(xOffsetAbsolute, yOffsetAbsolute);
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
            var xOffsetAbsolute,
                yOffsetAbsolute;

            if (this.is('rendered') && !this.is('disabled')) {
                xOffsetAbsolute = x - this.config.x;
                yOffsetAbsolute = y - this.config.y;

                this._translate(xOffsetAbsolute, yOffsetAbsolute);
            }
            return this;
        },

        resetPosition: function resetPosition() {
            var $element = this.getElement();

            if (this.is('rendered')) {
                // set default position
                $element.css({
                    top: this.config.y,
                    left: this.config.x
                });

                // reset translations
                this._translate(0, 0);
            }
        },

        /**
         * Gets the actual position of the component inside its container, with respect to the possible translation
         * @returns {Object}
         */
        getPosition: function getPosition() {
            var $element,
                position;

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

    function makePlaceable(component) {
        _.assign(component, placeableComponent);

        return component
            .off('.makePlaceable')
            .on('init.makePlaceable', function() {
                this.config.x = parseInt(this.config.x, 10) || defaultConfig.x;
                this.config.y = parseInt(this.config.y, 10) || defaultConfig.y;
            })
            .on('render.makePlaceable', function() {
                var $element = this.getElement();

                $element.css({
                    position: 'absolute'
                });

                this.resetPosition();
            });
    }

    makePlaceable.isPlaceable = function isPlaceable(component) {
        var api = ['center', 'moveBy', 'moveTo', 'getPosition', 'resetPosition'];

        return api.every(function(method) {
            return typeof component[method] === 'function';
        });
    };

    return makePlaceable;

});
