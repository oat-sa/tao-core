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

    var generisRouter;

    function generisRouterFactory() {
        if (generisRouter) {
            return generisRouter;
        }

        generisRouter = eventifier({
            /**
             * Add a new state to the history
             * @param {String} baseUrl - state url to save in the state. Might be modified
             * @param {Object} section
             * @param {String} [restoreWith = 'activate']
             */
            pushState: function pushState(baseUrl, options) {
                var sectionId = options.sectionId;
                var restoreWith = options.restoreWith || 'activate';

                var parsedUrl = urlUtil.parse(baseUrl);
                var query = parsedUrl.query;
                var newQuery = _.clone(query);
                var hasNoSection = !query.section;

                var state = {
                    sectionId: sectionId,
                    restoreWith : restoreWith
                };
                var stateUrl;

                if (sectionId !== query.section) {
                    newQuery.section = sectionId;
                }

                if (!_.isEqual(query, newQuery)) {
                    stateUrl = urlUtil.build(parsedUrl.path, newQuery);

                    if (hasNoSection) {
                        window.history.replaceState(state, null, stateUrl);
                        this.trigger('replacestate', stateUrl);

                        console.log('GGGGGGGGGGGGGGGGGGGG replacing state with url', stateUrl);
                    } else {
                        window.history.pushState(state, null, stateUrl);
                        this.trigger('pushstate', stateUrl);

                        console.log('GGGGGGGGGGGGGGGGGGGG pushing state with url', stateUrl);
                    }
                }
            },

            pushSectionState: function pushSectionState(baseUrl, sectionId, restoreWith) {
                var parsedUrl    = urlUtil.parse(baseUrl);
                var currentQuery = parsedUrl.query;
                var newQuery     = _.clone(currentQuery);
                var baseUrlHasSection = currentQuery.section;

                var stateUrl;
                var state = {
                    sectionId: sectionId,
                    restoreWith : restoreWith || 'activate'
                };

                if (!baseUrlHasSection) {
                    // adding missing section parameter
                    newQuery.section = sectionId;

                } else if (sectionId !== currentQuery.section) {
                    // changing section, we need to remove any uri
                    newQuery.section = sectionId;
                    delete newQuery.uri;
                }

                if (sectionId && !_.isEqual(currentQuery, newQuery)) {
                    stateUrl = urlUtil.build(parsedUrl.path, newQuery);

                    if (baseUrlHasSection) {
                        window.history.pushState(state, null, stateUrl);
                        this.trigger('pushstate', stateUrl);

                        console.log('GGGGGGGGGGGGGGGGGGGG pushing state with url', stateUrl);
                    } else {
                        window.history.replaceState(state, null, stateUrl);
                        this.trigger('replacestate', stateUrl);

                        console.log('GGGGGGGGGGGGGGGGGGGG replacing state with url', stateUrl);
                    }
                }
            },

            pushNodeState: function pushNodeState(baseUrl, nodeUri) {
                var parsedUrl    = urlUtil.parse(baseUrl);
                var currentQuery = parsedUrl.query;
                var newQuery     = _.clone(currentQuery);
                var baseUrlHasSection = currentQuery.section;

                var state = {
                    sectionId: sectionId,
                    restoreWith : restoreWith || 'activate'
                };
                var stateUrl;

                if (sectionId !== currentQuery.section) {
                    newQuery.section = sectionId;
                    if (baseUrlHasSection) {
                        delete newQuery.uri;
                    }
                }

                if (!_.isEqual(currentQuery, newQuery)) {
                    stateUrl = urlUtil.build(parsedUrl.path, newQuery);

                    if (baseUrlHasSection) {
                        window.history.pushState(state, null, stateUrl);
                        this.trigger('pushstate', stateUrl);

                        console.log('GGGGGGGGGGGGGGGGGGGG pushing state with url', stateUrl);
                    } else {
                        window.history.replaceState(state, null, stateUrl);
                        this.trigger('replacestate', stateUrl);

                        console.log('GGGGGGGGGGGGGGGGGGGG replacing state with url', stateUrl);
                    }
                }
            },



            /**
             * Restore a state from the history.
             * It calls activate or show on the section saved into the state.
             */
            restoreState: function restoreState() {
                var state = window.history.state;
                console.log('GGGGGGGGGGGGGGG restoring state ' + state);
                if(this.hasRestorableState()){
                    this.trigger('section' + (state.restoreWith || 'activate'), state.sectionId);
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
            generisRouter.restoreState();
        });

        return generisRouter;
    }

    return generisRouterFactory;
});