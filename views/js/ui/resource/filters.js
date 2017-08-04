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

    var defaultConfig = {
        title     : __('Filter by propety'),
        applyLabel: __('Apply'),
    };

    /**
     * Builds the filter component
     *
     * @param {jQueryElement} $container - where to append the component
     * @param {Object} config - the component config
     * @param {String} config.classUri - the root Class URI
     * @param {String} config.data - the root Class URI
     * @returns {filter} the component
     */
    return function filtersFactory($container, config){

        /**
         * @typedef {ui/component}
         */
        var filters = component({

            /**
             * Get the filte values
             * @returns {Object[]} the form values
             */
            getValues : function getValues(){
                if(this.is('rendered') && this.form){
                    return this.form.serializeArray();
                }
                return null;
            },

            /**
             * Update the filter form
             * @param {Object} data - the filtering data
             * @param {Object} data.properties - the list of propeties used to filter
             * @param {Object} data.ranges - the property ranges
             * @return {filter} chains
             * @fires filter#apply when the user wants to apply the filter
             */
            update : function update(data){
                var self = this;
                if(this.is('rendered')){

                    this.getElement().empty();

                    this.form = generisFormFactory({
                        properties : data.properties,
                        values     : data.ranges
                    }, {
                        submitText : this.config.applyLabel,
                        title      : this.config.title
                    }).on('submit', function(values){

                        /**
                         * Apply the filter values
                         * @event filter#apply
                         * @param {Object} values - the filter values
                         */
                        self.trigger('apply', values);
                    })
                    .render(this.getElement());
                }
                return this;
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
