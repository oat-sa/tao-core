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

    // hack to cope with flickering
    cy.get(formSelector).should('not.exist');
    cy.get(formSelector);
});

Cypress.Commands.add('addNode', (formSelector, addSelector) => {
    cy.log('COMMAND: addNode', addSelector);

    cy.get(addSelector).click();

    // hack to cope with the forms flickering
    cy.get(formSelector).should('not.exist');
    cy.get(formSelector);
});

Cypress.Commands.add('selectNode', (formSelector, name) => {
    cy.log('COMMAND: selectNode', name);

    cy.get(`li[title="${name}"] a`).click();
    cy.get(formSelector).should('exist');
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

    // hack to cope with the forms flickering
    cy.get(formSelector).should('not.exist');
    cy.get(formSelector).should('exist');

    cy.get(`li[title="${newName}"] a`).should('exist');
});

Cypress.Commands.add('deleteClass', (formSelector, deleteSelector, confirmSelector, name) => {
    cy.log('COMMAND: deleteClass', deleteSelector, name);

    cy.contains(name).click();
    cy.get(formSelector).should('exist');

    cy.get(deleteSelector).click();
    cy.get('.modal-body').then((body) => {
        if (body.find('label[for=confirm]').length) {
            cy.get('label[for=confirm]').click();
        }
        cy.get(confirmSelector).click();
    });
});

Cypress.Commands.add('deleteNode', (deleteSelector, name) => {
    cy.log('COMMAND: deleteNode', deleteSelector, name);

    cy.contains(name).click();

    cy.get(deleteSelector).click();
    cy.get('[data-control="ok"]').click();

    cy.get(`li[title="${name}"] a`).should('not.exist');
});

