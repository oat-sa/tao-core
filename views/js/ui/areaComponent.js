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
 * Component to be registered in the area broker
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'ui/component'
], function(_, componentFactory) {
    'use strict';

    var areaComponentApi = {
        /**
         * Set the elements that composes the area
         * @param allElements
         */
        setElements: function setElements(allElements) {
            this.elements = allElements;
        },

        /**
         * Returns the elements that compose the area
         * @returns {*}
         */
        getElements: function getElements() {
            return this.elements;
        }
    };

    return function areaComponentFactory(specs, defaults) {
        specs = _.defaults(specs || {}, areaComponentApi);

        return componentFactory(specs, defaults);
    };
});