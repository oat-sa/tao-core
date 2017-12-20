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
 * Allow to align a component with reference DOM element
 *
 * @example
 * component.alignWith($element, { hPos: 'center', vPos: 'center' });
 * component.alignWith($element, { hPos: 'right', vPos: 'bottom' });
 * ...
 *
 * You can also specify the h/v origin of the alignment:
 *
 * component.alignWith($element, { hPos: 'left', hOrigin: 'left' });
 *                    REFERENCE_ELEMENT
 *                    COMPONENT
 *
 * component.alignWith($element, { hPos: 'left', hOrigin: 'center' });
 *                    REFERENCE_ELEMENT
 *                COMPONENT
 *
 * component.alignWith($element, { hPos: 'left', hOrigin: 'right' });
 *                    REFERENCE_ELEMENT
 *           COMPONENT
 * *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'interact',
    'ui/component/placeable'
], function (_, interact, makePlaceable) {
    'use strict';

    var defaultConfig = {};

    var alignDefaults = {
        hPos: 'center',
        vPos: 'center'
    };

    var alignableComponent = {

        /**
         * Place the component using another element as a reference position
         * @param {jQuery} $element - the reference element
         * @param {Object} [options]
         * @param {('left'|'center'|'right')} [options.hPos] - horizontal position relative to the reference element
         * @param {('left'|'center'|'right')} [options.hOrigin] - the origin of the transformation
         * @param {Number} [options.hOffset] - horizontal offset
         * @param {('top'|'center'|'bottom')} [options.vPos] - vertical position relative to the reference element
         * @param {('top'|'center'|'bottom')} [options.vOrigin] - the origin of the transformation
         * @param {Number} [options.vOffset] - vertical offset
         * @returns {Component} chains
         */
        alignWith: function alignWith($element, options) {
            var alignedCoords = this._getAlignedCoords($element, options);
            return this.moveTo(alignedCoords.x, alignedCoords.y);
        },

        /**
         * Place the component so it is horizontally aligned with a reference element
         * @param {jQuery} $element - the reference element
         * @param {('left'|'center'|'right')} [hPos] - horizontal position relative to the reference element
         * @param {('left'|'center'|'right')} [hOrigin] - the origin of the transformation
         * @param {Number} [hOffset] - horizontal offset
         * @returns {Component} chains
         */
        hAlignWith: function hAlignWith($element, hPos, hOrigin, hOffset) {
            var alignedCoords = this._getAlignedCoords($element, { hPos: hPos, hOrigin: hOrigin, hOffset: hOffset });
            return this.moveToX(alignedCoords.x);
        },

        /**
         * Place the component so it is vertically aligned with a reference element
         * @param {jQuery} $element - the reference element
         * @param {('top'|'center'|'bottom')} [vPos] - vertical position relative to the reference element
         * @param {('top'|'center'|'bottom')} [vOrigin] - the origin of the transformation
         * @param {Number} [vOffset] - vertical offset
         * @returns {Component} chains
         */
        vAlignWith: function vAlignWith($element, vPos, vOrigin, vOffset) {
            var alignedCoords = this._getAlignedCoords($element, { vPos: vPos, vOrigin: vOrigin, vOffset: vOffset });
            return this.moveToY(alignedCoords.y);
        },

        /**
         * Get the coordinates of the component so it is aligned with a reference element
         * @param {jQuery} $element - the reference element
         * @param {Object} [options]
         * @param {('left'|'center'|'right')} [options.hPos] - horizontal position relative to the reference element
         * @param {('left'|'center'|'right')} [options.hOrigin] - the origin of the transformation
         * @param {Number} [options.hOffset] - horizontal offset
         * @param {('top'|'center'|'bottom')} [options.vPos] - vertical position relative to the reference element
         * @param {('top'|'center'|'bottom')} [options.vOrigin] - the origin of the transformation
         * @param {Number} [options.vOffset] - vertical offset
         * @returns {x,y} - the aligned coordinates
         * @private
         */
        _getAlignedCoords: function _getAlignedCoords($element, options) {
            var $container = this.getContainer(),
                componentOuterSize,
                containerOffset,
                elementOffset,
                elementWidth,
                elementHeight,
                x, y,
                hPos, vPos,
                hOrigin, vOrigin;

            options = options || {};

            componentOuterSize = this.getOuterSize();
            containerOffset    = $container.offset();
            elementOffset      = $element.offset();
            elementWidth       = $element.outerWidth();
            elementHeight      = $element.outerHeight();

            hPos    = options.hPos || alignDefaults.hPos;
            vPos    = options.vPos || alignDefaults.vPos;
            hOrigin = options.hOrigin || this._getDefaultHOrigin(options.hPos);
            vOrigin = options.vOrigin || this._getDefaultVOrigin(options.vPos);

            x = elementOffset.left - containerOffset.left;
            y = elementOffset.top - containerOffset.top;

            // compute X
            switch(hPos) {
                case 'center':  { x += elementWidth / 2; break; }
                case 'right':   { x += elementWidth;     break; }
            }
            switch(hOrigin) {
                case 'center':  { x -= componentOuterSize.width / 2; break; }
                case 'right':   { x -= componentOuterSize.width;     break; }
            }
            x += options.hOffset || 0;

            // compute Y
            switch(vPos) {
                case 'center': { y += elementHeight / 2; break; }
                case 'bottom': { y += elementHeight;     break; }
            }
            switch(vOrigin) {
                case 'center': { y -= componentOuterSize.height / 2; break; }
                case 'bottom': { y -= componentOuterSize.height;     break; }
            }
            y += options.vOffset || 0;

            return {
                x: x,
                y: y
            };
        },

        /**
         * The default hOrigin changes according to the hPos value
         * - left => right
         *              REFERENCE_ELEMENT
         *     COMPONENT
         * - center => center
         *              REFERENCE_ELEMENT
         *                  COMPONENT
         * - right => left
         *              REFERENCE_ELEMENT
         *                               COMPONENT
         * @returns {('left'|'center'|'right')}
         * @private
         */
        _getDefaultHOrigin: function _getDefaultHOrigin(hPos) {
            var hOrigin;
            switch(hPos) {
                default:
                case 'center': { hOrigin = 'center'; break; }
                case 'left':   { hOrigin = 'right';  break; }
                case 'right':  { hOrigin = 'left';   break; }
            }
            return hOrigin;
        },

        /**
         * The default vOrigin changes according to the vPos value
         * - top => bottom
         *                               COMPONENT
         *              REFERENCE_ELEMENT
         * - center => center
         *              REFERENCE_ELEMENT COMPONENT
         * - bottom => top
         *              REFERENCE_ELEMENT
         *                               COMPONENT
         * @returns {('top'|'center'|'bottom')}
         * @private
         */
        _getDefaultVOrigin: function _getDefaultVOrigin(vPos) {
            var vOrigin;
            switch(vPos) {
                default:
                case 'center': { vOrigin = 'center';  break; }
                case 'top':    { vOrigin = 'bottom';  break; }
                case 'bottom': { vOrigin = 'top';     break; }
            }
            return vOrigin;
        }
    };

    /**
     * @param {Component} component - an instance of ui/component
     * @param {Object} config
     */
    return function makeAlignable(component, config) {

        _.assign(component, alignableComponent);

        if (! makePlaceable.isPlaceable(component)) {
            makePlaceable(component);
        }

        return component
            .off('.makeAlignable')
            .on('init.makeAlignable', function() {
                _.defaults(this.config, config || {}, defaultConfig);
            });
    };

});
