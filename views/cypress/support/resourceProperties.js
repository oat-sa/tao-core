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

 import selectors from '../utils/selectors';

/**
 * Renames class to the given name (class should already be selected before running this command)
 * @param {String} formSelector - css selector for the class edition form
 * @param {String} newName
 */
 Cypress.Commands.add('renameSelectedClass', (formSelector, newName) => {
    cy.log('COMMAND: renameSelectedClass', newName)
        .getSettled(`${formSelector} ${selectors.labelSelector}`)
        .clear()
        .type(newName)
        .click()
        .getSettled('button[id="Save"]')
        .click()
        .wait('@editClassLabel')
        .get('#feedback-1, #feedback-2').should('not.exist')
        .get(formSelector).should('exist')
        .get(`${formSelector} ${selectors.labelSelector}`).should('have.value', newName)
        .wait('@treeRender');
});

/**
 * Renames node to the given name (node should already be selected before running this command)
 * @param {String} formSelector - css selector for the class edition form
 * @param {String} editUrl - url for the editing node POST request
 * @param {String} newName
 */
Cypress.Commands.add('renameSelectedNode', (formSelector, editUrl, newName) => {
    cy.log('COMMAND: renameSelectedNode', newName)
        .intercept('POST', `**${editUrl}`).as('edit')
        .get(`${formSelector} ${selectors.labelSelector}`)
        .clear()
        .type(newName)
        .get('button[id="Save"]')
        .click()
        .wait('@edit')
        .get('#feedback-1, #feedback-2').should('not.exist')
        .get(formSelector).should('exist')
        .get(`${formSelector} ${selectors.labelSelector}`).should('have.value', newName)
});

/**
 * Assigns value to the class property (works for the list with single selection of boolean values)
 * @param {String} nodeName
 * @param {String} nodePropertiesForm - css selector for the node properties edition form
 * @param {String} selectOption - css selector for the option to select
 * @param {String} treeRenderUrl - url for resource tree data GET request
 */
 Cypress.Commands.add('assignValueToProperty', (
    nodeName,
    nodePropertiesForm,
    selectOption,
    treeRenderUrl) => {

    cy.log('COMMAND: assignValueToProperty', nodeName, nodePropertiesForm);
    cy.getSettled(`li [title ="${nodeName}"] a`).last().click();
    cy.getSettled(nodePropertiesForm).find(selectOption).check();
    cy.intercept('GET', `**/${treeRenderUrl}/getOntologyData**`).as('treeRender')
    cy.getSettled('button[type="submit"]').click();
    cy.wait('@treeRender');
});
