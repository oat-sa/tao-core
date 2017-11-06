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
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'module',
    'jquery',
    'lodash',
    'i18n',
    'context',
    'helpers',
    'uiForm',
    'core/logger',
    'core/store',
    'layout/section',
    'layout/actions',
    'layout/tree',
    'layout/version-warning',
    'layout/loading-bar',
    'layout/nav',
    'layout/search',
    'ui/resource/selector',
    'provider/resources'
], function(module, $, _, __, context, helpers, uiForm, loggerFactory, store, sections, actionManager, treeFactory, versionWarning, loadingBar, nav, search, resourceSelectorFactory, resourceProviderFactory) {
    'use strict';

    var logger = loggerFactory('controller/main');

    /**
     * Set up the BRS tree
     * @param {jQueryElement} $container - the tree container with accurate data-attr
     * @returns {Promise} that resolves once rendered
     */
    var sectionTree = function sectionTree($container) {
        var resourceProvider = resourceProviderFactory();
        var treeId       = $container.attr('id');
        var rootClassUri = $container.data('rootnode');
        var treeActions  = _.reduce($container.data('actions'), function(acc, id, key){
            var action = actionManager.getBy(id);
            if(action){
                acc[key] = action;
            }
            return acc;
        }, {});

        return new Promise( function(resolve) {

            store('taotree').then(function(treeStore){
                return Promise.all([
                    resourceProvider.getClasses(rootClassUri),
                    treeStore.getItem(treeId)
                ])
                .then(function(results) {
                    var classes = results[0];
                    var defaultNode = results[1];

                    resourceSelectorFactory($container, {
                        icon : $container.data('icon') || 'test',
                        selectionMode: 'both',
                        selectClass : true,
                        classUri: rootClassUri,
                        classes: classes
                    })
                    .on('init', function(){
                        actionManager.exec(treeActions.init, {
                            uri: rootClassUri
                        });
                    })
                    .on('render', function() {
                        var self = this;

                        actionManager.on('removeNodes', function(actionContext, nodes){
                            _.forEach(nodes, self.removeNode, self);
                            self.changeSelectionMode('single');
                            self.selectDefaultNode(defaultNode);
                        });
                        actionManager.on('subClass instanciate duplicateNode', function(actionContext, node){
                            self.addNode(node, node.classUri);
                            self.select(node);
                        });
                        actionManager.on('refresh', function(node){
                            debugger;
                            self.refresh(node || defaultNode);
                        });

                        resolve();
                    })
                    .on('query', function(params) {
                        var self = this;

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
                        this.selectDefaultNode(defaultNode);
                    })
                    .on('change', function(selection) {
                        var length = _.size(selection);
                        var getContext = function getContext(resource) {
                            return _.defaults(resource, {
                                id : resource.uri,
                                permissions:  {
                                    "item-authoring": true,
                                    "item-class-new": true,
                                    "item-delete": true,
                                    "item-duplicate": true,
                                    "item-export": true,
                                    "item-import": true,
                                    "item-new": true,
                                    "item-preview": true,
                                    "item-properties": true,
                                    "item-translate": true
                                }
                            });
                        };

                        if(length === 1){
                            _.forEach(selection, function(resource) {
                                var selectedContext = getContext(resource);
                                actionManager.updateContext(selectedContext);
                                if(selectedContext.type === 'class'){
                                    actionManager.exec(treeActions.selectClass, selectedContext);
                                }
                                if(selectedContext.type === 'instance'){
                                    actionManager.exec(treeActions.selectInstance, selectedContext);
                                }

                                defaultNode = resource;
                                treeStore.setItem(treeId, defaultNode);
                            });
                        } else if (length > 1){
                            actionManager.updateContext( _.transform(selection, function(acc, resource){
                                acc.push(getContext(resource));
                                return acc;
                            }, []));
                        }
                    });
                })
                .catch(function(err) {
                    logger.error(err);
                });
            });
        });
    };

    /**
     * This controller initialize all the layout components used by the backend : sections, actions, tree, loader, etc.
     * @exports tao/controller/main
     */
    return {
        start: function start() {

            var $doc = $(document);

            versionWarning.init();

            //just before an ajax request
            $doc.ajaxSend(function() {
                loadingBar.start();
            });

            //when an ajax request complete
            $doc.ajaxComplete(function() {
                loadingBar.stop();
            });

            //navigation bindings
            nav.init();

            //search component
            search.init();

            //initialize sections
            sections.on('activate', function(section) {
                window.scrollTo(0, 0);

                // quick work around issue in IE11
                // IE randomly thinks there is no id and throws an error
                // I know it's not logical but with this 'fix' everything works fine
                if (!section || !section.id) {
                    return;
                }

                context.section = section.id;

                //initialize actions
                actionManager.init(section.panel);

                switch (section.type) {
                    case 'tree':
                        section.panel.addClass('content-panel');

                        //set up the tree
                        $('.taotree', section.panel).each(function() {
                            var $treeElt = $(this);
                            var $actionBar = $('.tree-action-bar-box', section.panel);

                            var treeUrl = context.root_url;
                            var serverParameters = {
                                extension: context.shownExtension,
                                perspective: context.shownStructure,
                                section: context.section,
                            };

                            //TODO use the treeUrl within the resource provider
                            if (/\/$/.test(treeUrl)) {
                                treeUrl += $treeElt.data('url').replace(/^\//, '');
                            } else {
                                treeUrl += $treeElt.data('url');
                            }

                            sectionTree($treeElt)
                                .then(function(){
                                    $actionBar.addClass('active');
                                })
                                .catch(function(err){
                                    logger.error(err);
                                });
                        });

                        $('.navi-container', section.panel).show();
                        break;
                    case 'content':

                        //or load the content block
                        this.loadContentBlock();

                        break;
                }
            })
            .init();

            //initialize legacy components
            helpers.init();
            uiForm.init();
        }
    };
});
