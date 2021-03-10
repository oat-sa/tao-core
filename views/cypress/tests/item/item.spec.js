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

import itemData from './itemData';

describe('Items', () => {
    const newItemName = itemData.name;

    /**
     * Set up the server & routes
     * Log in
     * Visit the page
     */
    beforeEach(() => {
        cy.setupServer();
        cy.addTreeRoutes();

        cy.loginAsAdmin();

        cy.loadItemsPage();

        cy.fixture('locators').as('locators');
    });

    /**
     * Delete newly created items after each step
     */
    afterEach(() => {
        if (Cypress.$(`[title="${newItemName}"]`).length > 0) {
            cy.deleteItem(`[title="${newItemName}"]`);
        }
    });

    /**
     * Item tests
     */
    describe('Item creation, editing and deletion', () => {
        it('can create and rename a new item', function() {
            cy.addItem(this.locators.itemsRootClass);

            cy.renameSelectedItem(newItemName);

            cy.get(this.locators.itemsRootClass)
                .contains(newItemName)
                .should('exist');
        });

        it('can delete item', function() {
            cy.addItem(this.locators.itemsRootClass);

            cy.renameSelectedItem(newItemName);

            cy.get(this.locators.actions.deleteItem).click();
            cy.get('.modal-body [data-control="ok"]').click();

            cy.wait('@deleteItem');
        });

        it('has correct action buttons when item is selected', function() {
            cy.addItem(this.locators.itemsRootClass);

            cy.renameSelectedItem(newItemName);

            cy.get(`[title="${newItemName}"]`)
                .closest(this.locators.itemsRootClass)
                .should('not.have.class', 'closed');

            cy.get(this.locators.actionsContainer).within(() => {
                ['newClass', 'deleteItem', 'import', 'export', 'moveTo', 'copyTo', 'duplicate', 'newItem'].forEach(
                    action => {
                        cy.get(this.locators.actions[action])
                            .should('exist');
                    }
                );
            });
        });
    });
});
