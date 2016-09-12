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
 *
 *
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'ui/component',
    'tpl!component/class/selector',
    'tpl!component/class/listItem'
], function ($, _, component, selectorTpl, listItemTpl) {
    'use strict';

    var buildTree = function buildTree(data){
        var nodeToListItem = function nodeToListItem(acc , node){
            var item = _.pick(node, ['uri', 'label']);
            if(node.children && node.children.length){
                item.childList = _.reduce(node.children, nodeToListItem, '');
            }
            acc += listItemTpl(item);
            return acc;
        };

        return _.reduce(data, nodeToListItem, '');
    };





    return function classesSelectorFactory($container, config, dataProvider){

        var classSelectorApi = {

            refresh : function refresh(){
                return this.loadClasses.then(this.updateContent);
            },
            loadClasses : function loadClasses(){
                return dataProvider.getAllClasses().then(function(data){
                    return buildTree(data);
                });
            },
            updateContent : function updateContent(tree){
                var $component = this.getElement();
                var $list   = $('.options > ul', $component);

                if(tree && tree.length){
                    $list.append(tree);

                    this.trigger('update');
                }
            }

        };

        var classSelector = component(classSelectorApi, {});

        classSelector
            .setTemplate(selectorTpl)
            .on('init', function(){
                this.render($container);
            })
            .on('render', function(){
                var self = this;
                var $component = this.getElement();
                var $selected  = $('.selected', $component);
                var $options   = $('.options', $component);

                $selected.on('click', function(e){
                    e.preventDefault();
                    $options.toggleClass('folded');
                });

                $options.on('click', 'a', function(e){
                    var $target = $(this);
                    var label = $target.text();
                    var uri = $target.data('uri');


                    e.preventDefault();

                    $selected.text(label)
                            .data('uri', uri);
                    $options.toggleClass('folded');

                    self.trigger('change', uri);
                });
            });

        classSelector.loadClasses().then(function(tree){
            config.tree = tree;
            classSelector.init(config);
        });
        return classSelector;

    };
});
