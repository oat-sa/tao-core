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

/**
 * Controls media size
 */
define([
    'jquery',
    'lodash',
    'ui/component',
    'tpl!ui/mediaEditor/plugins/mediaDimension/tpl/mediaDimension',
    'ui/mediaEditor/plugins/mediaDimension/helper',
    'nouislider',
    'ui/tooltip'
], function ($, _, component, tpl, helper) {
    'use strict';

    /**
     * Size properties of the media
     * @typedef {Object} sizeProps
     * @property px {{
     *        natural: {
     *          width: number,
     *          height: number,
     *        },
     *        current: {
     *          width: number,
     *          height: number,
     *        }
     *      }}
     * @property '%' {{
     *        natural: {
     *          width: number,
     *          height: number,
     *        },
     *        current: {
     *          width: number,
     *          height: number,
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
     * @property $editableContainer object
     * @property showResponsiveToggle boolean
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
     *    $editableContainer: object,
     *    showResponsiveToggle: boolean,
     *    responsive: boolean,
     *    showSync: boolean,
     *    showReset: boolean,
     *    denyCustomRatio: boolean,
     *    width: number,
     *    height: number,
     *    minWidth: number,
     *    maxWidth: number,
     *    sizeProps: sizeProps,
     *    precision: number
     * }}
     * @private
     */
    var _defaults = {
        $editableContainer: null,
        showResponsiveToggle: true,
        responsive: true,
        showSync: true,
        showReset: true,
        sizeProps: {
            px: {
                current: {
                    width: 0,
                    height: 0
                }
            },
            '%': {
                current: {
                    width: 0,
                    height: null
                }
            },
            ratio: {
                natural: 1,
                current: 1
            },
            currentUtil: '%',
            slider: {
                min: 1,
                max: 100,
                start: 100
            }
        },
        denyCustomRatio: false,
        syncDimensions: true,
        width: 0,
        height: 0,
        minWidth: 0,
        maxWidth: 0,
        precision: 5
    };

    /**
     * Creates mediaDimension component
     * @param config
     * @fires "changed" - on State changed
     * return {component|*}
     */
    return function mediaDimensionFactory($container, media, config) {
        /**
         * Collections of the jquery elements grouped by type
         */
        var $blocks, $slider, $fields;

        /**
         * Current component
         */
        var mediaDimensionComponent = component({
            update: function update() {
                // slide sliders
                $slider.val(_config.sizeProps['%'].current.width);
                // percent Input
                $fields['%'].width.val(helper.round(_config.sizeProps['%'].current.width, 0));
                // px inputs
                $fields.px.width.val(helper.round(_config.sizeProps.px.current.width, 0));
                $fields.px.height.val(helper.round(_config.sizeProps.px.current.height, 0));

                this.trigger('change', _config);
            }
        }, _defaults);

        /**
         * Calculate propSizes to have correct sizes for the shown image
         */
        var calculateCurrentSizes = function calculateCurrentSizes() {
            _config = helper.applyDimensions(_config, {
                width: (helper.containerWidth(_config) < media.width ? helper.containerWidth(_config) : media.width)
            });
            mediaDimensionComponent.update();
        };

        /**
         * Check that input in progress and we don't need to change anything
         * @param val
         * @returns {RegExpMatchArray | null}
         */
        var isInsignificantEnd = function isInsignificantEnd (val) {
            if (typeof val !== 'string') {
                val = val + '';
            }
            return val.match(/\.[0]*$/);
        };

        /**
         * Blocks are the two different parts of the form (either width|height or size)
         *
         * @param $elt
         * @returns {{}}
         * @private
         */
        var _initBlocks = function _initBlocks ($elt) {
            var _blocks = {},
                $responsiveToggleField = $elt.find('.media-mode-switch'),
                _checkMode = function () {
                    if ($responsiveToggleField.is(':checked')) {
                        _config.responsive = true;
                        _blocks.px.hide();
                        _blocks['%'].show();
                        _config.sizeProps.currentUtil = '%';
                    } else {
                        _config.responsive = false;
                        _blocks['%'].hide();
                        _blocks.px.show();
                        _config.sizeProps.currentUtil = 'px';
                    }

                    if ($fields) {
                        if ($fields['%'].width.val() > $slider.max) {
                            $fields['%'].width.val($slider.max);
                        }
                        _config = helper.applyDimensions(_config, {percent: $fields['%'].width.val()});
                        mediaDimensionComponent.update();
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
            });

            $responsiveToggleField.prop('checked', _config.responsive);

            // initialize it properly
            _checkMode();

            return _blocks;
        };

        /**
         * Toggle width/height synchronization
         *
         * @param $elt
         * @returns {*}
         * @private
         */
        var _initSyncBtn = function _initSyncBtn ($elt) {
            var $mediaSizer = $elt.find('.media-sizer'),
                $btn = $elt.find('.media-sizer-sync');

            if(!_config.showSync) {
                $btn.hide();
                $mediaSizer.addClass('media-sizer-sync-off');
            }
            // this stays intact even if hidden in case it will be
            // displayed from somewhere else
            $btn.on('click', function () {
                var $sizerEl = $(this).parents('.media-sizer');
                $sizerEl.toggleClass('media-sizer-synced');
                _config.syncDimensions = $sizerEl.hasClass('media-sizer-synced');
            });
            return $btn;
        };

        /**
         * Button to reset the size to its original values
         *
         * @param $elt
         * @returns {*}
         * @private
         */
        var _initResetBtn = function _initResetBtn ($elt) {
            var $btn = $elt.find('.media-sizer-reset');

            if(!_config.showReset) {
                $elt.find('.media-sizer').addClass('media-sizer-reset-off');
            }

            // this stays intact even if hidden in case it will be
            // displayed from somewhere else
            $btn.on('click', function() {
                _config.sizeProps = _config.originalSizeProps;
                calculateCurrentSizes();
                mediaDimensionComponent.update();
            });

            return $btn;
        };

        /**
         * Initialize the fields
         *
         * @returns {{}}
         * @private
         */
        var _initFields = function _initFields () {

            var dimensions = ['width', 'height'],
                field, _fields = {};

            _($blocks).forOwn(function ($block, unit) {
                _fields[unit] = {};

                $blocks[unit].find('input').each(function () {
                    _(dimensions).forEach(function (dim) {
                        field = $blocks[unit].find('[name="' + dim + '"]');
                        // there is no 'height' field for % - $('<input>') is a dummy to avoid checking if the field exists all the time
                        _fields[unit][dim] = field.length ? field : $('<input>');
                        _fields[unit][dim].prop({
                            unit: unit,
                            dimension: dim
                        });
                        _fields[unit][dim].val(_config.sizeProps[unit].current[dim]);

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
                                }()),
                                allowed = (_.contains(specChars, c) ||
                                    (c >= 48 && c <= 57) ||
                                    (c >= 96 && c <= 105));

                            if (!allowed) {
                                e.preventDefault();
                            }
                            return allowed;
                        });

                        _fields[unit][dim].on('keyup blur sliderchange', function () {
                            var $field = $(this),
                                value = $field.val().replace(/,/g, '.'),
                                newDimensions;

                            $field.val(value);
                            if (isInsignificantEnd(value)) {
                                // do nothing if .00 or something insignificant at the end of line
                                return;
                            }

                            if (value > $field.data('max')) {
                                $field.val($field.data('max'));
                                value = $field.data('max')+'';
                            }
                            else if (value < $field.data('min')) {
                                $field.val($field.data('min'));
                                value = $field.data('min')+'';
                            }

                            if ($field.prop('unit') === '%') {
                                _config.sizeProps['%'].current.width = value;
                                newDimensions = { percent: value };
                            } else {
                                if ($field.prop('dimension') === 'height') {
                                    newDimensions = { height: value };
                                } else {
                                    newDimensions = { width: value };
                                }
                            }
                            _config = helper.applyDimensions(_config, newDimensions);
                            mediaDimensionComponent.update();
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
        var _initSlider = function _initSlider ($elt) {
            var _slider;

            _slider = $elt.find('.media-sizer-slider');
            _slider.prop('unit', '%');
            _slider.noUiSlider({
                start: _config.sizeProps.slider.start,
                range: {
                    min: _config.sizeProps.slider.min,
                    max: _config.sizeProps.slider.max
                }
            })
                .on('slide', function () {
                    // to avoid .00
                    var percent = parseFloat($(this).val()+'');
                    helper.applyDimensions(_config, { percent: percent });
                    mediaDimensionComponent.update();
                });

            return _slider;
        };

        mediaDimensionComponent
            .on('init', function () {
                var mediaProps = {
                    px: {
                        current: {
                            width: media.width,
                            height: media.height
                        },
                        natural: {
                            width: media.width,
                            height: media.height
                        }
                    },
                    '%': {
                        current: {
                            width: 100
                        }
                    }
                };

                // rewrite with defined values
                _config = this.getConfig();
                _config.sizeProps = _.defaults(mediaProps, _config.sizeProps, _defaults.sizeProps);
                _config.sizeProps.ratio.natural = helper.getCurrentRatio(_config);
                _config.sizeProps.ratio.current = helper.getCurrentRatio(_config);
                _config.originalSizeProps = _.cloneDeep(_config.sizeProps);
                if (!_config.$editableContainer || !_config.$editableContainer.length) {
                    _config.$editableContainer = media.$node.parents();
                }
                this.render($container);
            })
            .on('render', function () {
                var $tpl, $mediaSizer;

                $tpl = $(tpl({
                    responsive: (typeof _config.responsive !== 'undefined') ? _config.responsive : true
                }));

                $tpl.appendTo(this.getContainer());

                $mediaSizer = $tpl.find('.media-sizer');
                if (_config.syncDimensions === true && !$mediaSizer.hasClass('media-sizer-synced')) {
                    $mediaSizer.addClass('media-sizer-synced');
                }

                $blocks = _initBlocks($tpl);
                $slider = _initSlider($tpl);
                $fields = _initFields();
                _initSyncBtn($tpl);
                _initResetBtn($tpl);

                if (typeof media.$node.attr('width') === 'undefined') {
                    // if no sizes are set then control panel initialization
                    calculateCurrentSizes();
                } else {
                    if (_config.responsive) {
                        // initialize by percent on the responsive mode
                        _config = helper.applyDimensions(_config, {percent: media.$node.attr('width')});
                    } else {
                        // non-responsive mode
                        _config.sizeProps.px.current = {
                            width: media.$node.prop('width'),
                            height: media.$node.prop('height')
                        };
                        // calculate percent
                        _config.sizeProps['%'].current.width = helper.round(media.$node.prop('width') * 100 / helper.containerWidth(_config), _config.precision);
                    }
                }
                mediaDimensionComponent.update();
            });

        _.defer(function(){
            mediaDimensionComponent.init(config);
        });

        return mediaDimensionComponent;
    };
});
