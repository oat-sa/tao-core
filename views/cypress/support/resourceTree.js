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

const labelSelector = '[data-testid=Label]';

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
        .wait('@addSubClass')
        .wait('@treeRender')
        .wait('@editClassLabel')
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
        .wait('@editClassLabel')
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
        .wait('@editClassLabel')
        .intercept('GET', `**/${ restResourceGetAll }**`).as('classToMove')
        .get('#feedback-2, #feedback-1').should('not.exist')
        .getSettled(moveSelector)
        .click()
        .wait('@classToMove')
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
    moveSelector,
    moveConfirmSelector,
    name,
    nameWhereMove,
    restResourceGetAll,
) => {
    cy.log('COMMAND: moveClassFromRoot', name)
        .get('#feedback-1, #feedback-2').should('not.exist')
        .getSettled(`${rootSelector} a:nth(0)`)
        .click()
        .wait('@editClassLabel')
        .get(`${rootSelector} li[title="${name}"] a`)
        .moveClass(moveSelector, moveConfirmSelector, name, nameWhereMove, restResourceGetAll)
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

    cy.get(deleteSelector).click();

    if (isItems) {
        cy.get('.modal-body label[for=confirm]')
            .click();
    }

    cy.intercept('POST', `**/${ deleteClassUrl }`).as('deleteClass')
    cy.get(confirmSelector)
      .click();
    cy.wait('@deleteClass')
});

Cypress.Commands.add('deleteClassFromRoot', (
    rootSelector,
    formSelector,
    deleteSelector,
    confirmSelector,
    name,
    deleteClassUrl,
    isItems
) => {

    cy.log('COMMAND: deleteClassFromRoot', name)
        .getSettled(`${rootSelector} a:nth(0)`)
        .click()
        .get(`li[title="${name}"] a`)
        .deleteClass(rootSelector, formSelector, deleteSelector, confirmSelector, deleteClassUrl, name, isItems)
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
        .wait('@editUrl')
        .getSettled(deleteSelector).click()
        .getSettled('[data-control="ok"]').click()
        .getSettled(`${rootSelector} a`)
        .contains('a', name).should('not.exist');
});

Cypress.Commands.add('renameSelectedClass', (formSelector, newName) => {
    cy.log('COMMAND: renameSelectedClass', newName)
        .getSettled(`${ formSelector } ${labelSelector}`)
        .clear()
        .type(newName)
        .click()
        .getSettled('button[id="Save"]')
        .click()
        .wait('@editClassLabel')
        .get('#feedback-1, #feedback-2').should('not.exist')
        .get(formSelector).should('exist')
        .get(`${ formSelector } ${labelSelector}`).should('have.value', newName);
});

Cypress.Commands.add('renameSelectedItem', (formSelector, editItemUrl, newName) => {
    cy.log('COMMAND: renameSelectedItem', newName)
        .intercept('POST', `**${ editItemUrl }`).as('editItem')
        .get(`${ formSelector } ${labelSelector}`)
        .clear()
        .type(newName)
        .get('button[id="Save"]')
        .click()
        .wait('@editItem')
        .get('#feedback-1, #feedback-2').should('not.exist')
        .get(formSelector).should('exist')
        .get(`${ formSelector } ${labelSelector}`).should('have.value', newName)
});

Cypress.Commands.add('renameSelectedTest', (formSelector, editTestUrl, newName) => {
    cy.log('COMMAND: renameSelectedItem', newName)
        .intercept('POST', `**${ editTestUrl }`).as('editTest')
        .get(`${ formSelector } ${labelSelector}`)
        .clear()
        .type(newName)
        .get('button[id="Save"]')
        .click()
        .wait('@editTest')
        .get('#feedback-1, #feedback-2').should('not.exist')
        .get(formSelector).should('exist')
        .get(`${ formSelector } ${labelSelector}`).should('have.value', newName)
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
    cy.wait('@editClass');
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
    cy.wait('@treeRender')
});

/**
 * Drag one element and drop it onto another, with mouse
 * Implementation is specific to 'jquery ui drag drop' library.
 * @param {String} dragSelector -  element to drag selector
 * @param {String} dropSelector  - element to drop to selector
 */
Cypress.Commands.add('dragAndDrop', (dragSelector, dropSelector) => {
    cy.get(dragSelector).should('exist')
    .get(dropSelector).should('exist');

    function getElementCenterCoords($el) {
        const rect = $el[0].getBoundingClientRect();
        const x = Math.round(rect.left + rect.width / 2);
        const y = Math.round(rect.top + rect.height / 2);
        return { x, y };
    }
    cy.get(dragSelector).then($draggable => {
        // Pick up this
        cy.get(dropSelector).then($droppable => {
            const { x: startX, y: startY } = getElementCenterCoords($draggable);
            const { x, y } = getElementCenterCoords($droppable);
            cy.get('#qti-block-element-placeholder').should('not.exist');
        
            cy.wrap($draggable)
            .trigger("mouseover", {force: true})
            .trigger('mousedown', {
                which: 1,
                pageX: startX,
                pageX: startY
            })
            .trigger('mousemove', {
                which: 1,
                pageX: x,
                pageY: y,
                clientX: x,
                clientY: y,
                force: true
            });

            cy.wrap($droppable)
            .trigger("mouseover", {force: true})
            .trigger('mousemove', {
                which: 1,
                pageX: x,
                pageY: y,
                clientX: x,
                clientY: y,
                force: true
            });

            cy.wrap($draggable).trigger('mouseup', { force: true });
            cy.document().trigger('mouseup', { force: true });
        }); // Drop over this
    }); 
}
);

