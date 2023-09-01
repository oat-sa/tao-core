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
import userRoles from '../utils/userRoles';

/**
 * Fills user properties in form while checking that login field does not already exist
 * @param {String} targetForm - contains selector targeting the Form to fill
 * @param {Object} userData - contains user details to fill form
 * @param {Array<string>} roles - contains each role that needs to be applied to user
 */
Cypress.Commands.add('addUser', (targetForm, userData, roles) => {
    cy.log('COMMAND: addUser');

    cy.get(targetForm)
        .within(() => {
            cy.get('input[name*=label]')
                .clear()
                .type(userData.label);
            cy.get('select[name*=userUILg]')
                .select(userData.language);
            cy.get('input[name*=login]')
                .clear()
                .type(userData.login);
            if (roles) {
                cy.get('input[type=checkbox][name*="userRoles"]')
                    .check(roles || userRoles.itemAuthor, { force: true });
            }
            cy.get('input[name*=password1]')
                .clear()
                .type(userData.password);
            cy.get('input[name*=password2]')
                .clear()
                .type(userData.password);
            cy.get('.login-info.ui-state-error').should('not.exist')
            cy.get('button[type=submit]').click();
        });
});

/**
 * Search for user in table using search input[name=filter]
 * Triggers delete action by clicking remove button
 * Confirms action in modal
 * @param {Object} userData - contains user details
 */
Cypress.Commands.add('deleteUser', (userData) => {
    cy.log('COMMAND: deleteUser');

    cy.intercept('GET', `**/Users/**/*filterquery=${userData.login}`).as('usersData')
    cy.get(`${selectors.manageUserTable} .filter input[name=filter]`)
        .type(`${userData.login}{enter}`);
    cy.wait('@usersData');

    cy.get(`${selectors.manageUserTable} table`)
        .contains('td', userData.login)
        .siblings('.actions')
        .find('.remove')
        .should('not.have.class', 'disabled')
        .click()

    cy.get('.modal-body').then((body) => {
        if (body.find('label[for=confirm]').length) {
            cy.get('label[for=confirm]').click();
        }

        cy.get(selectors.deleteConfirm).click();
    });
});
