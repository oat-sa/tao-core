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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * Work with 2D transformations on any container
 *
 * @author dieter <dieter@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'lib/unmatrix/unmatrix'
], function ($, _, _unmatrix) {
    'use strict';

    var ns = 'transformer';

    var vendorPrefixes = ['webkit', 'ms'];

    /**
     * Figure out the vendor prefix, if any
     */
    var prefix = (function () {
        var i = vendorPrefixes.length,
            style = window.getComputedStyle(document.body, null);

        if (style.getPropertyValue('transform')) {
            return '';
        }
        while (i--) {
            if (style[vendorPrefixes[i] + 'Transform'] !== undefined) {
                return '-' + vendorPrefixes[i] + '-';
            }
        }
    }());


    /**
     * Normalize property keys to the same format unmatrix uses
     *
     * @param config
     * @returns {*}
     * @private
     */
    function _normalizeConfig(config) {
        var xy = ['translate', 'scale', 'skew'],
            i = xy.length;

        while (i--) {
            if (config[xy[i]]) {
                if (_.isArray(config[xy[i]]) && config[xy[i]].length === 2) {
                    config[xy[i] + 'X'] = config[xy[i]][0];
                    config[xy[i] + 'Y'] = config[xy[i]][1];
                }
                else {
                    config[xy[i] + 'X'] = config[xy[i]];
                    config[xy[i] + 'Y'] = config[xy[i]];
                }
                delete config[xy[i]];
            }
        }

        return config;
    }


    /**
     * Transform the container with the given configuration
     *
     * @param $container
     * @param {Object} config
     * @param {Number} [config.translate] 20 || [20,30], assumes px
     * @param {Number} [config.translateX] dto.
     * @param {Number} [config.translateY] dto.
     * @param {Number} [config.rotate] 20, assumes deg
     * @param {Number} [config.skew] dto.
     * @param {Number} [config.scale] 2 || [2,3], assumes 'times original size'
     * @param {Number} [config.scaleX] dto.
     * @param {Number} [config.scaleY] dto.
     */
    function _transform($container, config) {
        var cssObj = {},
            defaults = _unmatrix('none'),
            classNames = [];

        config = _normalizeConfig(config);

        // memorize old transformation
        if (!$container.data('oriTrans')) {
            $container.data('oriTrans', _unmatrix($container[0]));
        }

        cssObj[prefix + 'transform'] = '';

        // generate the style
        _.forIn(config, function (value, key) {

            // ignore values that aren't numeric
            if (_.isNaN(value)) {
                return true;
            }
            value = parseFloat(value);

            // add original transformation if applicable
            if ($container.data('oriTrans')[key] !== defaults[key]) {
                if (key.indexOf('scale') > -1) {
                    value *= $container.data('oriTrans')[key];
                }
                else {
                    value += $container.data('oriTrans')[key];
                }
            }

            if (undefined !== defaults[key] && value !== defaults[key]) {
                if (key.indexOf('translate') > -1) {
                    value += 'px';
                }
                else if (key === 'rotate' || key.indexOf('skew') > -1) {
                    value += 'deg';
                }
                cssObj[prefix + 'transform'] += key + '(' + value + ') ';
                classNames.push('transform-' + key.replace(/(X|Y)$/i, ''));
            }
        });

        cssObj[prefix + 'transform'] = $.trim(cssObj[prefix + 'transform']);

        $container.css(cssObj);
        $container.removeClass('transform-translate transform-rotate transform-skew transform-scale');
        $container.addClass(_.unique(classNames).join(' '));

        $container.trigger('transform.' + ns, { config: config });
    }


    /**
     * @exports
     */
    return {
        /**
         * Call the transform function directly
         */
        transform: _transform,

        /**
         *
         * @param $container
         * @param value
         */
        translate: function ($container, value) {
            this.transform($container, { translate: value });
        },

        /**
         *
         * @param $container
         * @param value
         */
        translateX: function ($container, value) {
            this.transform($container, { translateX: value });
        },

        /**
         *
         * @param $container
         * @param value
         */
        translateY: function ($container, value) {
            this.transform($container, { translateY: value });
        },

        /**
         *
         * @param $container
         * @param value
         */
        rotate: function ($container, value) {
            this.transform($container, { rotate: value });
        },

        /**
         *
         * @param $container
         * @param value
         */
        skew: function ($container, value) {
            this.transform($container, { skew: value });
        },

        /**
         *
         * @param $container
         * @param value
         */
        skewX: function ($container, value) {
            this.transform($container, { skewX: value });
        },

        /**
         *
         * @param $container
         * @param value
         */
        skewY: function ($container, value) {
            this.transform($container, { skewY: value });
        },

        /**
         *
         * @param $container
         * @param value
         */
        scale: function ($container, value) {
            this.transform($container, { scale: value });
        },

        /**
         *
         * @param $container
         * @param value
         */
        scaleX: function ($container, value) {
            this.transform($container, { scaleX: value });
        },

        /**
         *
         * @param $container
         * @param value
         */
        scaleY: function ($container, value) {
            this.transform($container, { scaleY: value });
        },

        /**
         * Remove all transformations added by this code
         *
         * @param $container
         * @param value
         */
        reset: function ($container) {
            var cssObj = {};

            // when called on a container that has never been transformed
            if (!$container.data('oriTrans')) {
                return;
            }

            // generate the style
            _.forIn($container.data('oriTrans'), function (value, key) {
                cssObj[prefix + 'transform'] += key + '(' + value + ') ';
            });
            $container.css(cssObj);
            $container.removeClass('transform-translate transform-rotate transform-skew transform-scale');
            $container.trigger('reset.' + ns, $container.data('oriTrans'));
        }
    };
});