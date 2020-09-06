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
        init() {
            store('search')
                .then(store => {
                    searchComponent.searchStore = store;
                    initializeEvents();
                    manageSearchStoreUpdate();
                })
                .catch(e => {
                    actionManager.trigger('error', e);
                });
        }
    };

    /**
     * Sets events to init searchModal instance on search and results icons click, and enter keypress
     */
    function initializeEvents() {
        searchComponent.container = $('.action-bar .search-area');
        const $searchBtn = $('button.icon-find', searchComponent.container);
        const $searchInput = $('input', searchComponent.container);
        const $resultsBtn = $('button.icon-ul', searchComponent.container);

        $searchBtn.off('.searchComponent').on('click.searchComponent', createSearchModalInstance);

        $searchInput.off('.searchComponent').on('keypress.searchComponent', e => {
            if (e.which === 13) {
                createSearchModalInstance();
            }
        });

        $resultsBtn.off('.searchComponent').on('click.searchComponent', () => {
            searchComponent.searchStore
                .getItem('query')
                .then(storedSearchQuery => createSearchModalInstance(storedSearchQuery, false))
                .catch(e => {
                    actionManager.trigger('error', e);
                });
        });
    }

    /**
     * Creates a searchModal instance and set up searchStoreUpdate listener to update search component visuals when search store changes
     * @param {string} query - query for the searchComponent to be initialized with
     * @param {boolean} searchOnInit - if datatable request must be triggered on init, or use the stored results instead
     */
    function createSearchModalInstance(query, searchOnInit = true) {
        query = query || $('input', searchComponent.container).val();
        const url = searchComponent.container.data('url');
        const rootClassUri = getRootClassUri();
        const searchModalInstance = searchModal({ query, url, searchOnInit, rootClassUri });

        searchModalInstance.on('store-updated', manageSearchStoreUpdate);
        searchModalInstance.on('refresh', uri => {
            actionManager.trigger('refresh', { uri });
        });
    }

    /**
     * Callback to searchStoreUpdate event. First checks if current location is the same as the stored one, and if
     * it is not, clears the store. Then requests stored query and results if still necessary, and updates view
     */
    function manageSearchStoreUpdate() {
        searchComponent.searchStore
            .getItem('context')
            .then(storedContext => {
                if (storedContext !== context.shownStructure) {
                    searchComponent.searchStore.clear();
                    updateViewAfterSeachStoreUpdate('');
                } else {
                    let promises = [];
                    promises.push(searchComponent.searchStore.getItem('query'));
                    promises.push(searchComponent.searchStore.getItem('results'));
                    return Promise.all(promises).then(values => {
                        updateViewAfterSeachStoreUpdate(values[0], values[1]);
                    });
                }
            })
            .catch(e => actionManager.trigger('error', e));
    }

    /**
     * Updates template with the received query and results dataset
     * @param {string} storedSearchQuery - stored search query, to be set on search input
     * @param {object} storedSearchResults - stored search results dataset, to display number of saved results on .results-counter
     */
    function updateViewAfterSeachStoreUpdate(storedSearchQuery, storedSearchResults) {
        const $searchInput = $('input', searchComponent.container);
        const $resultsCounterContainer = $('.results-counter', searchComponent.container);
        const $searchAreaButtonsContainer = $('.search-area-buttons-container', searchComponent.container);

        $searchInput.val(storedSearchQuery);
        if (storedSearchResults) {
            $searchAreaButtonsContainer.addClass('has-results-counter');
            $resultsCounterContainer.text(storedSearchResults.totalCount > 99 ? '+99' : storedSearchResults.totalCount);
        } else {
            $searchAreaButtonsContainer.removeClass('has-results-counter');
            $resultsCounterContainer.text('');
        }
    }

    function getRootClassUri() {
        switch (context.shownStructure) {
            case 'items':
                return 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item';
            case 'tests':
                return 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test';
            case 'TestTaker':
                return 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject';
            case 'groups':
                return 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group';
            case 'delivery':
            case 'results':
                return 'http://www.tao.lu/Ontologies/TAODelivery.rdf#AssembledDelivery';
            case 'taoMediaManager':
                return 'http://www.tao.lu/Ontologies/TAOMedia.rdf#Media';
            case 'test_center':
                return 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#TestCenter';
            default:
            // TODO - class filter must not be present for other possible contexts
        }
    }

    return searchComponent;
});
