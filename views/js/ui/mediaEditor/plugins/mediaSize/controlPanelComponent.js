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
     * @property currentUtil string
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
         * Round a decimal value to n digits
         *
         * @param {number} value
         * @param {int} precision
         * @returns {number}
         * @private
         */
        function _round(value, precision) {
            var factor = Math.pow(10, precision);
            return Math.round(value * factor) / factor;
        }

        /**
         * Returns width, height, target element and the reset button
         * It's meant to be used when triggering an event
         *
         * @param $elt
         * @param options
         * @returns {{}}
         * @private
         */
        var _publicArgs = function _publicArgs($elt, options) {
            var params = _getValues();
            params.$target = options.target || $();
            params.$resetBtn = options.$resetBtn;
            return params;
        };

        /**
         * Retrieve current size values in current unit
         *
         * @param $elt
         * @returns {{}}
         * @private
         */
        var _getValues = function _getValues() {

            var attr = {};

            _.forOwn(_config.sizeProps[_config.sizeProps.currentUnit].current, function (value, dimension) {
                if (_.isNull(value)) {
                    value = '';
                }
                else {
                    value = _round(value, 0).toString();
                }
                if (_config.sizeProps.currentUnit === '%' && value !== '') {
                    value += _config.sizeProps.currentUnit;
                }
                attr[dimension] = value;
            });
            return attr;
        };

        /**
         * Toggle width/height synchronization
         *
         * @param $elt
         * @returns {*}
         * @private
         */
        var _initSyncBtn = function ($elt) {
            var $mediaSizer = $elt.find('.media-sizer'),
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
                    _sync($elt, _config.$fields.px.width, 'blur');
                }
            });
            return $syncBtn;
        };

        /**
         * Synchronize all parameters
         *
         * @param $elt
         * @param $field
         * @param eventType
         * @private
         */
        var _sync = function _sync($elt, $field, eventType) {
            var unit = $field.prop('unit'),
                dimension = $field.prop('dimension'),
                value = parseFloat($field.val()),
                heightValue,
                ratio,
                otherBlockUnit,
                otherBlockWidthValue,
                otherBlockHeightValue,
                currentValues;

            eventType = eventType === 'sliderchange' ? 'sliderEvent' : 'fieldEvent';

            // invalid entries
            if (isNaN(value)) {
                return;
            }

            // Re-calculate current ratio
            // change scenario: someone has typed height and width in pixels while syncing was off
            // whether current or natural ratio eventually will be used depends on options.denyCustomRatio
            if (_config.sizeProps.px.current.width > 0 && _config.sizeProps.px.current.height > 0) {
                _config.sizeProps.ratio.current = _config.sizeProps.px.current.width / _config.sizeProps.px.current.height;
            }
            ratio = _config.denyCustomRatio ? _config.sizeProps.ratio.natural : _config.sizeProps.ratio.current;
            ratio = ratio ? ratio : 1;

            // There is only one scenario where dimension != width: manual input of the height in px
            // this is treated here separately because then we just need to deal with widths below
            if (dimension === 'height' && unit === 'px') {
                _config.sizeProps.px.current.height = value;
                if (_config.syncDimensions) {
                    _config.sizeProps.px.current.width = value * ratio;
                    _config.sizeProps.ratio.current = _config.sizeProps.px.current.width / _config.sizeProps.px.current.height;
                    _config.$fields.px.width.val(_round(_config.sizeProps.px.current.width, 0));

                    // now all values can be set to the width since width entry is now the only scenario
                    value = parseFloat(_config.$fields.px.width.val());
                }
                else {
                    _config.sizeProps['%'].current.height = null;
                    // update medium
                    if (_config.applyToMedium) {
                        currentValues = this._getValues($elt);
                        _config.target.attr('width', currentValues.width);
                        _config.target.attr('height', currentValues.height);
                    }
                    $elt.trigger('sizechange', _publicArgs($elt, _config));
                    return;
                }
            }
            // *** as of here we can be sure that the dimension is 'width' *** //

            // remember that heightValue and otherUnit work _not_ on the same block
            if (unit === 'px') {
                otherBlockUnit = '%';
                otherBlockWidthValue = value * 100 / _config.sizeProps.containerWidth;
            }
            else {
                otherBlockUnit = 'px';
                otherBlockWidthValue = value * _config.sizeProps.containerWidth / 100;
            }

            // update the unit-side of the tree with the value
            _config.sizeProps[unit].current.width = value;
            _config.sizeProps[otherBlockUnit].current.width = otherBlockWidthValue;

            // update the height fields of the same and of the other block
            if (_config.syncDimensions) {
                heightValue = value / ratio;
                otherBlockHeightValue = otherBlockWidthValue / ratio;
                //same block
                _config.sizeProps[unit].current.height = heightValue;
                _config.$fields[unit].height.val(_round(heightValue, 0));
                //other block
                _config.sizeProps[otherBlockUnit].current.height = otherBlockHeightValue;
                _config.$fields[otherBlockUnit].height.val(_round(otherBlockHeightValue, 0));
            }

            /* sliders */
            // update same slider value only when fn is triggered by typing
            if (eventType !== 'sliderEvent') {
                _config.$sliders[unit].val(value);
            }
            // update other slider
            _config.$sliders[otherBlockUnit].val(otherBlockWidthValue);

            // update other width field
            _config.$fields[otherBlockUnit].width.val(_round(otherBlockWidthValue, 0));

            // reset percent height to null
            _config.sizeProps['%'].current.height = null;

            // update medium
            if (_config.applyToMedium) {
                currentValues = this._getValues($elt);
                _config.target.attr('width', currentValues.width);
                _config.target.attr('height', currentValues.height || 'auto');
            }
            $elt.trigger('sizechange', _publicArgs($elt, _config));
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
                _checkMode = function () {
                    if ($responsiveToggleField.is(':checked')) {
                        _blocks.px.hide();
                        _blocks['%'].show();
                        _config.sizeProps.currentUnit = '%';
                        if (_config.$fields && _config.$fields['%'].width.val() > _config.sizeProps.sliders['%'].max) {
                            _config.$fields['%'].width.val(_config.sizeProps.sliders['%'].max);
                            _sync($elt, _config.$fields['%'].width, 'blur');
                        }
                    } else {
                        _blocks['%'].hide();
                        _blocks.px.show();
                        _config.sizeProps.currentUnit = 'px';
                    }
                };

            if(!_config.showResponsiveToggle) {
                $elt.addClass('media-sizer-responsivetoggle-off');
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
                $elt.trigger('sizechange', _publicArgs($elt, _config));
            });

            $responsiveToggleField.prop('checked',
                !_config.sizeProps.hasOwnProperty('currentUtil')
                || (
                    _config.sizeProps.hasOwnProperty('currentUtil')
                    && _config.sizeProps.currentUtil === '%'
                )
            );

            // initialize it properly
            _checkMode();

            return _blocks;
        };

        /**
         * Initialize the fields
         *
         * @param $elt
         * @returns {{}}
         * @private
         */
        var _initFields = function ($elt) {

            var dimensions = ['width', 'height'],
                field, _fields = {};

            _(_config.$blocks).forOwn(function ($block, unit) {
                _fields[unit] = {};

                _config.$blocks[unit].find('input').each(function () {
                    _(dimensions).forEach(function (dim) {
                        field = _config.$blocks[unit].find('[name="' + dim + '"]');
                        // there is no 'height' field for % - $('<input>') is a dummy to avoid checking if the field exists all the time
                        _fields[unit][dim] = field.length ? field : $('<input>');
                        _fields[unit][dim].prop({
                            unit: unit,
                            dimension: dim
                        });
                        _fields[unit][dim].val(_round(_config.sizeProps[unit].current[dim], 0));
                        _fields[unit][dim].data({ min: 0, max: _config.sizeProps.sliders[unit].max });

                        _fields[unit][dim].on('keydown', function (e) {
                            var $field = $(this),
                                c = e.keyCode,
                                specChars = (function () {
                                    var chars = [8, 37, 39, 46];
                                    if ($field.val().indexOf('.') === -1) {
                                        chars.push(190);
                                        chars.push(110);
                                    }
                                    return chars;
                                }());

                            return (_.contains(specChars, c) ||
                                (c >= 48 && c <= 57) ||
                                (c >= 96 && c <= 105));
                        });

                        _fields[unit][dim].on('keyup blur sliderchange', function (e) {
                            var $field = $(this),
                                value = $field.val().replace(/,/g, '.');

                            $field.val(value);

                            if (value > $field.data('max')) {
                                $field.val($field.data('max'));
                            }
                            else if (value < $field.data('min')) {
                                $field.val($field.data('min'));
                            }

                            _sync($elt, $(this), e.type);
                        });
                    });
                });
            });

            return _fields;
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

        if (!_config || !_config.sizeProps) {
            throw new Error('Control panel of the media editor is required sizeProps parameter');
        }
        _config.originalSizeProps = _.cloneDeep(_config.sizeProps);

        controlPanelComponent
            .on('render', function () {
                var $tpl = $(tpl({
                    responsive: (typeof _config.responsive !== 'undefined') ? !!_config.responsive : true
                }));

                $tpl.appendTo(this.getContainer());

                _config.syncDimensions = $tpl.find('.media-sizer').hasClass('media-sizer-synced');

                _config.$blocks = _initBlocks($tpl);
                _config.$sliders = _initSliders();
                _config.$fields = _initFields($tpl);
                _config.$syncBtn = _initSyncBtn($tpl);
                _config.$resetBtn = _initResetBtn($tpl);

                _publicArgs($tpl, _config);
            })
            .init(_config);

        return controlPanelComponent;
    }
});
