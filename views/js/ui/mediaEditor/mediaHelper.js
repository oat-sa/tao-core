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
 */

define(['lodash'], function (_) {
    'use strict';

    /**
     * Default configuration
     * @type {{
     *   target: $(),
     *   showResponsiveToggle: boolean,
     *   sizeProps: {
     *     px: {
     *       natural: {
     *         width: number,
     *         height: number
     *       },
     *      current: {
     *        width: number,
     *        height: number
     *      }
     *    },
     *    "%": {
     *      natural: {
     *        width: number,
     *        height: null
     *      },
     *      current: {
     *        width: number,
     *        height: null
     *      }
     *    },
     *    ratio: {
     *      natural: number,
     *      current: number
     *    },
     *    currentUtil: string,
     *    containerWidth: number,
     *    sliders: {
     *      "%": {
     *        min: number,
     *        max: number,
     *        start: number
     *      },
     *      px: {
     *        min: number,
     *        max: number,
     *        start: number
     *      }
     *    }
     *  }
     * }}
     * @private
     */
    var _defaults = {
        target: null,
        showResponsiveToggle: true,
        sizeProps: {
            px: {
                natural: {
                    width: 100,
                    height: 100
                },
                current: {
                    width: 100,
                    height: 100
                }
            },
            '%': {
                natural: {
                    width: 100,
                    height: null
                },
                current: {
                    width: 100,
                    height: null
                }
            },
            ratio: {
                natural: 1,
                current: 1
            },
            currentUtil: '%',
            containerWidth: 400,
            slider: {
                min: 0,
                max: 100,
                start: 100
            }
        }
    };

    /**
     * Reloading image to get real size
     * @param srcPath string
     * @param next Function
     * @return {{width: null, height: null}}
     */
    var loadRealImageSize = function loadRealImageSize (srcPath, next) {
        var img = new Image();
        img.onload = function() {
            next({
                width: this.width,
                height: this.height
            });
        };
        img.src = srcPath;
    };

    return {
        media: null,
        getMedia: function getMedia() {
            return this.media;
        },
        init: function init($media, config, onInit) {
            var self = this;
            this.media = _.defaults(config || {}, _defaults);
            this.media.target = $media;
            loadRealImageSize($media.attr('src'), function (size) {

                self.media.sizeProps.px.natural = _.cloneDeep(size);
                self.media.sizeProps.px.current = _.cloneDeep(size);

                if (typeof onInit === 'function') {
                    onInit(self);
                }
            });
        }
    };
});
