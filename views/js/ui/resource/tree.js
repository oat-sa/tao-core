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
    'tpl!ui/resource/tpl/tree',
    'tpl!ui/resource/tpl/treeNode'
], function ($, _, __, component, treeTpl, treeNodeTpl) {
    'use strict';

    var defaultConfig = {

    };

    var reduceNode = function reduceNode(acc , node){

        if(node.children && node.children.length){
            node.childList = _.reduce(node.children, reduceNode, '');
        }
        acc += treeNodeTpl(node);
        return acc;
    };


    return function resourceTreeFactory($container, config){

        return component({

            reset : function reset(){


                this.trigger('reset');
            },

            getSelected : function getSelected(){
                return this.selected;
            },

            select : function select(uris){
                var $component;
                var self = this;
                var changed = false;

                if(this.is('rendered')){
                    $component = this.getElement();

                    if(!_.isArray(uris)){
                        uris = [uris];
                    }
                    _(uris)
                        .reject(function(uri){
                            return _.contains(self.selected, uri);
                        })
                        .forEach(function(uri){
                            var $node = $('.instance[data-uri="' + uri + '"]', $component);
                            if($node.length){
                                changed = true;
                                $node.addClass('selected');

                                self.selected.push(uri);
                            }
                        });
                    if(changed){
                        this.trigger('change', this.selected);
                    }
                }
            },

            unselect : function unselect(uris){
                var $component;
                var self = this;
                var changed = false;

                if(this.is('rendered')){
                    $component = this.getElement();

                    if(!_.isArray(uris)){
                        uris = [uris];
                    }
                    _(uris)
                        .filter(function(uri){
                            return _.contains(self.selected, uri);
                        })
                        .forEach(function(uri){
                            var $node = $('.instance[data-uri="' + uri + '"]', $component);
                            if($node.length){
                                changed = true;
                                $node.removeClass('selected');

                                self.selected = _.without(self.selected, uri);

                            }
                        });
                    if(changed){
                        this.trigger('change', this.selected);
                    }
                }
            },

            query : function query(params){
                if(!this.is('loading')){
                    this.trigger('query', _.defaults(params || {}, {
                        classUri : this.classUri
                    }));
                }
            },

            update: function update(nodes, params){
                var $root;
                var $component;

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

                    this.trigger('update');
                }
            }

        }, defaultConfig)
            .setTemplate(treeTpl)
            .on('init', function(){

                this.selected = [];
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
