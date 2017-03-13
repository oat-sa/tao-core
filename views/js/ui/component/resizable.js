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
 * Adds resizable behavior to a component.
 *
 * @example
 * var component = componentFactory();
 * makeResizable(component, { minWidth: 150, minHeight: 150, maxWidth: 500, maxHeight: 500 });
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'interact',
    'ui/component/placeable'
], function (_, interact, makePlaceable) {
    'use strict';

    var defaultConfig = {
        minWidth: 50,
        minHeight: 50,
        edges: { left: true, right: true, bottom: true, top: true }
    };

    var resizableComponent = {

        /**
         * Make sure the given value is within given boundaries. If not, set it to the closest boundary.
         * @param {Number} value
         * @param {Number} min - lower boundary
         * @param {Number} max - upper boundary
         * @returns {Number} The new value
         * @private
         */
        _getCappedValue: function (value, min, max) {
            var capped = value;
            if (! _.isUndefined(max) && ! _.isNull(max)) {
                capped = Math.min(capped, max);
            }
            if (! _.isUndefined(min) && ! _.isNull(min)) {
                capped = Math.max(capped, min);
            }
            return capped;
        },

        /**
         * Resize the mask (minimum constraints applies)
         * @param {Number} newWidth - the new width
         * @param {Number} newHeight - the new height
         * @param {Boolean} resizeFromLeft - if the left border has been dragged for the resize
         * @param {Boolean} resizeFromTop - if the bottom border has been dragged for the resize
         * @returns {Component} chains
         *
         * @fires Component#beforeresize
         * @fires Component#resize
         */
        resizeTo: function resizeTo(newWidth, newHeight, resizeFromLeft, resizeFromTop) {
            var currentSize,
                newX,
                newY,
                rightX,
                bottomY,
                position,
                shouldMove = false;

            if (this.is('rendered') && !this.is('disabled')) {
                /**
                 * @event Component#beforeresize the component is about to be resized (or not)
                 * @param {Number} width - the new expected width
                 * @param {Number} height - the new expected height
                 * @param {Boolean} resizeFromLeft - if resize happens from the left
                 * @param {Boolean} resizeFromTop - if resize happens from the top
                 */
                this.trigger('beforeresize', newWidth, newHeight, resizeFromLeft, resizeFromTop);

                currentSize = this.getSize();

                newWidth = this._getCappedValue(newWidth, this.config.minWidth, this.config.maxWidth);
                newHeight = this._getCappedValue(newHeight, this.config.minHeight, this.config.maxHeight);

                position = this.getPosition();

                // make sure the component will stay right-aligned if resized from the left
                if (resizeFromLeft && (newWidth !== currentSize.width)) {
                    rightX      = position.x + currentSize.width;
                    newX        = rightX - newWidth;
                    shouldMove  = true;
                }

                // make sure the component will stay bottom-aligned if resized from the top
                if (resizeFromTop && (newHeight !== currentSize.height)) {
                    bottomY     = position.y + currentSize.height;
                    newY        = bottomY - newHeight;
                    shouldMove  = true;
                }

                // We can now move the component to its new position, if needed...
                if (shouldMove) {
                    this.moveTo(
                        newX || position.x,
                        newY || position.y
                    );
                }

                // ... and then resize it!
                this.setSize(newWidth, newHeight);

                position = this.getPosition(); // update the position

                /**
                 * @event Component#resize the component has been resized
                 * @param {Number} width - the new width
                 * @param {Number} height - the new height
                 * @param {Boolean} resizeFromLeft - if resize happens from the left
                 * @param {Boolean} resizeFromTop - if resize happens from the top
                 * @param {Number} x - the new x position
                 * @param {Number} y - the new y position
                 */
                this.trigger('resize', newWidth, newHeight, resizeFromLeft, resizeFromTop, position.x, position.y);
            }
            return this;
        }
    };

    /**
     * @param {Component} component - an instance of ui/component
     * @param {Object} config
     * @param {Number} config.minWidth
     * @param {Number} config.minHeight
     * @param {Number} config.maxWidth
     * @param {Number} config.maxHeight
     * @param {jQuery|Element} config.resizeRestriction - interact restriction property. See {@link http://interactjs.io/docs/restriction/#restriction}
     * @param {Object} config.edges
     * @param {Object} config.edges.top - is resizing from the top allowed
     * @param {Object} config.edges.right - is resizing from the right allowed
     * @param {Object} config.edges.bottom - is resizing from the bottom allowed
     * @param {Object} config.edges.left - is resizing from the left allowed
     */
    return function makeResizable(component, config) {

        _.assign(component, resizableComponent);

        if (! makePlaceable.isPlaceable(component)) {
            makePlaceable(component);
        }

        return component
            .off('.makeResizable')
            .on('init.makeResizable', function() {
                _.defaults(this.config, config || {}, defaultConfig);
            })
            .on('render.makeResizable', function() {
                var self        = this,
                    $element    = this.getElement(),
                    element     = $element[0];

                if (! this.config.resizeRestriction) {
                    this.config.resizeRestriction = this.getContainer()[0];
                }

                interact(element)
                    .resizable({
                        autoScroll: true,
                        restrict: {
                            restriction: this.config.resizeRestriction
                        },
                        edges: this.config.edges
                    })
                    .on('resizemove', function (event) {
                        self.resizeTo(
                            event.rect.width,
                            event.rect.height,
                            event.edges.left,
                            event.edges.top
                        );
                    })
                    .on('resizestart', function () {
                        self.setState('sizing', true);
                        self.trigger('resizestart');
                    })
                    .on('resizeend', function () {
                        self.setState('sizing', false);
                        self.trigger('resizeend');
                    });
            });
    };

});
