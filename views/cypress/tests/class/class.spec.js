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

import classData from './classData';

describe('Classes', () => {
    const newClassName = classData.name;

    /**
     * - Set up the server & routes
     * - Log in
     * - Visit the page
     */
    beforeEach(() => {
        cy.setupServer();
        cy.addTreeRoutes();

        cy.loginAsAdmin();

        cy.loadItemsPage();

        cy.fixture('locators').as('locators');
    });

    /**
     * Delete added classes after each step
     */
    afterEach(() => {
        if (Cypress.$(`[title="${newClassName}"]`).length > 0) {
            cy.deleteClass(`[title="${newClassName}"]`);
        }
    });

    /**
     * Class tests
     */
    describe('Class creation, editing and deletion', () => {
        it('can create and rename a new class from the root class', function() {
            cy.addClass(this.locators.itemsRootClass);

            cy.renameSelectedClass(newClassName);

            cy.get(this.locators.itemsRootClass)
                .contains(newClassName)
                .should('exist');
        });

        it('can delete previously created class', function() {
            cy.addClass(this.locators.itemsRootClass);

            cy.renameSelectedClass(newClassName);

            cy.get(this.locators.actions.deleteClass).click('center');
            cy.get('.modal-body [data-control="ok"]').click();

            cy.wait('@deleteClass');

            cy.get(this.locators.itemsRootClass)
                .contains(newClassName)
                .should('not.exist');
        });

        it('has correct action buttons when class is selected', function() {
            cy.selectTreeNode(this.locators.itemsRootClass);

            // check the visible action buttons
            cy.get(this.locators.actionsContainer).within(() => {
                ['newClass', 'deleteClass', 'import', 'export', 'moveTo', 'newItem'].forEach(action => {
                    cy.get(this.locators.actions[action])
                        .should('exist');
                });
            });
        });
    });
});
