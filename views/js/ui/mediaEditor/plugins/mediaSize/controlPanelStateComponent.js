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
 */


define([
    'lodash',
    'ui/component'
], function (_, component) {
    'use strict';

    /**
     * Size properties of the media
     * @typedef {Object} sizeProps
     * @property px {{
     *        natural: {
     *          width: number,
     *          height: number
     *        },
     *        current: {
     *          width: number,
     *          height: number
     *        }
     *      }}
     * @property '%' {{
     *        natural: {
     *          width: number,
     *          height: number
     *        },
     *        current: {
     *          width: number,
     *          height: number
     *        }
     *      }}
     * @property ratio {{
     *   natural: number,
     *   current: number
     * }}
     * @property currentUtil string
     */

    /**
     * Size properties of the media control panel
     * @typedef {Object} mediaSizeProps
     * @property responsive boolean
     * @property sizeProps sizeProps
     * @property originalSizeProps sizeProps
     * @property syncDimensions boolean
     * @property denyCustomRatio boolean
     * @property precision number
     * @property showReset boolean
     */

    /**
     * Configuration
     * @type {mediaSizeProps}
     * @private
     */
    var _config;

    /**
     * Default values
     * precision - precision for all calculations (0.00001)
     *
     * @type {{
     *    responsive: boolean,
     *    showSync: boolean,
     *    showReset: boolean,
     *    denyCustomRatio: boolean,
     *    width: number,
     *    height: number,
     *    minWidth: number,
     *    maxWidth: number,
     *    sizeProps: SizeProps,
     *    precision: number
     * }}
     * @private
     */
    var _defaults = {
        responsive: true,
        showSync: true,
        showReset: true,
        sizeProps: {},
        denyCustomRatio: false,
        width: 0,
        height: 0,
        minWidth: 0,
        maxWidth: 0,
        precision: 5
    };

    /**
     * Creates control panel state component
     *
     * @param {Object} config
     * @fires "changed" - on State changed
     *
     * @returns {component|*}
     */
    return function controlPanelStateFactory (config) {

        /**
         * Round a decimal value to n digits
         *
         * @param {number} value
         * @returns {number}
         * @private
         */
        var _round = function _round(value) {
            var factor = Math.pow(10, _config.precision);
            return Math.round(value * factor) / factor;
        };

        /**
         * Getting number from the Input
         * @returns {number}
         * @private
         */
        var _parseVal = function _parseVal(val) {
            if (typeof val === 'string') {
                val = parseFloat(val);
            }
            return _round(val);
        };

        /**
         * Re-calculate current ratio
         * change scenario: someone has typed height and width in pixels while syncing was off
         * whether current or natural ratio eventually will be used depends on options.denyCustomRatio
         * @returns {number}
         * @private
         */
        var _getActualRatio = function _getActualRatio() {
            var ratio;

            if (_config.sizeProps.px.current.width > 0 && _config.sizeProps.px.current.height > 0) {
                _config.sizeProps.ratio.current = _config.sizeProps.px.current.width / _config.sizeProps.px.current.height;
            }
            ratio = _config.denyCustomRatio ? _config.sizeProps.ratio.natural : _config.sizeProps.ratio.current;
            return ratio ? ratio : 1;
        };

        var stateControl = {
            /**
             * Set property of the control panel
             * @param key
             * @param val
             */
            setProp: function setProp(key, val) {
                _config[key] = val;
            },

            /**
             * Get control panel property
             * @param key
             */
            getProp: function getProp(key) {
                return _config.hasOwnProperty(key) ? _config[key] : null;
            },

            /**
             * Set property of the media
             * @param key
             * @param val
             */
            setSizeProp: function setSizeProp(key, val) {
                _config.sizeProps[key] = val;
            },

            /**
             * Check if responsive mode
             * @returns {boolean}
             */
            isResponsive: function isResponsive() {
                return (typeof _config.responsive !== 'undefined') ? !!_config.responsive : true;
            },

            isResetAllowed: function isResetAllowed() {
                return _config.showReset;
            },

            recalculateRatio: function recalculateRatio() {
                return _config.sizeProps.ratio.current =
                    _round(_config.sizeProps.px.current.width / _config.sizeProps.sizeProps.px.current.height);
            },

            /**
             * Value in the percent
             * @param val
             */
            percentChange: function percentChange(val) {
                val = _parseVal(val);
                // set current % value
                _config.sizeProps['%'].current.width = val;
                // set to % input
                // todo move upper _config.sizeProps['%'].width.val(val);
                // set to sliders
                // todo move upper this.getProp('$sliders')['%'].val(val);
                // todo move upper this.getProp('$sliders')['px'].val(val);

                // recalculate px width
                _config.sizeProps['px'].current.width =
                    _round( (_config.sizeProps['px'].natural.width * val / 100) * _config.sizeProps.ratio.current);
                // recalculate px height
                _config.sizeProps['px'].current.height = _round(_config.sizeProps['px'].natural.height * val / 100);

                this.trigger('changed');
            },

            /**
             * Width in pixels
             * @param val
             */
            widthChange: function widthChange(val) {
                var ratio = _getActualRatio();
                var prevPercent = _config.sizeProps['%'].current.width;
                var prevVal = _config.sizeProps.px.current.width;
                val = _parseVal(val);
                _config.sizeProps.px.current.width = val;
                // todo move upper _config.sizeProps['px'].width.val(val);

                // if sync
                if (this.getProp('syncDimensions')) {
                    // calculate height
                    _config.sizeProps.px.current.height = _round(val * ratio);
                    // set new height to the px input
                    // todo move upper this.getProp('$fields').px.width.val(_config.sizeProps.px.current.height);

                    // calculate percent
                    _config.sizeProps['%'].current.width = _round(prevPercent * val / prevVal);
                    // set to % input
                    // todo move upper _config.sizeProps['%'].width.val(val);
                } else {
                    this.recalculateRatio();
                }
                this.trigger('changed');
            },

            /**
             * Height in pixels
             * @param val
             */
            heightChange: function heightChange(val) {
                var ratio = _getActualRatio();
                var prevPercent = _config.sizeProps['%'].current.width;
                var prevVal = _config.sizeProps['px'].current.height;
                val = _parseVal(val);
                // set height
                _config.sizeProps['px'].current.height = val;
                // set height to px input
                // todo move upper _config.sizeProps['px'].height.val(val);

                // if sync
                if (this.getProp('syncDimensions')) {
                    // calculate width
                    _config.sizeProps.px.current.width = _round(val / ratio);
                    // set new width to the px input
                    // todo move upper this.getProp('$fields').px.width.val(_config.sizeProps.px.current.width);

                    // calculate percent
                    _config.sizeProps['%'].current.width = _round(prevPercent * val / prevVal);
                    // set to % input
                    // todo move upper _config.sizeProps['%'].width.val(val);
                } else {
                    this.recalculateRatio();
                }

                this.trigger('changed');
            }
        };

        var controlPanelStateComponent = component(stateControl);

        _config = _.defaults(config || {}, _defaults);
        if (!_config || !_config.hasOwnProperty('sizeProps') || _.isEmpty(_config.sizeProps)) {
            throw new Error('Control panel of the media editor is required sizeProps parameter');
        }
        _config.originalSizeProps = _.cloneDeep(_config.sizeProps);

        controlPanelStateComponent
            .init(_config);

        return controlPanelStateComponent;
    };
});
