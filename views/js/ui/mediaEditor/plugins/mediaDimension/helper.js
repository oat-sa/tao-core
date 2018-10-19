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

define(['lib/gamp/gamp'], function (gamp) {
    'use strict';

    /**
     * Getting number from the Input
     * @param val
     * @param precision
     * @returns {number}
     * @private
     */
    var parseVal = function parseVal(val, precision) {
        if (typeof val === 'string') {
            val = parseFloat(val);
        }
        if (!val) {
            val = 0;
        }
        return gamp.round(val, precision);
    };

    /**
     * Re-calculate current ratio
     * change scenario: someone has typed height and width in pixels while syncing was off
     * whether current or natural ratio eventually will be used depends on options.denyCustomRatio
     * @param conf
     * @returns {number}
     * @private
     */
    var getActualRatio = function getActualRatio(conf) {
        var ratio;

        if (conf.sizeProps.px.current.width > 0 && conf.sizeProps.px.current.height > 0) {
            conf.sizeProps.ratio.current = gamp.round(conf.sizeProps.px.current.width / conf.sizeProps.px.current.height, conf.precision);
        }
        ratio = conf.denyCustomRatio ? conf.sizeProps.ratio.natural : conf.sizeProps.ratio.current;
        return ratio ? ratio : 1;
    };

    /**
     * Percent verification
     * @param val
     * @param conf
     * @return {*}
     */
    var applyNewPercent = function applyNewPercent (val, conf) {
        conf.sizeProps['%'].current.width = val;
        if (conf.sizeProps['%'].current.width > 100) {
            conf.sizeProps['%'].current.width = 100;
        }

        if (conf.sizeProps['%'].current.width < 1) {
            conf.sizeProps['%'].current.width = 1;
        }

        return conf;
    };

    /**
     * Calculates the values of the dependent fields by a given width
     * @param conf
     * @param width
     * @param maxWidth
     * @private
     */
    var calculateByWidth = function calculateByWidth (conf, width, maxWidth) {
        var ratio = getActualRatio(conf);
        var val = parseVal(width, conf.precision);
        conf.sizeProps.px.current.width = val;
        conf = applyNewPercent(gamp.round(val * 100 / maxWidth, conf.precision), conf);
        if (!conf.syncDimensions) {
            getActualRatio(conf);
        } else {
            conf.sizeProps.px.current.height = gamp.round(val / ratio, conf.precision);
        }
        return conf;
    };

    /**
     * Calculates the values of the dependent fields by a given height
     * @param conf
     * @param height
     * @param maxWidth
     * @private
     */
    var calculateByHeight = function calculateByHeight(conf, height, maxWidth) {
        var ratio = getActualRatio(conf);
        var val = parseVal(height, conf.precision);
        // set height
        conf.sizeProps.px.current.height = val;
        if (!conf.syncDimensions) {
            getActualRatio(conf);
        } else {
            conf.sizeProps.px.current.width = gamp.round(val * ratio, conf.precision);
            conf = applyNewPercent(gamp.round(val * 100 / maxWidth, conf.precision), conf);
        }
        return conf;
    };

    /**
     * Calculates the values of the dependent fields by a given percent
     * @param conf
     * @param percent
     * @param maxWidth
     * @return {*}
     */
    var setPercent = function setPercent(conf, percent, maxWidth) {
        percent = parseVal(percent, conf.precision);
        if (percent < 0) {
            percent = 0;
        } else if (percent > 100) {
            percent = 100;
        }

        conf = applyNewPercent(gamp.round(percent, conf.precision), conf);
        conf.sizeProps.ratio.current = conf.sizeProps.ratio.natural;
        // changing non-responsive mode accordingly
        conf.sizeProps.px.current.width =
            gamp.round( maxWidth * conf.sizeProps['%'].current.width / 100, conf.precision);
        conf.sizeProps.px.current.height =  gamp.round(conf.sizeProps.px.current.width / conf.sizeProps.ratio.natural, conf.precision);
        return conf;
    };

    /**
     * MediaDimension helper
     *
     * returns helper to control dimensions calculation
     */
    return {
        /**
         * Calculating current state of the media dimensions
         * @param conf
         * @param dimensions Object width|height|percent && mediaContainerWidth
         * @return {*}
         */
        applyDimensions: function applyDimensions (conf, dimensions) {
            conf.precision = !conf || !conf.hasOwnProperty('precision') ? 5 : parseInt(conf.precision, 10);
            if (dimensions) {
                if (dimensions.hasOwnProperty('width')) {
                    conf = calculateByWidth(conf, dimensions.width, dimensions.maxWidth);
                }
                if (dimensions.hasOwnProperty('height')) {
                    conf = calculateByHeight(conf, dimensions.height, dimensions.maxWidth);
                }
                if (dimensions.hasOwnProperty('percent')) {
                    conf = setPercent(conf, dimensions.percent, dimensions.maxWidth);
                }
            }
            return conf;
        },
        /**
         * Calculating precisions
         * @param val
         * @param precision
         * @return {*}
         */
        round: function round(val, precision) {
            return gamp.round(val, precision > 0 && precision < 100 ? precision : 5);
        },
        /**
         * Getting width of the media container
         * @param media
         * @return {*}
         */
        getMediaContainerWidth: function getMediaContainerWidth (media) {
            return media.$container.innerWidth();
        }
    };
});
