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
    'ui/hider',
    'tpl!ui/resource/tpl/list',
    'tpl!ui/resource/tpl/listNode'
], function ($, _, __, component, selectable, hider, listTpl, listNodeTpl) {
    'use strict';

    var defaultConfig = {
        multiple: true
    };

    return function resourceListFactory($container, config){
        var $list;
        var $loadMore;

        var resourceList = selectable(component({

            query : function query(params){
                if(!this.is('loading')){
                    this.trigger('query', _.defaults(params || {}, {
                        classUri : this.classUri
                    }));
                }
            },

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

                    this.trigger('update');
                }
            }
        }, defaultConfig));

        return resourceList
            .setTemplate(listTpl)
            .on('init', function(){

                this.classUri = this.config.classUri;

                this.render($container);
            })
            .on('render', function(){
                var self = this;

                var $component = this.getElement();
                $list     = $component.children('ul');
                $loadMore = $('.more', $component);

                $component.on('click', 'li', function(e){
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
            .init(config);

    };
});
