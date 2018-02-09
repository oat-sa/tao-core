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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */

/**
 * Let's you select a destination class in a move or a copy
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
     * Creates the selector component
     * @param {jQueryElement} $container - where the component is rendered
     * @param {Object} [config] - the configuration
     * @param {String} [config.classUri] - the root classUri
     * @param {String} [config.title] - header
     * @param {String} [config.description] - a description sentence
     * @param {String} [config.actionName] - the action button text
     * @param {String} [config.icon] - the action button icon
     * @param {Function} [config.preventSelection] - prevent selection callback (@see ui/resource/selectable)
     * @returns {destinationSelector} the component itself
     */
    return function destinationSelectorFactory($container, config){

        /**
         * @typedef {destinationSelector} the component
         */
        var destinationSelector = component({

            /**
             * Forwards data update to it's resource selector
             * @see ui/resource/selector#update
             */
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

                //set up the inner resource selector
                this.resourceSelector = resourceSelectorFactory($('.selector-container', $component), {
                    selectionMode: 'single',
                    selectClass : true,
                    classUri: this.config.classUri,
                    showContext : false,
                    showSelection : false,
                    preventSelection : this.config.preventSelection
                });

                //spread the events
                this.resourceSelector.spread(this, ['query', 'error', 'update']);

                //enable disable the action button
                this.resourceSelector.on('change', function(selected){
                    if(selected && _.size(selected) > 0){
                        $action.removeProp('disabled');
                    } else {
                        $action.prop('disabled', true);
                    }
                });

                //validate the selection
                $action.on('click', function(e){
                    var uris;
                    var selection = self.resourceSelector.getSelection();
                    e.preventDefault();

                    if(_.isPlainObject(selection)) {
                        uris = _.pluck(selection, 'uri');
                        if(uris.length){

                            /**
                             * @event destinationSelector#select
                             * @param {String} classUri - the destination class
                             */
                            self.trigger('select', uris[0]);
                        }
                    }
                });
            });

        _.defer(function(){
            destinationSelector.init(config);
        });

        return destinationSelector;
    };
});
