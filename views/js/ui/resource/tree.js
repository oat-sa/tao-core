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
 *
 * resourceTreeFactory(container, config)
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
     * Toggle the "more" button if the node is incomplete.
     * Parse the whole tree from the given node.
     * @param {jQueryElement} $node - the class node
     */
    var needMore = function needMore($node){
        var $more  = $node.children('.more');
        var totalCount = $node.data('count');
        var instancesCount =  $node.children('ul').children('.instance').length;

        if(totalCount > 0 && instancesCount > 0 && instancesCount < totalCount){
            hider.show($more);
        } else {
            hider.hide($more);
        }

        $node.children('ul').find('.class').each(function(){
            needMore($(this));
        });
    };

    /**
     * Manually update the count value of a class node.
     * useful when the nodes are added or removed directly.
     * @param {jQueryElement} $classNode - the node to update
     * @param {Number} update - the value to add to the count
     */
    var updateCount = function updateCount($classNode, update){
        var count = 0;
        if($classNode && $classNode.length && $classNode.hasClass('class')){
            count = $classNode.data('count');
            count += update;
            if(count < 0){
                count = 0;
            }
            $classNode
                .attr('data-count', count)
                .data('count', count);
        }
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
         * @augments {ui/resource/selectable}
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
                     * @param {Object} params - see format above
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
             * @param {Number|false} params.updateCount - force the update of the parent class count
             * @returns {resourceTree} chains
             * @fires resourceTree#update
             */
            update: function update(nodes, params){
                var self = this;
                var $root;
                var $component;

                function reduceNode(acc , node){

                    //filter already added nodes or classes when loading "more"
                    if(self.hasNode(node.uri) || (params && params.offset > 0 && node.type === 'class') ||
                        (node.type === 'class' && !node.state && !self.config.selectClass) ){
                        return acc;
                    }

                    if(node.type === 'class' && self.config.selectClass){
                        node.classUri = node.uri;
                        if(!node.state){
                            node.state = 'empty';
                        }
                        self.addNode(node.uri,  _.omit(node, ['count', 'state', 'children']));
                    }
                    if(node.type === 'instance'){
                        self.addNode(node.uri,  _.omit(node, ['count', 'state', 'children']));
                        node.icon = config.icon;
                    }
                    if(node.children && node.children.length){
                        node.childList = reduceNodes(node.children);
                    }

                    acc += treeNodeTpl(node);
                    return acc;
                }

                function reduceNodes(nodeList){
                    return _.sortBy(nodeList, function(a, b){
                        return b.label - a.label;
                    }).reduce(reduceNode, '');
                }

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
                    $root.children('ul').append(reduceNodes(nodes));

                    if(params && _.isNumber(params.updateCount)){
                        updateCount($root, params.updateCount);
                    }

                    needMore($root);
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

                this.setState('multiple', !!this.config.multiple);

                this.render($container);
            })
            .on('render', function(){
                var self = this;
                var $component = this.getElement();

                /**
                 * Open a class node
                 * @param {jQueryElement} $class
                 */
                var openClass = function openClass($class){
                    if($class.hasClass('closed')){
                        if(!$class.children('ul').children('li').length){
                            self.query({ classUri : $class.data('uri') });
                        }  else {
                            $class.removeClass('closed');
                        }
                    }
                };

                /**
                 * Close a class node
                 * @param {jQueryElement} $class
                 */
                var closeClass = function closeClass($class){
                    $class.addClass('closed');
                };

                /**
                 * Toggle a class node
                 * @param {jQueryElement} $class
                 */
                var toggleClass = function toggleClass($class){
                    if(!$class.hasClass('closed')){
                        closeClass($class);
                    } else {
                        openClass($class);
                    }
                };

                //Browse hierarchy
                if(self.config.selectClass){
                    //if we can

                    $component.on('click', '.class', function(e){
                        var $class = $(e.currentTarget);
                        e.preventDefault();
                        e.stopPropagation();

                        if($(e.target).hasClass('class-toggler')){
                            if(!$class.hasClass('empty')){
                                toggleClass($class);
                            }
                        } else {
                            if($class.hasClass('selected')){
                                self.unselect($class.data('uri'));
                            } else {
                                self.select($class.data('uri'), !self.is('multiple'));
                            }
                        }
                    });
                } else {
                    $component.on('click', '.class:not(.empty)', function(e){
                        var $class = $(e.currentTarget);
                        e.preventDefault();
                        e.stopPropagation();

                        toggleClass($class);
                    });
                }

                //selection
                $component.on('click', '.instance', function(e){
                    var $instance = $(e.currentTarget);
                    e.preventDefault();
                    e.stopPropagation();

                    if($instance.hasClass('selected')){
                        self.unselect($instance.data('uri'));
                    } else {
                        self.select($instance.data('uri'), !self.is('multiple'));
                    }
                });

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
            })
            .on('remove', function(uri){
                var $node;
                var $parent;

                if(this.is('rendered') && uri){
                    $node = $('[data-uri="' + uri + '"]', this.getElement());
                    if($node.hasClass('instance')){
                        $parent = $node.parents('.class');
                        updateCount($parent, -1);
                    }
                    $node.remove();
                }
            });

        //always defer the initialization to let consumers listen for init and render events.
        _.defer(function(){
            resourceTree.init(config);
        });
        return resourceTree;
    };
});
