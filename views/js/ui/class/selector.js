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
 * A Class Selector component
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'tpl!ui/class/tpl/selector',
    'tpl!ui/class/tpl/listItem',
    'css!ui/class/css/selector.css'
], function ($, _, __, component, selectorTpl, listItemTpl) {
    'use strict';

    var defaultConfig = {
        classes : [],
        placeholder : __('Select a class')
    };

    /**
     * Create a brand new class selector
     * @param {jQueryElement} $container - where the component is rendered
     * @param {Object} [config] - the configuration
     * @param {String} [config.classUri] - the selected class URI (by default)
     * @param {String} [config.placeholder] - when nothing is selected
     * @param {Object[]} [config.classes] - the class tree data, a collection of objects as {uri, label, children}
     * @returns {classSelector} the component itself
     */
    return function classesSelectorFactory($container, config){
        var $selected;
        var $options;

        //create an index to manage classes easily
        var classList = {};

        /**
         * Build the Class Tree from the data
         * @param {Object[]} classes - as {uri, label, children}
         * @returns {String} the HTML tree
         */
        var buildTree = function buildTree(classes){
            var nodeToListItem = function nodeToListItem(acc , node){
                var item = _.pick(node, ['uri', 'label']);

                classList[item.uri] = item.label;

                if(node.children && node.children.length){
                    item.childList = _.reduce(node.children, nodeToListItem, '');
                }
                acc += listItemTpl(item);
                return acc;
            };

            return _.reduce(classes, nodeToListItem, '');
        };

        /**
         * @typedef {classSelector} the component
         */
        var classSelector = component({

            /**
             * Set the selected class
             * @param {String} uri - the class URI to select
             * @returns {classSelector} chains
             * @fires classSelector#change
             */
            setValue : function setValue(uri){
                if(this.config.classUri !== uri && !_.isUndefined(classList[uri])){

                    this.config.classUri = uri;

                    if(this.is('rendered') && $selected.length){

                        $selected
                            .text(classList[uri])
                            .data('uri', uri)
                            .removeClass('empty');

                        /**
                         * @event classSelector#change
                         * @param {String} uri - the selected class URI
                         */
                        this.trigger('change', uri);
                    }
                }
                return this;
            },

            /**
             * Get the selected class
             * @returns {String} the selected class URI
             */
            getValue : function getValue(){
                return this.config.classUri;
            }

        }, defaultConfig)
            .setTemplate(selectorTpl)
            .on('init', function(){

                //generate the tree
                this.config.tree = buildTree(this.config.classes);

                if(this.config.classUri && classList[this.config.classUri]){
                    //set the default label
                    this.config.label =  classList[this.config.classUri];
                }

                this.render($container);
            })
            .on('render', function(){
                var self = this;
                var $component = this.getElement();
                $selected  = $('.selected', $component);
                $options   = $('.options', $component);

                $selected.on('click', function(e){
                    e.preventDefault();
                    $options.toggleClass('folded');
                });

                $options.on('click', 'a', function(e){

                    e.preventDefault();

                    self.setValue($(this).data('uri'));

                    $options.toggleClass('folded');
                });

            })
            .on('destroy', function(){
                classList = {};
            });

        _.defer(function(){
            classSelector.init(config);
        });

        return classSelector;
    };
});
