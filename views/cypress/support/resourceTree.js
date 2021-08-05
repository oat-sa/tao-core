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

/**
 * Commands
 */
Cypress.Commands.add('addClass', (
    formSelector,
    editClassLabelUrl,
    treeRenderUrl,
    addSubClassUrl
) => {
    cy.log('COMMAND: addClass')
        .intercept('GET', `**/${ treeRenderUrl }/getOntologyData**`).as('treeRender')
        .intercept('POST', `**/${ addSubClassUrl }`).as('addSubClass')
        .intercept('POST', `**/${ editClassLabelUrl }`).as('editClassLabel')
        .get('[data-context=resource][data-action=subClass]')
        .click()
        .wait('@addSubClass', { requestTimeout: 10000 })
        .wait('@treeRender', { requestTimeout: 10000 })
        .wait('@editClassLabel', { requestTimeout: 10000 })
        .get(formSelector).should('exist');
});

Cypress.Commands.add('addClassToRoot', (
    rootSelector,
    formSelector,
    name,
    editClassLabelUrl,
    treeRenderUrl,
    addSubClassUrl
) => {
    cy.log('COMMAND: addClassToRoot', name)
        .intercept('GET', `**/${ treeRenderUrl }/getOntologyData**`).as('treeRender')
        .intercept('POST', `**/${ editClassLabelUrl }`).as('editClassLabel')
        .wait('@editClassLabel', { requestTimeout: 10000 })
        .wait('@treeRender', { requestTimeout: 10000 })
        .get(`${rootSelector} a`)
        .first()
        .click()
        .addClass(formSelector, editClassLabelUrl, treeRenderUrl, addSubClassUrl)
        .renameSelected(formSelector, name)
});

Cypress.Commands.add('moveClass', (
    formSelector,
    moveSelector,
    moveConfirmSelector,
    name,
    nameWhereMove,
    editClassLabelUrl,
    restResourceGetAll
) => {
    cy.log('COMMAND: moveClass', name)
        .get(`li[title="${name}"] a`)
        .first()
        .click()
        .intercept('GET', `**/${ restResourceGetAll }**`).as('classToMove')
        .intercept('POST', `**/${ editClassLabelUrl }`).as('editClassLabel')
        .wait('@editClassLabel', { requestTimeout: 10000 })
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

Cypress.Commands.add('moveClassFromRoot', (
    rootSelector,
    formSelector,
    moveSelector,
    moveConfirmSelector,
    deleteSelector,
    confirmSelector,
    name,
    nameWhereMove,
    treeRenderUrl,
    editClassLabelUrl,
    restResourceGetAll,
    resourceRelations,
    addSubClassUrl
) => {
    cy.log('COMMAND: moveClassFromRoot', name)
        .addClassToRoot(rootSelector, formSelector, name, editClassLabelUrl, treeRenderUrl, addSubClassUrl)
        .addClassToRoot(rootSelector, formSelector, nameWhereMove, editClassLabelUrl, treeRenderUrl, addSubClassUrl)
        .intercept('GET', `**/${ treeRenderUrl }/getOntologyData**`).as('treeRender')
        .intercept('POST', `**/${ editClassLabelUrl }`).as('editClassLabel')
        .wait('@treeRender', { requestTimeout: 10000 })
        .wait('@editClassLabel', { requestTimeout: 10000 })
        .get(`${rootSelector} a`)
        .first()
        .click()
        .get(`${rootSelector} li[title="${name}"] a`)
        .moveClass(formSelector, moveSelector, moveConfirmSelector, name, nameWhereMove, editClassLabelUrl, restResourceGetAll)
        .deleteClass(
            rootSelector,
            formSelector,
            deleteSelector,
            confirmSelector,
            nameWhereMove,
            resourceRelations,
            true
        );
});

Cypress.Commands.add('deleteClass', (
    rootSelector,
    formSelector,
    deleteSelector,
    confirmSelector,
    name,
    resourceRelations,
    isMove = false
) => {
    cy.log('COMMAND: deleteClass', name)
        .get(`${rootSelector} a`)
        .contains('a', name).click()
        .get(formSelector)
        .should('exist')

    if(!isMove) {
        cy.intercept('GET', `**/${ resourceRelations }/**`).as('resourceRelations')
    }

    cy.get(deleteSelector).click()

    if(!isMove) {
        cy.wait('@resourceRelations', { requestTimeout: 10000 })
    }

    cy.get('.modal-body')
        .then($body => {
            if ($body.find('label[for=confirm]').length > 0) {
                $body.click();
            }
        })
        .get(confirmSelector)
        .click();
});

Cypress.Commands.add('deleteClassFromRoot', (
    rootSelector,
    formSelector,
    deleteSelector,
    confirmSelector,
    name,
    treeRenderUrl,
    resourceRelations
) => {
    cy.log('COMMAND: deleteClassFromRoot', name)
        .intercept('GET', `**/${ treeRenderUrl }/getOntologyData**`).as('treeRender')
        .wait('@treeRender', { requestTimeout: 20000 })
        .get(`${rootSelector} a`)
        .first()
        .click()
        .get(`li[title="${name}"] a`)
        .deleteClass(rootSelector, formSelector, deleteSelector, confirmSelector, name, resourceRelations)
});

Cypress.Commands.add('deleteEmptyClassFromRoot', (
    rootSelector,
    formSelector,
    deleteSelector,
    confirmSelector,
    name,
    editClassLabelUrl,
    treeRenderUrl,
    addSubClassUrl,
    resourceRelations
) => {
    cy.log('COMMAND: deleteEmptyClassFromRoot', name)
        .addClassToRoot(rootSelector, formSelector, name, editClassLabelUrl, treeRenderUrl, addSubClassUrl)
        .deleteClassFromRoot(rootSelector, formSelector, deleteSelector, confirmSelector, name, treeRenderUrl, resourceRelations);
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

Cypress.Commands.add('deleteNode', (
    rootSelector,
    deleteSelector,
    name,
    treeRenderUrl,
    editItemUrl
) => {
    cy.log('COMMAND: deleteNode', name)
        .intercept('GET', `**/${ treeRenderUrl }/getOntologyData**`).as('treeRender')
        // .intercept('POST', `**/${ editItemUrl }`).as('editItem')
        .wait('@treeRender', { requestTimeout: 10000 })
        // .wait('@editItem', { requestTimeout: 10000 })
        .get(`${rootSelector} a`)
        .contains('a', name).click()
        .get(deleteSelector).click()
        .get('[data-control="ok"]').click()
        .get(`${rootSelector} a`)
        .contains('a', name).should('not.exist');
});

Cypress.Commands.add('renameSelected', (formSelector, newName) => {
    // TODO: update selector when data-testid attributes will be added
    cy.log('COMMAND: renameSelectedClass', newName)
        .get(`${ formSelector } input[name*=label]`)
        .clear()
        .type(newName)
        .get('button[id="Save"]')
        .click()
        .get(formSelector).should('exist')
        .get(`${ formSelector } input[name*=label]`).should('have.value', newName);
});
