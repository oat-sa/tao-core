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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 *
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */


/**
 *
 * Examples:
 *
 * # Create new dateRange container
 * dateRangeFactory({
 *    pickerType: 'datetimepicker',
 *    renderTo: $dateRange,
 *    pickerConfig: {
 *        // configurations from lib/jquery.timePicker.js
 *        dateFormat: 'yy-mm-dd',
 *        timeFormat: 'HH:mm:ss'
 *    }
 * });
 *
 * # attach to exists form
 * <div class="container">
 *   <input type="text" name="from">
 *   <input type="text" name="to">
 * </div>
 *
 * dateRange({
 *     startInput: $inputFrom,
 *     endInput: $inputTo
 * }).render($container)j
 *
 */

define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'ui/datetime/picker',
    'tpl!ui/dateRange/tpl/select',
    'css!ui/dateRange/css/dateRange.css'
], function ($, _, __, component, dateTimePicker, formTpl) {
    'use strict';

    /**
     * Default config (by default works like DatePicker, without time)
     * @type {Object}
     * @private
     */
    var defaults = {
        resetButton : {
            enable : true,
            label : __('Reset'),
            title : __('Reset the range values')
        },
        applyButton : {
            enable : true,
            label : __('Apply'),
            title : __('Apply date range')
        },
        startPicker : {
            setup: 'datetime',
            format : 'YYYY-MM-DD HH:mm:SS',
            field : {
                name : 'periodStart',
            }
        },
        endPicker : {
            setup: 'datetime',
            format : 'YYYY-MM-DD HH:mm:SS',
            field : {
                name : 'periodEnd',
            }
        },
    };

    /**
     * Creates a dates range with date pickers
     *
     * @param {Object} config
     * @fires change when any date is changed
     * @fires submit when the submit button is clicked
     */
    function dateRangeFactory(container, config) {

        var useTemplate = !config ||
                (!config.startPicker.replaceField && !config.endPicker.replaceField);

        var dateRange = component({
            /**
             * Gets the start date of the range
             * @returns {String}
             */
            getStart : function getStart() {
                if (this.is('ready')) {
                    return this.startPicker.getValue();
                }
            },

            /**
             * Gets the end date of the range
             * @returns {String}
             */
            getEnd : function getEnd() {
                if (this.is('ready')) {
                    return this.endPicker.getValue();
                }
            }
        }, defaults);

        if (useTemplate) {
            dateRange.setTemplate(formTpl);
        }

        dateRange
            .on('init', function(){

                if(container){
                    this.render(container);
                }
            })
            .on('render', function () {
                var self = this;
                var element = this.getElement()[0];

                if (useTemplate) {
                    this.controls = {
                        filter : element.querySelector('[data-control="filter"]'),
                        reset  : element.querySelector('[data-control="reset"]'),
                        start  : element.querySelector('.start'),
                        end    : element.querySelector('.end')
                    };

                    this.startPicker = dateTimePicker(this.controls.start, this.config.startPicker);
                    this.endPicker   = dateTimePicker(this.controls.end, this.config.endPicker);

                } else {
                    this.startPicker = dateTimePicker(element, this.config.startPicker);
                    this.endPicker   = dateTimePicker(element, this.config.endPicker);
                }

                /**
                * Extend lib/jquery.timePicker for triggering events
                *
                * Calls `method` on the `startTime` and `endTime` elements, and configures them to
                * enforce date range limits.
                * @param  string method Can be used to specify the type of picker to be added
                * @param  Element startTime
                * @param  Element endTime
                * @param  obj options Options for the `timepicker()` call. Also supports `reformat`,
                *   a boolean value that can be used to reformat the input values to the `dateFormat`.
                * @return jQuery
                */
                //$.timepicker.triggeredHandleRange = function triggeredHandleRange(method, startTime, endTime, options) {
                    //options = $.extend({}, {
                        //minInterval: 0, // min allowed interval in milliseconds
                        //maxInterval: 0, // max allowed interval in milliseconds
                        //start: {},      // options for start picker
                        //end: {}         // options for end picker
                    //}, options);

                    //$.fn[method].call(startTime, $.extend({
                        //onClose: function (dateText, inst) {
                            //checkDates($(this), endTime);

                            /**
                            * @event close
                            * @param {String} property
                            * @param {String} value
                            */
                            //console.log('close', 'start', periodStart);
                            //self.trigger('close', 'start', periodStart);
                        //},
                        //onSelect: function (selectedDateTime) {

                            /**
                            * @event change
                            * @param {String} property
                            * @param {String} value
                            */

                            //console.log('change', 'start', selectedDateTime);
                            //self.trigger('change', 'start', selectedDateTime);

                            //selected($(this), endTime, 'minDate', true);
                        //}
                    //}, options, options.start));
                    //$.fn[method].call(endTime, $.extend({
                        //onClose: function (dateText, inst) {
                            //checkDates($(this), startTime);

                            /**
                            * @event close
                            * @param {String} property
                            * @param {String} value
                            */

                            //console.log('close', 'end', periodEnd);
                            //self.trigger('close', 'end', periodEnd);
                        //},
                        //onSelect: function (selectedDateTime) {

                            /**
                            * @event change
                            * @param {String} property
                            * @param {String} value
                            */
                            //console.log('change', 'end', selectedDateTime);
                            //self.trigger('change', 'end', selectedDateTime);

                            //selected($(this), startTime, 'maxDate', false);
                        //}
                    //}, options, options.end));

                    //checkDates(startTime, endTime);
                    //selected(startTime, endTime, 'minDate', true);
                    //selected(endTime, startTime, 'maxDate', false);

                    /**
                    * startTime should be before the endTime
                    * @param changed
                    * @param other
                    */
                    //function checkDates(changed, other) {
                        //var startdt = startTime[method]('getDate'),
                            //enddt = endTime[method]('getDate'),
                            //changeddt = changed[method]('getDate');

                        //if (startdt !== null) {
                            //var minDate = new Date(startdt.getTime()),
                                //maxDate = new Date(startdt.getTime());

                            //minDate.setMilliseconds(minDate.getMilliseconds() + options.minInterval);
                            //maxDate.setMilliseconds(maxDate.getMilliseconds() + options.maxInterval);

                            //if (options.minInterval > 0 && minDate > enddt) { // minInterval check
                                //endTime[method]('setDate', minDate);
                            //}
                            //else if (options.maxInterval > 0 && maxDate < enddt) { // max interval check
                                //endTime[method]('setDate', maxDate);
                            //}
                            //else if (startdt > enddt) {
                                //other[method]('setDate', changeddt);
                            //}
                        //}
                    //}

                    /**
                    * Select new date
                    * @param changed
                    * @param other
                    * @param option
                    * @param isStart - if changed startTime
                    */
                /*    function selected(changed, other, option, isStart) {
                        var date;

                        if (!changed.val()) {
                            return;
                        }
                        date = changed[method].call(changed, 'getDate');
                        if (isStart) {
                            periodStart = changed.val();
                        } else {
                            periodEnd = changed.val();
                        }
                        if (date !== null && options.minInterval > 0) {
                            if (option == 'minDate') {
                                date.setMilliseconds(date.getMilliseconds() + options.minInterval);
                            }
                            if (option == 'maxDate') {
                                date.setMilliseconds(date.getMilliseconds() - options.minInterval);
                            }
                        }
                        if (date.getTime) {
                            other[method].call(other, 'option', option, date);
                            if (isStart) {
                                periodEnd = other.val();
                            } else {
                                periodStart = other.val();
                            }
                        }
                    }

                    return $([startTime.get(0), endTime.get(0)]);
                };*/
    /*
                $.timepicker.triggeredHandleRange(
                    initConfig.pickerType,
                    $periodStart,
                    $periodEnd,
                    {
                        start: initConfig.pickerConfig,
                        end: initConfig.pickerConfig
                    }
                );
                */
                this.startPicker
                    .on('change', function(value){
                        if(value && self.endPicker && self.endPicker.is('ready')){
                            self.endPicker.updateConstraints('minDate', value);
                        }

                        self.trigger('change', 'start', value);
                    })
                    .on('close', function(){
                        self.trigger('close', 'start', this.getValue());
                    });

                this.endPicker
                    .on('change', function(value){
                        if(value && self.startPicker && self.startPicker.is('ready')){
                            self.startPicker.updateConstraints('maxDate', value);
                        }

                        self.trigger('change', 'end', value);
                    })
                    .on('close', function(){
                        self.trigger('close', 'end', this.getValue());
                    });

                if (useTemplate && this.controls.filter) {

                    this.controls.filter.addEventListener('click', function(e) {
                        e.preventDefault();

                        /**
                        * @event dateRange#submit
                        */
                        self.trigger('submit');
                    });
                }
                if (useTemplate && this.controls.reset) {

                    this.controls.reset.addEventListener('click', function(e) {
                        e.preventDefault();

                        self.startPicker
                            .updateConstraints('maxDate', null)
                            .clear();
                        self.endPicker
                            .updateConstraints('minDate', null)
                            .clear();

                        /**
                        * @event dateRange#submit
                        */
                        self.trigger('submit');
                    });
                }

                this.setState('ready', true);
                this.trigger('ready');
            })
            .on('destroy', function () {
                this.startPicker.destroy();
                this.endPicker.destroy();
            });

        _.defer(function(){
            dateRange.init(config);
        });

        return dateRange;
    }

    return dateRangeFactory;
});
