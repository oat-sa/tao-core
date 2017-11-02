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
 * Add positioning capabilities to a component. This is needed for draggable and resizable behavior.
 * - default position is set by css properties top/left
 * - any movement is achieved with css transform properties translateX/Y
 *
 * @example
 * var component = componentFactory();
 * makePlaceable(component, { initialX: 150, initialY: 150 });
 *
 * component.center();
 * component.moveTo(50, 50);
 * component.moveBy(10, 10);
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'ui/transformer'
], function (_, transformer) {
    'use strict';

    var defaultConfig = {
        initialX: 0,
        initialY: 0
    };

    var positioningMode = 'absolute';

    var placeableComponent = {

        /**
         * Set the translation of the component
         * @param {Number} xOffsetAbsolute
         * @param {Number} yOffsetAbsolute
         *
         * @fires Component#move
         * @private
         */
        _translate: function _translate(xOffsetAbsolute, yOffsetAbsolute) {
            var $element = this.getElement();

            transformer.translateXY($element, xOffsetAbsolute, yOffsetAbsolute);

            // retrieving current translate values is a costly process (see ui/transformer and/or lib/unmatrix)
            // thus, we store them as custom attributes for later use, and especially when a relative transform will be needed (eg, .moveBy())
            this._translateX = xOffsetAbsolute;
            this._translateY = yOffsetAbsolute;

            // we also save current coordinates instead so we don't need to compute them each time they are needed
            this._x = xOffsetAbsolute + this.config.initialX;
            this._y = yOffsetAbsolute + this.config.initialY;

            /**
             * @event Component#move - the component has moved
             * @param {Number} newX
             * @param {Number} newY
             */
            this.trigger('move', this._x, this._y);
        },

        /**
         * Center the component inside its parent container
         * @returns {Component} chains
         *
         * @fires Component#center
         */
        center: function center() {
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
                     * @event Component#center the component has been centered
                     * @param {Number} centerX
                     * @param {Number} centerY
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
         * @returns {Component} chains
         */
        moveBy: function moveBy(xOffsetRelative, yOffsetRelative) {
            var xOffsetAbsolute,
                yOffsetAbsolute;

            if (this.is('rendered') && !this.is('disabled')) {
                xOffsetAbsolute = this._translateX + xOffsetRelative;
                yOffsetAbsolute = this._translateY + yOffsetRelative;

                this._translate(xOffsetAbsolute, yOffsetAbsolute);
            }
            return this;
        },

        /**
         * Moves the component to the given position
         * @param {Number} x
         * @param {Number} y
         * @returns {Component} chains
         */
        moveTo: function moveTo(x, y) {
            var xOffsetAbsolute,
                yOffsetAbsolute;

            if (this.is('rendered') && !this.is('disabled')) {
                xOffsetAbsolute = x - this.config.initialX;
                yOffsetAbsolute = y - this.config.initialY;

                this._translate(xOffsetAbsolute, yOffsetAbsolute);
            }
            return this;
        },

        /**
         * Moves the component to the given X position
         * @param {Number} x
         * @returns {Component} chains
         */
        moveToX: function moveToX(x) {
            return this.moveTo(x, this._y);
        },

        /**
         * Moves the component to the given Y position
         * @param {Number} y
         * @returns {Component} chains
         */
        moveToY: function moveToY(y) {
            return this.moveTo(this._x, y);
        },

        /**
         * Restore the default position of the component
         * @returns {Component} chains
         */
        resetPosition: function resetPosition() {
            var $element = this.getElement();

            if (this.is('rendered')) {
                // set default position
                $element.css({
                    left: this.config.initialX,
                    top: this.config.initialY
                });

                // reset translations
                this._translate(0, 0);
            }
            return this;
        },

        /**
         * Gets the actual position of the component inside its container,
         * with respect to the possible translation
         * @returns {Object}
         */
        getPosition: function getPosition() {
            var position;

            if (this.is('rendered')) {
                position = {
                    x: this._x || 0,
                    y: this._y || 0
                };
            }
            return position;
        }
    };

    /**
     * @param {Component} component - an instance of ui/component
     * @param {Object} config
     * @param {Number} config.initialX - x start position
     * @param {Number} config.initialY - y start position
     */
    function makePlaceable(component, config) {
        _.assign(component, placeableComponent);

        return component
            .off('.makePlaceable')
            .on('init.makePlaceable', function() {
                _.defaults(this.config, config || {}, defaultConfig);
            })
            .on('render.makePlaceable', function() {
                var $element = this.getElement();

                $element.css({
                    position: positioningMode
                });

                this.resetPosition();
            });
    }

    /**
     * Check that the given component implements the placeableComponent API
     * @param {Component} component - an instance of ui/component
     * @returns {boolean}
     */
    makePlaceable.isPlaceable = function isPlaceable(component) {
        return Object.keys(placeableComponent).every(function(method) {
            return typeof component[method] === 'function';
        });
    };

    return makePlaceable;
});
