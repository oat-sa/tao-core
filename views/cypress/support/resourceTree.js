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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA ;
 */

import selectors from "../../../../taoTests/views/cypress/utils/selectors";

/**
 * Commands
 */
Cypress.Commands.add('addClass', formSelector => {
    cy.log('COMMAND: addClass')
        .intercept('GET', `**/taoItems/Items/getOntologyData**`).as('treeRender')
        .intercept('POST', `**/taoItems/Items/addSubClass`).as('addSubClass')
        .intercept('POST', `**/taoItems/Items/editClassLabel`).as('editClassLabel')
        .get('[data-context=resource][data-action=subClass]')
        .click()
        .wait('@addSubClass', { requestTimeout: 10000 })
        .wait('@treeRender', { requestTimeout: 10000 })
        .wait('@editClassLabel', { requestTimeout: 10000 })
        .get(formSelector).should('exist');
});

Cypress.Commands.add('addClassToRoot', (rootSelector, formSelector, name) => {
    cy.log('COMMAND: addClassToRoot', name)
        .intercept('POST', `**/taoItems/Items/editClassLabel`).as('editClassLabel')
        .wait('@editClassLabel', { requestTimeout: 10000 })
        .get(`${rootSelector} a`)
        .first()
        .click()
        .addClass(formSelector)
        .renameSelected(formSelector, name)
});

Cypress.Commands.add('moveClass', (formSelector, moveSelector, moveConfirmSelector, name, nameWhereMove) => {
    cy.log('COMMAND: moveClass', name)
        .get(`li[title="${name}"] a`)
        .first()
        .click()
        .intercept('GET', '**tao/RestResource/getAll**').as('classToMove')
        .get(moveSelector)
        .click()
        .wait('@classToMove', { requestTimeout: 10000 })
        .get(`.destination-selector a[title="${nameWhereMove}"]`)
        .click()
        .get('.actions button')
        .click()
        .get(moveConfirmSelector)
        .click()
        .get(`li[title="${name}"] a`).should('not.exist');
});

Cypress.Commands.add('moveClassFromRoot', (rootSelector, formSelector, moveSelector, moveConfirmSelector, name, nameWhereMove) => {
    cy.log('COMMAND: moveClassFromRoot', name)
        .addClassToRoot(rootSelector, formSelector, name)
        .addClassToRoot(rootSelector, formSelector, nameWhereMove)
        .intercept('GET', `**/taoItems/Items/getOntologyData**`).as('treeRender')
        .intercept('POST', `**/taoItems/Items/editClassLabel`).as('editClassLabel')
        .get(`${rootSelector} a`)
        .first()
        .wait('@treeRender', { requestTimeout: 10000 })
        .wait('@editClassLabel', { requestTimeout: 10000 })
        .click()
        .get(`${rootSelector} li[title="${name}"] a`)
        .moveClass(formSelector, moveSelector, moveConfirmSelector, name, nameWhereMove)
        .deleteClassFromRoot(
            rootSelector,
            formSelector,
            moveSelector,
            moveConfirmSelector,
            nameWhereMove
        );
});

Cypress.Commands.add('deleteClass', (formSelector, deleteSelector, confirmSelector, name) => {
    cy.log('COMMAND: deleteClass', name)
        .contains('a', name).click()
        .get(formSelector)
        .should('exist')
        .get(deleteSelector)
        .click()
        .get('.modal-body label[for=confirm]')
        .click()
        .get(confirmSelector)
        .click();
});

Cypress.Commands.add('deleteClassFromRoot', (rootSelector, formSelector, deleteSelector, confirmSelector, name) => {
    cy.log('COMMAND: deleteClassFromRoot', name)
        .get(`${rootSelector} a`)
        .first()
        .click()
        .get(`li[title="${name}"] a`)
        .deleteClass(formSelector, deleteSelector, confirmSelector, name)
});

Cypress.Commands.add('deleteEmptyClassFromRoot', (rootSelector, formSelector, deleteSelector, confirmSelector, name) => {
    cy.log('COMMAND: deleteEmptyClassFromRoot', name)
        .addClassToRoot(rootSelector, formSelector, name)
        .deleteClassFromRoot(rootSelector, formSelector, deleteSelector, confirmSelector, name);
});

Cypress.Commands.add('addNode', (formSelector, addSelector) => {
    cy.log('COMMAND: addNode');

    cy.get(addSelector).click();
    cy.get(formSelector).should('exist');
});

Cypress.Commands.add('selectNode', (rootSelector, formSelector, name) => {
    cy.log('COMMAND: selectNode', name);

    cy.get(`${rootSelector} a`).first().click();
    cy.contains('a', name).click();
    cy.get(formSelector).should('exist');
});

Cypress.Commands.add('deleteNode', (deleteSelector, name) => {
    cy.log('COMMAND: deleteNode', name);

    cy.contains('a', name).click();

    cy.get(deleteSelector).click();
    cy.get('[data-control="ok"]').click();

    cy.contains('a', name).should('not.exist');
});

Cypress.Commands.add('renameSelected', (formSelector, newName) => {
    // TODO: update selector when data-testid attributes will be added
    cy.log('COMMAND: renameSelectedClass', newName)
        .get(`${ formSelector } input[name*=label]`)
        .clear()
        .type(newName)
        .get('button[id="Save"]')
        .click()
        .intercept('POST', `**/taoItems/Items/editClassLabel`).as('editClassLabel')
        .wait('@editClassLabel', { requestTimeout: 10000 })
        .get(formSelector).should('exist')
        .get(`${formSelector} input[class="global"][type="text"]`).should('have.value', newName);
});
