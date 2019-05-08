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

Cypress.Commands.add('addTreeRoutes', () => {
    cy.route('POST', '**/edit*').as('editResource');
    cy.route('POST', '**/deleteItem').as('deleteItem');
    cy.route('POST', '**/deleteClass').as('deleteClass');
});

Cypress.Commands.add('selectTreeNode', (selector) => {
    const itemTreeSelector = '.resource-tree';

    cy.get(itemTreeSelector).within(() => {
        cy.get(selector)
            .then(($treeNode) => {
                if (!$treeNode.hasClass('selected')) {
                    // it can be offscreen due to scrollable panel (so let's force click)
                    cy.get($treeNode).find('a').first().click('top', {force: true});
                }
            });
    });
});

Cypress.Commands.add('renameSelectedNode', (newName) => {
    const contentContainer = '.content-container';

    // assumes that editing form has already been rendered
    cy.get(contentContainer).within(() => {
        cy.contains('label', 'Label')
            .siblings('input')
            .should('be.visible')
            .clear()
            .type(newName);

        cy.contains('Save')
            .click();
    });
});

Cypress.Commands.add('addClass', (selector) => {
    cy.selectTreeNode(selector);

    cy.contains('New class').click();

    cy.wait('@editResource').wait(300);
});

export default selectors = {
    itemTreeSelector: '.resource-tree',
    actionsContainer: '.tree-action-bar',
    contentContainer: '.content-container'
};