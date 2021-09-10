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
import urls from '../utils/urls';
import users from '../utils/users';
import userRoles from '../utils/userRoles';
import selectors from '../utils/selectors';
import { tryToDeleteUser } from '../utils/cleanup';

describe('Test Taker Role', () => {
    const userLogin = users.user_test_taker.login;
    const userPassword = users.user_test_taker.password;

    before(() => {
        cy.loginAsAdmin();
        tryToDeleteUser(users.user_test_taker);
        cy.intercept('GET', '**/add*').as('add');
        cy.visit(urls.addUser);
        cy.wait('@add');
        cy.addUser(selectors.addUserForm, users.user_test_taker, userRoles.testTaker);
        cy.intercept('GET', '**/logout*').as('logout');
        cy.logoutAttempt();
        cy.wait('@logout');
    });

    describe('Login', () => {
        it('Logged in successfully', function() {
            cy.loginAttempt(userLogin, userPassword);
            cy.contains('.settings-menu li', users.user_test_taker.login);
            cy.get('#logout');
        });
    });

    describe('Only has access to deliveries', () => {
        it('Is in delivery scope', function() {
            cy.get('body').should('have.class', 'delivery-scope')
        });

        it('Doesn\'t have access to tabs', function() {
            cy.get('.lft.main-menu').should('not.exist');
        });

        it('Can see listing of deliveries', () => {
            cy.get('.test-listing');
        });
    });
});
