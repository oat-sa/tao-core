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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksander Zagovorychev <zagovorichev@gmail.com>
 */

/**
 * Controls media size
 */
define([
    'lodash',
    'ui/component'
], function (_, component) {
    'use strict';

    /**
     * Default values
     *
     * @type {{
     *   editableMediaEl: null,
     *   controlPanelEl: null,
     *   responsive: boolean,
     * }}
     * @private
     */
    var _defaults = {
        editableMedia: null,
        controlPanel: null,
        responsive: true
    };

    /**
     * Creates media sizer
     *
     * @param {Object} config
     * @param {Boolean} [config.responsive] - If media can be responsive
     * @param {Boolean} [config.editableMediaEl] - JQuery media element, <img>, <canvas>, <iframe>, <video>... anything
     * @param {Boolean} [config.controlPanelEl] - JQuery element, container for the control panel
     * @fires "render" after the media size component rendering
     * @fires "destroy" after the media size component destroying
     *
     * @returns {component|*}
     */
    return function mediaSizeFactory (config) {

        var mediaSizeComponent;

        config = _.defaults(config || {}, _defaults);

        mediaSizeComponent = component();

        mediaSizeComponent.init(config);

        return mediaSizeComponent;
    }
});
