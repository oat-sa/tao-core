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

describe('Login', () => {
    const indexUrl = '/tao/Main/index';
    const loginUrl = '/tao/Main/login';

    it('forwards to login page', function () {
        cy.visit(indexUrl);
        cy.location('pathname').should('eq', loginUrl);
    });

    // helper that creates a login attempt with provided data
    const loginAttempt = (username, password) => {
        cy.get('#login', { timeout: 10000 }).type(username);
        cy.get('#password').type(password);

        cy.get('#connect').click();
    };

    it('cannot login with invalid user', function () {
        cy.visit(loginUrl);

        loginAttempt('invalid', '123');
        cy.get('.feedback[role=alert]').should('exist');
    });

    it('successful admin login', function () {
        const username = Cypress.env('adminUser');
        const password = Cypress.env('adminPass');

        cy.visit(loginUrl);
        loginAttempt(username, password);
        cy.location('pathname').should('eq', indexUrl);
    });
});