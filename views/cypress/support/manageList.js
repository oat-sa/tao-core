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

import selectorsList from '../utils/selectors/list';
import urls from '../utils/urls';

/**
 * Save list by URI or Last
 * using a pattern for list name for easy cleanup
 * @param {String} [null] uri - uri number of the list to Save otherwise will be saved the last
 * @returns {Function} cy.wait - response for add request
 */
Cypress.Commands.add('createList', (
) => {
    cy.log('COMMAND: createList');
    cy.intercept('POST', urls.list.index).as('createList');
    cy.getSettled(selectorsList.createListButton)
        .should('have.text', ' Create list')
        .should('be.visible')
        .click();

    return cy.wait('@createList');
});

/**
 * Save list by URI or Last
 * using a pattern for list name for easy cleanup
 * @param {String} [null] uri - uri number of the list to Save otherwise will be saved the last
 * @returns {Function} cy.wait - response for save request
 */
Cypress.Commands.add('saveList', (
    fileName = 'E2E Default list',
    uri = null
) => {
    let targetSelector = uri ? (`[id$="${uri}"]`) : selectorsList.listLast;

    cy.getSettled(targetSelector)
        .find(selectorsList.listNameInput)
        .clear()
        .type(fileName);

    cy.intercept('POST', urls.list.save).as('saveList');
    cy.getSettled(targetSelector)
        .find(selectorsList.saveElementButton)
        .should('be.visible')
        .click();

    return cy.wait('@saveList');
});

/**
 * Delete list by URI or Last
 * @param {String} [null] uri - uri number of the list to Delete otherwise will be deleted the last
 * @returns {Function} cy.wait - response for delete list request
 */
Cypress.Commands.add('deleteList', (
    uri = null
) => {
    let targetSelector = uri ? (`[id$="${uri}"]`) : selectorsList.listLast;

    cy.log(`Deleting list: ${targetSelector}`);

    cy.getSettled(targetSelector)
        .find(selectorsList.listDeleteButton)
        .scrollIntoView()
        .should('be.visible')
        .click();
    cy.intercept('POST', '**/taoBackOffice/Lists/removeList').as('removeList');
    cy.modalConfirm();
    cy.wait('@removeList');
});