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
 * A list component mostly used as a data viewer/selector for the resource selector.
 * The data flow works on the query/update model:
 * @example
 * resourceListFactory(container, config)
 *     .on('query', function(params){
 *         var self = this;
 *         fetch('someurl', params).then(nodes){
 *             self.update(nodedata, params);
 *         });
 *     });
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'ui/resource/selectable',
    'ui/hider',
    'tpl!ui/resource/tpl/list',
    'tpl!ui/resource/tpl/listNode'
], function ($, _, __, component, selectable, hider, listTpl, listNodeTpl) {
    'use strict';

    var defaultConfig = {
        multiple: true
    };

    /**
     * Builds the resource list component
     *
     * @param {jQueryElement} $container - where to append the component
     * @param {Object} config - the component config
     * @param {String} config.classUri - the root Class URI
     * @param {Objet[]} [config.nodes] - the nodes to preload
     * @param {String} [config.icon] - the icon class to show close to the resources
     * @param {Boolean} [config.multiple = true] - multiple vs unique selection
     * @returns {resourceList} the component
     */
    return function resourceListFactory($container, config){
        var $list;
        var $loadMore;

        /**
         * A selectable component
         * @typedef {ui/component} resourceList
         */
        var resourceList = selectable(component({

            /**
             * Ask for a query (forward the event)
             * @param {Object} [params] - the query parameters
             * @param {String} [params.classUri] - the class URI
             * @param {Number} [params.offset = 0] - for paging
             * @param {Number} [params.limit] - for paging
             * @returns {resourceList} chains
             * @fires resourceList#query
             */
            query : function query(params){
                if(!this.is('loading')){

                    /**
                     * Formulate the query
                     * @event resourceList#query
                     * @param {Object} params
                     */
                    this.trigger('query', _.defaults(params || {}, {
                        classUri : this.classUri
                    }));
                }
            },

            /**
             * Update the component with the given nodes
             * @param {Object[]} nodes - the tree nodes, with at least a URI as key and as property
             * @param {Object} params - the query parameters
             * @returns {resourceList} chains
             * @fires resourceList#update
             */
            update: function update(resources){
                var self = this;

                if(this.is('rendered')){

                    $list.append(_.reduce(resources.nodes, function(acc, node){
                        node.icon = self.config.icon;
                        acc += listNodeTpl(node);
                        return acc;
                    }, ''));

                    _.forEach(resources.nodes, function(node){
                        self.addNode(node.uri,  node);
                    });

                    if(resources.total > _.size(self.getNodes())){
                        hider.show($loadMore);
                    } else {
                        hider.hide($loadMore);
                    }

                    /**
                     * The list has been updated
                     * @event resourceList#update
                     */
                    this.trigger('update');
                }
            }
        }, defaultConfig));

        resourceList
            .setTemplate(listTpl)
            .on('init', function(){

                this.classUri = this.config.classUri;

                this.setState('multiple', !!this.config.multiple);

                this.render($container);
            })
            .on('render', function(){
                var self = this;

                var $component = this.getElement();
                $list     = $component.children('ul');
                $loadMore = $('.more', $component);

                //selection
                $component.on('click', 'li', function(e){
                    var $instance = $(e.currentTarget);
                    e.preventDefault();
                    e.stopPropagation();

                    if($instance.hasClass('selected')){
                        self.unselect($instance.data('uri'));
                    } else {
                        self.select($instance.data('uri'), !self.is('multiple'));
                    }
                });

                //load next page
                $loadMore.on('click', function(e){
                    e.preventDefault();

                    self.query({
                        offset: _.size(self.getNodes())
                    });
                });

                //initial data loading
                if(this.config.nodes){
                    this.update(this.config.nodes);
                } else  {
                    this.query();
                }
            })
            .on('query', function(){
                this.setState('loading', true);
            })
            .on('update', function(){
                this.setState('loading', false);
            })
            .on('remove', function(uri){
                if(this.is('rendered')){
                    $('[data-uri="' + uri + '"]', this.getElement()).remove();
                }
            });

        //always defer the initialization to let consumers listen for init and render events.
        _.defer(function(){
            resourceList.init(config);
        });

        return resourceList;
    };
});
