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
    'component/class/selector',
    'tpl!component/resource/selector'
], function ($, _, __, component, classesSelectorFactory, selectorTpl) {
    'use strict';

    var defaultConfig = {
        type : __('resource')
    };

    var resourceSelectorApi = {


    };

    return function resourceSelectorFactory(){


        return component(resourceSelectorApi, defaultConfig)
                .setTemplate(selectorTpl)
                .on('init', function(){

                    this.selected = [];

                    this.classSelector = classesSelectorFactory();
                    this.classSelector.init({
                        type: this.config.type,
                        clasUri : this.config.clasUri,
                        data : this.config.classesData
                    });
                })
                .on('render', function(){
                    var self = this;
                    var $component = this.getElement();

                    this.classSelector
                        .on('change', function(uri){

                        })
                        .render($('.class-context', $component));

                    $('.context > a').on('click', function(e) {
                        var $target = $(this);

                        e.preventDefault();

                        $('.context > a').removeClass('active');
                        $target.addClass('active');

                        $('main ul').removeClass('tree grid list').addClass($target.data('view-format'));
                    });


                    $('main ul', $component).on('click', 'li', function(){
                        var $target = $(this);

                        if($target.hasClass('selected')){
                            $target.removeClass('selected');
                            self.trigger('unselect', $target.text());
                        } else {
                            $target.addClass('selected');
                            self.trigger('select', $target.text());
                        }
                    });

                    $('main')
                        .scrollTop(0)
                        .on('scroll', _.throttle(function(){
                            var $this = $(this);
                            if( !$this.hasClass('loading') && $this.scrollTop() + $this.outerHeight() >= $this[0].scrollHeight ){
                                $this.addClass('loading');
                                _.delay(function(){
                                    $this.removeClass('loading');
                                    $this.find('ul').append('<li>Foo</li><li>Bar</li><li>Baz</li>');
                                }, 2000);
                            }
                        }, 200));

                })
                .on('select', function(target){
                    this.selected.push(target);
                    this.trigger('update');
                })
                .on('unselect', function(target){
                    _.remove(this.selected, function(elt){
                        return elt === target;
                    });
                    this.trigger('update');
                })
                .on('update', function(){
                    var $component = this.getElement();
                    $('footer .selected-num', $component).text(this.selected.length);
                });
    };
});
