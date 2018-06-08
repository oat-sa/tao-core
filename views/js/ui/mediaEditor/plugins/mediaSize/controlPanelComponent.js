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
 * Control Panel for the media sizer
 *
 * Usage:
 * controlPanelComponent({
 *  sizeProps: sizeProps,
 * })
 * .on('render', function () {
 * })
 * .render($demoContainer);
 */
define([
    'ui/component',
    'tpl!ui/mediaEditor/plugins/mediaSize/tpl/controlPanel',
    'nouislider',
    'ui/tooltip'
], function (component, tpl) {
    'use strict';

    /**
     * Configuration instance for the current initialization
     * @private
     */
    var _config;

    /**
     * @typedef {Object} SizeProps
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
     */

    /**
     * Default values
     *
     * @type {{
     *    responsive: boolean,
     *    showSync: boolean,
     *    showReset: boolean,
     *
     *    width: number,
     *    height: number,
     *    minWidth: number,
     *    maxWidth: number,
     *    sizeProps: SizeProps
     * }}
     * @private
     */
    var _defaults = {
        responsive: true,
        showSync: true,
        showReset: true,
        sizeProps: null
    };

    /**
     * Creates control panel for mediaSizer plugin
     *
     * @param {Object} config
     * @param {Boolean} [config.responsive] - If media can be responsive
     * @param {Boolean} [config.showSync] - todo
     * @param {Boolean} [config.showReset] - todo
     * @fires "render" after the component rendering
     * @fires "destroy" after the component destroying
     *
     * @returns {component|*}
     */
    return function mediaSizeFactory (config) {

        var controlPanelComponent = component();

        /**
         * Toggle width/height synchronization
         *
         * @param $elt
         * @returns {*}
         * @private
         */
        var _initSyncBtn = function ($elt) {
            var $mediaSizer = $elt.find('.media-sizer'),
                self = this,
                $syncBtn = $elt.find('.media-sizer-sync');

            if(!_config.showSync) {
                $syncBtn.hide();
                $mediaSizer.addClass('media-sizer-sync-off');
            }
            // this stays intact even if hidden in case it will be
            // displayed from somewhere else
            $syncBtn.on('click', function () {
                $mediaSizer.toggleClass('media-sizer-synced');
                _config.syncDimensions = $mediaSizer.hasClass('media-sizer-synced');
                if (_config.syncDimensions) {
                    self._sync($elt, _config.$fields.px.width, 'blur');
                }
            });
            return $syncBtn;
        };


        /**
         * Button to reset the size to its original values
         *
         * @param $elt
         * @returns {*}
         * @private
         */
        var _initResetBtn = function($elt) {
            var $resetBtn = $elt.find('.media-sizer-reset');

            if(!_config.showReset) {
                $elt.find('.media-sizer').addClass('media-sizer-reset-off');
            }

            // this stays intact even if hidden in case it will be
            // displayed from somewhere else
            $resetBtn.on('click', function() {
                // this will take care of all other size changes
                _config.$fields.px.width.val(_config.originalSizeProps.px.current.width).trigger('sliderchange');
            });
            return $resetBtn;
        };


        /**
         * Blocks are the two different parts of the form (either width|height or size)
         *
         * @param $elt
         * @returns {{}}
         * @private
         */
        var _initBlocks = function ($elt) {
            var _blocks = {},
                $responsiveToggleField = $elt.find('.media-mode-switch'),
                self = this,
                _checkMode = function () {
                    if ($responsiveToggleField.is(':checked')) {
                        _blocks.px.hide();
                        _blocks['%'].show();
                        _config.sizeProps.currentUnit = '%';
                        if (_config.$fields && _config.$fields['%'].width.val() > _config.sizeProps.sliders['%'].max) {
                            _config.$fields['%'].width.val(_config.sizeProps.sliders['%'].max);
                            self._sync($elt, _config.$fields['%'].width, 'blur');
                        }
                    }
                    else {
                        _blocks['%'].hide();
                        _blocks.px.show();
                        _config.sizeProps.currentUnit = 'px';
                    }
                };

            if(!_config.showResponsiveToggle) {
                $elt.find('.media-sizer').addClass('media-sizer-responsivetoggle-off');
            }

            _(['px', '%']).forEach(function (unit) {
                _blocks[unit] = $elt.find('.media-sizer-' + (unit === 'px' ? 'pixel' : 'percent'));
                _blocks[unit].prop('unit', unit);
                _blocks[unit].find('input').data('unit', unit).after($('<span>', {
                    'class': 'unit-indicator',
                    text: unit
                }));
            });

            $responsiveToggleField.on('click', function () {
                _checkMode();
                $elt.trigger('responsiveswitch', [$responsiveToggleField.is(':checked')]);
                $elt.trigger('sizechange', self._publicArgs($elt, options));
            });

            //initialize it properly
            _checkMode();

            return _blocks;
        };

        /**
         * Initialize the two sliders, one based on pixels the other on percentage
         *
         * @returns {{}}
         * @private
         */
        var _initSliders = function () {
            var _sliders = {};

            _(_config.$blocks).forOwn(function ($block, unit) {
                _sliders[unit] = $block.find('.media-sizer-slider');
                _sliders[unit].prop('unit', unit);
                _sliders[unit].noUiSlider({
                    start: _config.sizeProps.sliders[unit].start,
                    range: {
                        'min': _config.sizeProps.sliders[unit].min,
                        'max': _config.sizeProps.sliders[unit].max
                    }
                })
                    .on('slide', function () {
                        var $slider = $(this),
                            _unit = $slider.prop('unit');

                        _config.$fields[_unit].width.val(_round($slider.val(), 0)).trigger('sliderchange');
                    });
            });

            return _sliders;
        };

        _config = _.defaults(config || {}, _defaults);

        controlPanelComponent
            .on('render', function () {
                var $tpl = $(tpl({
                    responsive: (typeof _config.responsive !== 'undefined') ? !!_config.responsive : true
                }));

                // _config.sizeProps = _getSizeProps($tpl);
                // _config.originalSizeProps = _.cloneDeep(_config.sizeProps);

                _config.syncDimensions = $tpl.find('.media-sizer').hasClass('media-sizer-synced');

                // _config.$blocks = _initBlocks($tpl);
                // _config.$fields = _initFields($tpl);
                _config.$sliders = _initSliders($tpl);
                _config.$syncBtn = _initSyncBtn($tpl);
                _config.$resetBtn = _initResetBtn($tpl);

                $tpl.appendTo(this.getContainer());
            })
            .init(_config);

        return controlPanelComponent;
    }
});
