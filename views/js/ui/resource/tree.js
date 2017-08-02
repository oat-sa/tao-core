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
 * A tree component mostly used as a data viewer/selector for the resource selector.
 * The data flow works on the query/update model:
 * @example
 * resourceTreeFactory(container, config)
 *     .on('query', function(params){
 *            fectch('someUrl', params).then(nodes){
 *               this.update(nodeData, params);
 *            }
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
    'tpl!ui/resource/tpl/tree',
    'tpl!ui/resource/tpl/treeNode'
], function ($, _, __, component, selectable, hider, treeTpl, treeNodeTpl) {
    'use strict';

    //yes indent isn't handle by css
    var indentStep = 10;

    var defaultConfig = {
        multiple: true
    };

    /**
     * The actual CSS suffers from a limitation,
     * this function is used to fix the nested indents.
     * @param {jQueryElement} $list - the list element
     * @param {Number} level - the nesting level
     */
    var indentChildren = function indentChildren($list, level){
        var indent;
        if($list.length){
            indent = level *  indentStep;
            level++;
            $list.children('li').each(function(){
                var $target = $(this);
                $target.children('a').css('padding-left', indent + 'px');
                indentChildren($target.children('ul'), level);
            });
            $list.siblings('.more').css('padding-left',  indent + 'px');
        }
    };

    /**
     * Has the given node all it's children ?
     * @param {jQueryElement} $node
     * @returns {Boolean} true if the node needs more children
     */
    var needMore = function needMore($node){
        var totalCount = $node.data('count');
        var instancesCount =  $node.children('ul').children('.instance').length;
        return totalCount > 0 && instancesCount > 0 && instancesCount < totalCount;
    };

    /**
     * The factory that creates the resource tree component
     *
     * @param {jQueryElement} $container - where to append the component
     * @param {Object} config - the component config
     * @param {String} config.classUri - the root Class URI
     * @param {Objet[]} [config.nodes] - the nodes to preload
     * @param {String} [config.icon] - the icon class to show close to the resources
     * @param {Boolean} [config.multiple = true] - multiple vs unique selection
     * @returns {resourceTree} the component
     */
    return function resourceTreeFactory($container, config){

        /**
         * A selectable component
         * @typedef {ui/component} resourceTree
         */
        var resourceTree = selectable(component({

            /**
             * Ask for a query (forward the event)
             * @param {Object} [params] - the query parameters
             * @param {String} [params.classUri] - the current node class URI
             * @param {Number} [params.offset = 0] - for paging
             * @param {Number} [params.limit] - for paging
             * @returns {resourceTree} chains
             * @fires resourceTree#query
             */
            query : function query(params){
                if(!this.is('loading')){

                    /**
                     * Formulate the query
                     * @event resourceTree#query
                     * @param {Object} params
                     */
                    this.trigger('query', _.defaults(params || {}, {
                        classUri : this.classUri
                    }));
                }
                return this;
            },

            /**
             * Update the component with the given nodes
             * @param {Object[]} nodes - the tree nodes, with at least a URI as key and as property
             * @param {Object} params - the query parameters
             * @returns {resourceTree} chains
             * @fires resourceTree#update
             */
            update: function update(nodes, params){
                var self = this;
                var $root;
                var $component;


                var reduceNode = function reduceNode(acc , node){

                    //filter already added nodes or classes when loading "more"
                    if(self.hasNode(node.uri) || (params && params.offset > 0 && node.type === 'class') ){
                        return acc;
                    }
                    if(node.type === 'instance'){
                        self.addNode([node.uri],  _.omit(node, ['count', 'state', 'type', 'children']));
                        node.icon = config.icon;
                    }
                    if(node.children && node.children.length){
                        node.childList = _.reduce(node.children, reduceNode, '');
                    }

                    acc += treeNodeTpl(node);
                    return acc;
                };

                if(this.is('rendered')){
                    $component = this.getElement();

                    if(params && params.classUri){
                        $root = $('.class[data-uri="' + params.classUri + '"]', $component);
                    }
                    if(!$root || !$root.length){
                        $root = $component;
                    }
                    if(nodes[0].uri === $root.data('uri')){
                        nodes = nodes[0].children || [];
                    }
                    $root.children('ul').append(_.reduce(nodes, reduceNode, ''));
                    if(needMore($root)){
                        hider.show($root.children('.more'));
                    } else {
                        hider.hide($root.children('.more'));
                    }

                    indentChildren($component.children('ul'), 1);

                    $root.removeClass('closed');

                    /**
                     * The tree has been updated
                     * @event resourceTree#update
                     */
                    this.trigger('update');
                }
                return this;
            }
        }, defaultConfig));

        resourceTree
            .setTemplate(treeTpl)
            .on('init', function(){

                this.classUri = this.config.classUri;

                this.render($container);
            })
            .on('render', function(){
                var self = this;
                var $component = this.getElement();

                //browser hierarchy
                $component.on('click', '.class:not(.empty)', function(e){
                    var $class = $(e.currentTarget);
                    e.preventDefault();
                    e.stopPropagation();

                    if(!$class.hasClass('closed')){
                        $class.addClass('closed');
                    } else {
                        if(!$class.children('ul').children('li').length){
                            self.query({ classUri : $class.data('uri') });
                        }  else {
                            $class.removeClass('closed');
                        }
                    }
                });

                //selection
                $component.on('click', '.instance', function(e){
                    var $instance = $(e.currentTarget);
                    e.preventDefault();
                    e.stopPropagation();

                    if($instance.hasClass('selected')){
                        self.unselect($instance.data('uri'));
                    } else {
                        if(self.config.multiple !== true){
                            self.clearSelection();
                        }
                        self.select($instance.data('uri'));
                    }
                });

                //need more data
                $component.on('click', '.more', function(e){
                    var $root = $(e.currentTarget).parent('.class');
                    e.preventDefault();
                    e.stopPropagation();


                    self.query({
                        classUri:   $root.data('uri') ,
                        offset:     $root.children('ul').children('.instance').length
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
            });

        //always defer the initialization to let consumers listen for init and render events.
        _.defer(function(){
            resourceTree.init(config);
        });
        return resourceTree;
    };
});
