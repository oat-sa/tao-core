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
 * A resource selector component
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
                componentFactory : listFactory
            },
            tree : {
                icon  : 'icon-tree',
                title : __('View results as a tree'),
                componentFactory : treeFactory,
                active : true
            }
        }
    };


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
             */
            reset : function reset(){
                if(this.is('rendered')){
                    if(this.selectionComponent){
                        this.selectionComponent.destroy();
                        this.selectionComponent = null;
                    }
                    this.trigger('reset');
                }
            },

            getSelection : function getSelection(){
                if(this.selectionComponent){
                    this.selectionComponent.getSelection();
                }
                return null;
            },

            clearSelection : function clearSelection(){
                this.selected = [];
                if(this.selectionComponent){
                    this.selectionComponent.clearSelection();
                }
                return this;
            },

            query : function query(params){
                if(this.is('rendered')){
                    this.trigger('query', _.defaults(params || {}, {
                        classUri: this.classUri,
                        format:   this.format,
                        pattern:  $searchField.val()
                    }));
                }
                return this;
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
                    }
                }
                return this;
            },

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

                        this.selectionComponent = componentFactory($resultArea, {
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

        var resourceSelector = component(resourceSelectorApi, defaultConfig)
            .setTemplate(selectorTpl)
            .on('init', function(){

                this.classUri = this.config.classUri;
                this.format   = _.findKey(this.config.formats, { active : true });

                this.render($container);
            })
            .on('render', function(){
                var self = this;

                var $component   = this.getElement();

                $classContainer  = $('.class-context', $component);
                $resultArea      = $('main', $component);
                $searchField     = $('.search input', $component);
                $viewFormats     = $('.context > a', $component);
                $selectNum       = $('.selected-num', $component);
                $selectCtrl      = $('.selection-control input', $component);
                $selectCtrlLabel = $('.selection-control label', $component);

                //initialize the class selector
                this.classSelector = classesSelectorFactory($classContainer, this.config);
                this.classSelector.on('change', function(uri){
                    if(uri && uri !== self.classUri){
                        self.classUri = uri;
                        self.query({ 'new' : true });
                    }
                });

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

                this.query();
            })
            .on('formatchange', function(){
                this.trigger('change', {});
                this.query({ new : true});
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
