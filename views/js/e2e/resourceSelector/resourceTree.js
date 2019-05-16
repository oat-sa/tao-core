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
    toggler:           '.class-toggler',
    treeNode:          '.instance, .class',
    labelInput:        'input[name$="label"]',
    saveBtn:           '#Save',
    actionBtn:         '.action',
    actions: {
        newItem: '.action[data-action="instanciate"]',
        newClass: '.action[data-action="subClass"]',
        deleteItem: '.action[data-action="removeNode"][data-context="instance"]',
        deleteClass: '.action[data-action="removeNode"][data-context="class"]',
        import: '.action[data-action="loadClass"]',
        export: '.action[data-action="load"]',
        moveTo: '.action[data-action="moveTo"]',
        copyTo: '.action[data-action="copyTo"]',
        duplicate: '.action[data-action="duplicateNode"]'
    }
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

Cypress.Commands.add('loadItemsPage', () => {
    const fixtures = [];

    Cypress.Promise.all([
        cy.fixture('urls').then(fx => fixtures.push(fx)),
        cy.fixture('urlParams').then(fx => fixtures.push(fx)),
    ])
    .then(() => {
        const [urls, urlParams] = fixtures;

        // Provide the full URL parameters including 'uri'
        // to guarantee a predictable tree with the 'Item' root class selected
        cy.visit(`${urls.index}?${urlParams.taoItemsRoot}&${urlParams.nosplash}`);
        // Important to register this first response, or it will mess up future "wait"s:
        // Extended timeout because some envs can be slow to load all resources
        cy.wait('@editClass', { timeout: 10000 });
    });
});

Cypress.Commands.add('selectTreeNode', (cssSelector) => {
    cy.log('COMMAND: selectTreeNode', cssSelector);

    cy.get(selectors.resourceTree).within(() => {
        cy.get(cssSelector)
            .first()
            .then(($el) => {
                const $treeNode = $el.closest(selectors.treeNode);

                // click the node only if it isn't selected:
                if (!$treeNode.hasClass('selected')) {
                    // it can be offscreen due to scrollable panel (so let's force click)
                    cy.wrap($treeNode)
                        .should('not.have.class', 'selected')
                        .click('top', {force: true});

                    // 1 of 2 possible events indicates the clicked node's form loaded:
                    if ($treeNode.hasClass('class')) {
                        cy.wait('@editClass');
                    }
                    else {
                        cy.wait('@editItem');
                    }
                }
            });
    });
});

Cypress.Commands.add('renameSelectedClass', (newName) => {
    cy.log('COMMAND: renameSelectedClass', newName);

    // assumes that editing form has already been rendered
    cy.get(selectors.contentContainer).within(() => {
        cy.get(selectors.labelInput)
            .clear()
            .type(newName);

        cy.get(selectors.saveBtn).click();
    });
    // this event needs to fire twice before proceeding
    cy.wait('@editClass').wait('@editClass');
});

Cypress.Commands.add('renameSelectedItem', (newName) => {
    cy.log('COMMAND: renameSelectedItem', newName);

    // assumes that editing form has already been rendered
    cy.get(selectors.contentContainer).within(() => {
        cy.get(selectors.labelInput)
            .clear()
            .type(newName);

        cy.get(selectors.saveBtn).click();
    });
    // this event needs to fire twice before proceeding
    cy.wait('@editItem').wait('@editItem');
});

Cypress.Commands.add('addClass', (cssSelector) => {
    cy.log('COMMAND: addClass', cssSelector);

    cy.selectTreeNode(cssSelector);

    cy.get(selectors.actions.newClass).click();

    // this event needs to fire twice before proceeding
    cy.wait('@editClass').wait('@editClass');
});

Cypress.Commands.add('addItem', (cssSelector) => {
    cy.log('COMMAND: addItem', cssSelector);

    cy.selectTreeNode(cssSelector);

    cy.get(selectors.actions.newItem).click();

    // 2 different events must fire before proceeding
    cy.wait('@editClass').wait('@editItem');
});

Cypress.Commands.add('deleteClass', (cssSelector) => {
    cy.log('COMMAND: deleteClass', cssSelector);

    cy.selectTreeNode(cssSelector);

    cy.get(selectors.actions.deleteClass).click({force: true});
    cy.get('.modal-body [data-control="ok"]').click();

    cy.wait('@deleteClass');
});

Cypress.Commands.add('deleteItem', (cssSelector) => {
    cy.log('COMMAND: deleteItem', cssSelector);

    cy.selectTreeNode(cssSelector);

    cy.get(selectors.actions.deleteItem).click({force: true});
    cy.get('.modal-body [data-control="ok"]').click();

    cy.wait('@deleteItem');
});
