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
                var item;
                if(node.uri && node.label){
                    item = _.clone(node);
                    classList[item.uri] = item;

                    if(node.children && node.children.length){
                        item.childList = _.reduce(node.children, nodeToListItem, '');
                    }
                    acc += listItemTpl(item);
                }
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
                            .text(classList[uri].label)
                            .attr({
                                'title'    : classList[uri].label,
                                'data-uri' : uri
                            })
                            .data('uri', uri)
                            .removeClass('empty');

                        /**
                         * @event classSelector#change
                         * @param {String} uri - the selected class URI
                         * @param {Object} class - the class node
                         */
                        this.trigger('change', uri, classList[uri]);
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
            },

            /**
             * Get the selected class node
             * @returns {Object} the node
             */
            getClassNode : function getClassNode(){
                var node = null;
                if(this.config.classUri && classList[this.config.classUri]){
                    node =  classList[this.config.classUri];
                }
                return node;
            },

            /**
             * Empty the component: remove the selection, set back the placeholder
             * @returns {classSelector} chains
             * @fires classSelector#change
             */
            empty : function empty(){
                if(this.is('rendered') && $selected.length && this.config.classUri){
                    this.config = _.omit(this.config, 'classUri');

                    $selected
                            .text(this.config.placeholder)
                            .removeAttr('title')
                            .data('uri', null)
                            .removeAttr('data-uri')
                            .addClass('empty');


                    this.trigger('change');
                }
                return this;
            },

            /**
             * Does the given node exists ?
             *
             * @param {Object|String} node - the node or directly the URI
             * @param {String} [node.uri]
             * @returns {Boolean}
             */
            hasNode : function hasNode(node){
                var uri;
                if(node && classList){
                    uri = _.isString(node) ? node : node.uri;
                    return _.has(classList, uri);
                }
                return false;
            },

            /**
             * Removes the given node
             *
             * @param {Object|String} node - the node or directly the URI
             * @param {String} [node.uri]
             * @returns {Boolean}
             */
            removeNode : function removeNode(node){
                var uri;
                if(this.hasNode(node)){
                    uri = _.isString(node) ? node : node.uri;

                    //if the node is selected, we remove the selection
                    if(uri === this.config.classUri){
                        this.empty();
                    }

                    classList = _.omit(classList, uri);

                    if(this.is('rendered')){
                        $('[data-uri="' + uri + '"]', this.getElement()).parent('li').remove();
                    }
                    return !this.hasNode(node);
                }
                return false;
            },

            /**
             * Add a node.
             *
             * @param {Object} node - the node to add
             * @param {String} node.uri
             * @param {String} node.label
             * @param {Object[]} node.children - let's you add a sub hierarchy
             * @param {String} [parentUri] - where to append the new node
             * @returns {classSelector} chains
             */
            addNode : function addNode(node, parentUri){
                var subTree;
                var $parentNode;
                if(this.is('rendered') && node && !this.hasNode(node)){

                    //this will also update the classList
                    subTree = buildTree([node]);

                    if(parentUri){
                        $parentNode = $('[data-uri="' + parentUri + '"]', $options);
                    }
                    if(!$parentNode || !$parentNode.length){
                        $parentNode = $('[data-uri]:first-child', $options);
                    }

                    //attach the sub tree
                    if($parentNode.parent('li').children('ul').length){
                        $parentNode.parent('li').children('ul').append(subTree);
                    } else {
                        $parentNode.parent('li').append('<ul>' + subTree + '</ul>');
                    }
                }
                return this;
            },

            /**
             * Update a node (the label for now)
             *
             * @param {Object} node - the node to update
             * @param {String} node.uri
             * @param {String} node.label
             * @returns {classSelector} chains
             */
            updateNode : function updateNode(node){
                if(node && node.uri && this.hasNode(node)  && classList[node.uri].label !== node.label){
                    classList[node.uri].label = node.label;
                    if(this.is('rendered')){

                        $('[data-uri="' + node.uri + '"]', this.getElement())
                            .attr('title', node.label)
                            .text(node.label);
                    }
                }
                return this;
            },

            /**
             * Update multiple nodes, recursively
             * @see {classSelector#updateNode}
             *
             * @param {Object[]} node - the node to update
             * @param {String} node.uri
             * @param {String} node.label
             * @param {Object[]} node.children
             * @returns {classSelector} chains
             */
            updateNodes : function updateNodes(nodes){
                var self = this;
                _.forEach(nodes, function(node){
                    if(node.children){
                        self.updateNodes(node.children);
                    }
                    self.updateNode(node);
                });
                return this;
            }

        }, defaultConfig)
            .setTemplate(selectorTpl)
            .on('init', function(){

                //generate the tree
                this.config.tree = buildTree(this.config.classes);

                if(this.config.classUri && classList[this.config.classUri]){
                    //set the default label
                    this.config.label =  classList[this.config.classUri].label;
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
