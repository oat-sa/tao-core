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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

/**
 * CSS Selectors
 */
const selectors = {
    resourceTree:      '.resource-tree',
    actionsContainer:  '.tree-action-bar',
    contentContainer:  '.content-container',
    itemsRootClass:    '.class[data-uri="http://www.tao.lu/Ontologies/TAOItem.rdf#Item"]',
    deleteClassAction: '.action[data-action="removeNode"][data-context="class"]',
    deleteItemAction:  '.action[data-action="removeNode"][data-context="instance"]',
    toggler:           '.class-toggler'
};

export default {
    selectors: selectors
};

/**
 * Commands
 */
Cypress.Commands.add('addTreeRoutes', () => {
    cy.route('POST', '**/editItem').as('editItem');
    cy.route('POST', '**/editClassLabel').as('editClass');
    cy.route('POST', '**/deleteItem').as('deleteItem');
    cy.route('POST', '**/deleteClass').as('deleteClass');
});

Cypress.Commands.add('selectTreeNode', (selector) => {
    cy.log('COMMAND: selectTreeNode', selector);

    cy.get(selectors.resourceTree).within(() => {
        cy.get(selector)
            .then(($treeNode) => {
                if (!$treeNode.hasClass('selected')) {
                    // it can be offscreen due to scrollable panel (so let's force click)
                    cy.get($treeNode).find('a').first().click('top', {force: true});
                }
                // 2 possible events can indicate loading happened:
                if ($treeNode.hasClass('class')) {
                    cy.wait('@editClass').wait(300);
                }
                else {
                    cy.wait('@editItem').wait(300);
                }
            });

    });
});

Cypress.Commands.add('renameSelectedClass', (newName) => {
    cy.log('COMMAND: renameSelectedClass', newName);

    // assumes that editing form has already been rendered
    cy.get(selectors.contentContainer).within(() => {
        cy.contains('label', 'Label')
            .siblings('input')
            .should('be.visible')
            .clear()
            .type(newName);

        cy.contains('Save')
            .click();
    });
    cy.wait('@editClass').wait(300);
});

Cypress.Commands.add('renameSelectedItem', (newName) => {
    cy.log('COMMAND: renameSelectedItem', newName);

    // assumes that editing form has already been rendered
    cy.get(selectors.contentContainer).within(() => {
        cy.contains('label', 'Label')
            .siblings('input')
            .should('be.visible')
            .clear()
            .type(newName);

        cy.contains('Save')
            .click();
    });
    cy.wait('@editItem').wait(300);
});

Cypress.Commands.add('addClass', (selector) => {
    cy.log('COMMAND: addClass', selector);

    cy.selectTreeNode(selector);

    cy.contains('New class').click();

    cy.wait(['@editClass', '@editClass']).wait(300); // this event needs to fire twice before proceeding
});

Cypress.Commands.add('addItem', (selector) => {
    cy.log('COMMAND: addItem', selector);

    cy.selectTreeNode(selector);

    cy.contains('New item').click();

    cy.wait(['@editItem', '@editClass']).wait(300); // 2 different events must fire before proceeding
});
