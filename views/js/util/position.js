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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * This util helps you to manage DOM elements positions.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([], function(){
    'use strict';

    return {

        /**
         * Check if an element is inside another, based on it's coords
         * (this is not a check if the element is a DOM children of the container).
         * The entire element rectanlge must be inside the container to return true.
         *
         * @param {HTMLElement} container - the container
         * @param {HTMLElement} element - the element to check against the container
         * @returns {Boolean*} or undefined if the parameters are incorrect, so check your return value type.
         */
        isInside : function isInside(container, element) {
            var containerCoords;
            var elementCoords;
            if(container instanceof HTMLElement && element instanceof HTMLElement){
                containerCoords = container.getBoundingClientRect();
                elementCoords   = element.getBoundingClientRect();

                if(typeof containerCoords === 'object' && typeof elementCoords === 'object'){

                    return elementCoords.top >= containerCoords.top       && elementCoords.top <= containerCoords.bottom &&
                           elementCoords.left >= containerCoords.left     && elementCoords.left <= containerCoords.right &&
                           elementCoords.bottom <= containerCoords.bottom && elementCoords.bottom >= containerCoords.top &&
                           elementCoords.right <= containerCoords.right   && elementCoords.right >= containerCoords.left;
                }
            }
        },

        /**
         * Check if an element is over another, based on it's top/left coords
         * (this is not a check if the element is a DOM children of the container).
         * The element top/left corner must be inside the container to return true.
         *
         * TODO support other corners
         *
         * @param {HTMLElement} container - the container
         * @param {HTMLElement} element - the element to check against the container
         * @returns {Boolean*} or undefined if the parameters are incorrect, so check your return value type.
         */
        isOver : function isInside(container, element) {
            var containerCoords;
            var elementCoords;
            if(container instanceof HTMLElement && element instanceof HTMLElement){
                containerCoords = container.getBoundingClientRect();
                elementCoords   = element.getBoundingClientRect();

                if(typeof containerCoords === 'object' && typeof elementCoords === 'object'){

                    return elementCoords.top >= containerCoords.top  && elementCoords.top <= containerCoords.bottom &&
                           elementCoords.left >= containerCoords.left && elementCoords.left <= containerCoords.right;
                }
            }
        }
    };
});
