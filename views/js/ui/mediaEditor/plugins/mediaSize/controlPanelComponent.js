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
    'jquery',
    'lodash',
    'ui/component',
    'tpl!ui/mediaEditor/plugins/mediaSize/tpl/controlPanel',
    'ui/mediaEditor/plugins/mediaSize/controlPanelStateComponent',
    'nouislider',
    'ui/tooltip'
], function ($, _, component, tpl, controlPanelStateComponentFactory) {
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
     * Creates control panel for mediaSizer plugin
     *
     * @param {Object} config
     * @param {Boolean} [config.responsive] - If media can be responsive
     * @param {Boolean} [config.showSync]
     * @param {Boolean} [config.showReset]
     * @fires "render" after the component rendering
     * @fires "destroy" after the component destroying
     * @fires "change" on size changed
     *
     * @returns {component|*}
     */
    return function controlPanelFactory (config) {

        /**
         * Collections of the jquery elements grouped by type
         */
        var $blocks, $sliders, $fields, $resetBtn;

        /**
         * State of the component
         */
        var controlPanelStateComponent = controlPanelStateComponentFactory(config);

        /**
         * Current component
         */
        var controlPanelComponent = component();

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
         * Retrieve current size values in current unit
         *
         * @returns {{}}
         * @private
         */
        var _getValues = function _getValues() {
            var attr = {};
            _.forOwn(controlPanelStateComponent.getProp('sizeProps')[controlPanelStateComponent.getProp('sizeProps').currentUtil].current,
                function (value, dimension) {
                    if (_.isNull(value)) {
                        value = '';
                    }
                    else {
                        value = value.toString();
                    }
                    if (controlPanelStateComponent.getProp('sizeProps').currentUnit === '%' && value !== '') {
                        value += controlPanelStateComponent.getProp('sizeProps').currentUnit;
                    }
                    attr[dimension] = value;
                });
            return attr;
        };

        /**
         * Returns width, height, target element and the reset button
         * It's meant to be used when triggering an event
         *
         * @returns {{}}
         * @private
         */
        var _publicArgs = function _publicArgs() {
            var params = _getValues();
            // todo I don't need a target in the state or anywhere else
            params.$target = controlPanelStateComponent.getProp('target') || $();
            params.$resetBtn = $resetBtn;
            return params;
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
                        _blocks.px.hide();
                        _blocks['%'].show();
                        controlPanelStateComponent.setSizeProp('currentUtil', '%');
                        if ($fields
                            && $fields['%'].width.val() > $sliders['%'].max
                        ) {
                            $fields['%'].width.val(controlPanelStateComponent.getProp('sizeProps').sliders['%'].max);
                            controlPanelStateComponent.percentChange($fields['%'].width.val());
                        }
                    } else {
                        _blocks['%'].hide();
                        _blocks.px.show();
                        controlPanelStateComponent.setProp('currentUnit', 'px');
                    }
                };

            if(!controlPanelStateComponent.getProp('showResponsiveToggle')) {
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
                $elt.trigger('sizechange', _publicArgs());
            });

            $responsiveToggleField.prop('checked', controlPanelStateComponent.getProp('sizeProps').currentUtil === '%');

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

            if(!controlPanelStateComponent.getProp('showSync')) {
                $btn.hide();
                $mediaSizer.addClass('media-sizer-sync-off');
            }
            // this stays intact even if hidden in case it will be
            // displayed from somewhere else
            $btn.on('click', function () {
                var $sizerEl = $(this).parents('.media-sizer');
                $sizerEl.toggleClass('media-sizer-synced');
                controlPanelStateComponent.setProp('syncDimensions', $sizerEl.hasClass('media-sizer-synced'));
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

            if(!controlPanelStateComponent.isResetAllowed()) {
                $elt.find('.media-sizer').addClass('media-sizer-reset-off');
            }

            // this stays intact even if hidden in case it will be
            // displayed from somewhere else
            $btn.on('click', function() {
                controlPanelStateComponent.reset();
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
                        _fields[unit][dim].val(controlPanelStateComponent.getProp('sizeProps')[unit].current[dim]);

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
                                value = $field.val().replace(/,/g, '.');

                            $field.val(value);
                            if (isInsignificantEnd(value)) {
                                // do nothing if .00 or something insignificant at the end of line
                                return;
                            }

                            /*
                            TODO if it is needed - set as an edge of values using values of the container
                            if (value > $field.data('max')) {
                                $field.val($field.data('max'));
                                value = $field.data('max')+'';
                            }
                            else if (value < $field.data('min')) {
                                $field.val($field.data('min'));
                                value = $field.data('min')+'';
                            }*/

                            if ($field.prop('unit') === '%') {
                                controlPanelStateComponent.percentChange(value);
                            } else {
                                if ($field.prop('dimension') === 'height') {
                                    controlPanelStateComponent.heightChange(value);
                                } else {
                                    controlPanelStateComponent.widthChange(value);
                                }
                            }
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
        var _initSliders = function _initSliders () {
            var _sliders = {};

            _($blocks).forOwn(function ($block, unit) {
                _sliders[unit] = $block.find('.media-sizer-slider');
                _sliders[unit].prop('unit', unit);
                _sliders[unit].noUiSlider({
                    start: controlPanelStateComponent.getProp('sizeProps').sliders[unit].start,
                    range: {
                        'min': controlPanelStateComponent.getProp('sizeProps').sliders[unit].min,
                        'max': controlPanelStateComponent.getProp('sizeProps').sliders[unit].max
                    }
                })
                    .on('slide', function () {
                        var $slider = $(this);
                        var sliderVal = $slider.val();
                        // to avoid .00
                        sliderVal = parseFloat(sliderVal) + '';
                        controlPanelStateComponent.percentChange(sliderVal);
                    });
            });

            return _sliders;
        };

        controlPanelStateComponent.on('changed', function () {
            // slide sliders
            $sliders['%'].val(controlPanelStateComponent.getProp('sizeProps')['%'].current.width);
            $sliders.px.val(controlPanelStateComponent.getProp('sizeProps')['%'].current.width);
            // percent Input
            $fields['%'].width.val(controlPanelStateComponent.getProp('sizeProps')['%'].current.width);
            // px inputs
            $fields.px.width.val(controlPanelStateComponent.getProp('sizeProps').px.current.width);
            $fields.px.height.val(controlPanelStateComponent.getProp('sizeProps').px.current.height);

            controlPanelComponent.trigger('change', controlPanelStateComponent);
        });

        controlPanelComponent
            .on('render', function () {
                var $tpl = $(tpl({
                    responsive: controlPanelStateComponent.isResponsive()
                }));
                var $mediaSizer = $tpl.find('.media-sizer');

                $tpl.appendTo(this.getContainer());

                if (controlPanelStateComponent.getProp('syncDimensions') === true
                    && !$mediaSizer.hasClass('media-sizer-synced')
                ) {
                    $mediaSizer.addClass('media-sizer-synced');
                }

                $blocks = _initBlocks($tpl);
                $sliders = _initSliders();
                $fields = _initFields();
                _initSyncBtn($tpl);
                $resetBtn = _initResetBtn($tpl);

                // control state
                _publicArgs();
            })
            .init(_config);

        return controlPanelComponent;
    };

});
