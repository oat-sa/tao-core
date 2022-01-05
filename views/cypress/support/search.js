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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA ;
 */

/**
 * Enter searchString in to search input click search and wait for response
 * @param {Object} settings
 * @param {String} settings.search - string to search for
 * @param {String} settings.method - method for search result request
 * @param {String} settings.path - path to search result request
 * @returns {Function} cy.wait - response for search request
 */
Cypress.Commands.add('searchFor', (settings) => {
    const defaultSettings = {
        search: '',
        method: 'GET',
        path: '**/tao/Search/search*'
    };
    settings = Object.assign(defaultSettings, settings);

    cy.log('COMMAND: searchFor', settings.search);
    cy.intercept(settings.method, settings.path).as('searchFor');
    cy.getSettled('input[name=query]')
        .should('be.visible')
        .clear()
        .type(settings.search);
    cy.getSettled('button.icon-find')
        .should('be.visible')
        .click();

    return cy.wait('@searchFor');
});
