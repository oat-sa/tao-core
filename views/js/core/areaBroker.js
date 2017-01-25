/*
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
 * Copyright (c) 2016 (original work) Open Assessment Technlogies SA
 *
 */

/**
 * The area broker is a kind of areas hub, it gives the access to predefined areas.
 *
 *
 * @example
 * var broker = areaBroker(['content', 'panel'], $container);
 * broker.defineAreas({
 *    content : $('.content', $container),
 *    //...
 * });
 *
 * //then
 * var $content = broker.getArea('content');
 * var $content = broker.getContentArea();
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/promise'
], function ($, _, Promise) {
    'use strict';

    /**
     * Creates a new area broker.
     * @param {String[]} requireAreas - the list of required areas to map
     * @param {jQueryElement|HTMLElement|String} $container - the main container
     * @param {Object} mapping - keys are the area names, values are jQueryElement
     * @returns {broker} the broker
     * @throws {TypeError} without a valid container
     */
    return function areaBroker(requiredAreas, $container, mapping){

        var broker,
            areas,
            renderers = {},
            components = {};

        if(typeof $container === 'string' || $container instanceof HTMLElement){
            $container = $($container);
        }
        if(!$container || !$container.length){
            throw new TypeError('Please provide the areaBroker a container');
        }

        requiredAreas = requiredAreas || [];

        function defaultRenderer($container, components) {
            if (!components || !_.isObject(components)) {
                Promise.resolve();
            }

            _.forOwn(components, function ($component) {
                if(typeof $component === 'string' || $component instanceof HTMLElement){
                    $component = $($component);
                }
                // check component type ?
                $container.append($component);
            });
            return Promise.resolve(); // This is synchronous but we build an async api as some components might need it in the future
        }

        /**
         * The Area broker instance
         * @typedef broker
         */
        broker = {

            /**
             * Map the areas to elements.
             *
             * This method needs to be called before getting areas.
             * It's separated from the factory call in order to prepare the mapping in a separated step.
             *
             * @param {Object} mapping - keys are the area names, values are jQueryElement
             * @throws {TypeError} if the required areas are not part of the mapping
             */
            defineAreas : function defineAreas(mapping){
                var self = this,
                    keys, required;

                if(!_.isPlainObject(mapping)){
                    throw new TypeError('A mapping has the form of a plain object');
                }

                keys = _.keys(mapping);
                required = _.all(requiredAreas, function(val){
                    return _.contains(keys, val);
                });
                if(!required){
                    throw new TypeError('You have to define a mapping for at least : ' + requiredAreas.join(', '));
                }

                areas = mapping;

                // set default renderer for areas
                _.forOwn(areas, function (area) {
                    self.setRenderer(area, defaultRenderer);
                });
            },

            /**
             * Get the main container
             * @returns {jQueryElement} the container
             */
            getContainer : function getContainer(){
                return $container;
            },

            /**
             * Get the area element
             * @param {String} name - the area name
             * @returns {jQueryElement} the area element
             * @throws {Error} if the mapping hasn't been made previously
             */
            getArea : function getArea(name){
                if(!areas){
                    throw new Error('Sorry areas have not been defined yet!');
                }
                return areas[name];
            },

            getAllAreas : function getAllAreas() {
                if(!areas){
                    throw new Error('Sorry areas have not been defined yet!');
                }
                return areas;
            },

            /**
             * Adds a component to the given area
             * @param {String} areaName
             * @param {String} componentId - can be used by the rendered to reference the component
             * @param {String|HtmlElement|jQuery} $component
             * @throws {TypeError} in case of invalid parameters
             */
            addComponent : function addComponent(areaName, componentId, $component) {
                if (!areas && !areas[areaName]) {
                    throw new TypeError('There is no areas defined or no area named ' + areaName);
                }
                if (typeof componentId !== 'string') {
                    throw new TypeError('componentId should be a string');
                }
                if(typeof $component === 'string' || $component instanceof HTMLElement){
                    $component = $($component);
                }
                if(!$component || !$component.length){
                    throw new TypeError('Please provide the areaBroker a valid component');
                }

                if (!components[areaName]) {
                    components[areaName] = {};
                }
                components[areaName][componentId] = $component;
            },

            /**
             * Get a component of a specific area
             * @param {String} areaName
             * @param {String} componentId
             * @returns {jQuery} the component element
             */
            getComponent : function getComponent(areaName, componentId) {
                return components && components[areaName] && components[areaName][componentId];
            },

            setRenderer : function setRenderer(areaName, renderer) {
                if (!areas && !areas[areaName]) {
                    throw new Error('There is no areas defined or no area named ' + areaName);
                }
                if (!_.isFunction(renderer)) {
                    throw new Error('A renderer has to be a function');
                }
                renderers[areaName] = renderer;
            },

            hasRenderer : function hasRenderer(areaName) {
                return renderers && renderers[areaName];
            },

            render : function render(areaName) {
                if (this.hasRenderer(areaName)) {
                    return renderers[areaName](this.getArea(areaName), components[areaName]);
                } else {
                    return Promise.resolve();
                }
            }
        };

        broker.defineAreas(mapping);

        _.forEach(requiredAreas, function(area){
            var areaIdentifier = area[0].toUpperCase() + area.slice(1);
            broker['get' + areaIdentifier + 'Area']         = _.bind(_.partial(broker.getArea, area), broker);
            broker['add' + areaIdentifier + 'Component']    = _.bind(_.partial(broker.addComponent, area), broker);
            broker['set' + areaIdentifier + 'Renderer']     = _.bind(_.partial(broker.setRenderer, area), broker);
        });

        return broker;
    };

});
