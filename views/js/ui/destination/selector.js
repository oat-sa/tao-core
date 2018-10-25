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
    'ui/dialog/confirm',
    'ui/resource/selector',
    'ui/loadingButton/loadingButton',
    'ui/taskQueueButton/standardButton',
    'tpl!ui/destination/tpl/selector',
    'css!ui/destination/css/selector.css'
], function ($, _, __, component, confirmDialog, resourceSelectorFactory, loadingButtonFactory, taskCreationButtonFactory, selectorTpl) {
    'use strict';

    var defaultConfig = {
        title : __('Copy to'),
        description : __('Select a destination'),
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
     * @param {String} [config.confirm] - when defined, confirmation message that will be displayed before triggering the action
     * @param {String} [config.actionName] - the action button text
     * @param {String} [config.icon] - the action button icon
     * @param {Object} [config.taskQueue] - define the taskQueue model to be used (only useful if the triggered action uses the task queue)
     * @param {String} [config.taskCreationUrl] - the task creation endpoint (only required if the option taskQueue is defined)
     * @param {Object} [config.taskCreationData] - optionally define the data that will be sent to the task creation endpoint
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
                var button, self = this;
                var $component = this.getElement();

                /**
                 * Get the current selected class uri
                 * @returns {String} the selected uri
                 */
                var getSelectedUri = function getSelectedUri(){
                    var select = self.resourceSelector.getSelection();
                    var uris;
                    //validate the selection
                    if(_.isPlainObject(select)) {
                        uris = _.pluck(select, 'uri');
                        if(uris.length){
                            return uris[0];
                        }
                    }
                };

                if(this.config.taskQueue){
                    button = taskCreationButtonFactory({
                        type : 'info',
                        icon : this.config.icon,
                        label : this.config.actionName,
                        terminatedLabel : 'Interrupted',
                        taskQueue: this.config.taskQueue,
                        taskCreationData: this.config.taskCreationData || {},
                        taskCreationUrl: this.config.taskCreationUrl,
                        taskReportContainer: $container
                    }).on('finished', function(result){
                        self.trigger('finished', result, button);
                        this.reset();//reset the button
                    }).on('continue', function(){
                        self.trigger('continue');
                    });
                }else{
                    button = loadingButtonFactory({
                        type : 'info',
                        icon : this.config.icon,
                        label : this.config.actionName,
                        terminatedLabel : 'Interrupted'
                    });
                }

                button.on('started', function(){
                    function triggerAction() {
                        /**
                         * @event destinationSelector#select
                         * @param {String} classUri - the destination class
                         */
                        self.trigger('select', getSelectedUri());
                    }

                    if (self.config.confirm) {
                        confirmDialog(self.config.confirm, triggerAction, function() {
                            button.terminate()
                                .reset();
                        });
                    } else {
                        triggerAction();
                    }
                }).on('error', function(err){
                    self.trigger('error', err);
                }).render($component.find('.actions')).disable();

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
                        button.enable();

                        //append the selected class URI to the task creation data
                        if(_.isPlainObject(button.config.taskCreationData)){
                            button.config.taskCreationData.classUri = getSelectedUri();
                        }
                    } else {
                        button.disable();
                    }
                });
            });

        _.defer(function(){
            destinationSelector.init(config);
        });

        return destinationSelector;
    };
});
