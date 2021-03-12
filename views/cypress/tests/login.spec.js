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
    beforeEach(() => {
        cy.fixture('urls').as('urls');
    });

    it('forwards to login page', function () {
        cy.visit(this.urls.root);
        cy.location('pathname').should('eq', this.urls.login);
    });

    // helper that creates a login attempt with provided data
    const loginAttempt = (username, password) => {
        cy.contains('label', 'Login')
            .parent()
            .within(() => {
                cy.get('input').type(username);
            });

        cy.contains('label', 'Password')
            .parent()
            .within(() => {
                cy.get('input').type(password);
            });

        cy.contains('Log in').click();
    };

    it('cannot login with invalid user', function () {
        cy.visit(this.urls.login);

        loginAttempt('invalid', '123');
        cy.contains('Invalid login or password. Please try again.');
    });

    it('successful admin login', function () {
        const username = Cypress.env('adminUser');
        const password = Cypress.env('adminPass');

        cy.visit(this.urls.login);
        loginAttempt(username, password);
        cy.location('pathname').should('eq', this.urls.index);
    });
});