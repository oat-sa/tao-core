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
 * broker.getContent().init();
 * broker.getContent().render();
 *
 * broker.initAll();
 * broker.renderAll();
 *
 * // you can override the default rendered component if you need a specific layout for an area
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
    'ui/areaComponent'
], function ($, _, Promise, areaComponentFactory) {
    'use strict';


    function getDefaultComponent() {
        return areaComponentFactory()
            .on('render', defaultRenderer);
    }


    /**
     * Default renderer. It simply appends all the registered elements of an area, in the registration order, into the area container
     * @param {jQuery} $areaContainer - where to render
     * @param {Object[]} allElements - elements to render
     * @param {String} allElements[].id - id of the element
     * @param {jQuery} allElements[].$element - the element itself
     * @returns {Promise} - if async rendering is needed, otherwise undefined
     */
    function defaultRenderer($areaContainer) {
        var allElements = this.getElements();
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
            components = {},
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

                // set the default component for all areas
                _.forOwn(areas, function (area, areaName) {
                    self.setComponent(areaName, getDefaultComponent());
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
             * Override the default component for a given area.
             * Use a component to manage the rendering of the area.
             * @param {String} areaName
             * @param {Object} component - an instance of ui/areaComponent
             */
            setComponent : function setComponent(areaName, component) {
                if (!areas[areaName]) {
                    throw new TypeError('There is no areas defined or no area named ' + areaName);
                }
                if (!_.isObject(component)) {
                    throw new TypeError('A component has to be an object');
                }
                components[areaName] = component;

                // set a reference to the components elements on the component object
                if (!_.isArray(elements[areaName])) {
                    elements[areaName] = []; // we use an array to maintain element insertion order
                }
                component.setElements(elements[areaName]);
            },

            /**
             * Returns the component for a given area
             * @param {String} areaName
             * @returns {Object}
             */
            getComponent : function getComponent(areaName){
                return components[areaName];
            },

            /**
             * Adds a element to the given area.
             * An element is a jQuery element that will be made accessible to the component during the rendering process
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
             * Initialise all the areas
             */
            initAll : function initAll() {
                _.invoke(components, 'init');
            },

            /**
             * Render all the areas
             * @returns {Promise}
             */
            renderAll : function renderAll() {
                var self = this,
                    execStack = [];

                _.keys(areas).forEach(function (areaName) {
                    var $componentContainer = self.getArea(areaName);

                    if (components[areaName] && _.isFunction(components[areaName].render)) {
                        execStack.push(components[areaName].render($componentContainer));
                    }
                });

                // we use an async API even though the component rendering is not (yet) async.
                return Promise.all(execStack);
            }

            // todo: implement initAll
            // todo: implement destroyAll

        };

        broker.defineAreas(mapping);

        // define aliases for required areas
        _.forEach(requiredAreas, function(area){
            var areaIdentifier = area[0].toUpperCase() + area.slice(1);
            broker['get' + areaIdentifier]               = _.bind(_.partial(broker.getComponent, area), broker);
            broker['get' + areaIdentifier + 'Area']      = _.bind(_.partial(broker.getArea, area), broker);
            broker['add' + areaIdentifier + 'Element']   = _.bind(_.partial(broker.addElement, area), broker);
            broker['set' + areaIdentifier + 'Component'] = _.bind(_.partial(broker.setComponent, area), broker);
        });

        broker.initAll();

        return broker;
    };

});
