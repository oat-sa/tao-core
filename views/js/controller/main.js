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
    'jquery',
    'lodash',
    'context',
    'helpers',
    'uiForm',
    'util/url',
    'core/logger',
    'layout/section',
    'layout/actions',
    'layout/version-warning',
    'layout/loading-bar',
    'layout/nav',
    'layout/search',
    'layout/tree/loader',
], function($, _, context, helpers, uiForm, urlUtil, loggerFactory, sections, actionManager, versionWarning, loadingBar, nav, search, treeLoader){
    'use strict';

    var logger = loggerFactory('controller/main');

    /**
     * Loads and set up the given tree for a section, based on the tree provider
     * @param {jQueryElement} $container - the tree container with accurate data-attr
     * @returns {Promise} that resolves once rendered
     */
    var sectionTree = function sectionTree($container) {
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

        //get the current tree based on the type attr, or fallback to jstree
        try {
            treeProvider = treeLoader.getProvider($container.data('type'));
        } catch(err) {
            treeProvider = treeLoader.getProvider('jstree');
        }

        return treeProvider.init($container, {
            id : $container.attr('id'),
            url : treeUrl,
            rootClassUri : $container.data('rootnode'),
            icon : $container.data('icon'),
            actions : treeActions
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
