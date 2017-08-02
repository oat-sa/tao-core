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
 * The resource selector component
 * Handles multiple view/selection formats (now tree and list).
 *
 * Let's you change the root class and filter by labels.
 *
 * The data flow is based on the query/update model :
 *
 * @example
 * resourceSelectorFactory(container, config)
 *     .on('query', function(params){
 *         var self = this;
 *         fetch('someurl', params).then(nodes){
 *             self.update(nodedata, params);
 *         });
 *     });
 *
 *
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'core/promise',
    'ui/component',
    'ui/class/selector',
    'ui/resource/tree',
    'ui/resource/list',
    'tpl!ui/resource/tpl/selector',
    'css!ui/resource/css/selector.css',
], function ($, _, __, Promise, component, classesSelectorFactory, treeFactory, listFactory, selectorTpl) {
    'use strict';


    var defaultConfig = {
        type : __('resources'),
        icon : 'item',
        multiple : true,
        formats : {
            list : {
                icon  : 'icon-ul',
                title : __('View results as a list'),
                componentFactory : listFactory
            },
            tree : {
                icon  : 'icon-tree',
                title : __('View results as a tree'),
                componentFactory : treeFactory,
                active : true
            }
        },
        limit: 30
    };

    /**
     * The factory that creates the resource selector component
     *
     * @param {jQueryElement} $container - where to append the component
     * @param {Object} config - the component config
     * @param {String} config.classUri - the root Class URI
     * @param {Object[]} config.formats - the definition of the supported viewer/selector component
     * @param {Objet[]} [config.nodes] - the nodes to preload, the format is up to the formatComponent
     * @param {String} [config.icon] - the icon class that represents a resource
     * @param {String} [config.type] - describes the resource type
     * @param {Boolean} [config.multiple = true] - multiple vs unique selection
     * @param {Number} [config.limit = 30] - the default page size for data paging
     * @returns {resourceSelector} the component
     */
    return function resourceSelectorFactory($container, config){
        var $classContainer;
        var $resultArea;
        var $searchField;
        var $viewFormats;
        var $selectNum;
        var $selectCtrl;
        var $selectCtrlLabel;

        var resourceSelectorApi = {

            /**
             * Reset the component
             * @returns {resourceSelector} chains
             * @fires resourceSelector#reset
             */
            reset : function reset(){
                if(this.is('rendered')){
                    if(this.selectionComponent){
                        this.selectionComponent.destroy();
                        this.selectionComponent = null;
                    }
                }
                return this.trigger('reset');
            },

            /**
             * Get the selected nodes
             * @returns {Object?} the selection
             */
            getSelection : function getSelection(){
                if(this.selectionComponent){
                    return this.selectionComponent.getSelection();
                }
                return null;
            },

            /**
             * Clear the current selection
             * @returns {resourceSelector} chains
             */
            clearSelection : function clearSelection(){
                if(this.selectionComponent){
                    this.selectionComponent.clearSelection();
                }
                return this;
            },

            /**
             * Ask for a query (forward the event)
             * @param {Object} [params] - the query parameters
             * @param {String} [params.classUri] - the current node class URI
             * @param {String} [params.format] - the selected format
             * @param {String} [params.pattern] - label filtering pattern
             * @param {Number} [params.offset = 0] - for paging
             * @param {Number} [params.limit] - for paging
             * @returns {resourceSelector} chains
             * @fires resourceSelector#query
             */
            query : function query(params){
                if(this.is('rendered')){

                    /**
                     * Formulate the query
                     * @event resourceSelector#query
                     * @param {Object} params - see format above
                     */
                    this.trigger('query', _.defaults(params || {}, {
                        classUri: this.classUri,
                        format:   this.format,
                        pattern:  $searchField.val(),
                        limit  : this.config.limit
                    }));
                }
                return this;
            },

            /**
             * Switch the format, so the viewer/selector component
             * @param {String} format - the new format
             * @returns {resourceSelector} chains
             * @fires resourceSelector#formatchange
             */
            changeFormat : function changeFormat(format){
                var $viewFormat;
                if(this.is('rendered')){

                    $viewFormat = $viewFormats.filter('[data-view-format="' + format + '"]');
                    if($viewFormat.length === 1 && !$viewFormat.hasClass('active')){

                        $viewFormats.removeClass('active');
                        $viewFormat.addClass('active');

                        this.format = format;

                        /**
                         * The view format has changed
                         * @event resourceSelector#formatchange
                         * @param {String} format - the new format name
                         */
                        this.trigger('formatchange', format);
                    }
                }
                return this;
            },

            /**
             * Update the component with the given resources
             * @param {Object[]} resources - the data, with at least a URI as key and as property
             * @param {Object} params - the query parameters
             * @returns {resourceSelector} chains
             * @fires resourceSelector#update
             * @fires resourceSelector#change
             * @fires resourceSelector#error
             */
            update: function update(resources, params){
                var self = this;

                var componentFactory;

                if(this.is('rendered') && this.format){

                    componentFactory = this.config.formats[this.format] && this.config.formats[this.format].componentFactory;
                    if(!_.isFunction(componentFactory)){
                        return this.trigger('error', new TypeError('Unable to load the component for the format ' + this.format));
                    }

                    if(params.new && this.selectionComponent){
                        this.selectionComponent.destroy();
                        this.selectionComponent = null;
                    }

                    if(!this.selectionComponent || params.new){

                        this.selectionComponent = componentFactory($resultArea, _.defaults({
                            classUri : this.classUri,
                            nodes    : resources
                        }, this.config))
                        .on('query', function(queryParams){
                            self.query(queryParams);
                        })
                        .on('update', function(){
                            self.trigger('update');
                        })
                        .on('change', function(selected){
                            self.trigger('change', selected);
                        })
                        .on('error', function(err){
                            self.trigger('error', err);
                        });

                    } else {
                        this.selectionComponent.update(resources, params);
                    }
                }
            }
        };

        /**
         * The resource selector component
         * @typedef {ui/component} resourceSelector
         */
        var resourceSelector = component(resourceSelectorApi, defaultConfig)
            .setTemplate(selectorTpl)
            .on('init', function(){

                this.classUri = this.config.classUri;
                this.format   = this.config.format || _.findKey(this.config.formats, { active : true });

                this.render($container);
            })
            .on('render', function(){
                var self = this;

                //we ensure the sub-components are rendered
                return new Promise(function(resolve){
                    var $component = self.getElement();

                    $classContainer  = $('.class-context', $component);
                    $resultArea      = $('main', $component);
                    $searchField     = $('.search input', $component);
                    $viewFormats     = $('.context > a', $component);
                    $selectNum       = $('.selected-num', $component);
                    $selectCtrl      = $('.selection-control input', $component);
                    $selectCtrlLabel = $('.selection-control label', $component);

                    //the search field
                    $searchField.on('keydown', function(e){
                        var value = $(this).val().trim();
                        if(value.length > 2 || e.which === 13){
                            self.query({ 'new' : true });
                        }
                    });

                    //the format switcher
                    $viewFormats.on('click', function(e) {
                        var $target = $(this);
                        var format = $target.data('view-format');
                        e.preventDefault();

                        self.changeFormat(format);
                    });

                    //the select all control
                    $selectCtrl.on('change', function(){
                        if($(this).prop('checked') === false){
                            self.selectionComponent.clearSelection();
                        } else {
                            self.selectionComponent.selectAll();
                        }
                    });

                    //initialize the class selector
                    self.classSelector = classesSelectorFactory($classContainer, self.config);
                    self.classSelector
                        .on('render', resolve)
                        .on('change', function(uri){
                            if(uri && uri !== self.classUri){
                                self.classUri = uri;
                                self.query({ 'new' : true });
                            }
                        });
                    self.query();
                });
            })
            .on('formatchange', function(){
                this.trigger('change', {});
                this.query({ 'new' : true});
            })
            .on('change', function(selected){

                var nodesCount = _.size(this.selectionComponent.getNodes());
                var selectedCount = _.size(selected);

                $selectNum.text(selectedCount);

                if(selectedCount === 0 ){
                    $selectCtrlLabel.attr('title', __('Select loaded %s', this.config.type));
                    $selectCtrl.prop('checked', false)
                               .prop('indeterminate', false);
                } else if (selectedCount === nodesCount) {
                    $selectCtrlLabel.attr('title', __('Clear selection'));
                    $selectCtrl.prop('checked', true)
                               .prop('indeterminate', false);
                } else {
                    $selectCtrlLabel.attr('title', __('Select loaded %s', this.config.type));
                    $selectCtrl.prop('checked', false)
                               .prop('indeterminate', true);
                }
            });

        _.defer(function(){
            resourceSelector.init(config);
        });
        return resourceSelector;
    };
});
