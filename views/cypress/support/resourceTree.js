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
Cypress.Commands.add('addClass', formSelector => {
    cy.log('COMMAND: addClass');
    cy.get('[data-context=resource][data-action=subClass]').click();
    cy.get(formSelector).should('exist');
});

Cypress.Commands.add('addClassToRoot', (rootSelector, formSelector, name) => {
    cy.log('COMMAND: addClassToRoot', name);

    // timeout for the tree to load
    cy.get(`${rootSelector} a`).first().then(root => {
        if (root.find(`li[title="${name}"] a`).length === 0) {
            cy.addClass(formSelector);
            cy.renameSelected(formSelector, name);
        }
    });
});

Cypress.Commands.add('addPropertyToClass', (
    className,
    editClass,
    classOptions,
    newPropertyName,
    propertyEdit) => {

    cy.log('COMMAND: addPropertyToClass',newPropertyName);

    cy.get(`li [title ="${className}"]`).last().click();
    cy.get(editClass).find('li[class="action btn-info small"]').last().click();
    cy.get(classOptions).find('a[class="btn-info property-adder small"]').click();

    // Wait so the modal is accessible from cy, otherwise it selects wrong button
     cy.wait(1000);

    cy.get('span[class="icon-edit"]').last().click();
    cy.get(propertyEdit).find('input').first().clear('input').type(newPropertyName);
    cy.get(propertyEdit).find('select[class="property-type property"]').select('list');
    cy.get(propertyEdit).find('select[class="property-listvalues property"]').select('Boolean');

    cy.get('button[type="submit"]').click();
});

Cypress.Commands.add('assignValueToProperty', (
    itemName,
    itemForm,
    selectTrue) => {

    console.log('I assign values to properties in an item')
    cy.get(`li [title ="${itemName}"] a`).last().click();
    cy.get(itemForm).find(selectTrue).check();
    cy.get('button[type="submit"]').click();
})

Cypress.Commands.add('deleteClass', (formSelector, deleteSelector, confirmSelector, name) => {
    cy.log('COMMAND: deleteClass', name);

    cy.contains('a', name).click();
    cy.get(formSelector).should('exist');
    // Wait for update to finish otherwise the modal is not accessible from cy
    cy.wait(1000);
    cy.get(deleteSelector).click();
    cy.get('.modal-body').then((body) => {
        if (body.find('label[for=confirm]').length) {
            cy.get('label[for=confirm]').click();
        }
        cy.get(confirmSelector).click();
    });
});

Cypress.Commands.add('deleteClassFromRoot', (rootSelector, formSelector, deleteSelector, confirmSelector, name) => {
    cy.log('COMMAND: deleteClassFromRoot', name);

    cy.get(`${rootSelector} a`).first().click();

    // timeout for the tree to load
    cy.get(rootSelector).then(root => {
        if (root.find(`li[title="${name}"] a`).length > 0) {
            cy.deleteClass(formSelector, deleteSelector, confirmSelector, name);
        }
    });
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
    cy.log('COMMAND: renameSelectedClass', newName);

    // TODO: update selector when data-testid attributes will be added
    cy.get(formSelector)
        .within(() => {
            cy.get('input[name*=label]')
                .clear()
                .type(newName);
            cy.get('button').click();
        });

    cy.get(formSelector).should('exist');

    cy.contains('a', newName).should('exist');
});

