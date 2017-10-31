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
 * A switch component, toggles between on and off
 *
 * @example
 * switchFactory(container, config)
 *     .on('change', function(value){
 *              console.log('The light is ' + value);
 *     });
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'core/areaBroker',
    'ui/component',
    'ui/hider',
    'ui/resource/selector',
    'ui/dialog/confirm',
    'tpl!ui/mediaSelector/tpl/selector',
    'tpl!ui/mediaSelector/tpl/properties',
    'css!ui/mediaSelector/css/selector.css',
    'ui/previewer',
    'ui/uploader',
], function($, _, __, areaBroker, component, hider, resourceSelectorFactory, confirmDialog, selectorTpl, propertiesTpl){
    'use strict';

    var defaultConfig = {
        startUploading : false
    };

    /**
     * The factory that creates a switch component
     *
     * @param {jQueryElement} $container - where to append the component
     * @returns {switchComponent} the component
     */
    return function mediaSelectorFactory($container, config){
        var media = null;

        var areas;

        /**
         * The component API
         */
        var api = {

            getSelectedMedia : function getSelectedMedia(){
                return media;
            },

            selectMedia : function selectMedia(selected){

                media = selected;

                if(media && this.is('rendered')){

                    $('.action a',  areas.getArea('actions')).removeClass('disabled');

                    this.togglePreview();

                    areas.getArea('preview').previewer('update', {
                        url : media.url,
                        mime : media.mime,
                        name : media.label
                    });

                    areas.getArea('properties').html(propertiesTpl(media));
                }
            },

            unSelectMedia : function unSelectMedia(){
                media = null;

                if(this.is('rendered')){
                    $('.action a:not([data-action="toggleUpload"])',  areas.getArea('actions')).addClass('disabled');
                }
            },

            deleteMedia : function deleteMedia(){
                var self = this;
                if(media && media.label){
                    confirmDialog(__('Are you sure you want to remove %s ?', media.label), function accept(){
                        self.resourceSelector.removeNode(media.uri);
                        self.trigger('delete', media);
                    });
                }
                return this;
            },

            downloadMedia : function downloadMedia(){
                if(media && media.label){
                    window.open(media.url, '_blank');
                }
                return this;
            },

            toggleUpload : function toggleUpload(){
                if (this.is('rendered') && !this.is('upload')) {
                    hider.hide(areas.getArea('view'));
                    hider.show(areas.getArea('upload'));
                    this.setState('preview', false);
                    this.setState('upload', true);
                }
            },

            togglePreview : function togglePreview(){
                if (this.is('rendered') && !this.is('preview')) {
                    hider.hide(areas.getArea('upload'));
                    hider.show(areas.getArea('view'));
                    this.setState('upload', false);
                    this.setState('preview', true);
                }
            },

            select : function select(){
                self.trigger('select', media);
            },



        };

        var switchComponent = component(api, defaultConfig)
            .setTemplate(selectorTpl)
            .on('init', function(){

                this.setState('preview', !this.config.startUploading);
                this.setState('upload', this.config.startUploading);

                this.render($container);
            })
            .on('render', function(){
                var self = this;

                var $component = this.getElement();
                areas = areaBroker([], $component, {
                    'selector'   : $('.resource-selector-container', $component),
                    'actions'    : $('.actions', $component),
                    'view'       : $('.media-view', $component),
                    'preview'    : $('.media-preview', $component),
                    'properties' : $('.media-properties', $component),
                    'upload'     : $('.media-upload', $component),
                    'uploader'   : $('.media-uploader', $component)
                });

                areas.getArea('uploader').uploader({
                    upload      : true,
                    multiple    : true,
                    fileSelect  : function(files, done){
                        console.log(files);
                        done(files);
                    }
                });

                areas.getArea('preview').previewer({ url : 'foo'});

                this.resourceSelector =  resourceSelectorFactory(areas.getArea('selector'), {
                    classes : this.config.classes,
                    multiple : false
                })
                .on('query', function(params){
                    this.update(self.config.nodes, params);
                })
                .on('classchange', function(newClass){
                    console.log(newClass);
                })
                .on('change', function(selection){
                    if(_.size(selection) > 0){
                        self.selectMedia(_.values(selection)[0]);
                    } else {
                        self.unSelectMedia();
                    }
                });


                areas.getArea('actions').on('click', '.action a', function(e){
                    var $actionElt;
                    var action;
                    e.preventDefault();

                    $actionElt = $(this);

                    if(!$actionElt.hasClass('disabled')){
                        action = $actionElt.data('action');

                        if(_.isFunction(self[action])){
                            self[action]();
                        }
                    }
                });
            });

        _.defer(function(){
            switchComponent.init(config);
        });
        return switchComponent;
    };
});
