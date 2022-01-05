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
import selectorsItem from '../../../../taoQtiItem/views/cypress/utils/selectors';

const className = `Test E2E class GenerisSearch`;
const classNameEmpty = `Test E2E class GenerisSearchEmpty`;
const search = 'gen';
const totalMockedItems = 22;
const entriesPerPage = 20;
const entriesOnLastPage = 2;
/**
 * Test case params
 * @param {Object} testingCases - Configuration for search scenarios
 * @param {String} testingCases.search - string to search for
 * @param {number} testingCases.expectSearch - expected results on the page
 * @param {String} testingCases.filter - apply filter
 * @param {number} testingCases.filterExpect - expected results on the page after filtering
 */
    const testingCases = [
    //Case: 0
    {
        search: 'E2E GenerisSearchItem_1',
        expectSearch: 10,
        filter: className,
        filterExpect: 3
    },
    //Case: 1
    {
        search: 'E2E GenerisSearchItem_20',
        expectSearch: 1,
        filter: classNameEmpty,
        expectAfter: 0
    }
];

/**
 * Create entries to search against for
 */
function createData() {
    cy.addClassToRoot(
        selectorsItem.root,
        selectorsItem.itemClassForm,
        className,
        selectorsItem.editClassLabelUrl,
        selectorsItem.treeRenderUrl,
        selectorsItem.addSubClassUrl
    );

    for(let i = 1; i < totalMockedItems; i++) {
        cy.addNode(selectorsItem.itemForm, selectorsItem.addItem, `E2E GenerisSearchItem_${i}`);
    }

    cy.addClassToRoot(
        selectorsItem.root,
        selectorsItem.itemClassForm,
        classNameEmpty,
        selectorsItem.editClassLabelUrl,
        selectorsItem.treeRenderUrl,
        selectorsItem.addSubClassUrl
    );
}

/**
 * Remove entries that was created by test case
 */
function clearData () {
    cy.getSettled(`${selectorsItem.root}`).then(($resourceTree)=>{
        [className, classNameEmpty].forEach((name) => {
            if ($resourceTree.find(`li[title="${name}"]`).length > 0) {
                cy.deleteClassFromRoot(
                    selectorsItem.root,
                    selectorsItem.itemClassForm,
                    selectorsItem.deleteClass,
                    selectorsItem.deleteConfirm,
                    name,
                    selectorsItem.deleteClassUrl,
                    true
                );
            } else {
                cy.log(`${name} is not exists`);
            }
        });
    });
}

describe('Generis search', () => {
    before(() => {
        cy.loginAsAdmin();
        cy.intercept('POST', '**/edit*').as('editItem');
        cy.visit('/tao/Main/index?structure=items&ext=taoItems&section=manage_items');
        cy.wait('@editItem');

        clearData();
        createData();
    });

    after(() => {
        cy.intercept('POST', '**/edit*').as('editItem');
        cy.visit('/tao/Main/index?structure=items&ext=taoItems&section=manage_items');
        cy.wait('@editItem');

        clearData();
    });

    afterEach(() => {
        cy.intercept('POST', '**/edit*').as('editItem');
        cy.visit('/tao/Main/index?structure=items&ext=taoItems&section=manage_items');
        cy.wait('@editItem');
        // or Close search popup
        // cy.getSettled('#modal-close-btn').click();
    });

    testingCases.forEach((testCase, index) => {
        it(`${index}: Search for ${testCase.search} in ${testCase.filter}, expecting: ${testCase.expectSearch}`, function () {
            // Search for 'it'
            cy.searchFor({search: testCase.search})
                .then((interception) => {
                    assert.exists(interception.response.body, 'response body');
                    assert.isTrue(interception.response.body.success, 'Response is successful');
                    assert.equal(interception.response.body.records, testCase.expectSearch, 'Records received');

                    // response.body.data is missing when 0 results
                    if(testCase.expectSearch > 0) {
                        assert.equal(interception.response.body.data.length, testCase.expectSearch, 'Total of data entries');
                    }
                });
            cy.getSettled('.search-modal')
                .should('be.visible');

            // Validate search result
            cy.get('[data-item-identifier]')
                .should('have.length', testCase.expectSearch);

            // Apply filter
            // cy.getSettled('.class-filter').should('be.visible').click();
            // cy.get(`a[title="${filter}"]`).should('be.visible').click();

            // Search again
            // cy.getSettled('button').contains('Search').click();
            // cy.get('[data-item-identifier]').should('have.length', testCase.filterExpect);
        });
    });

    it('Test filtering', function () {
        // Go to search popup
        cy.searchFor({search});
        cy.getSettled('.search-modal')
            .should('be.visible');
        cy.get('[data-item-identifier]')
            .should('have.length.gt', 0);

        // Select filter
        cy.getSettled('.class-filter')
            .should('be.visible')
            .click();
        cy.get(`a[title="${classNameEmpty}"]`)
            .should('be.visible')
            .click();

        // Search again
        cy.getSettled('button')
            .contains('Search')
            .click();
        cy.get('[data-item-identifier]')
            .should('have.length', 0);
    });

    it('Clear button', function () {
        // Go to search popup
        cy.searchFor({search});
        cy.getSettled('.search-modal')
            .should('be.visible');

        // Make sure that state is full
        cy.getSettled('[data-item-identifier]')
            .should('have.length.gt', 0);
        cy.getSettled('[placeholder="Search Item"]')
            .should('have.value', search);
        cy.getSettled('.class-filter')
            .should('be.visible')
            .click();
        cy.get(`a[title="${classNameEmpty}"]`)
            .should('be.visible')
            .click();
        cy.getSettled('.class-filter')
            .should('be.visible')
            .should('have.value', classNameEmpty);

        // Hit the 'red button' clear
        cy.getSettled('button')
            .contains('Clear')
            .click();

        // Check state after clear
        cy.get('[data-item-identifier]')
            .should('have.length', 0);
        cy.getSettled('[placeholder="Search Item"]')
            .should('be.empty');
        cy.getSettled('.class-filter')
            .should('be.visible')
            .should('have.value', 'Item');
    });

    it('Pagination test', function () {
        // TODO: adapt initialization
        // Go to search popup
        cy.searchFor({search: 'E2E GenerisSearchItem_'})
            .then((interception) => {
                assert.exists(interception.response.body, 'Response body');
                assert.isTrue(interception.response.body.success, 'Response is successful');
                assert.isAbove(interception.response.body.totalCount, entriesPerPage, `Total records are above per page limit (${entriesPerPage})`);
                assert.equal(interception.response.body.page, 1, 'First page of search results');
                assert.equal(interception.response.body.records, entriesPerPage, 'Records received');
            });
        cy.getSettled('.search-modal')
            .should('be.visible');

        // Make sure that amount of entries are complete the page
        cy.getSettled('[data-item-identifier]')
            .should('have.length', entriesPerPage);

        // Validate pagination and click() on the next page button
        cy.getSettled('.pagination button')
            .contains('Previous')
            .scrollIntoView()
            .should('be.visible')
            .should('be.disabled');
        cy.getSettled('.pagination button')
            .contains('Next')
            .should('be.visible')
            .should('not.be.disabled')
            .click();

        // cy.intercept('GET', '**/tao/Search/search*').as('searchForNextPage');
        cy.wait('@searchFor')
            .then((interception) => {
                assert.equal(interception.response.body.page, 2, 'Second page of search results');
                assert.equal(interception.response.body.records, entriesOnLastPage, 'Records received');
            });

        // Validate results on second page
        cy.get('[data-item-identifier]')
            .should('have.length', entriesOnLastPage);
        cy.getSettled('.pagination button')
            .contains('Next')
            .scrollIntoView()
            .should('be.visible')
            .should('be.disabled');
        cy.getSettled('.pagination button')
            .contains('Previous')
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
        cy.getSettled('[data-item-identifier]')
            .should('have.length', entriesPerPage);
        cy.getSettled('.pagination button')
            .contains('Previous')
            .scrollIntoView()
            .should('be.visible')
            .should('be.disabled');
        cy.getSettled('.pagination button')
            .contains('Next')
            .should('be.visible')
            .should('not.be.disabled');
    });
});
