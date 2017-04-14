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
    'lodash'
], function ($, _) {
    'use strict';

    return function selectable(){

        var selection = {};
        var nodes     = {};

        return {
            getSelection : function getSelection(){
                return selection;
            },

            clearSelection : function clearSelection(){
                if(this.is('rendered') && _.size(selection) > 0){
                    selection = {};
                    $('.selected', this.getElement()).removeClass('selected');
                    this.trigger('change', selection);
                }
                return this;
            },

            select : function select(uris){
                var $component;
                var changed = false;

                if(this.is('rendered')){
                    $component = this.getElement();

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
                                $node.addClass('selected');

                                selection[uri] = nodes[uri];
                            }
                        });
                    if(changed){
                        this.trigger('change', selection);
                    }
                }
            },

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
                                $node.removeClass('selected');

                                selection = _.omit(selection, uri);

                            }
                        });
                    if(changed){
                        this.trigger('change', selection);
                    }
                }
            },

            selectAll : function selectAll(){
                return this.select(_.keys(nodes));
            },


            getNodes : function getNodes(){
                return nodes;
            },

            setNodes : function setNodes(newNodes){
                nodes = newNodes;
            },

            addNode : function add(uri, node){
                nodes[uri] = node;
            }
        };
    };
});
