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
    'ui/component',
    'ui/mediaEditor/mediaHelper',
    'ui/mediaEditor/plugins/mediaDimension/mediaDimensionComponent'
], function ($, _, component, helper, mediaDimensionComponent) {
    'use strict';

    /**
     * target - jQuery element with media $()
     * container - container to which an target is attached
     *
     * @type {{$media: null, tools: {mediaDimension: {$container: null, active: boolean}}}}
     * @private
     */
    var _defaults = {
        $media: null,
        tools: {
            mediaDimension: {
                $container: null,
                active: false
            }
        }
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
        mediaEditorComponent
            .on('init', function () {
                if (this.getConfig().$media && this.getConfig().$media.length) {
                    helper.init(this.getConfig().$media, {}, function (prop) {
                        if (_config.tools.mediaDimension.active && _config.tools.mediaDimension.$container.length) {
                            mediaDimensionComponent(prop.getMedia())
                                .on('changed', function (media) {
                                    if (media.sizeProps.currentUtil === 'px') {
                                        _config.$media.css({
                                            width: media.sizeProps.px.current.width,
                                            height: media.sizeProps.px.current.height
                                        });
                                    } else {
                                        // percent
                                        _config.$media.css({
                                            height: '',
                                            width: media.sizeProps['%'].current.width + '%'
                                        });
                                    }
                                })
                                .render(_config.tools.mediaDimension.$container);
                        }
                    });
                }
            })
            .init(_config);
        return mediaEditorComponent;
    };
});
