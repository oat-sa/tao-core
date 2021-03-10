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

import urls from '../fixtures/urls.json';
import urlParams from '../fixtures/urlParams.json';

/**
 * Commands
 */
Cypress.Commands.add('addTreeRoutes', () => {
    cy.route('POST', '**/createItem').as('createItem');
    cy.route('POST', '**/editItem').as('editItem');
    cy.route('POST', '**/editClassLabel').as('editClass');
    cy.route('POST', '**/deleteItem').as('deleteItem');
    cy.route('POST', '**/deleteClass').as('deleteClass');
    cy.route('GET', '**/authoring?id=https://*/ontologies/tao.rdf#*').as('authoring');
});

Cypress.Commands.add('loadItemsPage', () => {
    cy.visit(urls.index);
});

Cypress.Commands.add('selectTreeNode', cssSelector => {
    cy.log('COMMAND: selectTreeNode', cssSelector);

    cy.fixture('locators').then(locators => {
        cy.get(cssSelector)
            .first()
            .then($el => {
                const $treeNode = $el.closest(locators.itemsRootClass);

                // click the node only if it isn't selected:
                if (!$treeNode.find(locators.itemClass).hasClass('open')) {
                    // it can be offscreen due to scrollable panel (so let's force click)
                    cy.wrap($treeNode).click('top', {force: true});

                    // 1 of 2 possible events indicates the clicked node's form loaded:
                    if ($treeNode.hasClass('class')) {
                        cy.wait('@editClass');
                    } else {
                        cy.wait('@editItem');
                    }
                }
            });
    });
});

Cypress.Commands.add('renameSelectedClass', newName => {
    cy.log('COMMAND: renameSelectedClass', newName);

    cy.fixture('locators').then(locators => {
        // assumes that editing form has already been rendered
        cy.get(locators.contentContainer)
        cy.get(locators.labelInput)
            .clear()
            .type(newName);

        cy.get(locators.saveBtn).click();
        cy.wait('@editClass');
    });
});

Cypress.Commands.add('renameSelectedItem', newName => {
    cy.log('COMMAND: renameSelectedItem', newName);

    cy.fixture('locators').then(locators => {
        // assumes that editing form has already been rendered
        cy.get(locators.contentContainer, {timeout: 10000})
        cy.get(locators.labelInput)
            .clear()
            .type(newName);

        cy.get(locators.saveBtn).click();
        cy.wait('@editItem', {timeout: 10000}).wait('@editItem');
    });
});

Cypress.Commands.add('addClass', cssSelector => {
    cy.log('COMMAND: addClass', cssSelector);

    cy.fixture('locators').then(locators => {
        cy.selectTreeNode(cssSelector);

        cy.get(locators.actions.newClass).click();

        cy.wait('@editClass');
    });
});

Cypress.Commands.add('addItem', cssSelector => {
    cy.log('COMMAND: addItem', cssSelector);

    cy.fixture('locators').then(locators => {
        cy.selectTreeNode(cssSelector);

        cy.get(locators.actions.newItem).click();

        // 2 different events must fire before proceeding
        cy.wait('@createItem').wait('@editItem');
    });
});

Cypress.Commands.add('deleteClass', cssSelector => {
    cy.log('COMMAND: deleteClass', cssSelector);

    cy.fixture('locators').then(locators => {
        cy.selectTreeNode(cssSelector);

        cy.get(locators.actions.deleteClass).click();
        cy.get('.modal-body [data-control="ok"]').click();

        cy.wait('@deleteClass');
    });
});

Cypress.Commands.add('deleteItem', cssSelector => {
    cy.log('COMMAND: deleteItem', cssSelector);

    cy.fixture('locators').then(locators => {
        cy.selectTreeNode(cssSelector);

        cy.get(locators.actions.deleteItem).click();
        cy.get('.modal-body [data-control="ok"]').click();

        cy.wait('@deleteItem');
    });
});

Cypress.Commands.add('goToItemAuthoring', cssSelector => {
    cy.log('COMMAND: goToItemAuthoring', cssSelector);

    cy.fixture('locators').then(locators => {
        cy.selectTreeNode(cssSelector);

        cy.get(locators.actions.gotToAuthoring).click();

        cy.wait('@authoring');
    });
});

