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
 *
 * Opens the media selector in a modal popup
 *
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'ui/component',
    'ui/modal',
    'ui/mediaSelector/selector',
    'tpl!ui/mediaSelector/tpl/modal',
], function($, _, component, modal, mediaSelectorFactory, modalTpl){
    'use strict';


    return function modalSelectorFactory($container, config){


        var modalSelector = component({
            open : function open(){
                if(this.is('rendered')){
                    this.getElement().modal('open');
                }
            },
            close : function close(){
                if(this.is('rendered')){
                    this.getElement().modal('close');
                }
            }
        }, {})
            .setTemplate(modalTpl)
            .on('init', function(){

                this.render($container || $('body'));
            })
            .on('render', function(){
                var self = this;

                this.getElement()
                    .on('close.modal', function(){
                        self.trigger('close');
                    })
                    .on('opened.modal', function(){
                        self.trigger('open');
                    })
                    .modal({
                        startClosed: true,
                        minWidth : 'responsive',
                    });

                mediaSelectorFactory(this.getElement(), this.config)
                    .on('select', function(selection){
                        self.close();
                        self.trigger('select', selection);
                    })
                    .on('delete', function(node){
                        console.log('delete', node);
                    })
                    .on('download', function(node){
                        console.log('download', node);
                    })
                    .on('upload', function(node){
                        console.log('download', node);
                    })
                    .on('query', function(params){

                        this.update([], params);
                    });

            });

        _.defer(function(){
            modalSelector.init(config);
        });
        return modalSelector;
    };

});
