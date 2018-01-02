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
 * Main controller for the backend
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'module',
    'jquery',
    'lodash',
    'context',
    'router',
    'helpers',
    'uiForm',
    'util/url',
    'core/logger',
    'layout/generisRouter',
    'layout/section',
    'layout/actions',
    'layout/version-warning',
    'layout/loading-bar',
    'layout/nav',
    'layout/search',
    'layout/tree/loader',
    'layout/section-height',
], function(module, $, _, context, router, helpers, uiForm, urlUtil, loggerFactory, generisRouter, sections, actionManager,versionWarning, loadingBar, nav, search, treeLoader, sectionHeight){
    'use strict';

    var logger = loggerFactory('controller/main');

    /**
     * Loads and set up the given tree for a section, based on the tree provider
     * @param {jQueryElement} $container - the tree container with accurate data-attr
     * @param {Object} section - the section the tree belongs to
     * @param {String} section.id - id of the section
     * @param {String} [section.defaultUri] - the URI of the node to select by default
     * @returns {Promise} that resolves once rendered
     */
    var sectionTree = function sectionTree($container, section) {
        var treeProvider;

        //get the tree actions
        var treeActions  = _.reduce($container.data('actions'), function(acc, id, key){
            var action = actionManager.getBy(id);
            if(action){
                acc[key] = action;
            }
            return acc;
        }, {});

        var treeUrl = urlUtil.build([context.root_url, $container.data('url')]);

        var treeType = $container.data('type');

        //get the current tree based on the type attr, or fallback to jstree
        treeProvider = treeLoader(treeType);

        if(!treeType){
            //fill with the default value
            $container.data('type', treeProvider.name);
        }

        return treeProvider.init($container, {
            id           : $container.attr('id'),
            url          : treeUrl,
            rootClassUri : $container.data('rootnode'),
            icon         : $container.data('icon'),
            actions      : treeActions,
            sectionId    : section.id,
            loadNode     : section.defaultUri
        });
    };

    /**
     * This controller initialize all the layout components used by the backend : sections, actions, tree, loader, etc.
     * @exports tao/controller/main
     */
    return {
        start: function start() {

            var config = module.config();
            var $doc = $(document);

            versionWarning.init();
            generisRouter.init();

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

                //search component
                search.init();

                switch (section.type) {
                    case 'tree':
                        section.panel.addClass('content-panel');
                        sectionHeight.init(section.panel);

                        //set up the tree
                        $('.taotree', section.panel).each(function() {
                            var $treeElt = $(this);
                            var $actionBar = $('.tree-action-bar-box', section.panel);

                            sectionTree($treeElt, section)
                                .then(function(){
                                    $actionBar.addClass('active');
                                    sectionHeight.setHeights(section.panel);
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

            //dispatch also extra registered controllers
            if(config && _.isArray(config.extraRoutes)){
                _.forEach(config.extraRoutes, function(route){
                    router.dispatch(route);
                });
            }
        }
    };
});
