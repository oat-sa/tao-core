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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/areaBroker'
], function ($, _, areaBrokerFactory) {
    'use strict';

    var areaBroker;

    /**
     * A counter utilised to generate the mock identifiers
     * @type {Number}
     */
    var mockId = 0;

    var classes = {
        areaBroker: 'area-broker-mock',
        area: 'test-area'
    };

    var defaultContainerSelector = '#qunit-fixture';

    /**
     * Builds and returns a new areaBroker with dedicated areas.
     * @param {String} config.id - area broker id, will be used as a class on container
     * @param {String[]} config.defaultAreas - mandatory areas to create
     * @param {jQuery} config.$brokerContainer - where to create the area broker - default to #qunit-fixture
     * @param {String[]} config.areas - A list of areas to create, or...
     * @param {Object} config.mapping - ... a list of already created areas
     * @returns {areaBroker} - Returns the new areaBroker
     */
    function areaBrokerMock(config) {
        var $areaBrokerDom,
            defaultAreas;

        config = config || {};

        $areaBrokerDom = $('<div />')
            .attr('id', 'area-broker-mock-' + (mockId++))
            .addClass(config.id || classes.areaBroker);

        defaultAreas = config.defaultAreas || [];

        // Create all areas from scratch
        if (!config.mapping) {
            config.mapping = {};
            if (!config.areas) {
                config.areas = defaultAreas;
            } else {
                config.areas = _.keys(_.merge(_.object(config.areas), _.object(defaultAreas)));
            }

            _.forEach(config.areas, function (areaId) {
                config.mapping[areaId] = $('<div />')
                    .addClass('test-area')
                    .addClass(areaId)
                    .appendTo($areaBrokerDom);
            });

        // Create only missing areas
        } else {
            _.union(defaultAreas, (config.areas || [])).forEach(function(areaId) {
                // create missing areas
                if (!config.mapping[areaId]) {
                    config.mapping[areaId] = $('<div />')
                        .addClass('test-area')
                        .addClass(areaId)
                        .appendTo($areaBrokerDom);
                }
            });
        }

        if (! config.$brokerContainer) {
            config.$brokerContainer = $(defaultContainerSelector);
        }
        config.$brokerContainer.append($areaBrokerDom);

        areaBroker = areaBrokerFactory(defaultAreas, $areaBrokerDom, config.mapping);

        return areaBroker;
    }

    return areaBrokerMock;
});
