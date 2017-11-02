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
    'layout/section',
    'layout/actions',
    'layout/tree',
    'layout/version-warning',
    'layout/loading-bar',
    'layout/nav',
    'layout/search',
    'ui/resource/selector',
    'provider/resources'
], function(module, $, _, __, context, helpers, uiForm, loggerFactory, sections, actions, treeFactory, versionWarning, loadingBar, nav, search, resourceSelectorFactory, resourceProviderFactory) {
    'use strict';

    var logger = loggerFactory('controller/main');

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
                actions.init(section.panel);

                switch (section.type) {
                    case 'tree':
                        section.panel.addClass('content-panel');
                        //sectionHeight.init(section.panel);

                        //set up the tree
                        $('.taotree', section.panel).each(function() {
                            var $treeElt = $(this);
                            var $actionBar = $('.tree-action-bar-box', section.panel);

                            var rootNode = $treeElt.data('rootnode');
                            var treeUrl = context.root_url;
                            var treeActions = {};
                            var serverParameters = {
                                extension: context.shownExtension,
                                perspective: context.shownStructure,
                                section: context.section,
                            };
                            var resourceProvider = resourceProviderFactory();

                            $.each($treeElt.data('actions'), function(key, val) {
                                if (actions.getBy(val)) {
                                    treeActions[key] = val;
                                }
                            });

                            //TODO use the treeUrl within the resource provider
                            if (/\/$/.test(treeUrl)) {
                                treeUrl += $treeElt.data('url').replace(/^\//, '');
                            } else {
                                treeUrl += $treeElt.data('url');
                            }

                            actions.exec(treeActions.init, {
                                uri: rootNode
                            });

                            resourceProvider.getClasses(rootNode, serverParameters)
                                .then(function(classes) {
                                    resourceSelectorFactory($treeElt, {
                                        icon : $treeElt.data('icon'),
                                        selectionMode: 'both',
                                        selectClass : true,
                                        showSelection : false,
                                        classUri: rootNode,
                                        classes: classes
                                    })
                                    .on('render', function() {
                                        $actionBar.addClass('active');
                                    })
                                    .on('query', function(params) {
                                        var self = this;

                                        //ask the server the resources from the component query
                                        resourceProvider.getResources(_.defaults(params, serverParameters))
                                            .then(function(items) {
                                                self.update(items, params);
                                            })
                                            .catch(function(err) {
                                                logger.error(err);
                                            });
                                    })
                                    .on('change', function(selection) {
                                        var length = _.size(selection);
                                        var permissions  = {
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
                                        };

                                        if(length === 1){
                                            _.forEach(selection, function(resource, uri) {
                                                if(resource.classUri){
                                                    actions.updateContext({
                                                        uri: uri,
                                                        id: uri,
                                                        classUri: uri,

                                                        permissions:  permissions
                                                    });

                                                    actions.exec(treeActions.selectClass, {
                                                        uri: uri,
                                                        id: uri,
                                                        classUri: uri
                                                    });

                                                } else {
                                                    actions.updateContext({
                                                        uri: uri,
                                                        id: uri,
                                                        classUri: rootNode,

                                                        permissions: permissions
                                                    });

                                                    actions.exec(treeActions.selectInstance, {
                                                        uri: uri,
                                                        id: uri,
                                                        classUri: rootNode
                                                    });
                                                }
                                            });
                                        } else if (length > 1){
                                            //multiple mode
                                        }
                                    });
                                })
                                .catch(function(err) {
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

/*
define([
    'module',
    'jquery',
    'i18n',
    'context',
    'helpers',
    'uiForm',
    'layout/section',
    'layout/actions',
    'layout/tree',
    'layout/version-warning',
    'layout/section-height',
    'layout/loading-bar',
    'layout/nav',
    'layout/search'
],
function (module, $, __, context, helpers, uiForm, section, actions, treeFactory, versionWarning, sectionHeight, loadingBar, nav, search) {
    'use strict';

    return {
        start : function(){

            var $doc = $(document);

            versionWarning.init();

            //just before an ajax request
            $doc.ajaxSend(function () {
                loadingBar.start();
            });

            //when an ajax request complete
            $doc.ajaxComplete(function () {
                loadingBar.stop();
            });

            //navigation bindings
            nav.init();

            //search component
            search.init();

            //initialize sections
            section.on('activate', function(section){

                window.scrollTo(0,0);

                // quick work around issue in IE11
                // IE randomly thinks there is no id and throws an error
                // I know it's not logical but with this 'fix' everything works fine
                if(!section || !section.id) {
                    return;
                }

                context.section = section.id;

                //initialize actions
                actions.init(section.panel);

                switch(section.type){
                case 'tree':
                    section.panel.addClass('content-panel');
                    sectionHeight.init(section.panel);

                    //set up the tree
                    $('.taotree', section.panel).each(function(){
                        var $treeElt = $(this),
                            $actionBar = $('.tree-action-bar-box', section.panel);

                        var rootNode = $treeElt.data('rootnode');
                        var treeUrl = context.root_url;
                        var treeActions = {};
                        $.each($treeElt.data('actions'), function (key, val) {
                            if (actions.getBy(val)) {
                                treeActions[key] = val;
                            }
                        });

                        if(/\/$/.test(treeUrl)){
                            treeUrl += $treeElt.data('url').replace(/^\//, '');
                        } else {
                            treeUrl += $treeElt.data('url');
                        }
                        treeFactory($treeElt, treeUrl, {
                            serverParameters : {
                                extension    : context.shownExtension,
                                perspective  : context.shownStructure,
                                section      : context.section,
                                classUri     : rootNode ? rootNode : undefined
                            },
                            actions : treeActions
                        });
                        $treeElt.on('ready.taotree', function() {
                            $actionBar.addClass('active');
                            sectionHeight.setHeights(section.panel);
                        });
                    });

                    $('.navi-container', section.panel).show();
                    break;
                case 'content' :

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
});*/

