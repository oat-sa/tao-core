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
 *
 * This component represents the app dateTimePicker form
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

    var setups = {
        'date-range' : {
            mode : 'range',
            localizedFormat : 'L',
            fieldFormat  : 'YYYY-MM-DD'
        },
        'datetime-range' : {
            mode : 'range',
            enableTime : true,
            localizedFormat : 'L LT',
            fieldFormat  : 'YYYY-MM-DD HH:mm'
        },
        date : {
            localizedFormat : 'L',
            fieldFormat  : 'YYYY-MM-DD'
        },
        time : {
            enableTime : true,
            noCalendar : true,
            localizedFormat : 'LT',
            fieldFormat  : 'HH:mm'
        },
        datetime : {
            enableTime : true,
            localizedFormat : 'L LT',
            fieldFormat  : 'YYYY-MM-DD HH:mm'
        }
    };

    var defaultConfig = {
        setup: 'date',
        triggerButton : false,
        locale : false
    };

    /**
     * The component factory
     *
     * @param {HTMLElement|jQuery} container - where to append the component
     * @param {Object} [config]
     * @param {String} [config.setup = date] - the picker setup
     * @param {String} [config.local = en]
     * @param {String} [config.dateTimeFormat = '']
     * @param {Boolean} [config.triggerButton = false] - does the field have a button on it's right to trigger the calendar opening
     * @param {Object} [config.field] - the input field configuration
     * @param {String} [config.field.name] - the input field name
     * @param {String} [config.field.id] - the input field id
     * @param {String} [config.field.placeholder] - the input field placeholder
     * @param {String} [config.field.pattern] - the input field pattern mask
     * @returns {dateTimePickerComponent} the component instance
     */
    return function dateTimePickerComponentFactory(container, options) {

        /**
         * @typedef {Object} dateTimePickerComponent
         */
        var dateTimePickerComponent = component({

            getValue : function getValue(){
                if(this.is('rendered')){
                    return this.controls.input.value;
                }
                return null;
            },

            getFormat : function getFormat(){
                var self = this;
                if( this.pickerConfig ){
                    if( this.pickerConfig.locale && this.pickerConfig.localizedFormat ){

                        return this.pickerConfig.localizedFormat.split(' ').map( function(format){
                            return moment(new Date())
                                .locale(self.pickerConfig.locale)
                                .localeData()
                                .longDateFormat(format).toLowerCase();
                        }).join(' ');
                    }

                    return this.pickerConfig.fieldFormat;
                }

                return '';
            },

            isAmPm : function isAmPm(){
                var expendedFormat = this.getFormat();
                return expendedFormat && /a$/.test(expendedFormat);
            },

            open : function open(){
                if(this.is('rendered')){
                    this.picker.open();
                }
                return this;
            },
            close : function close(){
                if(this.is('rendered')){
                    this.picker.close();
                }
                return this;
            },
            clear : function clear(){
                if(this.is('rendered')){
                    this.picker.close();
                    this.picker.clear();
                }
                return this;
            },
            toggle : function toogle(){
                if(this.is('rendered')){
                    this.picker.toggle();
                }
                return this;
            }

        }, defaultConfig);

        dateTimePickerComponent
            .setTemplate(dateTimePickerTpl)
            .on('init', function(){
                var self = this;
                this.config.field = this.config.field || {};

                if(this.config.replaceField && this.config.replaceField instanceof HTMLInputElement){
                    this.config.field.name        = this.config.replaceField.name;
                    this.config.field.placeholder = this.config.replaceField.placeholder;
                    this.config.field.value       = this.config.replaceField.value;
                    this.config.field.pattern     = this.config.replaceField.pattern;

                    this.config.replaceField.parentNode.removeChild(this.config.replaceField);
                }

                this.pickerConfig = _.defaults(setups[this.config.setup] || setups.datetime, {
                    allowInput : true
                });

                if(this.config.locale && _.isObject(flatpickrLocalization.default[this.config.locale])){
                    this.pickerConfig.locale = this.config.locale;
                }

                this.pickerConfig['time_24hr'] = !this.isAmPm();

                this.pickerConfig.formatDate = function formatDate(date){
                    var localizedMoment = moment(date);
                    var format = self.pickerConfig.fieldFormat;
                    if(self.pickerConfig.locale){
                        localizedMoment.locale(self.config.locale);
                        format = self.pickerConfig.localizedFormat;
                    }
                    return localizedMoment.format(format);
                };
                this.pickerConfig.parseDate = function parseDate(dateString){
                    if(self.pickerConfig.locale){
                        return moment(dateString, self.pickerConfig.localizedFormat, self.config.locale).toDate();
                    }
                    return moment(dateString, self.pickerConfig.fieldFormat).toDate();
                };

                if(this.config.triggerButton){
                    this.setState('triggerMode', true);
                    this.pickerConfig.clickOpens = false;
                }

                if(!this.config.field.placeholder){
                    this.config.field.placeholder = this.getFormat();
                }

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

                this.pickerConfig.appendTo = element;


                if(this.is('triggerMode')){

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
                    self.trigger('change', self.getValue());
                });

                this.picker = flatpickr(this.controls.input, this.pickerConfig);
            });

        setTimeout(function(){
            dateTimePickerComponent.init(options);
        }, 0);

        return  dateTimePickerComponent;
    };
});
