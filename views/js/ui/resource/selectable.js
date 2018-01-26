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
 * Selectable API : to make a group of nodes selectable.
 * This module is intended to be assigned to ui/component.
 *
 * The list of nodes is mandatory to maintain the match between the DOM and the nodes.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash'
], function ($, _) {
    'use strict';

    /**
     * The CSS class to distinguish selected elements
     */
    var selectedClass = 'selected';

    /**
     * Creates a selectable context
     *
     * @param {component} component - the component instance to make selectable
     * @returns {selectable} the augmented component
     * @throws {TypeError} without a propert component
     */
    return function selectableFactory(component){

        var selection = {};
        var nodes     = {};

        //validate the component in parameter
        var isAComponent = _.all(['on', 'trigger', 'init', 'render', 'is', 'getElement'], function(method){
            return _.isFunction(component[method]);
        });

        if(!_.isObject(component) || !isAComponent){
            throw  new TypeError('Selectable expects a component');
        }

        /**
         * @typedef {Object} selectable
         */
        return _.assign(component, {

            /**
             * Get all selectable nodes
             * @returns {Object[]} nodes
             */
            getNodes : function getNodes(){
                return nodes;
            },

            /**
             * Get a given node
             * @returns {Object?} the node
             */
            getNode : function getNode(uri){
                return uri && _.isPlainObject(nodes[uri]) ? nodes[uri] : false;
            },

            /**
             * Set the selectable nodes
             * @param {Object[]} nodes
             */
            setNodes : function setNodes(newNodes){
                if(_.isArray(newNodes)){
                    nodes = _.reduce(newNodes, function(acc, node){
                        if(node.uri){
                            acc[node.uri] = node;
                        }
                        return acc;
                    }, {});
                }
                else if (_.isObject(newNodes)){
                    nodes = newNodes;
                }
            },

            /**
             * Add a node
             * @param {String} uri - the key
             * @param {Object} node - the node to add
             * @returns {Boolean}
             * @fires selectable#add
             */
            addNode : function addNode(uri, node){
                if(_.isPlainObject(node)){
                    nodes[uri] = node;

                    /**
                     * @event selectable#add a node is added
                     * @param {String} uri - the URI of the added node
                     */
                    this.trigger('add', uri, node);

                    return true;
                }
                return false;
            },


            /**
             * Remove a node
             * @param {String} uri - the URI of the node to remove
             * @returns {Boolean}
             * @fires selectable#remove
             */
            removeNode : function removeNode(uri){
                if(this.hasNode(uri)){
                    //removes from the selection too
                    if(selection[uri]){
                        this.unselect(uri);
                    }
                    nodes = _.omit(nodes, uri);

                    /**
                     * @event selectable#remove a node is removed
                     * @param {String} uri - the URI of the removed node
                     */
                    this.trigger('remove', uri);

                    return true;
                }
                return false;
            },

            /**
             * Check if the given node exists
             * @param {String} uri - the node's URI
             * @returns {Boolean} true if the node exists
             */
            hasNode : function hasNode(uri){
                return typeof nodes[uri] !== 'undefined';
            },

            /**
             * Retrieve the current selection
             * @returns {Object} the selection
             */
            getSelection : function getSelection(){
                return selection;
            },

            /**
             * Clear the current selection
             * @returns {selectable} chains
             * @fires selectable#change
             */
            clearSelection : function clearSelection(){
                if(_.size(selection) > 0){
                    selection = {};
                }
                if(this.is('rendered')){
                    $('.' + selectedClass, this.getElement()).removeClass(selectedClass);
                    this.trigger('change', selection);
                }
                return this;
            },

            /**
             * Apply the selection to the given URIs.
             * @param {String[]} uris - the list of URIs to select
             * @param {Boolean} [only=false] - if true the selection is done "only" on the given URIs (unselect previous)
             * @returns {selectable} chains
             * @fires selectable#change
             */
            select : function select(uris, only){
                var $component;
                var changed = false;

                if(this.is('rendered')){
                    $component = this.getElement();

                    if(only){
                        selection = {};
                        $('.' + selectedClass, this.getElement()).removeClass(selectedClass);
                    }
                    if(!_.isArray(uris)){
                        uris = [uris];
                    }
                    _(uris)
                        .reject(function(uri){
                            return typeof selection[uri] !== 'undefined' || !nodes[uri];
                        })
                        .forEach(function(uri){
                            var $node = $('[data-uri="' + uri + '"]', $component);
                            if($node.length){
                                changed = true;
                                $node.addClass(selectedClass);

                                selection[uri] = nodes[uri];
                            }
                        });
                    if(changed){
                        this.trigger('change', selection);
                    }
                }
                return this;
            },

            /**
             * Removes the given URIs from the selection.
             * @param {String[]} uris - the list of URIs to select
             * @returns {selectable} chains
             * @fires selectable#change
             */
            unselect : function unselect(uris){
                var $component;
                var changed = false;

                if(this.is('rendered')){
                    $component = this.getElement();

                    if(!_.isArray(uris)){
                        uris = [uris];
                    }
                    _(uris)
                        .filter(function(uri){
                            return typeof selection[uri] !== 'undefined' || !nodes[uri];
                        })
                        .forEach(function(uri){
                            var $node = $('[data-uri="' + uri + '"]', $component);
                            if($node.length){
                                changed = true;
                                $node.removeClass(selectedClass);

                                selection = _.omit(selection, uri);
                            }
                        });
                    if(changed){
                        this.trigger('change', selection);
                    }
                }
                return this;
            },

            /**
             * Select all nodes.
             * @returns {selectable} chains
             * @fires selectable#change
             */
            selectAll : function selectAll(){
                return this.select(_.keys(nodes));
            },
        });
    };
});
