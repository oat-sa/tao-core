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
    'ui/class/selector',
    'ui/resource/tree',
    'ui/resource/list',
    'tpl!ui/resource/tpl/selector',
], function ($, _, __, component, classesSelectorFactory, treeFactory, listFactory, selectorTpl) {
    'use strict';

    var defaultConfig = {
        type : __('resource'),
        multiple : true,
        formats : {
            list : {
                icon  : 'icon-ul',
                title : __('View results as a list'),
                component : listFactory
            },
            tree : {
                icon  : 'icon-tree',
                title : __('View results as a tree'),
                component : treeFactory,
                active : true
            }
        }
    };


    return function resourceSelectorFactory($container, config){
        var $classContainer;
        var $resultArea;
        var $searchField;
        var $viewFormats;

        var resourceSelectorApi = {

            reset : function reset(){
                if(this.is('rendered')){
                    $resultArea.empty();
                    this.selected = [];
                    this.trigger('reset');
                }
            },

            getSelected : function getSelected(){
                return this.selected;
            },

            query : function query(params){
                if(this.is('rendered')){

                    this.trigger('query', _.defaults(params, {
                        classUri : this.classUri,
                        format :  this.format,
                        pattern : $searchField.val()
                    }));
                }
            },

            changeFormat : function changeFormat(format){
                var $viewFormat;
                if(this.is('rendered')){

                    $viewFormat = $viewFormats.filter('[data-view-format="' + format + '"]');
                    if($viewFormat.length === 1 && !$viewFormat.hasClass('active')){

                        $viewFormats.removeClass('active');
                        $viewFormat.addClass('active');

                        this.format = format;

                        this.trigger('formatchange', format);

                        this.query({ new : true});
                    }
                }
            },

            update: function update(resources, params){
                var self = this;
                var selectionComponentFactory = this.config.formats[this.format].component;
                if(this.is('rendered') && _.isFunction(selectionComponentFactory)){

                    if(params.new && this.selectionComponent){
                        this.selectionComponent.destroy();
                        this.selectionComponent = null;
                    }

                    if(!this.selectionComponent || params.new){

                        this.selectionComponent = selectionComponentFactory($resultArea, {
                            classUri : this.classUri,
                            nodes    : resources
                        })
                        .on('query', function(queryParams){
                            self.query(queryParams);
                        })
                        .on('change', function(selected){
                            self.trigger('change', selected);
                        });
                    } else {
                        this.selectionComponent.update(resources, params);
                    }
                }
            }
        };

        return component(resourceSelectorApi, defaultConfig)
            .setTemplate(selectorTpl)
            .on('init', function(){

                this.selected = [];
                this.classUri = this.config.classUri;

                this.format   = _.findKey(this.config.formats, { active : true });

                this.render($container);
            })
            .on('render', function(){
                var self = this;

                var $component      = this.getElement();
                $classContainer = $('.class-context', $component);
                $resultArea     = $('main', $component);
                $searchField    = $('.search input', $component);
                $viewFormats    = $('.context > a', $component);

                this.classSelector = classesSelectorFactory($classContainer, this.config);
                this.classSelector.on('change', function(uri){
                    if(uri && uri !== self.classUri){
                        self.classUri = uri;
                        self.query({ new : true });
                    }
                });

                $searchField.on('keydown', function(e){
                    var value = $(this).val().trim();
                    if(value.length > 2 || e.which === 13){
                        self.query({ new : true });
                    }
                });

                $viewFormats.on('click', function(e) {
                    var $target = $(this);
                    var format = $target.data('view-format');
                    e.preventDefault();

                    self.changeFormat(format);
                });

                this.query();
            })
            .on('change', function(selected){
                this.selected = selected;
                $('footer .selected-num', this.getElement()).text(this.selected.length);
            })
            .init(config);
    };
});
