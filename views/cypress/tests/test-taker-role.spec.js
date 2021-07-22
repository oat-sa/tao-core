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
import selectors from '../utils/selectors';

describe('Test Taker Role', () => {
    const userLogin = users.user_test_taker.login;
    const userPassword = users.user_test_taker.password;

    before(() => {
        cy.loginAsAdmin();
    });

    describe('Create test taker user', () => {
        it('Admin can manage test takers', function() {
            cy.intercept('GET', '/taoTestTaker/TestTaker/*').as('testTakerTree');
            cy.visit(urls.testTakersManager);
            cy.wait('@testTakerTree', {
                requestTimeout: 10000
            });
        });

        it('Admin can create test taker', function() {
            cy.intercept('POST', '/taoTestTaker/TestTaker/addInstance').as('addTestTaker');
            cy.get('#testtaker-new').click();
            cy.wait('@addTestTaker', {
                requestTimeout: 10000
            }).its('response.body.success').should('eq', true);
            cy.intercept('POST', '/tao/GenerisTree/getData').as('testTakerData');
            cy.wait('@testTakerData', {
                requestTimeout: 10000
            });
            cy.addUser(selectors.addTestTakerForm, users.user_test_taker);
        });
    });

    describe('Login as test taker user', () => {
        it('Login Successfully', function() {
            cy.intercept('GET', '**/logout*').as('logout');
            cy.logoutAttempt();
            cy.wait('@logout', {
                requestTimeout: 10000
            });
            cy.loginAttempt(userLogin, userPassword);
            cy.contains('.settings-menu li', users.user_test_taker.login);
            cy.get('#logout');
        });
    });

    describe('Only has access to deliveries', () => {
        it("Is in delivery scope", function() {
            cy.get('body').should('have.class', 'delivery-scope')
        });

        it("Doesn't have access to tabs", function() {
            cy.get('.lft.main-menu').should('not.exist');
        });

        it('Can see listing of deliveries', () => {
            cy.get('.test-listing');
        });
    });

    after(() => {
        cy.logoutAttempt();
        cy.loginAsAdmin();
        cy.visit(urls.manageUsers);
        cy.deleteUser(users.user_test_taker);
    });
});
