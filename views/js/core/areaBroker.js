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
 * // ... or you can use the areaBroker rendering capabilities
 * broker.addElement('content', 'myButton', $myButton);
 * broker.addContentElement('myButton', $myButton);
 *
 * broker.render('content');
 * broker.renderAll();
 *
 * // you can override the default renderer if you need a specific layout for an area
 * function myRenderer($areaContainer, $allElements) {
 *     var $myButton = _.find($allElements, { id: 'myButton'});
 *     var $buttonWrapper = $('<div>', { class: 'my-fancy-button-wrapper' });
 *
 *     $areaContainer.append($buttonWrapper.append($myButton));
 * }
 *
 * broker.setRenderer('content', myRenderer);
 * broker.setContentRenderer(myRenderer);
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/promise',
    'ui/component'
], function ($, _, Promise) {
    'use strict';

    /**
     * Default renderer. It simply appends all the registered elements of an area, in the registration order, into the area container
     * @param {jQuery} $areaContainer - where to render
     * @param {Object[]} allElements - elements to render
     * @param {String} allElements[].id - id of the element
     * @param {jQuery} allElements[].$element - the element itself
     * @returns {Promise} - if async rendering is needed, otherwise undefined
     */
    function defaultRenderer($areaContainer, allElements) {
        if (allElements && _.isArray(allElements)) {
            allElements.forEach(function (entry) {
                $areaContainer.append(entry.$element);
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
            elements = {};

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

                // set the default renderer for all areas
                _.forOwn(areas, function (area, areaName) {
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
             * Adds a element to the given area
             * @param {String} areaName
             * @param {String} elementId - can be used by the rendered to reference the element
             * @param {String|HtmlElement|jQuery} $element
             * @throws {TypeError} in case of invalid parameters
             */
            addElement : function addElement(areaName, elementId, $element) {
                if (!areas[areaName]) {
                    throw new TypeError('There is no areas defined or no area named ' + areaName);
                }
                if (typeof elementId !== 'string') {
                    throw new TypeError('elementId should be a string');
                }
                if(typeof $element === 'string' || $element instanceof HTMLElement){
                    $element = $($element);
                }
                if(!$element || !$element.length){
                    throw new TypeError('Please provide the areaBroker a valid element');
                }

                if (_.find(elements[areaName], { id: elementId })) {
                    throw new TypeError('This elementId has already been taken: ' + elementId);
                }

                console.log('adding ' + areaName + '.' + elementId);
                if (!elements[areaName]) {
                    elements[areaName] = []; // we use an array to maintain insertion order
                }
                elements[areaName].push({
                    id: elementId,
                    $element: $element
                });
            },

            /**
             * Get a element of a specific area
             * @param {String} areaName
             * @param {String} elementId
             * @returns {jQuery} the element element or undefined
             */
            getElement : function getElement(areaName, elementId) {
                var found = elements[areaName] && _.find(elements[areaName], { id: elementId });
                return found && found.$element;
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
                var rendered;

                if (this.hasRenderer(areaName)) {
                    //debugger;
                    console.log('rendering ' + areaName);
                    if (elements[areaName]) {
                        console.log(elements[areaName].reduce(function(prev, curr) {
                            return { id: prev.id + ' ' + curr.id };
                        }).id);
                    }
                    rendered = renderers[areaName](this.getArea(areaName), elements[areaName]);
                }
                // we wrap the result into a Promise in case the registered renderer doesn't return one
                return Promise.resolve(rendered);
            },

            /**
             * Render all the areas
             * @returns {Promise}
             */
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

        // define aliases for required areas
        _.forEach(requiredAreas, function(area){
            var areaIdentifier = area[0].toUpperCase() + area.slice(1);
            broker['get' + areaIdentifier + 'Area']         = _.bind(_.partial(broker.getArea, area), broker);
            broker['add' + areaIdentifier + 'Element']    = _.bind(_.partial(broker.addElement, area), broker);
            broker['set' + areaIdentifier + 'Renderer']     = _.bind(_.partial(broker.setRenderer, area), broker);
        });

        return broker;
    };

});
