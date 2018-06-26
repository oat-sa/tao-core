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

define([], function () {
    'use strict';

    /**
     * Round a decimal value to n digits
     *
     * @param {number} value
     * @param {number} precision
     * @returns {number}
     * @private
     */
    var _round = function _round(value, precision) {
        var factor = Math.pow(10, precision);
        return Math.round(value * factor) / factor;
    };

    /**
     * Getting number from the Input
     * @param val
     * @param precision
     * @returns {number}
     * @private
     */
    var _parseVal = function _parseVal(val, precision) {
        if (typeof val === 'string') {
            val = parseFloat(val);
        }
        if (!val) {
            val = 0;
        }
        return _round(val, precision);
    };

    /**
     * Re-calculate current ratio
     * change scenario: someone has typed height and width in pixels while syncing was off
     * whether current or natural ratio eventually will be used depends on options.denyCustomRatio
     * @param conf
     * @returns {number}
     * @private
     */
    var _getActualRatio = function _getActualRatio(conf) {
        var ratio;

        if (conf.sizeProps.px.current.width > 0 && conf.sizeProps.px.current.height > 0) {
            conf.sizeProps.ratio.current = _round(conf.sizeProps.px.current.width / conf.sizeProps.px.current.height, conf.precision);
        }
        ratio = conf.denyCustomRatio ? conf.sizeProps.ratio.natural : conf.sizeProps.ratio.current;
        return ratio ? ratio : 1;
    };

    /**
     *
     * @param conf
     * @param width
     * @private
     */
    var calculateByWidth = function calculateByWidth (conf, width) {
        var ratio = _getActualRatio(conf);
        var val = _parseVal(width, conf.precision);
        conf.sizeProps.px.current.width = val;
        if (!conf.syncDimensions) {
            _getActualRatio(conf);
        } else {
            conf.sizeProps.px.current.height = _round(val / ratio, conf.precision);
        }
        return conf;
    };

    /**
     *
     * @param conf
     * @param height
     * @private
     */
    var calculateByHeight = function calculateByHeight(conf, height) {
        var ratio = _getActualRatio(conf);
        var val = _parseVal(height, conf.precision);
        // set height
        conf.sizeProps.px.current.height = val;
        if (!conf.syncDimensions) {
            _getActualRatio(conf);
        } else {
            conf.sizeProps.px.current.width = _round(val * ratio, conf.precision);
        }
        return conf;
    };

    /**
     *
     * @param conf
     * @param percent
     * @return {*}
     */
    var setPercent = function getValidPercent(conf, percent) {
        percent = _parseVal(percent, conf.precision);
        if (percent < 0) {
            percent = 0;
        } else if (percent > 100) {
            percent = 100;
        }

        conf.sizeProps['%'].current.width = _round(percent, conf.precision);
        return conf;
    };

    return {
        applyDimensions: function applyDimensions (conf, dimensions) {

            conf.precision = !conf || !conf.hasOwnProperty('precision') ? 5 : parseInt(conf.precision);

            if (dimensions) {
                if (dimensions.hasOwnProperty('width')) {
                    conf = calculateByWidth(conf, dimensions.width);
                }
                if (dimensions.hasOwnProperty('height')) {
                    conf = calculateByHeight(conf, dimensions.height);
                }
                if (dimensions.hasOwnProperty('percent')) {
                    conf = setPercent(conf, dimensions.percent);
                }
            }
            return conf;
        }
    };
});
