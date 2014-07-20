/**
 * @author Dieter Raber <dieter@taotesting.com>
 * @requires jquery
 * @requires core/pluginifier
 */
define([
    'jquery',
    'core/pluginifier',
    'tpl!ui/mediasizer/mediasizer',
    'nouislider',
    'tooltipster'
], function($, Pluginifier, tpl) {
    'use strict';

    var ns = 'mediasizer';
    var dataNs = 'ui.' + ns;

    var defaults = {
        disableClass: 'disabled'
    };

    var supportedMedia = ['img'];

    /**
     * Round a decimal value to n digits
     *
     * @param value
     * @param precision
     * @returns {number}
     * @private
     */
    function _round(value, precision) {
        if (precision === undefined) {
            precision = 1;
        }
        var factor = 1;
        while (precision--) {
            factor *= 10;
        }
        return Math.round(value * factor) / factor;
    }

    /**
     * The MediaSizer component, that helps you to show/hide an element
     * @exports ui/toggler
     */
    var MediaSizer = {



        /**
         * Creates object that contains all size related data of the medium (= image, video, etc.)
         *
         * @param $elt
         * @returns {{px: {natural: {width: number, height: number}, current: {width: number, height: number}}, '%': {natural: {width: number, height: number}, current: {width: number, height: null|number}}, ratio: {natural: number, current: number}, containerWidth: number}}
         * @private
         */
        _getSizeProps: function($elt) {
            var options = $elt.data(dataNs),
                $medium = options.target,
                medium = $medium[0],
                containerWidth = options.parentSelector ? $medium.parents(options.parentSelector).innerWidth() : $medium.parent().innerWidth();

            return {
                px: {
                    natural: {
                        width: medium.naturalWidth,
                        height: medium.naturalHeight
                    },
                    current: {
                        width: medium.width,
                        height: medium.height
                    }
                },
                '%': {
                    natural: {
                        width: 100,
                        height: null
                    },
                    current: {
                        width: medium.width * 100 / containerWidth,
                        height: null // height does not work on % - this is just in case you have to loop or something
                    }
                },
                ratio: {
                    natural: medium.naturalWidth / medium.naturalHeight,
                    current: medium.width / medium.height
                },
                containerWidth: containerWidth,
                sliders: {
                    '%': {
                        max: 100,
                        start: medium.width * 100 / containerWidth
                    },
                    px: {
                        max: Math.max(containerWidth, medium.naturalWidth),
                        start: medium.width
                    }
                }
            };

        },


        /**
         * Toggle width/height synchronization
         *
         * @param $elt
         * @private
         */
        _initSyncBtn: function($elt) {
            var options = $elt.data(dataNs),
                $mediaSizer = $elt.find('.media-sizer'),
                self = this;
            $elt.find('.media-sizer-link').on('click', function() {
                $mediaSizer.toggleClass('media-sizer-synced');
                options.syncDimensions = $mediaSizer.hasClass('media-sizer-synced');
                if (options.syncDimensions) {
                    self._sync($elt, options.$fields.px.width, 'blur');
                }
            });
        },


        /**
         * Blocks are the two different parts of the form (either width|height or size)
         *
         * @param $elt
         * @returns {{}}
         * @private
         */
        _initBlocks: function($elt) {
            var _blocks = {};

            _(['px', '%']).forEach(function(unit) {
                _blocks[unit] = $elt.find('.media-sizer-' + (unit === 'px' ? 'pixel' : 'percent'));
                _blocks[unit].prop('unit', unit);
                _blocks[unit].find('input').data('unit', unit).after($('<span>', {
                    'class': 'unit-indicator',
                    text: unit
                }));
            });

            $elt.find('.media-mode-switch').on('click', function() {
                if (this.checked) {
                    _blocks['px'].hide();
                    _blocks['%'].show();
                } else {
                    _blocks['%'].hide();
                    _blocks['px'].show();
                }
            });

            return _blocks;
        },


        /**
         * Initialize the two sliders, one based on pixels the other on percentage
         *
         * @param $elt
         * @returns {{}}
         * @private
         */
        _initSliders: function($elt) {
            var options = $elt.data(dataNs),
                unit,
                _sliders = {};

            _(options.$blocks).forOwn(function($block, unit) {
                _sliders[unit] = $block.find('.media-sizer-slider');
                _sliders[unit].prop('unit', unit);
                _sliders[unit].noUiSlider({
                    start: options.sizeProps.sliders[unit].start,
                    range: {
                        'min': 0,
                        'max': options.sizeProps.sliders[unit].max
                    }
                })
                    .on('slide', function() {
                        var $slider = $(this),
                            unit = $slider.prop('unit'),
                            precision = unit === 'px' ? 0 : 1;

                        options.$fields[unit].width.val(_round($slider.val(), precision)).trigger('sliderchange');
                    })
            });

            return _sliders;
        },

        /**
         * Synchronize all parameters
         *
         * @param $elt
         * @param $field
         * @param eventType
         * @private
         */
        _sync: function($elt, $field, eventType) {
            eventType = eventType === 'sliderchange' ? 'sliderEvent' : 'fieldEvent';

            var options = $elt.data(dataNs),
                unit = $field.prop('unit'),
                dimension = $field.prop('dimension'),
                value = parseFloat($field.val()),
                heightValue,
                ratio,
                precision,
                otherBlockUnit,
                otherBlockWidthValue,
                otherBlockHeightValue,
                otherBlockPrecision,
                currentValues;

            // invalid entries
            if (isNaN(value)) {
                return;
            }

            // Re-calculate current ratio
            // change scenario: someone has typed height and width in pixels while syncing was off
            // whether current or natural ratio eventually will be used depends on options.denyCustomRatio
            if(options.sizeProps.px.current.width > 0 && options.sizeProps.px.current.height > 0) {
              options.sizeProps.ratio.current = options.sizeProps.px.current.width / options.sizeProps.px.current.height;
            }
            ratio = options.denyCustomRatio ? options.sizeProps.ratio.natural : options.sizeProps.ratio.current;

            // There is only one scenario where dimension != width: manual input of the height in px
            // this is treated here separately because then we just need to deal with widths below
            if(dimension === 'height' && unit === 'px') {
                options.sizeProps.px.current.height = value;
                if(options.syncDimensions) {
                    options.sizeProps.px.current.width = value * ratio;
                    options.sizeProps.ratio.current = options.sizeProps.px.current.width / options.sizeProps.px.current.height;
                    options.$fields.px.width.val(_round(options.sizeProps.px.current.width, 0));

                    // now all values can be set to the width since width entry is now the only scenario
                    value = parseFloat(options.$fields.px.width.val());
                }
                else {
                    options.sizeProps['%'].current.height = null;
                    // update medium
                    if (options.applyToMedium) {
                        currentValues = this._getValues($elt, unit);
                        options.target.attr('width', currentValues.width);
                        options.target.attr('height', currentValues.height);
                    }
                    return;
                }
            }
            // *** as of here we can be sure that the dimension is 'width' *** //


            // remember that heightValue and otherUnit work _not_ on the same block
            if (unit === 'px') {
                precision = 0;
                otherBlockPrecision = 1;
                otherBlockUnit = '%';
                otherBlockWidthValue = value * 100 / options.sizeProps.containerWidth;
            } else {
                precision = 1;
                otherBlockPrecision = 0;
                otherBlockUnit = 'px';
                otherBlockWidthValue = value * options.sizeProps.containerWidth / 100;
            }

            // update the unit-side of the tree with the value
            options.sizeProps[unit].current.width = value;
            options.sizeProps[otherBlockUnit].current.width = otherBlockWidthValue;

            // update the height fields of the same and of the other block
            if (options.syncDimensions) {
                heightValue = value / ratio;
                otherBlockHeightValue = otherBlockWidthValue / ratio;
                //same block
                 options.sizeProps[unit].current.height = heightValue;
                 options.$fields[unit].height.val(_round(heightValue, precision));
                //other block
                options.sizeProps[otherBlockUnit].current.height = otherBlockHeightValue;
                options.$fields[otherBlockUnit].height.val(_round(otherBlockHeightValue, otherBlockPrecision));
            }

            /* sliders */
            // update same slider value only when fn is triggered by typing
            if (eventType !== 'sliderEvent') {
                options.$sliders[unit].val(value);
            }
            // update other slider
            options.$sliders[otherBlockUnit].val(otherBlockWidthValue);

            // update other width field
            options.$fields[otherBlockUnit].width.val(_round(otherBlockWidthValue, otherBlockPrecision));

            // reset percent height to null
            options.sizeProps['%'].current.height = null;

            // update medium
        if (options.applyToMedium) {
            currentValues = this._getValues($elt, unit);
            options.target.attr('width', currentValues.width);
            options.target.attr('height', currentValues.height);
        }
    },


        /**
         * Initialize the fields
         *
         * @param $elt
         * @returns {{}}
         * @private
         */
        _initFields: function($elt) {
            var options = $elt.data(dataNs),
                dimensions = ['width', 'height'],
                field, _fields = {},
                precision,
                self = this;

            _(options.$blocks).forOwn(function($block, unit) {
                _fields[unit] = {};
                precision = unit === 'px' ? 0 : 1;
                options.$blocks[unit].find('input').each(function() {
                    _(dimensions).forEach(function(dim) {
                        field = options.$blocks[unit].find('[name="' + dim + '"]');
                        // there is no 'height' field for % - $('<input>') is a dummy to avoid checking if the field exists all the time
                        _fields[unit][dim] = field.length ? field : $('<input>');
                        _fields[unit][dim].prop({
                            unit: unit,
                            dimension: dim
                        });
                        _fields[unit][dim].val(_round(options.sizeProps[unit].current[dim], precision));
                        _fields[unit][dim].on('keyup blur sliderchange', function(e) {
                            self._sync($elt, $(this), e.type);
                        });
                    });
                });
            });

            return _fields;
        },



        /**
         * Retrieve current size values in current unit
         *
         * @param $elt
         * @param unit
         * @returns {{}}
         * @private
         */
        _getValues: function($elt, unit) {
            var options = $elt.data(dataNs),
                attr = {},
                precision = unit === 'px' ? 0 : 1;

            _.forOwn(options.sizeProps[unit].current, function(value, dimension) {
                if (_.isNull(value)) {
                    value = '';
                } else {
                    value = _round(value, precision).toString();
                }
                if (unit === '%' && value !== '') {
                    value += unit;
                }
                attr[dimension] = value;
            });
            return attr;
        },


        /**
         * Initialize the plugin.
         *
         * Called the jQuery way once registered by the Pluginifier.

         * @example $('selector').mediaSizer({target : $('target') });
         * @public
         *
         * @constructor
         * @returns {*}
         */
        init: function(options) {

            //get options using default
            options = $.extend(true, {}, defaults, options);

            var self = MediaSizer;

            return this.each(function() {
                var $elt = $(this),
                    $target = options.target,
                    type = $target[0].nodeName.toLowerCase();

                if (!_.contains(supportedMedia, type)) {
                    throw new Error('MediaSizer::init() Unsupported element type ' + type);
                }

                if (!$elt.data(dataNs)) {

                    $elt.html(tpl());

                    //add data to the element
                    $elt.data(dataNs, options);

                    options.sizeProps = self._getSizeProps($elt);
                    options.originalSizeProps = _.cloneDeep(options.sizeProps);

                    // options.parentSelector = '[class*="col-"]';

                    options.syncDimensions = $elt.find('.media-sizer').hasClass('media-sizer-synced');
                    options.denyCustomRatio = !!options.denyCustomRatio;

                    options.applyToMedium = !!options.applyToMedium;
                    options.applyToMedium = true;

                    options.$blocks = self._initBlocks($elt);
                    options.$fields = self._initFields($elt);
                    options.$sliders = self._initSliders($elt);


                    self._initSyncBtn($elt);


                    /**
                     * The plugin have been created.
                     * @event MediaSizer#create.toggler
                     */
                    $elt.trigger('create.' + ns);
                }
            });
        },


        /**
         * Destroy the plugin completely.
         * Called the jQuery way once registered by the Pluginifier.
         *
         * @example $('selector').toggler('destroy');
         * @public
         */
        destroy: function() {
            this.each(function() {
                var $elt = $(this);
                var options = $elt.data(dataNs);


                /**
                 * The plugin have been destroyed.
                 * @event MediaSizer#destroy.toggler
                 */
                $elt.trigger('destroy.' + ns);
            });
        }
    };

    //Register the toggler to behave as a jQuery plugin.
    Pluginifier.register(ns, MediaSizer);

});