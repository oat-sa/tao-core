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
 * Copyright (c) 2014-2020 (update and modification) Open Assessment Technologies SA;
 */

/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'layout/actions', 'ui/searchModal', 'core/store'], function ($, actionManager, searchModal, store) {
    /**
     * Behavior of the tao backend global search.
     * It runs by himself using the init method.
     *
     * @example  search.init();
     *
     * @exports layout/search
     */
    const searchComponent = {
        container: null,
        searchStore: null,
        init: function init() {
            initSearchStore().then(initializeEvents);
        }
    };

    function initSearchStore() {
        return store('search').then(function (store) {
            searchComponent.searchStore = store;
        });
    }

    function initializeEvents() {
        this.container = $('.action-bar .search-area');
        const $searchInput = $('input', container);
        const $searchBtn = $('button', container);
        manageSearchStoreUpdate.bind(this)();

        if (container && container.length) {
            //clicking the button trigger the request
            $searchBtn.off('click').on('click', function (e) {
                const url = $('.action-bar .search-area').data('url');
                const query = $searchInput.val();
                e.preventDefault();
                searchModal({
                    query: query,
                    url: url,
                    events: actionManager
                });
            });

            //or press ENTER
            $searchInput.off('keypress').on('keypress', function (e) {
                if (e.which === 13) {
                    const url = $('.action-bar .search-area').data('url');
                    const query = $searchInput.val();
                    e.preventDefault();
                    const searchModalInstance = searchModal({
                        query: query,
                        url: url,
                        events: actionManager
                    });
                    searchModalInstance.on('searchStoreUpdate', function () {
                        manageSearchStoreUpdate();
                    });
                }
            });
        }
    }

    async function manageSearchStoreUpdate() {
        const storedSearchQuery = await searchComponent.searchStore.getItem('query');
        const storedSearchResults = await searchComponent.searchStore.getItem('results');
        const $resultsCounter = $('.icon-ul', container);
        const $searchInput = $('input', container);

        $searchInput.val(storedSearchQuery);
        storedSearchResults ? $resultsCounter.css('display', 'initial') : $resultsCounter.css('display', 'none');
    }

    return searchComponent;
});
