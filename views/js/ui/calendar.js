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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *               
 */
define(
    [
        'lodash', 
        'jquery', 
        'moment', 
        'jqueryui',
        'jquery.timePicker'
    ],
    function (_, $, moment) {
        'use stirct';
        /**
         * Timepicker widget.
         * 
         * @constructor
         * @property {object}  options Widget options
         * @property {string}  options.selector An input element selector
         * @property {string}  options.altFieldSelector An input element whoose value will be send to server
         * @property {string}  options.format The date format.
         * @property {string}  options.pickerOptions.timezoneList An array of timezones used to populate the timezone select. 
         *                                          Example: <pre>[{label: "+03:00", value: +180}]</pre>
         *                                    
         */
        return function (options) {
            var that = this,
                timezoneOffset,
                timezoneName;
        
            this.init = function () {
                
                
                var $el = $(options.selector),
                    $altEl = $(options.altFieldSelector),
                    defaultOptions = {
                        format : 'YYYY-MM-DD HH:mm',
                        pickerOptions : {
                            dateFormat : 'yy-mm-dd',
                            timeFormat : 'HH:mm z',
                            onSelect : function (val, ui) {
                                var m = moment(val, options.format),
                                    utc;
                                utc = moment((m.unix() - (ui.timezone * 60)) * 1000);
                                $altEl.val(utc.format(options.format));
                            },
                            showTimezone : true,
                            showMillisec : false,
                            showMicrosec : false,
                            showSecond : false,
                            beforeShow : function (textbox, instance) {
                                $(textbox).parent().append(instance.dpDiv);
                            },
                            afterInject : function () {
                                if (!this.timezone_select.data('new')) {
                                    this.timezone_select.find('option').prop('selected', false);
                                    this.timezone_select.find('option:contains("' + timezoneName + '")').prop('selected', true);
                                    this.timezone_select.data('new', true);
                                }
                                timezoneName = this.timezone_select.find(":selected").text();
                            }
                        }
                    };
            
                options = _.merge(defaultOptions, options);
                
                timezoneName = $altEl.data('timezone');
                timezoneOffset = this.getTimezoneOffset(timezoneName);
                
                if (!$el.length || !$altEl.length) {
                    throw new Error("Calendar requires selector and altFieldSelector options of existing DOM elements");
                }

                $el.datetimepicker(options.pickerOptions);
            };
            
            /**
             * 
             * @returns {undefined}
             */
            this.getTimezoneOffset = function (tzName) {
                var value;
                if (options.pickerOptions.timezoneList) {
                    value = _.result(_.find(options.pickerOptions.timezoneList, {label: tzName}), 'value');
                }
                if (value === undefined) {
                    value = (new Date()).getTimezoneOffset() * -1;
                }
                return value;
            };
            
            /**
             * Convert time to user time zone
             * @param {string} val Time value (must much <b>options.format</b> format)
             * @returns {string} Time value in <b>options.format</b> format
             */
            this.UTCToUser = function (val) {
                var m = moment(val, options.format),
                    utc;
                utc = moment((m.unix() - (timezoneOffset * 60)) * 1000);
                return utc.format(options.format);
            };
            
            this.init();
        };
    }
);


