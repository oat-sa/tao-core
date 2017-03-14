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
 * }).render($container)
 *
 */

define([
    'jquery',
    'lodash',
    'ui/component',
    'tpl!ui/dateRange/tpl/select',
    'jquery.timePicker'
], function ($, _, component, formTpl) {
    'use strict';

    /**
     * Default config (by default works like DatePicker, without time)
     * @type {Object}
     * @private
     */
    var _defaults = {
        /**
         * datepicker|datetimepicker|timepicker
         */
        pickerType: 'datepicker',
        pickerConfig: {
            // configurations from lib/jquery.timePicker.js
            dateFormat: 'yy-mm-dd'
        },
        // default date "from" for range
        startDate: '',
        // default date "to" for range
        endDate: '',
        renderTo: null,

        // In the case where the inputs are already created on the page
        // don't use default template, just bind them to datePicker
        // example: { startInput: $('input.date_from', $form), endInput: $('input.date_to', $form) }
        startInput: null,
        endInput: null
    };

    /**
     * Creates a dates range with date pickers
     *
     * @param {Object} config
     * @param {String} [config.start] - The initial start date (default: none)
     * @param {String} [config.end] - The initial end date (default: none)
     * @param {String} [config.dateFormat] - The date picker format (default: 'yy-mm-dd')
     * @fires change when any date is changed
     * @fires submit when the submit button is clicked
     */
    function dateRangeFactory(config) {
        var initConfig = _.defaults(config || {}, _defaults);
        var periodStart = initConfig.startDate || '';
        var periodEnd = initConfig.endDate || '';
        var $periodStart, $periodEnd;
        var componentDateRange;
        var $filterBtn, $resetBtn;

        var dateRange = {
            /**
             * Gets the start date of the range
             * @returns {String}
             */
            getStart: function getStart() {
                return periodStart;
            },

            /**
             * Gets the end date of the range
             * @returns {String}
             */
            getEnd: function getEnd() {
                return periodEnd;
            }
        };

        /**
         * User can determine input for date "From" and input date "To" for date range
         * in this case default template "formTpl" won't be used, dateRange will be attached to existing form
         * @returns {boolean}
         */
        var hasInputs = function hasInputs() {

            var isDefined = false;
            if (initConfig.startInput && initConfig.endInput) {
                isDefined = true;
            }

            return isDefined;
        };

        componentDateRange = component(dateRange);

        if (hasInputs()) {
            $periodStart = initConfig.startInput;
            $periodEnd = initConfig.endInput;
        } else {
            componentDateRange.setTemplate(formTpl);
        }

        componentDateRange.on('render', function () {
            var self = this;
            var $form = this.getElement();

            if (!hasInputs()) {
                $periodStart = $form.find('input[name=periodStart]');
                $periodEnd = $form.find('input[name=periodEnd]');

                $filterBtn = $form.find('[data-control="filter"]');
                $resetBtn = $form.find('[data-control="reset"]');
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
            $.timepicker.triggeredHandleRange = function triggeredHandleRange(method, startTime, endTime, options) {
                options = $.extend({}, {
                    minInterval: 0, // min allowed interval in milliseconds
                    maxInterval: 0, // max allowed interval in milliseconds
                    start: {},      // options for start picker
                    end: {}         // options for end picker
                }, options);

                $.fn[method].call(startTime, $.extend({
                    onClose: function (dateText, inst) {
                        checkDates($(this), endTime);

                        /**
                         * @event close
                         * @param {String} property
                         * @param {String} value
                         */
                        self.trigger('close', 'start', periodStart);
                    },
                    onSelect: function (selectedDateTime) {

                        /**
                         * @event change
                         * @param {String} property
                         * @param {String} value
                         */
                        self.trigger('change', 'start', selectedDateTime);

                        selected($(this), endTime, 'minDate', true);
                    }
                }, options, options.start));
                $.fn[method].call(endTime, $.extend({
                    onClose: function (dateText, inst) {
                        checkDates($(this), startTime);

                        /**
                         * @event close
                         * @param {String} property
                         * @param {String} value
                         */
                        self.trigger('close', 'end', periodEnd);
                    },
                    onSelect: function (selectedDateTime) {

                        /**
                         * @event change
                         * @param {String} property
                         * @param {String} value
                         */
                        self.trigger('change', 'end', selectedDateTime);

                        selected($(this), startTime, 'maxDate', false);
                    }
                }, options, options.end));

                checkDates(startTime, endTime);
                selected(startTime, endTime, 'minDate', true);
                selected(endTime, startTime, 'maxDate', false);

                /**
                 * startTime should be before the endTime
                 * @param changed
                 * @param other
                 */
                function checkDates(changed, other) {
                    var startdt = startTime[method]('getDate'),
                        enddt = endTime[method]('getDate'),
                        changeddt = changed[method]('getDate');

                    if (startdt !== null) {
                        var minDate = new Date(startdt.getTime()),
                            maxDate = new Date(startdt.getTime());

                        minDate.setMilliseconds(minDate.getMilliseconds() + options.minInterval);
                        maxDate.setMilliseconds(maxDate.getMilliseconds() + options.maxInterval);

                        if (options.minInterval > 0 && minDate > enddt) { // minInterval check
                            endTime[method]('setDate', minDate);
                        }
                        else if (options.maxInterval > 0 && maxDate < enddt) { // max interval check
                            endTime[method]('setDate', maxDate);
                        }
                        else if (startdt > enddt) {
                            other[method]('setDate', changeddt);
                        }
                    }
                }

                /**
                 * Select new date
                 * @param changed
                 * @param other
                 * @param option
                 * @param isStart - if changed startTime
                 */
                function selected(changed, other, option, isStart) {
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
            };

            $.timepicker.triggeredHandleRange(
                initConfig.pickerType,
                $periodStart,
                $periodEnd,
                {
                    start: initConfig.pickerConfig,
                    end: initConfig.pickerConfig
                }
            );

            if ($filterBtn && $filterBtn.length) {
                $filterBtn.on('click', function (event) {
                    event.preventDefault();

                    periodStart = $periodStart.val();
                    periodEnd = $periodEnd.val();

                    /**
                     * @event submit
                     */
                    self.trigger('submit');
                });
            }

            if ($resetBtn && $resetBtn.length) {
                $resetBtn.on('click', function (event) {
                    event.preventDefault();

                    $periodStart.val('');
                    $periodEnd.val('');
                    $filterBtn.click();
                });
            }

        }).on('destroy', function () {
            if (hasInputs()) {
                // detach timePicker
                $periodStart.datepicker('destroy');
                $periodEnd.datepicker('destroy');
            }
        }).init(initConfig);

        return componentDateRange;
    }

    return dateRangeFactory;
});
