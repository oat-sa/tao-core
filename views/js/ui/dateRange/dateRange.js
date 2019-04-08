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
 * Copyright (c) 2016-2019 (original work) Open Assessment Technologies SA ;
 *
 */

/**
 * Component that let's you get a date range with 2 fields and the buttons to submit the value
 *
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 *
 * This component is left for backward compatibility only, please
 * consider using {@link ui/datetime/picker} instead with the range modes.
 *
 *
 * @example
 *
 * # Create new dateRange container
 * dateRangeFactory($dateRange)
 *      .on('submit', function(){
 *            var start = this.getStart();
 *            var end   = this.getEnd();
 *       });
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
     * Default configuration
     * @type {Object}
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
     * @param {HTMLElement|jQuery} container - where to append the component
     * @param {Object} [config]
     * @param {Object} [config.resetButton]
     * @param {Boolean} [config.resetButton.enable] - enable or not the reset button
     * @param {String} [config.resetButton.label] - the reset button label
     * @param {String} [config.resetButton.title] - the reset button title (HTML title)
     * @param {Boolean} [config.applyButton.enable] - enable or not the apply button
     * @param {String} [config.applyButton.label] - the apply button label
     * @param {String} [config.applyButton.title] - the apply button title (HTML title)
     * @param {Object} [config.startPicker] - the configuration sent to the start picker, see ui/datetime/picker
     * @param {Object} [config.startPicker] - the configuration sent to the end picker ,s see ui/datetime/picker
     * @fires dateRange#change when any date is changed
     * @fires dateRange#close when a picker is closed
     * @fires dateRange#submit when the submit button is clicked
     */
    function dateRangeFactory(container, config) {

        // if the picker replace fields we don't use the component template
        var useTemplate = !config ||
                (!config.startPicker.replaceField && !config.endPicker.replaceField);

        /**
         * @typedef {Object} dateRange
         */
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

                this.startPicker
                    .on('change', function(value){
                        if(value && self.endPicker && self.endPicker.is('ready')){
                            self.endPicker.updateConstraints('minDate', value);
                        }

                        /**
                         * The values get changed
                         * @event dateRange#change
                         * @param {String} target - start or end
                         * @param {String} value - the changed value
                         */
                        self.trigger('change', 'start', value);
                    })
                    .on('close', function(){

                        /**
                         * The picker get closed
                         * @event dateRange#close
                         * @param {String} target - start or end
                         * @param {String} value - the changed value
                         */
                        self.trigger('close', 'start', this.getValue());
                    })
                    .spread('error', this);

                this.endPicker
                    .on('change', function(value){
                        if(value && self.startPicker && self.startPicker.is('ready')){
                            self.startPicker.updateConstraints('maxDate', value);
                        }

                        /**
                         * @see dateRange#change
                         */
                        self.trigger('change', 'end', value);
                    })
                    .on('close', function(){

                        /**
                         * @see dateRange#close
                         */
                        self.trigger('close', 'end', this.getValue());
                    })
                    .spread('error', this);

                if (useTemplate && this.controls.filter) {

                    this.controls.filter.addEventListener('click', function(e) {
                        e.preventDefault();

                        /**
                         * The values get submitted
                         * @event dateRange#submit
                         * @param {String} start - the start/from date
                         * @param {String} end - the end/to date
                         */
                        self.trigger('submit', this.getStart(), this.getEnd());
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
                         * @see dateRange#submit
                         */
                        self.trigger('submit', '', '');
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
