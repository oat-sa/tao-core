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
 * The area broker is a kind of areas hub.
 * Tt gives the access to predefined areas and can also handle the rendering of those areas.
 *
 * @example
 * var broker = areaBroker(['content', 'panel'], $container);
 * broker.defineAreas({
 *    content : $('.content', $container),
 *    //...
 * });
 *
 * // then, you can either retrieve and use the area container directly...
 * var $content = broker.getArea('content');
 * var $content = broker.getContentArea();
 *
 * // ... or you can bind a component to an area
 * broker.setComponent('content', myComponent);
 *
 * // and use the component lifecycle methods to handle the rendering
 * broker.getContent().init();
 * broker.getContent().render();
 * broker.getContent().destroy();
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash'
], function ($, _) {
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
                var keys, required;

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
             * Set the component of the given area, that will be able to handle the rendering of the area
             * @param {String} areaName
             * @param {Object} component - an instance or extension of ui/component
             */
            setComponent : function setComponent(areaName, component) {
                if (!areas[areaName]) {
                    throw new TypeError('There is no areas defined or no area named ' + areaName);
                }
                if (!_.isObject(component)) {
                    throw new TypeError('A component has to be an object');
                }
                components[areaName] = component;

                // expose the component
                this['get' + areaName[0].toUpperCase() + areaName.slice(1)] = this.getComponent.bind(this, areaName);
            },

            /**
             * Returns the component for a given area
             * @param {String} areaName
             * @returns {Object}
             */
            getComponent : function getComponent(areaName){
                return components[areaName];
            }

        };

        broker.defineAreas(mapping);

        // define aliases for required areas
        _.forEach(requiredAreas, function(area){
            var areaIdentifier = area[0].toUpperCase() + area.slice(1);
            broker['get' + areaIdentifier + 'Area']      = _.bind(_.partial(broker.getArea, area), broker);
        });

        return broker;
    };

});
