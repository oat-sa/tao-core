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
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'interact',
    'ui/component/placeable'
], function (_, interact, makePlaceable) {
    'use strict';

    var resizableComponent = {

        _getCappedValue: function (value, min, max) {
            var capped = value;
            if (typeof (max) !== 'undefined') {
                capped = Math.min(capped, max);
            }
            if (typeof (min) !== 'undefined') {
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
         * @returns {movableComponent} chains
         *
         * @fires movableComponent#resize
         */
        resizeTo: function resizeTo(newWidth, newHeight, resizeFromLeft, resizeFromTop) {
            var $element,
                currentWidth,
                currentHeight,
                newX,
                newY,
                rightX,
                bottomY,
                shouldMove = false;

            if (this.is('rendered') && !this.is('disabled')) {
                $element = this.getElement();

                currentWidth = parseFloat($element.data('width')) || $element.width();
                currentHeight = parseFloat($element.data('height')) || $element.height();

                newWidth = this._getCappedValue(newWidth, this.config.minWidth, this.config.maxWidth);
                newHeight = this._getCappedValue(newHeight, this.config.minHeight, this.config.maxHeight);

                // make sure the component will stay right-aligned if resized from the left
                if (resizeFromLeft && (newWidth !== currentWidth)) {
                    rightX = $element.data('x') + currentWidth;
                    newX = rightX - newWidth;
                    shouldMove = true;
                }

                // make sure the component will stay bottom-aligned if resized from the top
                if (resizeFromTop && (newHeight !== currentHeight)) {
                    bottomY = $element.data('y') + currentHeight;
                    newY = bottomY - newHeight;
                    shouldMove = true;
                }

                // first we move the component to its new position, if needed
                if (shouldMove) {
                    this.moveTo(
                        newX || $element.data('x'),
                        newY || $element.data('y')
                    );
                }

                // then we resize it!
                this.setSize(newWidth, newHeight);

                $element.data('width', newWidth);
                $element.data('height', newHeight);

                /**
                 * @event movableComponent#resize the component has been resized
                 * @param {Number} width - the new width
                 * @param {Number} height - the new height
                 */
                this.trigger('resize', newWidth, newHeight);
            }
            return this;
        }
    };

    return function makeResizable(component) {
        _.assign(component, resizableComponent);

        if (! makePlaceable.isPlaceable(component)) {
            makePlaceable(component);
        }

        return component
            .off('.makeResizable')
            .on('render.makeResizable', function() {
                var self        = this,
                    $element    = this.getElement(),
                    element     = $element[0],
                    $container  = this.getContainer(),
                    container   = $container[0];

                interact(element)
                    .resizable({
                        autoScroll: true,
                        restrict: {
                            restriction: container
                        },
                        edges: { left: true, right: true, bottom: true, top: true }
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
