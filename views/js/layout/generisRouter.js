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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */
/**
 * The purpose of this router is to allow navigation between Generis views entities (sections, tree items...).
 * It does not dispatch any controller (that's the backoffice.js' job) but ensures that history URLs are well-formed
 * so the usual controllers will restore correct state when triggered.
 *
 * State restoring only needs 2 parameters: SectionId and RestoreWith.
 * If the URL contains a URI, it will automatically be loaded on state restoration.
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/eventifier',
    'util/url'
], function(
    $,
    _,
    eventifier,
    urlUtil
) {
    'use strict';

    /**
     * The router instance
     */
    var generisRouter;

    /**
     * Keep track of the latest known state
     */
    var topState;


    function generisRouterFactory() {
        if (generisRouter) {
            return generisRouter;
        }

        generisRouter = eventifier({
            pushSectionState: function pushSectionState(baseUrl, sectionId, restoreWith) {
                var parsedUrl    = urlUtil.parse(baseUrl);
                var currentQuery = parsedUrl.query;
                var newQuery     = _.clone(currentQuery);
                var baseUrlHasSection = currentQuery.section;

                var stateUrl;
                var newState = {
                    sectionId: sectionId,
                    restoreWith : restoreWith || 'activate',
                    nodeUri: currentQuery.uri
                };

                if (!baseUrlHasSection) {
                    // adding missing section parameter
                    newQuery.section = sectionId;

                } else if (sectionId !== currentQuery.section) {
                    // changing section, we need to remove any uri
                    newQuery.section = sectionId;
                    delete newQuery.uri;
                    delete newState.nodeUri;
                }

                if (sectionId && !_.isEqual(currentQuery, newQuery)) {
                    stateUrl = urlUtil.build(parsedUrl.path, newQuery);

                    if (baseUrlHasSection) {
                        window.history.pushState(newState, null, stateUrl);
                        this.trigger('pushsectionstate', stateUrl);

                        console.log('GGGGGGGGGGGGGGGGGGGG pushing state with url', stateUrl);
                    } else {
                        window.history.replaceState(newState, null, stateUrl);
                        this.trigger('replacesectionstate', stateUrl);

                        console.log('GGGGGGGGGGGGGGGGGGGG replacing state with url', stateUrl);
                    }
                    topState = newState;
                }
            },

            pushNodeState: function pushNodeState(baseUrl, nodeUri) {
                var parsedUrl    = urlUtil.parse(baseUrl);
                var currentQuery = parsedUrl.query;
                var newQuery     = _.clone(currentQuery);
                var baseUrlHasUri = currentQuery.uri;

                var currentState = window.history.state || {};
                var newState = {
                    sectionId: currentState.sectionId || currentQuery.section || '',
                    restoreWith : currentState.restoreWith || 'activate',
                    nodeUri: nodeUri
                };
                var stateUrl;

                if (nodeUri !== currentQuery.uri) {
                    newQuery.uri = nodeUri;
                }

                if (nodeUri && !_.isEqual(currentQuery, newQuery)) {
                    stateUrl = urlUtil.build(parsedUrl.path, newQuery);

                    if (baseUrlHasUri) {
                        window.history.pushState(newState, null, stateUrl);
                        this.trigger('pushnodestate', stateUrl);

                        console.log('GGGGGGGGGGGGGGGGGGGG pushing state with url', stateUrl);
                    } else {
                        window.history.replaceState(newState, null, stateUrl);
                        this.trigger('replacenodestate', stateUrl);

                        console.log('GGGGGGGGGGGGGGGGGGGG replacing state with url', stateUrl);
                    }
                    topState = newState;
                }
            },

            /**
             * Restore a state from the history.
             * It calls activate or show on the section saved into the state.
             */
            restoreState: function restoreState(fromPopState) {
                var state = window.history.state || {};
                console.log('GGGGGGGGGGG restoring state ', (fromPopState) ? 'POPPING' : 'BACK NEW CONTROLLER');
                console.log(state);
                console.log('GGGGGGGGGGG topstate is ');
                console.log(topState);
                if(this.hasRestorableState()){
                    // generisRouter has already been used
                    if (fromPopState) {
                        topState = topState || {};

                        // changing section
                        if (topState.sectionId !== state.sectionId) {
                            this.trigger('section' + state.restoreWith, state.sectionId);

                        // changing uri
                        } else {
                            this.trigger('urichange', state.nodeUri, state.sectionId);
                        }

                    // we are restoring in section initialisation: uri will be read and set during tree initialisation
                    } else {
                        this.trigger('section' + state.restoreWith, state.sectionId);
                    }
                    console.log('GGGGGGGGGG saving current state in topState');
                    topState = state;
                }
            },

            hasRestorableState: function hasRestorableState() {
                var state = window.history.state;
                return state && state.restoreWith && state.sectionId;
            }
        });

        //back & forward button, and push state
        $(window).on('popstate', function () {
            console.log('GGGGGGGGGGGGGGGGG poping state in generisRouter, about to restore state:');
            generisRouter.restoreState(true);
        });

        return generisRouter;
    }

    return generisRouterFactory;
});