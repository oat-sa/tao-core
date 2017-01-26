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
 * todo complete documentation

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
     * Default renderer. It simply appends all the registered components of an area, in the registration order, into the area container
     * @param {jQuery} $renderTo - where to render
     * @param {Array} allComponents - components to render
     * @returns {Promise}
     */
    function defaultRenderer($renderTo, allComponents) {
        if (allComponents && _.isArray(allComponents)) {
            allComponents.forEach(function (entry) {
                $renderTo.append(entry.$component);
            });
        }
    }

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
             * @param {Object} areasMapping - keys are the area names, values are jQueryElement
             * @throws {TypeError} if the required areas are not part of the mapping
             */
            defineAreas : function defineAreas(areasMapping){
                var self = this,
                    keys, required;

                if(!_.isPlainObject(areasMapping)){
                    throw new TypeError('A mapping has the form of a plain object');
                }

                keys = _.keys(areasMapping);
                required = _.all(requiredAreas, function(val){
                    return _.contains(keys, val);
                });
                if(!required){
                    throw new TypeError('You have to define a mapping for at least : ' + requiredAreas.join(', '));
                }

                areas = areasMapping;

                // set the default renderer for required areas
                requiredAreas.forEach(function (areaName) {
                    self.setRenderer(areaName, defaultRenderer);
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
                if(_.isEmpty(areas)){
                    throw new Error('Sorry areas have not been defined yet!');
                }
                return areas[name];
            },

            /**
             * Adds a component to the given area
             * @param {String} areaName
             * @param {String} componentId - can be used by the rendered to reference the component
             * @param {String|HtmlElement|jQuery} $component
             * @throws {TypeError} in case of invalid parameters
             */
            addComponent : function addComponent(areaName, componentId, $component) {
                if (!areas[areaName]) {
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

                if (_.find(components[areaName], { id: componentId })) {
                    throw new TypeError('This componentId has already been taken: ' + componentId);
                }

                if (!components[areaName]) {
                    components[areaName] = []; // we use an array to maintain insertion order
                }
                components[areaName].push({
                    id: componentId,
                    $component: $component
                });
            },

            /**
             * Get a component of a specific area
             * @param {String} areaName
             * @param {String} componentId
             * @returns {jQuery} the component element or undefined
             */
            getComponent : function getComponent(areaName, componentId) {
                var found = components[areaName] && _.find(components[areaName], { id: componentId });
                return found && found.$component;
            },

            /**
             * Override the default renderer for a given area
             * @param {String} areaName
             * @param {function} renderer - should return a Promise. Check the default renderer for the expected signature.
             */
            setRenderer : function setRenderer(areaName, renderer) {
                if (!areas[areaName]) {
                    throw new TypeError('There is no areas defined or no area named ' + areaName);
                }
                if (!_.isFunction(renderer)) {
                    throw new TypeError('A renderer has to be a function');
                }
                renderers[areaName] = renderer;
            },

            /**
             * Render an area with the corresponding renderer
             * @param {String} areaName
             * @returns {Promise}
             */
            render : function render(areaName) {
                if (this.hasRenderer(areaName)) {
                    // we wrap the render call into a Promise in case the registered function doesn't return one
                    return Promise.resolve(renderers[areaName](this.getArea(areaName), components[areaName]));
                } else {
                    return Promise.resolve();
                }
            },

            renderAll : function renderAll() {
                var self = this,
                    execStack = [];

                _.keys(areas).forEach(function (areaName) {
                    execStack.push(self.render(areaName));
                });

                return Promise.all(execStack);
            },

            /**
             * Check if a renderer is defined for the given area
             * @param {String} areaName
             * @returns {Boolean}
             */
            hasRenderer : function hasRenderer(areaName) {
                return renderers && _.isFunction(renderers[areaName]);
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
