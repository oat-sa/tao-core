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
    'tpl!component/resource/selector',
    'tpl!component/resource/listItem'
], function ($, _, __, component, classesSelectorFactory, selectorTpl, listItemTpl) {
    'use strict';

    var defaultConfig = {
        type : __('resource'),
        multiple : true
    };



    return function resourceSelectorFactory($container, config, dataProvider){

        var resourceSelectorApi = {

            reset : function reset(){
                var $component = this.getElement();

                this.loaded = 0;

                $('.status', $component).hide();
                $('main ul', $component).empty();

                this.trigger('reset');
            },

            search : function search(classUri, pattern){
                var self = this;
                var paging = {
                    offset : this.loaded,
                    size   : 25
                };
                classUri = classUri || this.classUri;

                return dataProvider.getResources(classUri, pattern, paging).then(function(result){
                    var $component = self.getElement();

                    self.loaded += result.data.length;

                    $('.status', $component).show().find('.matches').text(result.total + ' matches');
                    $('main ul', $component).append(_.reduce(result.data, function(acc, item){
                        item.desc = item.firstname + ' ' + item.lastname;
                        acc += listItemTpl(item);
                        return acc;
                    }, ''));

                    self.trigger('data', result.data);
                });
            }

        };

        return component(resourceSelectorApi, defaultConfig)
            .setTemplate(selectorTpl)
            .on('init', function(){
                var self = this;

                this.selected = [];
                this.loaded   = 0;
                this.classUri = config.classUri;

                this.render($container);

                dataProvider.getSearchParams().then(function(params){
                    self.params = _.transform(params, function(acc, label, uri){
                        acc[uri] = label.toLowerCase().replace(/\s/, '');
                    });
                });

            })
            .on('render', function(){
                var self = this;
                var $component = this.getElement();

                this.classSelector = classesSelectorFactory($('.class-context', $component), this.config);
                this.classSelector
                    .on('change', function(uri){
                        self.reset();
                        self.search(uri);
                        self.classUri = uri;
                    });



                $('.search .input', $component)
                    .on('focus', function(){
                        var $target = $(this);
                        if($target.hasClass('placeholder')){
                            $target.text('').removeClass('placeholder');
                        }
                    })
                    .on('keydown', _.debounce(function(e){
                        var $target = $(this);
                        if(e.which === 13){
                            e.preventDefault();
                            self.reset();
                            self.search(self.classUri, $target.text());

                        } else if(e.which === 32){
                            moveCursorTo($('.search .input', $component), false);
                            $target.find('span').removeProp('contenteditable').removeAttr('contenteditable').addClass('closed');
                        } else {
                            var value   = $target
                                            .clone()    //clone the element
                                            .children() //select all the children
                                            .remove()   //remove all the children
                                            .end()  //again go back to selected element
                                            .text();
                            if(value.length > 3 &&  $('.search .options', $component).hasClass('folded')){
                                var match = false;
                                $('.search .options ul', $component).empty();

                                _(self.params).pick(function(val){
                                    return new RegExp(value).test(val);
                                }).forEach(function(param){
                                    match = true;

                                    $('.search .options ul', $component).append('<li><a href="#"><span class="icon-tag"></span> ' + param + ':</span>');
                                });
                                if(match){

                                    $('.search .options', $component).removeClass('folded');
                                    _.delay(function(){
                                        $('.search .options', $component).addClass('folded');
                                    }, 3500);
                                }
                            }
                        }
                    }, 50));

                $('.search .options', $component).on('click', 'a', function(e){
                    var $target = $(this);
                    e.preventDefault();


                    $('.search .input', $component).html('<span>' + $target.text() + '</span>').focus();
                    $('.search .options', $component).addClass('folded');

                    _.defer(function(){
                        moveCursorTo($('.search .input span', $component), false);
                    });
                });


                $('.context > a').on('click', function(e) {
                    var $target = $(this);
                    var format = $target.data('view-format');
                    e.preventDefault();

                    $('.context > a').removeClass('active');
                    $target.addClass('active');

                    $('main ul').removeClass('tree grid list').addClass(format);

                });


                $('main ul', $component).on('click', 'li', function(){
                    var $target = $(this);

                    if($target.hasClass('selected')){
                        $target.removeClass('selected');
                        self.trigger('unselect', $target.text('uri'));
                    } else {
                        $target.addClass('selected');
                        self.trigger('select', $target.data('uri'));
                    }
                });

                $('.status a', $component).on('click', function(e){
                    var selection = [];
                    e.preventDefault();


                    $('main ul li', $component).each(function(){
                        var $item = $(this);
                        selection.push($item.data('uri'));
                        $item.addClass('selected');
                    });

                    self.trigger('select', selection);

                });

                $('footer .menu-opener', $component).on('click', function(e){
                    e.preventDefault();

                    $('footer .menu', $component).toggleClass('folded');
                });

                $('footer .get-selection', $component).on('click', function(e){
                    e.preventDefault();

                    $('.status', $component).hide();
                    $('main ul', $component).empty();
                    dataProvider.getResources(self.config.classUri, undefined, {offset: 0, size : self.selected.length}).then(function(result){

                        $('main ul', $component).append(_.reduce(result.data, function(acc, item){
                            item.desc = item.firstname + ' ' + item.lastname;
                            item.selected = true;
                            acc += listItemTpl(item);
                            return acc;
                        }, ''));
                    });
                });

                $('main', $component)
                    .scrollTop(0)
                    .on('scroll', _.throttle(function(){
                        var $this = $(this);
                        if( !$this.hasClass('loading') && $this.scrollTop() + $this.outerHeight() >= $this[0].scrollHeight ){
                            $this.addClass('loading');
                            self.search().then(function(){
                                $this.removeClass('loading');
                            });
                        }
                    }, 200));

            })
            .on('select', function(targets){
                if(_.isString(targets)){
                    this.selected.push(targets);
                } else if(_.isArray(targets)){
                    this.selected = this.selected.concat(targets);
                }
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
            })
            .init(config);
    };
});
