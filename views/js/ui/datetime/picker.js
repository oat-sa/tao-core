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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA
 *
 */

/**
 * Date/Time picker component.
 * It supports different setups : date-range, datetime-range, date, time and datetime
 * It supports localized format.
 * It supports either hooking a field, replacing it and adding controls.
 *
 * It wraps the library Flatpickr  (https://flatpickr.js.org)
 *
 * @example
 *      dateTimePicker(container, {
 *          setup : 'date',
 *          format : 'YYYY-MM-DD',
 *          controlButtons : true
 *      })
 *      .on('change', function(value){
 *         if (value === '1983-04-03'){
 *              //...
 *         }
 *      });
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'i18n',
    'moment',
    'ui/component',
    'lib/flatpickr/flatpickr',
    'lib/flatpickr/l10n/index',
    'tpl!ui/datetime/tpl/picker',
    'css!lib/flatpickr/flatpickr.css',
    'css!ui/datetime/css/picker.css'

], function(_, __, moment, component, flatpickr, flatpickrLocalization, dateTimePickerTpl){
    'use strict';

    /**
     * The supported formats
     */
    var formats = {
        date : {
            default : 'YYYY-MM-DD',
            localized : 'L'
        },
        time : {
            default :  'HH:mm',
            localized : 'LT'
        },
        datetime : {
            default : 'YYYY-MM-DD HH:mm',
            localized : 'L LT'
        }
    };

    /**
     * Possible setups for the picker
     */
    var setups = {
        'date-range' : {
            mode : 'range',
            label : __('date range'),
            format : formats.date
        },
        'datetime-range' : {
            mode : 'range',
            label : __('date time range'),
            enableTime : true,
            format : formats.datetime
        },
        date : {
            mode : 'single',
            format : formats.date
        },
        time : {
            mode : 'single',
            enableTime : true,
            label : __('time'),
            noCalendar : true,
            format : formats.time
        },
        datetime : {
            mode : 'single',
            enableTime : true,
            label : __('date time'),
            format : formats.datetime
        }
    };

    /**
     * List of supported constraints
     */
    var supportedConstraints = ['minDate', 'maxDate', 'enable', 'disable'];

    /**
     * The default configuration
     * @see dateTimePickerFactory
     */
    var defaultConfig = {
        setup : 'date',
        controlButtons : false,
        locale : false,
        useLocalizedFormat : false,
        constraints : {}
    };

    /**
     * Get the long date/time format from the localized format (LT to 'DD/MM/YYYY HH:mm')
     * @param {String} locale - 2 digits locale code (en, fr, de, etc.)
     * @param {String} localizedFormat - see moment's localized format (L, LT, LLLL, ...)
     * @returns {String} the long date/time format
     */
    var getLongLocalizedFormat = function getLongLocalizedFormat(locale, localizedFormat) {
        if (/[LT]+/.test(localizedFormat) && locale) {
            return localizedFormat.split(' ').map( function(format){
                return moment(new Date())
                    .locale(locale)
                    .localeData()
                    .longDateFormat(format);
            }).join(' ');
        }
        return false;
    };

    /**
     * Does the given date/time format uses the am/pm pattern ?
     * @param {String} format - moment format
     * @returns {Boolean} true if the contains am/pm
     */
    var isFormatAmPm = function isFormatAmPm(format) {
        return format && /a$/i.test(format);
    };

    /**
     * Does the given date/time format contains seconds ?
     * @param {String} format - moment format
     * @returns {Boolean} true if the format contains seconds
     */
    var isFormatInSeconds = function isFormatInSeconds(format) {
        return format && /(:ss)+/i.test(format);
    };

    /**
     * The component factory
     *
     * @param {HTMLElement|jQuery} container - where to append the component
     * @param {Object} [config]
     * @param {String} [config.setup = date] - the picker setup in date-range, datetime-range, date, time and datetime
     * @param {String} [config.locale] - the picker local
     * @param {String} [config.useLocalzedFormat = false] - does the locale is used to define the format
     * @param {String} [config.format] - define your own date/time format for the instance
     * @param {Boolean} [config.controlButtons = false] - does the field have controls to trigger opening and reset
     * @param {Object} [config.constraints] - date time selection constraints
     * @param {Object} [config.constraints] - date time selection constraints
     * @param {Array<String|Date>} [config.constraints.disable] - list of dates to disable
     * @param {Array<String|Date>} [config.constraints.enable] - list of dates to enable (if some are disabled)
     * @param {String|Date} [config.constraints.minDate] - minimum date to start picking from
     * @param {String|Date} [config.constraints.maxDate] - maximum date to start picking from
     *
     * @param {HTMLInputElement} [config.replaceField] - an input field to replace. The field attr are taken instead of config.field
     * @param {Object} [config.field] - the input field configuration
     * @param {String} [config.field.name] - the input field name
     * @param {String} [config.field.id] - the input field id
     * @param {String} [config.field.placeholder] - the input field placeholder
     * @param {String} [config.field.pattern] - the input field pattern mask
     * @param {String} [config.field.value] - the input field value
     * @param {String} [config.field.label] - label the field for a11y
     *
     * @returns {dateTimePickerComponent} the component instance
     */
    return function dateTimePickerFactory(container, options) {
        var format = '';

        /**
         * @typedef {Object} dateTimePicker
         */
        var dateTimePicker = component({

            /**
             * Get the current value
             * @returns {String} the field value, null if none
             */
            getValue : function getValue() {
                if (this.is('rendered')) {
                    return this.controls.input.value;
                }
                return null;
            },

            /**
             * Set the current value
             * @param {String} value - the new value matching the format
             */
            setValue : function setValue(value) {
                if (this.is('ready')) {
                    if (_.isString(value)) {
                        this.controls.input.value = value;
                    }
                    this.picker.setDate(value,  true);
                }
                return null;
            },

            /**
             * Get the date/time format description, ie. 'YYYY-MM-DD'
             * @returns {String} the format
             */
            getFormat : function getFormat() {
                return format;
            },

            /**
             * Open the picker
             * @returns {dateTimePicker} chains
             * @fires dateTimePicker#open
             */
            open : function open() {
                if (this.is('ready')) {
                    this.picker.open();
                }
                return this;
            },

            /**
             * Close the picker
             * @returns {dateTimePicker} chains
             * @fires dateTimePicker#close
             */
            close : function close() {
                if (this.is('ready')) {
                    this.picker.close();
                }
                return this;
            },

            /**
             * Clear the field content and close the picker
             * @returns {dateTimePicker} chains
             * @fires dateTimePicker#close
             * @fires dateTimePicker#clear
             */
            clear : function clear() {
                if (this.is('ready')) {
                    this.picker.close();
                    this.picker.clear();

                    /**
                      * The picker get cleared
                      * @event dateTimePicker#clear
                      */
                    this.trigger('clear');
                }
                return this;
            },

            /**
             * Clear the field content and close the picker
             * @returns {dateTimePicker} chains
             * @fires dateTimePicker#open
             * @fires dateTimePicker#close
             */
            toggle : function toogle() {
                if (this.is('ready')) {
                    this.picker.toggle();
                }
                return this;
            },

            /**
             * Update constraints on a running instance
             * @param {String} constraint - the constraint name in minDate, maxDate, enable, disable
             * @param {*} vlaue - the constraint value to update
             * @returns {dateTimePicker} chains
             * @fires dateTimePicker#open
             * @fires dateTimePicker#close
             */
            updateConstraints : function updateConstraints(constraint, value){
                if (this.is('ready')) {
                    if (_.contains(supportedConstraints, constraint)) {
                        this.picker.set(constraint, value);
                    }
                }
                return this;
            }

        }, defaultConfig);

        dateTimePicker
            .setTemplate(dateTimePickerTpl)
            .on('init', function(){
                var self = this;

                var locale;
                var setup = setups[this.config.setup] || setups.datetime;

                //map the locale from the options to the picker locale
                if (this.config.locale && _.isObject(flatpickrLocalization.default[this.config.locale])) {
                    locale = this.config.locale;
                }

                //date/time format
                if (locale && this.config.useLocalizedFormat) {

                    //get the format from the locale
                    format = getLongLocalizedFormat(locale, setup.format.localized);

                } else {

                    //get the format from the config
                    format = this.config.format || setup.format.default;
                }

                //input field configuration
                this.config.field = this.config.field || {};

                // replace a field by the date picker input field
                //TODO consider replacing data-attr and classes
                if (this.config.replaceField && this.config.replaceField instanceof HTMLInputElement) {
                    this.config.field.id          = this.config.replaceField.id;
                    this.config.field.name        = this.config.replaceField.name;
                    this.config.field.placeholder = this.config.replaceField.placeholder;
                    this.config.field.value       = this.config.replaceField.value;
                    this.config.field.pattern     = this.config.replaceField.pattern;

                    this.config.replaceField.parentNode.removeChild(this.config.replaceField);
                }

                if (!this.config.field.placeholder && format && setup.mode === 'single') {
                    this.config.field.placeholder = format.toLowerCase();
                }
                if (!this.config.field.label) {
                    this.config.field.label = setup.label;
                }

                /**
                 * Build the configuration of the picker
                 * @see https://flatpickr.js.org/options/
                 */
                this.pickerConfig = {
                    mode :          setup.mode,
                    enableTime :    !!setup.enableTime,
                    noCalendar :    !!setup.noCalendar,
                    time_24hr :     !isFormatAmPm(format),
                    enableSeconds : setup.enableTime && isFormatInSeconds(format),
                    allowInput :    true,
                    clickOpens :    !this.config.controlButtons,
                    disableMobile : true,

                    /**
                     * How flatpickr will format the given date
                     * @param {Date} date
                     * @returns {String} the formatted date
                     */
                    formatDate : function formatDate(date) {
                        return moment(date).format(format);
                    },

                    /**
                     * How flatpickr parse the given input
                     * @param {String} dateString
                     * @returns {Date}
                     */
                    parseDate : function parseDate(dateString) {
                        return moment(dateString, format).toDate();
                    },

                    /**
                     * When the picker is opened
                     * @fires dateTimePicker#open
                     */
                    onOpen : function onOpen(){

                        /**
                         * The picker get opened
                         * @event dateTimePicker#open
                         */
                        self.trigger('open');
                    },

                    /**
                     * When the picker is opened
                     * @fires dateTimePicker#close
                     */
                    onClose : function onClose(){

                        /**
                         * The picker get closed
                         * @event dateTimePicker#close
                         */
                        self.trigger('close');
                    },

                    /**
                     * Hook flatpickr error handler
                     * @param {Error} err - the thrown error
                     */
                    errorHandler : function errorHandler(err){
                        if(err instanceof Error){
                            //if an invalid date is provided
                            //add a visual feedback indicating why the field get emptied
                            if(/^Invalid date/.test(err.message)){
                                self.controls.input.classList.add('error');
                                _.delay(function(){
                                    self.controls.input.classList.remove('error');
                                }, 1000);
                            } else {

                                /**
                                 * Unexpected error
                                 * @event dateTimePicker#error
                                 * @param {Error} err
                                 */
                                self.trigger('error', err);
                            }
                        }
                    }
                };
                //locale should be defined only if set...
                if(locale){
                    this.pickerConfig.locale = locale;
                }

                _.forEach(this.config.constraints, function(value, constraint){
                    if(_.contains(supportedConstraints, constraint) && value){
                        self.pickerConfig[constraint] = value;
                    }
                });


                //render into the container
                if(container){
                    setTimeout(function(){
                        self.render(container);
                    }, 0);
                }
            })
            .on('render', function(){
                var self    = this;

                var element = this.getElement()[0];

                this.controls = {
                    input : element.querySelector('input'),
                };

                //always scope the picker to the component container
                //in order to scope and style each instance
                this.pickerConfig.appendTo = element;

                //behavior of the right buttons if configured
                if(this.config.controlButtons){

                    this.controls.toggleButton = element.querySelector('.picker-toggle'),
                    this.controls.clearButton  = element.querySelector('.picker-clear'),

                    this.controls.toggleButton.addEventListener('click', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        self.toggle();
                    });

                    this.controls.clearButton.addEventListener('click', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        self.clear();
                    });
                }

                this.controls.input.addEventListener('change', function(){

                    /**
                      * A value get changed
                      * @event dateTimePicker#change
                      * @param {String} value - the date/time value
                      */
                    self.trigger('change', self.getValue());
                });


                //instantiate the picker
                _.defer(function(){
                    self.picker = flatpickr(self.controls.input, self.pickerConfig);

                    self.enable()
                        .setState('ready', true)
                        .trigger('ready');
                });
            })
            .on('enable', function(){
                if(this.controls){
                    this.controls.input.disabled = false;
                    if(this.config.controlButtons){
                        this.controls.toggleButton.disabled = false;
                        this.controls.clearButton.disabled  = false;
                    }
                }
            })
            .on('disable', function(){
                if(this.controls){
                    this.controls.input.disabled = true;
                    if(this.config.controlButtons){
                        this.controls.toggleButton.disabled = true;
                        this.controls.clearButton.disabled  = true;
                    }
                }
            });

        //defered init to catch the event
        setTimeout(function(){
            dateTimePicker.init(options);
        }, 0);

        return  dateTimePicker;
    };
});
