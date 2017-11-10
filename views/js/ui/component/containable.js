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
 * Make sure that, when being positioned, a component stays fully visible within a given container.
 * This is suitable for static positioning with placeable or alignable methods, not for dynamic user behavior (draggable or resizable).
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'interact',
    'ui/component/placeable'
], function (_, interact, makePlaceable) {
    'use strict';

    var ns = '.makeContainable';

    var defaultConfig = {};

    var containableComponent = {

        containIn: function containIn($container, options) {
            var self = this;

            this
                .off('move' + ns)
                .on('move' + ns, function() {
                    // unbind the present listener to avoid infinite loop
                    self.off('move' + ns);

                    // position component
                    self._containComponent($container, options);

                    // re-bind listener
                    self.containIn($container, options);
                });
            return this;
        },

        _containComponent: function _containComponent($container, options) {
            var position = this.getPosition(),
                size = this.getSize(),
                containerSize = {
                    width: $container.innerWidth(),
                    height: $container.innerHeight()
                };

            var newX, newY,
                paddingTop, paddingRight, paddingBottom, paddingLeft;

            options = options || {};

            paddingTop    = options.paddingTop    || options.padding || 0;
            paddingRight  = options.paddingRight  || options.padding || 0;
            paddingBottom = options.paddingBottom || options.padding || 0;
            paddingLeft   = options.paddingLeft   || options.padding || 0;

            if (position.x < 0) {
                newX = 0 + paddingLeft;
            } else if (position.x + size.width > containerSize.width) {
                newX = containerSize.width - size.width - paddingRight;
            } else {
                newX = position.x;
            }

            if (position.y < 0) {
                newY = 0 + paddingTop;
            } else if (position.y + size.height > containerSize.height) {
                newY = containerSize.height - size.height - paddingBottom;
            } else {
                newY = position.y;
            }

            this.moveTo(newX, newY);

            this.trigger('contained', newX, newY);
        }
    };

    /**
     * @param {Component} component - an instance of ui/component
     * @param {Object} config
     */
    return function makeContainable(component, config) {

        _.assign(component, containableComponent);

        if (! makePlaceable.isPlaceable(component)) {
            makePlaceable(component);
        }

        return component
            .off(ns)
            .on('init' + ns, function() {
                _.defaults(this.config, config || {}, defaultConfig);
            });
    };

});
