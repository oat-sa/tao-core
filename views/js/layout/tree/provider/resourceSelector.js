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
 * Copyright (c) 2014-2017 Open Assessment Technologies SA;
 */

/**
 * Tree provider : resource-selector
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'i18n',
    'core/promise',
    'core/store',
    'core/logger',
    'layout/actions',
    'layout/generisRouter',
    'provider/resources',
    'ui/resource/selector'
], function(_, __, Promise, store, loggerFactory, actionManager, generisRouter, resourceProviderFactory, resourceSelectorFactory){
    'use strict';

    var logger = loggerFactory('layout/tree/provider/resourceSelector');

    var resourceProvider = resourceProviderFactory();

    /**
     * The resource-selector tree provider
     */
    return {

        /**
         * Tree provider name
         */
        name : 'resource-selector',

        /**
         * Init is the tree provider entry point
         * @param {jQueryElement} $container - that will contain the tree
         * @param {Object} [options] - additional configuration options
         * @param {String} [options.id] - the tree identifier
         * @param {String} [options.url] - the endpoint to load data
         * @param {String} [options.rootClassUri] - the URI of the root class
         * @param {Object} [options.actions] - which actions to perform from the tree
         * @param {String} [options.loadNode] - the URI of the node to select by default
         * @param {String} [options.sectionId] - the section the selector belongs to
         * @returns {Promise} resolves when the tree is rendered
         */
        init: function init($container, options){

            return new Promise(function(resolve){

                store('taotree').then(function(treeStore){

                    return Promise.all([
                        resourceProvider.getClasses(options.rootClassUri),
                        resourceProvider.getClassProperties(options.rootClassUri),
                        treeStore.getItem(options.id)
                    ])
                    .then(function(results) {
                        var classes     = results[0];
                        var filters     = results[1];
                        var defaultNode = results[2];
                        var preloadNode = typeof options.loadNode !== 'undefined';

                        resourceSelectorFactory($container, {
                            icon : options.icon || 'test',
                            searchPlaceholder : __('Filter'),
                            selectionMode: 'both',
                            selectClass : true,
                            classUri: options.rootClassUri,
                            classes: classes,
                            filters: filters
                        })
                        .on('init', function(){
                            actionManager.exec(options.actions.init, {
                                uri: options.rootClassUri
                            });
                        })
                        .on('render', function() {
                            var self = this;

                            actionManager.on('removeNodes', function(actionContext, nodes){

                                //make the component in loading state
                                //to prevent handling intermediate changes
                                self.setState('loading', true);

                                _.forEach(nodes, self.removeNode, self);
                                self.changeSelectionMode('single');

                                self.setState('loading', false);
                                self.selectDefaultNode(defaultNode);
                            });
                            actionManager.on('removeNode', function(actionContext, node){
                                self.removeNode(node);
                                self.selectDefaultNode(defaultNode);
                            });
                            actionManager.on('subClass instanciate duplicateNode', function(actionContext, node){
                                self.changeSelectionMode('single');
                                self.addNode(node, node.classUri);
                                self.select(node);
                            });
                            actionManager.on('refresh', function(node){
                                self.refresh(node || defaultNode);
                            });

                            generisRouter.on('urichange', function(nodeUri, sectionId) {
                                if (options.sectionId === sectionId) {
                                    self.refresh(nodeUri);
                                }
                            });

                            resolve();
                        })
                        .on('query', function(params) {
                            var self = this;

                            if(preloadNode){
                                params.selectedUri = options.loadNode;
                                preloadNode = false;
                            }

                            //ask the server the resources from the component query
                            resourceProvider.getResources(params)
                                .then(function(resources) {
                                    self.update(resources, params);
                                })
                                .catch(function(err) {
                                    logger.error(err);
                                });
                        })
                        .on('update.first', function(){

                            this.off('update.first');

                            //on the 1st update we select the default node
                            //or fallback on 1st instance, or even 1st class
                            this.selectDefaultNode(options.loadNode || defaultNode);
                        })
                        .on('change', function(selection) {
                            var self   = this;
                            var length = _.size(selection);
                            var getContext = function getContext(resource) {
                                return _.defaults(resource, {
                                    id : resource.uri,
                                    permissions:  {}
                                });
                            };

                            //ignore changes while loading or modifying the selector
                            if(self.is('loading')){
                                return;
                            }

                            if(length === 1){
                                _.forEach(selection, function(resource) {
                                    var selectedContext = getContext(resource);
                                    actionManager.updateContext(selectedContext);

                                    if(selectedContext.type === 'class'){
                                        actionManager.exec(options.actions.selectClass, selectedContext);
                                    }
                                    if(selectedContext.type === 'instance'){
                                        actionManager.exec(options.actions.selectInstance, selectedContext);
                                    }

                                    generisRouter.pushNodeState(location.href, resource.uri);

                                    defaultNode = resource;
                                    treeStore.setItem(options.id, defaultNode);
                                });
                            } else if (length > 1){
                                actionManager.updateContext( _.transform(selection, function(acc, resource){
                                    acc.push(getContext(resource));
                                    return acc;
                                }, []));
                            }
                        })
                        .on('error', function(err){
                            logger.error(err);
                        });
                    });
                });
            });
        }
    };
});
