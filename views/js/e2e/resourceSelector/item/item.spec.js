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

import itemData from './itemData';
import * as selectors from '../resourceTree';

describe('Items', () => {
    const newItemName = itemData.name;
    const modifiedItemName = `renamed ${itemData.name}`;

    /**
     * - Set up the server & routes
     * - Log in
     * - Visit the page
     */
    before(() => {
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

        // select the created item
        cy.get(selectors.resourceTree).within(() => {
            cy.get(selectors.itemsRootClass).click('top', {force: true});
            cy.contains(newItemName).click('top', {force: true});
        });

        // delete created nodes
        cy.get(selectors.deleteItemAction).click({force: true});
        cy.get('.modal-body [data-control="ok"]').click();

        cy.wait('@deleteItem');
    });

    describe('Item creation, edit and delete', () => {
        it.only('items page loads', function() {
            cy.get(selectors.resourceTree);
        });

        it('can create and rename a new item', function() {
            cy.addItem(selectors.itemsRootClass);

            cy.renameSelectedNode(newItemName);

            cy.wait('@editResource').wait(300);

            cy.get(selectors.resourceTree)
                .contains(newItemName)
                .should('exist')
                .and('be.visible');
        });

        it('can delete previously created item', function() {
            cy.addItem(selectors.itemsRootClass);

            cy.renameSelectedNode(newItemName);

            cy.wait('@editResource').wait(300);

            cy.get(selectors.deleteItemAction).click();
            cy.get('.modal-body [data-control="ok"]').click();

            cy.wait('@deleteItem');
        });

        it('has correct action buttons when item is selected', function() {
            // select first unselected item
            cy.get(selectors.resourceTree)
                .find('li.instance.selectable:not(.selected)').first()
                .click({ force: true });

            cy.wait('@editResource');

            cy.get(selectors.actionsContainer).within(() => {
                Cypress._.forEach([
                    'New class',
                    'Delete',
                    'Import',
                    'Export',
                    'Duplicate',
                    'Copy To',
                    'Move To',
                    'New item'
                ], (buttonText) => {
                    cy.contains(buttonText)
                        .should('exist')
                        .and('be.visible');
                });
            });

        });

        it('has correct action buttons when nothing is selected', function() {
            // deselect selected list item
            cy.get(selectors.resourceTree)
                .find('li.selected').first()
                .click({ force: true });

            cy.get(selectors.actionsContainer).within(() => {
                Cypress._.forEach([
                    'New class',
                    'Delete',
                    'Import',
                    'Export',
                    'Duplicate',
                    'Copy To',
                    'Move To',
                    'New item'
                ], (buttonText) => {
                    cy.contains(buttonText)
                        .should('not.be.visible');
                });
            });
        });

    });
});
