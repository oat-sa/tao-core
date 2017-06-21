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
 * This adds draggable behavior to a component.
 *
 * @example
 * var component = componentFactory();
 * makeDraggable(component);
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

    /**
     * @param {Component} component - an instance of ui/component
     * @param {Object} config
     * @param {jQuery|Element} config.dragRestriction - interact restriction property. See {@link http://interactjs.io/docs/restriction/#restriction}
     */
    return function makeDraggable(component, config) {
        if (! makePlaceable.isPlaceable(component)) {
            makePlaceable(component);
        }

        return component
            .off('.makeDraggable')
            .on('init.makeDraggable', function() {
                _.defaults(this.config, config || {});
            })
            .on('render.makeDraggable', function() {
                var self        = this,
                    $element    = this.getElement(),
                    element     = $element[0],
                    rootNode    = document.querySelector('html');

                if (! this.config.dragRestriction) {
                    this.config.dragRestriction = this.getContainer()[0];
                }

                interact(element)
                    .draggable({
                        autoScroll: true,
                        restrict: {
                            restriction: this.config.dragRestriction,
                            elementRect: {left: 0, right: 1, top: 0, bottom: 1}
                        },
                        onmove: function onMove(event) {
                            var xOffset = Math.round(event.dx),
                                yOffset = Math.round(event.dy);

                            self.moveBy(xOffset, yOffset);

                            /**
                             * @event Component#dragmove the component has been dragged
                             * @param {Number} xOffset
                             * @param {Number} yOffset
                             */
                            self.trigger('dragmove', xOffset, yOffset);
                        }
                    })
                    .on('dragstart', function () {
                        self.setState('moving', true);
                        self.trigger('dragstart');
                    })
                    .on('dragend', function () {
                        self.setState('moving', false);
                        self.trigger('dragend');
                    });


                //fix cursor issue with interact <= 1.2.6
                //cursor remains in moving/sizing by only clicking
                $element
                    .off('.makeDraggable')
                    .on('click.makeDraggable', function(){
                        _.delay(function(){
                            if(!self.is('sizing') && !self.is('moving') && rootNode){
                                rootNode.style.cursor = 'default';
                            }
                        }, 25);
                    });

            });
    };

});
