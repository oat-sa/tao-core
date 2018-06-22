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
 * Media editor
 * tools:
 *  - mediaSize - (change media size, responsive mode, sync width to heights, reset)
 *  - mediaAlignment - (position of the media)
 *  to be implemented:
 *  *- cropper
 *  *- change colors
 *  *- etc.
 */
define([
    'jquery',
    'lodash',
    'ui/component'
], function ($, _, component) {
    'use strict';

    /**
     * target - jQuery element with media $()
     * container - container to which an target is attached
     *
     * @type {{target: null, container: null}}
     * @private
     */
    var _defaults = {
        target: null,
        targetContainer: null,
        mediaSizer: true,
        mediaSizerControlPanelContainer: null,
        mediaAlignment: false,
        mediaAlignmentControlPanelContainer: null
    };

    /**
     * Creates media editor
     *
     * @param {Object} config
     * @returns {component|*}
     */
    return function mediaEditorFactory(config) {

        var _config;

        /**
         * Current component
         */
        var mediaEditorComponent = component();

        _config = _.defaults(config || {}, _defaults);
        mediaEditorComponent.init(_config);
        return mediaEditorComponent;
    };
});
