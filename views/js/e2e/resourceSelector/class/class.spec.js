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

import classData from './classData';
import subClassData from './subClassData';
import { selectors}  from '../resourceTree';

describe('Classes', () => {
    const newClassName = classData.name;
    const newSubClassName = subClassData.name;

    /**
     * - Set up the server & routes
     * - Log in
     * - Visit the page
     */
    beforeEach(() => {
        cy.setupServer();
        cy.addTreeRoutes();

        cy.login('admin');

        cy.fixture('urls')
            .as('urls')
            .then(urls => {
                cy.visit(`${urls.index}?structure=items&ext=taoItems`);
            });
    });

    /**
     * Destroy everything we created, leaving the environment clean for next time.
     */
    afterEach(() => {
        // maybe the tree is already empty?
        if (Cypress.$(selectors.itemsRootClass).find('.class, .instance').length === 0) {
            return;
        }

        // select the created class
        cy.get(selectors.resourceTree).within(() => {
            cy.get(selectors.itemsRootClass).click('top', {force: true});
            cy.contains(newClassName).click('top', {force: true});
        });

        // delete created nodes
        cy.get(selectors.deleteClassAction).click({force: true});
        cy.get('.modal-body [data-control="ok"]').click();

        cy.wait('@deleteClass');
    });

    /**
     * Class tests
     */
    describe('Class creation, edit and delete', () => {

        it('items page loads', function() {
            cy.get(selectors.resourceTree);
        });

        it('can create and rename a new class from the root class', function() {
            cy.addClass(selectors.itemsRootClass);

            cy.renameSelectedClass(newClassName);

            cy.get(selectors.resourceTree)
                .contains(newClassName)
                .should('exist')
                .and('be.visible');
        });

        it('can delete previously created class', function() {
            cy.addClass(selectors.itemsRootClass);

            cy.renameSelectedClass(newClassName);

            cy.get(selectors.deleteClassAction).click('center');
            cy.get('.modal-body [data-control="ok"]').click();

            cy.wait('@deleteClass');

            cy.get(selectors.resourceTree)
                .contains(newClassName)
                .should('not.exist');
        });

        it.skip('can create a new subclass from created class', function() {
            cy.addClass(selectors.itemsRootClass);

            cy.renameSelectedClass(newClassName);

            cy.addClass(`[title="${newClassName}"]`);

            cy.renameSelectedClass(newSubClassName); // causes tree to close (bug?)

            cy.get(selectors.resourceTree).within(() => {
                // reopen tree branch
                cy.get(`[title="${newClassName}"]`)
                    .find(selectors.toggler).first()
                    .click({force: true}); // doesn't seem to open node

                cy.contains(newSubClassName)
                    .should('exist');
            });
        });

        it('has correct action buttons when class is selected', function() {
            // select the root Item class
            cy.selectTreeNode(selectors.itemsRootClass);

            // check the visible action buttons
            cy.get(selectors.actionsContainer).within(() => {
                Cypress._.forEach([
                    'New class',
                    'Delete',
                    'Import',
                    'Export',
                    'Move To',
                    'New item'
                ], (buttonText) => {
                    // there are multiple buttons with the same name, hidden and not hidden
                    // so we have to be careful to select the right ones
                    cy.get('.action:not(.hidden)')
                        .contains(buttonText)
                        .should('exist')
                        .and('be.visible');
                });
            });
        });

    });
});
