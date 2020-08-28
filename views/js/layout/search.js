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
define(['jquery', 'layout/actions', 'ui/searchModal', 'core/store', 'context'], function (
    $,
    actionManager,
    searchModal,
    store,
    context
) {
    /**
     * Seach bar component for TAO action bar. It exposes
     * the container, the indexeddb store that manages
     * search results, and @init function
     */
    const searchComponent = {
        container: null,
        searchStore: null,
        init: function () {
            initSearchStore().then(() => {
                initializeEvents();
                manageSearchStoreUpdate();
            });
        }
    };

    /**
     * Create/opens search store and assigns it to searchStore property
     * @returns {Promise} - promise that will be completed when store is opened
     */
    function initSearchStore() {
        return store('search').then(function (store) {
            searchComponent.searchStore = store;
        });
    }

    /**
     * Sets event to init searchModal instance on search and results icons click, and enter keypress
     */
    function initializeEvents() {
        searchComponent.container = $('.action-bar .search-area');
        const $searchBtn = $('button.icon-find', searchComponent.container);
        const $searchInput = $('input', searchComponent.container);
        const $resultsBtn = $('button.icon-ul', searchComponent.container);

        $searchBtn.off('click').on('click', () => createSearchModalInstance());

        $searchInput.off('keypress').on('keypress', e => (e.which === 13 ? createSearchModalInstance() : undefined));

        $resultsBtn.off('click').on('click', () => {
            searchComponent.searchStore
                .getItem('query')
                .then(storedSearchQuery => createSearchModalInstance(storedSearchQuery, false));
        });
    }

    /**
     * Creates a searchModal instance and set up searchStoreUpdate listener to update
     * search component visuals when search store changes
     * @param {boolean} searchOnInit - if datatable request must be triggered on init, or uset the stored results instead
     */
    function createSearchModalInstance(query, searchOnInit = true) {
        query = query ? query : $('input', searchComponent.container).val();
        const url = $('.action-bar .search-area').data('url');
        const searchModalInstance = searchModal({
            query: query,
            url: url,
            events: actionManager,
            searchOnInit: searchOnInit
        });

        searchModalInstance.on('searchStoreUpdate', manageSearchStoreUpdate);
    }

    /**
     * Callback to searchStoreUpdate event. First checks if current location is the same as the stored one, and if
     * it is not, clears the store. Then requests stored query and results if still necessary, and updates view
     */
    function manageSearchStoreUpdate() {
        searchComponent.searchStore.getItem('context').then(storedContext => {
            if (storedContext !== context.shownStructure) {
                searchComponent.searchStore.clear();
                updateViewAfterSeachStoreUpdate('');
            } else {
                let promises = [];
                promises.push(searchComponent.searchStore.getItem('query'));
                promises.push(searchComponent.searchStore.getItem('results'));
                Promise.all(promises).then(values => {
                    updateViewAfterSeachStoreUpdate(values[0], values[1]);
                });
            }
        });
    }

    /**
     * Updates template with the received query and results dataset
     * @param {string} storedSearchQuery - stored search query, to be set on search input
     * @param {object} storedSearchResults - stored search results dataset, to display number of saved results on .results-counter
     */
    function updateViewAfterSeachStoreUpdate(storedSearchQuery, storedSearchResults) {
        const $resultsCounter = $('.icon-ul', searchComponent.container);
        const $searchInput = $('input', searchComponent.container);
        const $resultsCounterContainer = $('.results-counter', searchComponent.container);
        const $searchAreaButtonsContainer = $('.search-area-buttons-container', searchComponent.container);

        $searchInput.val(storedSearchQuery);
        if (storedSearchResults) {
            $resultsCounter.css('display', 'initial');
            $searchAreaButtonsContainer.css('right', '15px');
            $resultsCounterContainer.text(storedSearchResults.records > 99 ? '+99' : storedSearchResults.records);
        } else {
            $searchAreaButtonsContainer.css('right', '30px');
            $resultsCounter.css('display', 'none');
            $resultsCounterContainer.text('');
        }
    }

    return searchComponent;
});
