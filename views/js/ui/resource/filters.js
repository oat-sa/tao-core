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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

/**
 * A filter form to select the properties you want to filter
 *
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'ui/generis/form/form',
    'tpl!ui/resource/tpl/filters',
], function ($, _, __, component, generisFormFactory, filtersTpl) {
    'use strict';

    /**
     * The list of supported properties
     *
     * FIXME add radio as soon as supported
     */
    var supportedWidgets = [
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox',
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox',
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea'
    ];

    var defaultConfig = {
        title     : __('Search by properties'),
        applyLabel: __('Apply'),
    };

    /**
     * Builds the filter component
     *
     * @param {jQueryElement} $container - where to append the component
     * @param {Object} config - the component config
     * @param {String} config.classUri - the root Class URI
     * @param {String} config.data - the root Class URI
     * @param {Object} config.data.properties - the list of properties used to filter
     * @param {Object} config.data.ranges - the property ranges
     * @param {String} [config.title] - the form title
     * @param {String} [config.applyLabel] - the label of the apply button
     * @returns {filter} the component
     */
    return function filtersFactory($container, config){

        /**
         * @typedef {ui/component}
         */
        var filters = component({

            /**
             * Get the filter values
             * @returns {Object[]} the form values
             */
            getValues : function getValues(){
                if(this.is('rendered') && this.form){
                    return this.form.getValues();
                }
                return null;
            },

            /**
             * Set the value for a given field
             * @param {String} uri - the property URI
             * @param {String|String[]} value - the field value
             * @return {filter} chains
             */
            setValue : function setValue(uri, value){
                var widget;
                if(this.is('rendered') && this.form){
                    widget = this.form.getWidget(uri);
                    if(widget){
                        widget.set(value);
                    }
                }

                return this;
            },

            /**
             * Reset the filter form
             * @return {filter} chains
             */
            reset : function reset(){
                return  this.update(this.config.data);
            },

            /**
             * Update the filter form
             * @param {Object} data - the filtering data
             * @param {Object} data.properties - the list of properties used to filter
             * @param {Object} data.ranges - the property ranges
             * @return {filter} chains
             * @fires filter#change when the user wants to apply the filter
             */
            update : function update(data){
                var self = this;
                var properties;
                if(this.is('rendered')){

                    this.getElement().empty();

                    properties = _.filter(data.properties, function(property){
                        return _.contains(supportedWidgets, property.widget);
                    });

                    this.form = generisFormFactory({
                        properties : properties,
                        values     : data.ranges
                    }, {
                        submitText : this.config.applyLabel,
                        title      : this.config.title
                    }).on('submit reset', function(){

                        /**
                         * Apply the filter values
                         * @event filter#change
                         * @param {Object} values - the filter values
                         */
                        self.trigger('change', this.getValues());
                    })
                    .render(this.getElement());
                }
                return this;
            },

            /**
             * Get a text that represents the actual query
             * @returns {String} the query
             */
            getTextualQuery : function getTextualQuery(){
                var self = this;
                var result;
                if(this.is('rendered')){
                    result = _.reduce(this.form.getValues(), function(acc, value, uri){
                        var widget =  self.form.getWidget(uri);
                        var displayValue;
                        if(widget){
                            if(!_.isEmpty(acc)){
                                acc += __(' AND ');
                            }
                            acc += widget.config.label + __(' is ');
                            if(widget.config.range){
                                displayValue = _.map(_.isArray(value) ? value : [value], function(val){
                                    var selectedValue = _.find(widget.config.range, { uri : val });
                                    return selectedValue && selectedValue.label;
                                });
                            } else {
                                displayValue = value;
                            }
                            if(_.isString(displayValue)){
                                acc += displayValue;
                            }
                            if(_.isArray(displayValue)){
                                acc += displayValue.join(', ');
                            }
                        }
                        return acc;
                    }, '');
                }
                return result;
            }
        }, defaultConfig);

        filters
            .setTemplate(filtersTpl)
            .on('init', function(){
                this.render($container);
            })
            .on('render', function(){

                if(this.config.data){
                    this.update(this.config.data);
                }
            });

        //always defer the initialization to let consumers listen for init and render events.
        _.defer(function(){
            filters.init(config);
        });

        return filters;
    };
});
