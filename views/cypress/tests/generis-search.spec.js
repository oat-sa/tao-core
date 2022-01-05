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
import { getRandomNumber } from '../../../../tao/views/cypress/utils/helpers';
import selectorsItem from '../../../../taoQtiItem/views/cypress/utils/selectors';

const className = `Test E2E class generisSearch`;
const classNameEmpty = `Test E2E class generisSearch empty`;
const search = 'gen';

describe('Generis search', () => {
    before(() => {
        // TODO: Cleanup (if needed)

        cy.loginAsAdmin();
        cy.intercept('POST', '**/edit*').as('editItem');
        cy.visit('/tao/Main/index?structure=items&ext=taoItems&section=manage_items');
        cy.wait('@editItem');

        // TODO: Avoid multiple add
        // cy.addClassToRoot(
        //     selectorsItem.root,
        //     selectorsItem.itemClassForm,
        //     className,
        //     selectorsItem.editClassLabelUrl,
        //     selectorsItem.treeRenderUrl,
        //     selectorsItem.addSubClassUrl
        // );
        // TODO: More uniq names
        // ['Generis 1', 'Generis 2', 'Generis 3'].forEach((itemName)=>{
        //     cy.addNode(selectorsItem.itemForm, selectorsItem.addItem, itemName);
        // });

        // cy.addClassToRoot(
        //     selectorsItem.root,
        //     selectorsItem.itemClassForm,
        //     classNameEmpty,
        //     selectorsItem.editClassLabelUrl,
        //     selectorsItem.treeRenderUrl,
        //     selectorsItem.addSubClassUrl
        // );
    });

    after(() => {
        // TODO: Cleanup before & after

        // cy.visit('/tao/Main/index?structure=items&ext=taoItems&section=manage_items');
        // cy.wait('@editItem');

        // cy.deleteClassFromRoot(
        //     selectorsItem.root,
        //     selectorsItem.itemClassForm,
        //     selectorsItem.deleteClass,
        //     selectorsItem.deleteConfirm,
        //     className,
        //     selectorsItem.deleteClassUrl,
        //     true
        // );

        // cy.deleteClassFromRoot(
        //     selectorsItem.root,
        //     selectorsItem.itemClassForm,
        //     selectorsItem.deleteClass,
        //     selectorsItem.deleteConfirm,
        //     classNameEmpty,
        //     selectorsItem.deleteClassUrl,
        //     true
        // );
    });

    afterEach(() => {
        cy.getSettled('#modal-close-btn').click();
    });

    /**
     * Test case params
     * @param {String} testingCases.search - string to search for
     * @param {string[]} testingCases.method - nodes to create
     * @param {number} testingCases.path - expected results
     * @returns {Function} cy.wait - response for search request
     */
    const testingCases = [
        //Case: 0
        {
            search: 'Generis',
            expectSearch: 3,
            filter: className,
            filterExpect: 3
        },
        //Case: 1
        {
            search: 'Generissearch 1',
            expectSearch: 1,
            filter: classNameEmpty,
            expectAfter: 0
        }
    ];

    testingCases.forEach((testCase, index) => {
        it(`${index}: Search for ${testCase.search} in ${testCase.filter}, expecting: ${testCase.expectSearch}`, function () {
            // Search for 'it'
            cy.searchFor({search: testCase.search})
                .then((interception) => {
                    assert.equal(interception.response.body.records, testCase.expectSearch, 'Total records');

                    if(testCase.expectSearch > 0) {
                        assert.equal(interception.response.body.data.length, testCase.expectSearch, 'Data entries');
                    }
                });
            cy.getSettled('.search-modal').should('be.visible');

            // Validate search result
            cy.get('[data-item-identifier]').should('have.length', testCase.expectSearch);

            // Apply filter
            // cy.getSettled('.class-filter').should('be.visible').click();
            // cy.get(`a[title="${filter}"]`).should('be.visible').click();

            // Search again
            // cy.getSettled('button').contains('Search').click();
            // cy.get('[data-item-identifier]').should('have.length', testCase.filterExpect);
        });
    });

    xit('Test filtering', function () {
        // Go to search popup
        cy.searchFor({search});
        cy.getSettled('.search-modal').should('be.visible');
        cy.get('[data-item-identifier]').should('have.length.gt', 0);

        // Select filter
        cy.getSettled('.class-filter').should('be.visible').click();
        cy.get(`a[title="${classNameEmpty}"]`).should('be.visible').click();

        // Search again
        cy.getSettled('button').contains('Search').click();
        cy.get('[data-item-identifier]').should('have.length', 0);
    });

    xit('Clear button', function () {
        // Go to search popup
        cy.searchFor({search});
        cy.getSettled('.search-modal').should('be.visible');

        // Make sure that state is full
        cy.getSettled('[data-item-identifier]').should('have.length.gt', 0);
        cy.getSettled('[placeholder="Search Item"]').should('have.value', search);
        cy.getSettled('.class-filter').should('be.visible').click();
        cy.get(`a[title="${classNameEmpty}"]`).should('be.visible').click();
        cy.getSettled('.class-filter').should('be.visible').should('have.value', classNameEmpty);

        // Hit the red button
        cy.getSettled('button').contains('Clear').click();

        // Check state after clear
        cy.get('[data-item-identifier]').should('have.length', 0);
        cy.getSettled('[placeholder="Search Item"]').should('be.empty');
        cy.getSettled('.class-filter').should('be.visible').should('have.value', 'Item');
    });

    xit('Pagination test', function () {
        // TODO: adapt initialization
        // Go to search popup
         cy.searchFor({search: 'g'});
         cy.getSettled('.search-modal').should('be.visible');

        // Make sure that state is full
        cy.getSettled('[data-item-identifier]').should('have.length.gt', 25);

        // TODO: add click on next

        // TODO: validate search data
        cy.get('[data-item-identifier]').should('have.length', 4);
    });
});
