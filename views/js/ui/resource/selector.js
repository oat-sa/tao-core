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
 * FIXME search and advanced search switch to the list format
 * because backend implementation doesn't support well the
 * tree behavior.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'core/promise',
    'ui/component',
    'ui/hider',
    'ui/class/selector',
    'ui/resource/tree',
    'ui/resource/list',
    'ui/resource/filters',
    'tpl!ui/resource/tpl/selector',
    'css!ui/resource/css/selector.css',
], function ($, _, __, Promise, component, hider, classesSelectorFactory, treeFactory, listFactory, filtersFactory, selectorTpl) {
    'use strict';

    var labelUri = 'http://www.w3.org/2000/01/rdf-schema#label';

    var defaultConfig = {
        type : __('resources'),
        noResultsText : _('No resources found'),
        searchPlaceholder : __('Search'),
        icon : 'item',
        multiple : true,
        filters: false,
        formats : {
            list : {
                icon  : 'icon-ul',
                title : __('View resources as a list'),
                componentFactory : listFactory
            },
            tree : {
                icon  : 'icon-tree',
                title : __('View resources as a tree'),
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
     * @param {Object|Boolean} [config.filters = false] - false or filters config, see ui/resource/filters
     * @returns {resourceSelector} the component
     */
    return function resourceSelectorFactory($container, config){
        var $classContainer;
        var $resultArea;
        var $noResults;
        var $searchField;
        var $viewFormats;
        var $selectNum;
        var $selectCtrl;
        var $selectCtrlLabel;
        var $filterToggle;
        var $filterContainer;

        var resourceSelectorApi = {

            /**
             * Empty the selection component
             * @returns {resourceSelector} chains
             * @fires resourceSelector#empty
             */
            empty : function empty(){
                if(this.is('rendered')){
                    if(this.selectionComponent){
                        this.selectionComponent.destroy();
                        this.selectionComponent = null;
                    }
                }
                return this.trigger('empty');
            },

            /**
             * Reset the component
             * @returns {resourceSelector} chains
             * @fires resourceSelector#reset
             */
            reset : function reset(){
                if(this.is('rendered')){
                    this.empty();

                    this.searchQuery = {};

                    if(this.config.filters){
                        if(this.filtersComponent){
                            this.filtersComponent.reset();
                        }
                        $searchField
                            .val('')
                            .attr('title', null)
                            .attr('placeholder', this.config.searchPlaceholder);
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
             * Set the search query
             * @param {String|Object} query - label query if string or property filters
             * @returns {resourceSelector} chains
             */
            setSearchQuery : function setSearchQuery(query){
                this.searchQuery = {};
                this.searchQuery[labelUri] = '';

                if(_.isString(query) && !_.isEmpty(query)){
                    this.searchQuery[labelUri] = query;
                }
                if(_.isPlainObject(query)){
                    this.searchQuery = query;
                }
                return this;
            },

            /**
             * Clear the search query to submit
             * @returns {Object} the query
             */
            getSearchQuery : function getSearchQuery(){
                if(_.size(this.searchQuery) === 0){
                    this.searchQuery[labelUri] = '';
                }
                return this.searchQuery;
            },

            /**
             * Ask for a query (forward the event)
             * @param {Object} [params] - the query parameters
             * @param {String} [params.classUri] - the current node class URI
             * @param {String} [params.format] - the selected format
             * @param {String} [params.search] - the search query
             * @param {Number} [params.offset = 0] - for paging
             * @param {Number} [params.limit] - for paging
             * @returns {resourceSelector} chains
             * @fires resourceSelector#query
             */
            query : function query(params){
                var defaultParams;
                var search;
                if(this.is('rendered') && ! this.is('loading')){

                    this.setState('loading', true);

                    params = params || {};
                    search = this.getSearchQuery();
                    defaultParams = {
                        classUri: this.classUri,
                        format:   this.format,
                        limit  : this.config.limit,
                        search : _.isObject(search) ? JSON.stringify(search) : ''
                    };

                    /**
                     * Formulate the query
                     * @event resourceSelector#query
                     * @param {Object} params - see format above
                     */
                    this.trigger('query', _.defaults(params, defaultParams));
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
                if(this.is('rendered') && this.format !== format){

                    $viewFormat = $viewFormats.filter('[data-view-format="' + format + '"]');
                    if($viewFormat.length === 1 && !$viewFormat.hasClass('active')){

                        $viewFormats.removeClass('active');
                        $viewFormat.addClass('active');

                        this.empty();

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

                    hider.hide($noResults);

                    if(!this.selectionComponent){

                        this.selectionComponent = componentFactory($resultArea, _.defaults({
                            classUri : this.classUri,
                            nodes    : resources
                        }, this.config))
                        .on('query', function(queryParams){
                            self.query(queryParams);
                        })
                        .on('update', function(){
                            if(_.size(this.getNodes()) === 0 && $('li', $resultArea).length === 0){
                                hider.show($noResults);
                            }
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

                    this.setState('loading', false);
                }
                return this;
            },

            /**
             * Update the filters component
             * @param {Object?} filterConfig - the new filter configuration
             * @returns {resourceSelector} chains
             */
            updateFilters : function updateFilters(filterConfig){
                if(this.is('rendered') && filterConfig !== false && this.filtersComponent){
                    this.filtersComponent.update(filterConfig);
                }
                return this;
            }
        };

        /**
         * The resource selector component
         * @typedef {ui/component} resourceSelector
         */
        var resourceSelector = component(resourceSelectorApi, defaultConfig)
            .setTemplate(selectorTpl)
            .on('init', function(){

                this.searchQuery = {};
                this.classUri    = this.config.classUri;
                this.format      = this.config.format || _.findKey(this.config.formats, { active : true });

                this.render($container);
            })
            .on('render', function(){
                var self = this;

                //we ensure the sub-components are rendered
                return new Promise(function(resolve){
                    var $component = self.getElement();

                    $classContainer  = $('.class-context', $component);
                    $resultArea      = $('main', $component);
                    $noResults       = $('.no-results', $resultArea);
                    $searchField     = $('.search input', $component);
                    $filterToggle    = $('.filters-opener', $component);
                    $filterContainer = $('.filters-container', $component);
                    $viewFormats     = $('.context > a', $component);
                    $selectNum       = $('.selected-num', $component);
                    $selectCtrl      = $('.selection-control input', $component);
                    $selectCtrlLabel = $('.selection-control label', $component);

                    //the search field
                    $searchField.on('keyup', _.debounce(function(e){
                        var value = $(this).val().trim();
                        if(value.length > 2 || value.length === 0 || e.which === 13){
                            if(self.config.filters){
                                //reset the placeholder
                                $(this).attr('title', null)
                                       .attr('placeholder', self.config.searchPlaceholder);
                            }
                            self.empty()
                                .changeFormat('list')
                                .setSearchQuery(value)
                                .query();
                        }
                    }, 300));

                    //the format switcher
                    $viewFormats.on('click', function(e) {
                        var $target = $(this);
                        var format = $target.data('view-format');
                        e.preventDefault();

                        self.reset()
                            .changeFormat(format)
                            .query();
                    });

                    //the select all control
                    $selectCtrl.on('change', function(){
                        if($(this).prop('checked') === false){
                            self.selectionComponent.clearSelection();
                        } else {
                            self.selectionComponent.selectAll();
                        }
                    });

                    //the advanced filters
                    if(self.config.filters !== false){

                        self.filtersComponent = filtersFactory($filterContainer, {
                            classUri : self.classUri,
                            data     : self.config.filters
                        })
                        .on('change', function(values){
                            var textualQuery = this.getTextualQuery();

                            $searchField.val('')
                                        .attr('title', textualQuery)
                                        .attr('placeholder', textualQuery);

                            self.empty()
                                .changeFormat('list')
                                .setSearchQuery(values)
                                .query();

                            $filterContainer.addClass('folded');
                        });

                        $filterToggle.on('click', function(e){
                            var searchVal;
                            e.preventDefault();

                            if($filterContainer.hasClass('folded')){

                                //if a value is in the search field, we add it to the label
                                searchVal = $searchField.val().trim();
                                if(!_.isEmpty(searchVal)){
                                    self.filtersComponent.setValue(labelUri, searchVal);
                                }
                                $filterContainer.removeClass('folded');

                            } else {
                                $filterContainer.addClass('folded');
                            }
                        });
                    }

                    //initialize the class selector
                    self.classSelector = classesSelectorFactory($classContainer, self.config);
                    self.classSelector
                        .on('render', resolve)
                        .on('change', function(uri){
                            if(uri && uri !== self.classUri){
                                self.classUri = uri;

                                //close the filters
                                if($filterContainer.length){
                                    $filterContainer.addClass('folded');
                                }

                                /**
                                 * When the component's root class URI changes
                                 * @event resourceSelector#classchange
                                 * @param {String} classUri - the new class URI
                                 */
                                self.trigger('classchange', uri);

                                self.reset()
                                    .query();
                            }
                        });

                    self.query();
                });
            })
            .on('change', function(selected){

                var nodesCount = _.size(this.selectionComponent.getNodes());
                var selectedCount = _.size(selected);

                //the number selected at the bottom
                $selectNum.text(selectedCount);

                //update the state of the "Select All" checkbox
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
