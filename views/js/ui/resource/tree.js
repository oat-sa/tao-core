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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * A resource selector component
 *
 *
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'ui/resource/selectable',
    'tpl!ui/resource/tpl/tree',
    'tpl!ui/resource/tpl/treeNode'
], function ($, _, __, component, selectable, treeTpl, treeNodeTpl) {
    'use strict';

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
            indent = level *  10;
            level++;
            $list.children('li').each(function(){
                var $target = $(this);
                $target.children('a').css('padding-left', indent + 'px');
                indentChildren($target.children('ul'), level);
            });
        }
    };

    return function resourceTreeFactory($container, config){

        var selectableApi = selectable();

        var resourceTreeApi = {

            reset : function reset(){
                this.trigger('reset');
            },

            query : function query(params){
                if(!this.is('loading')){
                    this.trigger('query', _.defaults(params || {}, {
                        classUri : this.classUri
                    }));
                }
            },

            update: function update(nodes, params){
                var self = this;
                var $root;
                var $component;

                var reduceNode = function reduceNode(acc , node){
                    if(node.type === 'instance'){
                        self.addNode([node.uri],  _.omit(node, ['count', 'state', 'type', 'children']));
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
                        $root = $('.class.closed[data-uri="' + params.classUri + '"]', $component);
                    }
                    if(!$root || !$root.length){
                        $root = $component;
                    }
                    if(nodes[0].uri === $root.data('uri')){
                        nodes = nodes[0].children || [];
                    }
                    $root.removeClass('closed')
                         .children('ul')
                         .append(_.reduce(nodes, reduceNode, ''));

                    indentChildren($component.children('ul'), 1);


                    this.trigger('update');
                }
            }
        };

        return component(_.assign(resourceTreeApi, selectableApi), defaultConfig)
            .setTemplate(treeTpl)
            .on('init', function(){

                this.classUri = this.config.classUri;

                this.render($container);
            })
            .on('render', function(){
                var self = this;
                var $component = this.getElement();

                $component.on('click', '.class', function(e){
                    var $class = $(e.currentTarget);
                    var count;
                    e.preventDefault();
                    e.stopPropagation();

                    if(!$class.hasClass('closed')){
                        $class.addClass('closed');
                    } else {
                        count = parseInt($class.data('count'), 10);
                        if(count > 0){
                            if(!$class.children('ul').children('li').length){
                                self.query({classUri : $class.data('uri') });
                            }  else {
                                $class.removeClass('closed');
                            }
                        }
                    }
                });
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
            .init(config);
    };
});
