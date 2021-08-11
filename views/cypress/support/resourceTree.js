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
    treeRenderUrl,
    addSubClassUrl
) => {
    cy.log('COMMAND: addClass')
        .intercept('GET', `**/${ treeRenderUrl }/getOntologyData**`).as('treeRender')
        .intercept('POST', `**/${ addSubClassUrl }`).as('addSubClass')
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
        .getSettled(`${rootSelector} a:nth(0)`)
        .click()
        .wait('@editClassLabel', { requestTimeout: 10000 })
        .addClass(formSelector, treeRenderUrl, addSubClassUrl)
        .renameSelectedClass(formSelector, name);
});

Cypress.Commands.add('moveClass', (
    moveSelector,
    moveConfirmSelector,
    name,
    nameWhereMove,
    restResourceGetAll
) => {
    cy.log('COMMAND: moveClass', name)
        .getSettled(`li[title="${name}"] a:nth(0)`)
        .click()
        .wait('@editClassLabel', { requestTimeout: 10000 })
        .intercept('GET', `**/${ restResourceGetAll }**`).as('classToMove')
        .get('#feedback-2, #feedback-1').should('not.exist')
        .getSettled(moveSelector)
        .click()
        .wait('@classToMove', { requestTimeout: 10000 })
        .getSettled(`.destination-selector a[title="${nameWhereMove}"]`)
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
    restResourceGetAll,
    deleteClassUrl,
    isItems
) => {
    cy.log('COMMAND: moveClassFromRoot', name)
        .get('#feedback-1, #feedback-2').should('not.exist')
        .getSettled(`${rootSelector} a:nth(0)`)
        .click()
        .wait('@editClassLabel', { requestTimeout: 10000 })
        .get(`${rootSelector} li[title="${name}"] a`)
        .moveClass(moveSelector, moveConfirmSelector, name, nameWhereMove, restResourceGetAll)
        .deleteClass(
            rootSelector,
            formSelector,
            deleteSelector,
            confirmSelector,
            deleteClassUrl,
            nameWhereMove,
            isItems
        );
});

Cypress.Commands.add('deleteClass', (
    rootSelector,
    formSelector,
    deleteSelector,
    confirmSelector,
    deleteClassUrl,
    name,
    isItems = false
) => {
    cy.log('COMMAND: deleteClass', name)
        .getSettled(`${rootSelector} a`)
        .contains('a', name).click()
        .get(formSelector)
        .should('exist')

    cy.get(deleteSelector).click()

    if (isItems) {
        cy.get('.modal-body label[for=confirm]')
            .click();
    }

    cy.intercept('POST', `**/${ deleteClassUrl }`).as('deleteClass')
    cy.get(confirmSelector)
      .click();
    cy.wait('@deleteClass', { requestTimeout: 10000 })
});

Cypress.Commands.add('deleteClassFromRoot', (
    rootSelector,
    formSelector,
    deleteSelector,
    confirmSelector,
    name,
    deleteClassUrl,
    resourceRelations,
    isMove,
    isItems
) => {
    cy.log('COMMAND: deleteClassFromRoot', name)
        .getSettled(`${rootSelector} a:nth(0)`)
        .click()
        .get(`li[title="${name}"] a`)
        .deleteClass(rootSelector, formSelector, deleteSelector, confirmSelector, deleteClassUrl, name, resourceRelations, isMove, isItems)
});

Cypress.Commands.add('addNode', (formSelector, addSelector) => {
    cy.log('COMMAND: addNode');

    cy.getSettled(addSelector).click();
    cy.get(formSelector).should('exist');
});

Cypress.Commands.add('selectNode', (rootSelector, formSelector, name) => {
    cy.log('COMMAND: selectNode', name);
    cy.getSettled(`${rootSelector} a:nth(0)`).click();
    cy.contains('a', name).click();
    cy.get(formSelector).should('exist');
});

Cypress.Commands.add('deleteNode', (
    rootSelector,
    deleteSelector,
    editUrl,
    name,
) => {
    cy.log('COMMAND: deleteNode', name)
        .intercept('POST', `**/${ editUrl }`).as('editUrl')
        .getSettled(`${rootSelector} a`)
        .contains('a', name).click()
        .wait('@editUrl', { requestTimeout: 10000 })
        .getSettled(deleteSelector).click()
        .getSettled('[data-control="ok"]').click()
        .getSettled(`${rootSelector} a`)
        .contains('a', name).should('not.exist');
});

Cypress.Commands.add('renameSelectedClass', (formSelector, newName) => {
    // TODO: update selector when data-testid attributes will be added
    cy.log('COMMAND: renameSelectedClass', newName)
        .getSettled(`${ formSelector } input[name*=label]`)
        .clear()
        .type(newName)
        .click()
        .getSettled('button[id="Save"]')
        .click()
        .wait('@editClassLabel')
        .get('#feedback-1, #feedback-2').should('not.exist')
        .get(formSelector).should('exist')
        .get(`${ formSelector } input[name*=label]`).should('have.value', newName);
});

Cypress.Commands.add('renameSelectedItem', (formSelector, editItemUrl, newName) => {
    // TODO: update selector when data-testid attributes will be added
    cy.log('COMMAND: renameSelectedItem', newName)
        .intercept('POST', `**${ editItemUrl }`).as('editItem')
        .get(`${ formSelector } input[name*=label]`)
        .clear()
        .type(newName)
        .get('button[id="Save"]')
        .click()
        .wait('@editItem', { requestTimeout: 10000 })
        .get('#feedback-1, #feedback-2').should('not.exist')
        .get(formSelector).should('exist')
        .get(`${ formSelector } input[name*=label]`).should('have.value', newName)
});

Cypress.Commands.add('addPropertyToClass', (
    className,
    editClass,
    classOptions,
    newPropertyName,
    propertyEdit,
    editClassUrl) => {

    cy.log('COMMAND: addPropertyToClass',newPropertyName);

    cy.getSettled(`li [title ="${className}"]`).last().click();
    cy.getSettled(editClass).click();
    cy.getSettled(classOptions).find('a[class="btn-info property-adder small"]').click();

    cy.getSettled('span[class="icon-edit"]').last().click();
    cy.get(propertyEdit).find('input').first().clear('input').type(newPropertyName);
    cy.get(propertyEdit).find('select[class="property-type property"]').select('list');
    cy.get(propertyEdit).find('select[class="property-listvalues property"]').select('Boolean');
    cy.intercept('POST', `**/${ editClassUrl }`).as('editClass');
    cy.get('button[type="submit"]').click();
    cy.wait('@editClass', { requestTimeout: 10000 });
});

Cypress.Commands.add('assignValueToProperty', (
    itemName,
    itemForm,
    selectTrue,
    treeRenderUrl) => {

    cy.log('COMMAND: assignValueToProperty', itemName, itemForm);
    cy.getSettled(`li [title ="${itemName}"] a`).last().click();
    cy.getSettled(itemForm).find(selectTrue).check();
    cy.intercept('GET', `**/${ treeRenderUrl }/getOntologyData**`).as('treeRender')
    cy.getSettled('button[type="submit"]').click();
    cy.wait('@treeRender', { requestTimeout: 10000 })
});
