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
    'ui/component',
    'lib/flatpickr/flatpickr',
    'tpl!ui/datetime/tpl/picker',
    //'css!ui/datetime/css/picker.css',
    'css!lib/flatpickr/flatpickr.css'
], function(_, __, component, flatpickr, dateTimePickerTpl){
    'use strict';

    var setups = {
        range : {
            dateFormat : 'd/m/Y',
            mode : 'range'
        },
        date : {
            dateFormat : 'd/m/Y'
        },
        time : {
            enableTime : true,
            noCalendar : true
        },
        datetime : {
            dateFormat : 'd/m/Y H:i',
            enableTime : true
        }
    };
    var defaultConfig = {
        setup: 'date'
    };

    /**
     * The component factory
     *
     * @param {HTMLElement|jQuery} container - where to append the component
     * @param {Object} [config]
     * @returns {dateTimePickerComponent} the component instance
     */
    return function dateTimePickerComponentFactory(container, config) {

        /**
         * @typedef {Object} dateTimePickerComponent
         */
        var dateTimePickerComponent = component({


        }, defaultConfig);

        dateTimePickerComponent
            .setTemplate(dateTimePickerTpl)
            .on('init', function(){
                var self = this;

                if(container){
                    setTimeout(function(){
                        self.render(container);
                    }, 0);
                }
            })
            .on('render', function(){
                var self    = this;

                var element = this.getElement()[0];
                var input   = element.querySelector('input');
                var pickerConfig = _.defaults(setups[config.setup], {
                    allowInput : true,

                });

                console.log(pickerConfig);
                this.picker = flatpickr(input, pickerConfig);
            });

        setTimeout(function(){
            dateTimePickerComponent.init(config);
        }, 0);

        return  dateTimePickerComponent;
    };
});
