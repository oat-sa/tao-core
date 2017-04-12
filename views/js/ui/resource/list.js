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
    'tpl!ui/resource/tpl/list',
    'tpl!ui/resource/tpl/listNode'
], function ($, _, __, component, listTpl, listNodeTpl) {
    'use strict';

    var defaultConfig = {

    };

    return function resourceTreeFactory($container, config){

        return component({

            reset : function reset(){
                this.selected = [];

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
                            var $node = $('[data-uri="' + uri + '"]', $component);
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
                            var $node = $('[data-uri="' + uri + '"]', $component);
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

            update: function update(nodes){
                if(this.is('rendered')){

                    this.getElement()
                         .children('ul')
                         .append(_.reduce(nodes, function(acc, node){
                             acc += listNodeTpl(node);
                             return acc;
                         }, ''));

                    this.trigger('update');
                }
            }

        }, defaultConfig)
            .setTemplate(listTpl)
            .on('init', function(){

                this.selected = [];
                this.classUri = this.config.classUri;

                this.render($container);
            })
            .on('render', function(){
                var self = this;
                var $component = this.getElement();

                $component.on('click', 'li', function(e){
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
