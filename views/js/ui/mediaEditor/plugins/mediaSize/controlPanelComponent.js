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
], function ($, component, tpl, controlPanelStateComponent) {
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
     * @param {Boolean} [config.showSync] - todo
     * @param {Boolean} [config.showReset] - todo
     * @fires "render" after the component rendering
     * @fires "destroy" after the component destroying
     *
     * @returns {component|*}
     */
    return function controlPanelFactory (config) {

        /**
         * State of the component
         */
        var controlPanelStateComponent = controlPanelStateComponent(config);

        /**
         * Current component
         */
        var controlPanelComponent = component();

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
                        controlPanelStateComponent.setSizeProp('currentUtil', '%');
                        if (controlPanelStateComponent.getProp('$fields')
                            && controlPanelStateComponent.getProp('$fields')['%'].width.val() > controlPanelStateComponent.getProp('$sliders')['%'].max) {
                            controlPanelStateComponent.getProp('$fields')['%'].width.val(_config.sizeProps.sliders['%'].max);
                            controlPanelStateComponent.percentChange(controlPanelStateComponent.getProp('$fields')['%'].width.val());
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

            $responsiveToggleField.prop('checked', controlPanelStateComponent.isResponsive());

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
        var _initSyncBtn = function ($elt) {
            var $mediaSizer = $elt.find('.media-sizer'),
                $syncBtn = $elt.find('.media-sizer-sync');

            if(!controlPanelStateComponent.getProp('showSync')) {
                $syncBtn.hide();
                $mediaSizer.addClass('media-sizer-sync-off');
            }
            // this stays intact even if hidden in case it will be
            // displayed from somewhere else
            $syncBtn.on('click', function () {
                $mediaSizer.toggleClass('media-sizer-synced');
                controlPanelStateComponent.setProp('syncDimensions', $mediaSizer.hasClass('media-sizer-synced'));
                if ($mediaSizer.hasClass('media-sizer-synced')) {
                    controlPanelStateComponent.percentChange(controlPanelStateComponent.getProp('$fields').px.width);
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
                controlPanelStateComponent.getProp('$fields').px.width
                        .val(controlPanelStateComponent.getProp('originalSizeProps').px.current.width)
                        .trigger('sliderchange');
            });
            return $resetBtn;
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

            _(controlPanelStateComponent.getProp('$blocks')).forOwn(function ($block, unit) {
                _fields[unit] = {};

                controlPanelStateComponent.getProp('$blocks')[unit].find('input').each(function () {
                    _(dimensions).forEach(function (dim) {
                        field = controlPanelStateComponent.getProp('$blocks')[unit].find('[name="' + dim + '"]');
                        // there is no 'height' field for % - $('<input>') is a dummy to avoid checking if the field exists all the time
                        _fields[unit][dim] = field.length ? field : $('<input>');
                        _fields[unit][dim].prop({
                            unit: unit,
                            dimension: dim
                        });
                        _fields[unit][dim].val(controlPanelStateComponent.getProp('sizeProps')[unit].current[dim]);
                        _fields[unit][dim].data({ min: 0, max: controlPanelStateComponent.getProp('sizeProps').sliders[unit].max });

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

                            if ($field.prop('unit') === '%') {
                                controlPanelStateComponent.percentChange(value);
                            } else {
                                if ($field.prop('dimension') === 'height') {
                                    controlPanelStateComponent.heightChange(value);
                                } else {
                                    controlPanelStateComponent.widthChange(value)
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
        var _initSliders = function () {
            var _sliders = {};

            _(controlPanelStateComponent.getProp('$blocks')).forOwn(function ($block, unit) {
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
                        controlPanelStateComponent.percentChange($slider.val());
                        /*controlPanelStateComponent.getProp('sizeProps').$fields[_unit].width
                            .val($slider.val())
                            .trigger('sliderchange');*/
                    });
            });

            return _sliders;
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
            params.$target = controlPanelStateComponent.getProp('target') || $();
            params.$resetBtn = controlPanelStateComponent.getProp('$resetBtn');
            return params;
        };

        /**
         * Retrieve current size values in current unit
         *
         * @returns {{}}
         * @private
         */
        var _getValues = function _getValues() {
            var attr = {};
            _.forOwn(controlPanelStateComponent.getProp('sizeProps')[controlPanelStateComponent.getProp('sizeProps').currentUnit].current, function (value, dimension) {
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

        controlPanelComponent
            .on('render', function () {
                var $tpl = $(tpl({
                    responsive: controlPanelStateComponent.isResponsive()
                }));

                $tpl.appendTo(this.getContainer());

                controlPanelStateComponent.setProp('syncDimensions', $tpl.find('.media-sizer').hasClass('media-sizer-synced'));
                controlPanelStateComponent.setProp('$blocks', _initBlocks($tpl));
                controlPanelStateComponent.setProp('$sliders', _initSliders());
                controlPanelStateComponent.setProp('$fields', _initFields($tpl));
                controlPanelStateComponent.setProp('$syncBtn', _initSyncBtn($tpl));
                controlPanelStateComponent.setProp('$resetBtn', _initResetBtn($tpl));

                // control state
                _publicArgs();
            })
            .init(_config);

        return controlPanelComponent;
    }
});
