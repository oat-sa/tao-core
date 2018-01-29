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
 * A Class Selector component
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'ui/resource/selector',
    'tpl!ui/destination/tpl/selector',
    'css!ui/destination/css/selector.css'
], function ($, _, __, component, resourceSelectorFactory, selectorTpl) {
    'use strict';

    var defaultConfig = {
        title : __('Copy to'),
        description : __('Select a destination class'),
        actionName : __('Copy'),
        icon : 'copy'
    };

    /**
     * @param {jQueryElement} $container - where the component is rendered
     * @param {Object} [config] - the configuration
     * @returns {destinationSelector} the component itself
     */
    return function destinationSelectorFactory($container, config){

        /**
         * @typedef {destinationSelector} the component
         */
        var destinationSelector = component({

            update : function udpate(results, params){
                if(this.resourceSelector){
                    this.resourceSelector.update(results, params);
                }
            }
        }, defaultConfig)
            .setTemplate(selectorTpl)
            .on('init', function(){


                this.render($container);
            })
            .on('render', function(){
                var self = this;
                var $component = this.getElement();
                var $action    = $('.action', $component);


                this.resourceSelector = resourceSelectorFactory($('.selector-container', $component), {
                    selectionMode: 'single',
                    selectClass : true,
                    classUri: this.config.classUri,
                    showContext : false,
                    showSelection : false
                });

                this.resourceSelector.spread(this, ['query', 'error']);

                this.resourceSelector.on('change', function(selected){
                    if(selected){
                        $action.removeProp('disabled');
                    } else {
                        $action.prop('disabled', true);
                    }
                });

                $action.on('click', function(e){
                    e.preventDefault();

                    self.trigger('select', self.resourceSelector.getSelection());
                });

/*                .on('query', function(params) {
                    var self = this;

                    params.classOnly = true;
                    resourceProvider().getResources(params).then(function(results){
                        var resources;
                        if (results && results.resources){
                            resources = results.resources;
                        } else {
                            resources = results;
                        }

                        //ask the server the resources from the component query
                        self.update(resources, params);
                    });

                })
                .on('error', function(err){
                    console.error(err);
                });

*/
            });

        _.defer(function(){
            destinationSelector.init(config);
        });

        return destinationSelector;
    };
});
