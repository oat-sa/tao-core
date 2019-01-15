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
    'lib/daterangepicker/daterangepicker',
    'tpl!ui/datetime/tpl/picker',
    //'css!ui/datetime/css/picker.css',
    'css!lib/daterangepicker/daterangepicker.css'
], function(_, __, component, DateRangePicker, dateTimePickerTpl){
    'use strict';

    var setups = {
        range : {

        },
        date : {
            singleDatePicker: true,
        },
        time : {
            timePicker : true
        },
        datetime : {
            singleDatePicker: true,
            timePicker : true,
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


                new DateRangePicker(input, _.defaults(setups[config.setup], {
                    buttonClasses : 'small',
                    applyButtonClasses : 'btn-info',
                    cancelButtonClasses: 'btn-button',
                    //parentEl : element
                }));

            });

        setTimeout(function(){
            dateTimePickerComponent.init(config);
        }, 0);

        return  dateTimePickerComponent;
    };
});
