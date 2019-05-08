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
import * as selectors from '../resourceTree';

describe('ResourceSelector Tree', () => {
    const newClassName = classData.name;

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
     * The Items tree should always have a root class, 'Item'.
     * So without assuming anything about other nodes, let's aim to create
     * and test a structure like the following:
     *
     * [root] Item class
     *   - [L1] Temporary class
     *     - [L2] Temporary subclass
     */
    beforeEach(() => {
        // select the root Item class
        cy.selectTreeNode(selectors.itemsRootClass);

        // create a class
        cy.contains('New class').click();
        cy.wait('@editResource').wait(300); // re-rendering time buffer :(
        // rename it
        cy.renameSelectedNode(newClassName);
        cy.wait('@editResource').wait(300);

        // create a subclass
        cy.contains('New class').click();
        cy.wait('@editResource').wait(300);
    });

    /**
     * Destroy everything we created, leaving the environment clean for next time.
     */
    afterEach(() => {
        // select the created class
        cy.get(selectors.resourceTree).within(() => {
            cy.get(selectors.itemsRootClass).click('top', {force: true});
            cy.contains(newClassName).click('top', {force: true});
        });

        // delete created nodes
        cy.get(selectors.deleteClassAction).click('center');
        cy.get('.modal-body [data-control="ok"]').click();

        cy.wait('@deleteClass');
    });

    /**
     * Tree browsing tests
     */
    describe('Tree node opening and closing', () => {

        it.only('can open and close the root node', function() {
            // close via toggler
            cy.get(selectors.itemsRootClass)
                .find(selectors.toggler).first()
                .click({force: true});

            // is root closed?
            cy.get(selectors.itemsRootClass)
                .should('have.class', 'closed');
            cy.get(selectors.itemsRootClass)
                .find('.instance, .class')
                .should(($children) => {
                    expect($children).to.be.hidden;
                });

            // open via toggler
            cy.get(selectors.itemsRootClass)
                .find(selectors.toggler).first()
                .click({force: true});

            // is root open?
            cy.get(selectors.itemsRootClass)
                .should.not('have.class', 'closed');
            cy.get(selectors.itemsRootClass)
                .find('.instance, .class')
                .should(($children) => {
                    expect($children).to.not.be.hidden;
                });
        });

        it.only('can open and close a subnode of the root node', function() {
            // close via toggler
            cy.get(selectors.itemsRootClass)
                .find('.class:not(.closed):first')
                .find(selectors.toggler).first()
                .click({force: true});

            // is class closed?
            cy.get(selectors.itemsRootClass)
                .find('.class:first')
                .should('have.class', 'closed');
            cy.get(selectors.itemsRootClass)
                .find('.class:first')
                .find('.instance, .class')
                .should.not('be.visible');
                // .should(($children) => {
                //     expect($children).to.be.hidden;
                // });

            // is root class still open?
            cy.get(selectors.itemsRootClass)
                .should.not('have.class', 'closed');

            // open via toggler
            cy.get(selectors.itemsRootClass)
                .find('.class:not(.closed):first')
                .find(selectors.toggler).first()
                .click({force: true});

            // is class open?
            cy.get(selectors.itemsRootClass)
                .find('.class:first')
                .should.not('have.class', 'closed');
            cy.get(selectors.itemsRootClass)
                .find('.class:first')
                .find('.instance, .class')
                .should(($children) => {
                    expect($children).to.not.be.hidden;
                });
        });

        it('cannot open or close an empty class', function() {
            cy.get(selectors.itemsRootClass)
                .find('.class.empty:first() a')
                .should(($el) => {
                    expect($el).to.not.have.class('class-toggler');
                });
        });

    });
});
