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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA ;
 */


import urls from '../utils/urls';
import selectors from '../utils/selectors';

const NAME_BIG = 'Test E2E class GenerisSearchBig';
const NAME_SMALL = 'Test E2E class GenerisSearchSmall';
const NAME_EMPTY = 'Test E2E class GenerisSearchEmpty';
const search = 'Test E2E class GenerisSearch';
const testItemsGroup = {
    [NAME_BIG]: 16,
    [NAME_SMALL]: 5,
    [NAME_EMPTY]: 0
};

/**
 * Create entries to search against for
 */
const createData = () => {
    Object.keys(testItemsGroup).forEach((name) => {
        cy.addClassToRoot(
            selectors.resourceTree.items.root,
            selectors.resourceTree.items.itemClassForm,
            name,
            selectors.resourceTree.items.editClassLabelUrl,
            selectors.resourceTree.items.treeRenderUrl,
            selectors.resourceTree.items.addSubClassUrl
        );

        for(let i = 1; i <= testItemsGroup[name]; i++) {
            cy.addNode(
                selectors.resourceTree.items.itemForm,
                selectors.resourceTree.items.addItem
            );
        }
    });
}

/**
 * Remove entries that was created by test case
 */
const clearData = () => {
    cy.getSettled(`${selectors.resourceTree.items.root}`)
        .then(($resourceTree) => {
            Object.keys(testItemsGroup).forEach((name) => {
                const copies = $resourceTree.find(`li[title="${name}"]`).length;

                // Possible duplicates
                for(let i = 0; i < copies; i++) {
                    cy.deleteClassFromRoot(
                        selectors.resourceTree.items.root,
                        selectors.resourceTree.items.itemClassForm,
                        selectors.resourceTree.items.deleteClass,
                        selectors.resourceTree.items.deleteConfirm,
                        name,
                        selectors.resourceTree.items.deleteClassUrl,
                        true
                    );
                }
            });
        });
}

describe('Generis search', () => {
    before(() => {
        cy.loginAsAdmin();

        cy.intercept('POST', urls.edit).as('editItem');
        cy.visit(urls.itemsManager);
        cy.wait('@editItem');

        clearData();
        createData();
    });

    after(() => {
        cy.intercept('POST', urls.edit).as('editItem');
        cy.visit(urls.itemsManager);
        cy.wait('@editItem');

        clearData();
    });

    context('Testing without page reload between cases', () => {
        [{
            search: search,
            expected: 20,
            total: 21
        },{
            search: (`${NAME_BIG} 9`),
            expected: 1,
            total: 1,
        }].forEach((testcase, index) => {
            it(`${index}: Search for "${testcase.search}", expecting: ${testcase.expected} of ${testcase.total}`, () => {
                // Search for 'testcase.search'
                cy.searchFor({search: testcase.search})
                    .then((interception) => {
                        // Validate response
                        assert.exists(interception.response.body, 'Response body');
                        assert.isTrue(interception.response.body.success, 'Successful state');
                        assert.equal(interception.response.body.records, testcase.expected, 'Records');
                        assert.equal(interception.response.body.totalCount, testcase.total, 'Total');

                        // response.body.data is missing when 0 results
                        if(testcase.expected > 0) {
                            assert.equal(interception.response.body.data.length, testcase.expected, 'Total of data entries');
                        }
                    });
                cy.getSettled(selectors.search.modal.dialog)
                    .should('be.visible');

                // Validate search results
                cy.getSettled(selectors.search.modal.textInput)
                    .should('be.visible')
                    .should('have.value', testcase.search);
                cy.get(selectors.search.modal.entries)
                    .should('be.visible')
                    .should('have.length', testcase.expected);

                // Validate initial search input
                cy.getSettled(selectors.search.modal.closeButton)
                    .click();
                cy.getSettled(selectors.search.textInput)
                    .should('be.visible')
                    .should('have.value', testcase.search);
                cy.getSettled(selectors.search.openResultsButton)
                    .should('be.visible')
                    .should('have.text', testcase.total);
            });
        });
    });

    context('Testing with page reload between cases', () => {
        beforeEach(() => {
            cy.intercept('POST', urls.edit).as('editItem');
            cy.visit(urls.itemsManager);
            cy.wait('@editItem');
        });

        [{
            lookup: NAME_BIG,
            filter: NAME_BIG,
            expected: testItemsGroup[NAME_BIG]
        },{
            lookup: NAME_SMALL,
            filter: NAME_EMPTY,
            expected: 0
        },{
            lookup: NAME_SMALL,
            filter: NAME_BIG,
            expected: 0
        }].forEach((testcase, index) => {
            it(`${index}: Filter for "${testcase.lookup}" within "${testcase.filter}", expecting: ${testcase.expected}`, () => {
                // Go to search popup
                cy.searchFor({search});
                cy.getSettled(selectors.search.modal.dialog)
                    .should('be.visible');

                // Select filter
                cy.getSettled(selectors.search.modal.textInput)
                    .clear()
                    .type(testcase.lookup);
                cy.getSettled(selectors.search.modal.filterButton)
                    .should('be.visible')
                    .click();
                cy.getSettled(`a[title="${testcase.filter}"]`)
                    .scrollIntoView()
                    .should('be.visible')
                    .click();

                // Search again
                cy.getSettled('button').contains('Search')
                    .click();
                cy.wait('@searchFor');

                // Validate filtered results
                cy.get(selectors.search.modal.entries)
                    .should('have.length', testcase.expected);
            });
        });

        it('Restore search results', () => {
                // Search for 'testcase.search'
                cy.searchFor({search: NAME_SMALL});
                cy.getSettled(selectors.search.modal.dialog)
                    .should('be.visible');

                // Soft check of search results
                cy.get(selectors.search.modal.entries)
                    .should('be.visible')
                    .should('have.length', testItemsGroup[NAME_SMALL]);

                // Validate initial search input
                cy.getSettled(selectors.search.modal.closeButton)
                    .click();
                cy.getSettled(selectors.search.textInput)
                    .should('be.visible')
                    .should('have.value', NAME_SMALL);
                cy.getSettled(selectors.search.openResultsButton)
                    .should('be.visible')
                    .should('have.text', testItemsGroup[NAME_SMALL])
                    .click();
                cy.getSettled(selectors.search.modal.dialog)
                    .should('be.visible');

                // Validate restored search results
                cy.getSettled(selectors.search.modal.textInput)
                    .should('be.visible')
                    .should('have.value', NAME_SMALL);
                cy.get(selectors.search.modal.entries)
                    .should('be.visible')
                    .should('have.length', testItemsGroup[NAME_SMALL]);
            });

        it('Clear search form using clear button', () => {
            // Go to search popup
            cy.searchFor({search});
            cy.getSettled(selectors.search.modal.dialog)
                .should('be.visible');

            // Make sure that state is full
            cy.get(selectors.search.modal.entries)
                .should('have.length.gt', 0);
            cy.getSettled(selectors.search.modal.textInput)
                .should('have.value', search);

            cy.getSettled(selectors.search.modal.filterButton)
                .should('be.visible')
                .click();
            cy.getSettled(`a[title="${NAME_EMPTY}"]`)
                .scrollIntoView()
                .should('be.visible')
                .click();
            cy.getSettled(selectors.search.modal.filterButton)
                .should('be.visible')
                .should('have.value', NAME_EMPTY);

            // Hit the 'red button' clear
            cy.getSettled('button').contains('Clear')
                .click();

            // Check state after clear
            cy.get(selectors.search.modal.entries)
                .should('not.exist');
            cy.getSettled(selectors.search.modal.textInput)
                .should('be.empty');
            cy.getSettled(selectors.search.modal.filterButton)
                .should('be.visible')
                .should('have.value', 'Item');
        });

        it('Pagination through search results', () => {
            const entriesPerPage = 20;
            const entriesOnLastPage = 1; // entriesPerPage - sum of all testItemsGroup[].nodes

            // Go to search popup
            cy.searchFor({search})
                .then((interception) => {
                    assert.exists(interception.response.body, 'Response body');
                    assert.isTrue(interception.response.body.success, 'Response is successful');
                    assert.isAbove(interception.response.body.totalCount, entriesPerPage, `Total records are above per page limit (${entriesPerPage})`);
                    assert.equal(interception.response.body.page, 1, 'First page of search results');
                    assert.equal(interception.response.body.records, entriesPerPage, 'Records received');
                });
            cy.getSettled(selectors.search.modal.dialog)
                .should('be.visible');

            // Make sure that amount of entries are complete the page
            cy.get(selectors.search.modal.entries)
                .should('have.length', entriesPerPage);

            // Validate pagination and click() on the next page button
            cy.getSettled(selectors.search.modal.paginationButton).contains('Previous')
                .scrollIntoView()
                .should('be.visible')
                .should('be.disabled');
            cy.getSettled(selectors.search.modal.paginationButton).contains('Next')
                .should('be.visible')
                .should('not.be.disabled')
                .click();

            cy.wait('@searchFor')
                .then((interception) => {
                    assert.equal(interception.response.body.page, 2, 'Second page of search results');
                    assert.equal(interception.response.body.records, entriesOnLastPage, 'Records received');
                });

            // Validate results on second page
            cy.get(selectors.search.modal.entries)
                .should('have.length', entriesOnLastPage);
            cy.getSettled(selectors.search.modal.paginationButton).contains('Next')
                .scrollIntoView()
                .should('be.visible')
                .should('be.disabled');
            cy.getSettled(selectors.search.modal.paginationButton).contains('Previous')
                .should('be.visible')
                .should('not.be.disabled')
                .click();

            // Back to the first page
            cy.wait('@searchFor')
                .then((interception) => {
                    assert.equal(interception.response.body.page, 1, 'Second page of search results');
                    assert.equal(interception.response.body.records, entriesPerPage, 'Records received');
                });

            // Validate the first page, again
            cy.get(selectors.search.modal.entries)
                .should('have.length', entriesPerPage);
            cy.getSettled(selectors.search.modal.paginationButton).contains('Previous')
                .scrollIntoView()
                .should('be.visible')
                .should('be.disabled');
            cy.getSettled(selectors.search.modal.paginationButton).contains('Next')
                .should('be.visible')
                .should('not.be.disabled');
        });
    });
});
