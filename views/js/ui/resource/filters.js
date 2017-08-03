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

    };

    /**
     * Builds the resource list component
     *
     * @param {jQueryElement} $container - where to append the component
     * @param {Object} config - the component config
     * @param {String} config.classUri - the root Class URI
     * @returns {} the component
     */
    return function filtersFactory($container, config){

        /**
         * @typedef {ui/component}
         */
        var filters = component({
            getValues : function getValues(){
                if(this.is('rendered') && this.form){
                    return this.form.serializeArray();
                }
                return null;
            }
        }, defaultConfig);

        filters
            .setTemplate(filtersTpl)
            .on('init', function(){
                this.form = generisFormFactory({
                    properties : this.config.data.properties,
                    values     : this.config.data.ranges
                }, {
                    submitText: __('Apply'),
                    title: __('Filtering')
                });

                this.render($container);
            })
            .on('render', function(){
                var self = this;

                var $element = this.getElement();

                this.form
                    .on('submit', function(values){
                        self.trigger('apply', values);
                    })
                    .render($element);
            });

        //always defer the initialization to let consumers listen for init and render events.
        _.defer(function(){
            filters.init(config);
        });

        return filters;
    };
});
