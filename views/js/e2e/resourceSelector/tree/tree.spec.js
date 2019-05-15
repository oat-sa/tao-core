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

import classData from '../class/classData';
import { selectors }  from '../resourceTree';

describe('ResourceSelector Tree', () => {
    const newClassName = classData.name;

    /**
     * - Set up the server & routes
     * - Log in
     * - Visit the page
     */
    beforeEach(() => {
        cy.setupServer();
        cy.addTreeRoutes();

        cy.login('admin');

        cy.loadItemsPage();

        /**
         * The Items tree should always have a root class, 'Item'.
         * So without assuming anything about other nodes, let's aim to create
         * and test a structure like the following:
         *
         * [root] Item class
         *   - [L1] Temporary class
         *     - [L2] Temporary subclass
         */

        // select the root Item class
        cy.selectTreeNode(selectors.itemsRootClass);

        // create a class
        cy.addClass(selectors.itemsRootClass);
        // // rename it
        cy.renameSelectedClass(newClassName);

        // create a subclass
        cy.addClass(`[title="${newClassName}"]`);
    });

    /**
     * Destroy everything we created, leaving the environment clean for next time.
     */
    afterEach(() => {
        if (Cypress.$(`[title="${newClassName}"]`).length > 0) {
            cy.deleteClass(`[title="${newClassName}"]`);
        }
    });

    /**
     * Tree browsing tests
     */
    describe('Tree node opening and closing', () => {

        it('can open and close the root node', function() {
            // @action: close via toggler
            cy.get(selectors.itemsRootClass)
                .find(selectors.toggler).first()
                .click({force: true});

            // @test: is root closed?
            cy.get(selectors.itemsRootClass)
                .should('have.class', 'closed')
                .find('.instance, .class')
                .should('not.be.visible');

            // @action: open via toggler
            cy.get(selectors.itemsRootClass)
                .find(selectors.toggler).first()
                .click({force: true});

            // @test: is root open?
            cy.get(selectors.itemsRootClass)
                .should('not.have.class', 'closed')
                .find('.instance, .class')
                .should('be.visible');
        });

        it('can open and close a subnode of the root node', function() {
            cy.get(selectors.resourceTree).within(() => {
                // @action: close via toggler
                cy.get(`[title="${newClassName}"]`)
                    .find(selectors.toggler).first()
                    .click({force: true});

                // @test: is class closed?
                cy.get(`[title="${newClassName}"]`)
                    .closest(selectors.treeNode)
                    .should('have.class', 'closed')
                    .find('.instance, .class')
                    .should('not.be.visible');

                // @test: is root class still open?
                cy.get(selectors.itemsRootClass)
                    .should('not.have.class', 'closed');

                // @action: open via toggler
                cy.get(`[title="${newClassName}"]`)
                    .find(selectors.toggler).first()
                    .click({force: true});

                // @test: is class open?
                cy.get(`[title="${newClassName}"]`)
                    .closest(selectors.treeNode)
                    .should('not.have.class', 'closed')
                    .find('.instance, .class')
                    .should('be.visible');
            });
        });

        it('cannot open or close an empty class', function() {
            cy.get(selectors.itemsRootClass)
                .find('.class.empty:first() a')
                .should('not.have.class', 'class-toggler');
        });
    });
});
