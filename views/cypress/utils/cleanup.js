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
 * Copyright (c) 2021 Open Assessment Technologies SA ;
 */

import selectors from './selectors';
import urls from './urls';

/** Tries to delete user with given credentials as cleanup process
 * @param {Object} user - contains user details
 */

export function tryToDeleteUser(user) {
    cy.visit(urls.manageUsers);
    cy.intercept('GET', `**/Users/**/*filterquery=${user.login}`).as('usersData');
    cy.get(`${selectors.manageUserTable} .filter input[name=filter]`)
        .type(`${user.login}{enter}`);
    cy.wait('@usersData').then((interception) => {
        if (interception.response.body.data) {
            cy.get(`${selectors.manageUserTable} table`)
                .contains('td', user.login)
                .siblings('.actions')
                .find('.remove')
                .should('not.have.class', 'disabled')
                .click();

            cy.get('.modal-body').then((body) => {
                if (body.find('label[for=confirm]').length) {
                    cy.get('label[for=confirm]').click();
                }

                cy.get(selectors.deleteConfirm).click();
            });
        } else {
            return true;
        }
    });
};
