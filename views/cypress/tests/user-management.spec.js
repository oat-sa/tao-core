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
import urls from '../utils/urls';
import users from '../utils/users';
import userRoles from '../utils/userRoles';

describe('User Management', () => {
    describe('Add User', () => {
        before(() => {
            cy.loginAsAdmin();
            cy.intercept('GET', '**/add*').as('add');
            cy.visit(urls.addUser);

            cy.wait('@add');
        });

        describe('Visit add users page', () => {
            it('should be Users settings menu button', function () {
                cy.get('.setting-menu-container .settings-menu .active.li-users').should('have.length', 1);
            });

            it('should be form with properties', function () {
                cy.get('section.content-container').find('form').should('have.length', 1);
            });
        });

        describe('Can create user', () => {
            it('fill user form', function () {
                cy.addUser(selectors.addUserForm, users.user_cypress, userRoles.itemAuthor)
            });
        })
    });

    describe('Delete User', () => {
        before(() => {
            cy.loginAsAdmin();
            cy.intercept('GET', '**/Users/data*').as('usersData')
            cy.wait('@usersData');
        });

        describe('Visit manage users page', () => {
            it('should be Users settings menu button', function () {
                cy.get('.setting-menu-container .settings-menu .active.li-users').should('have.length', 1);
            });

            it('should be table with actions', function () {
                cy.get(`${selectors.manageUserTable} table td.actions`)
            });
        });

        describe('Can delete user', () => {
            it('Find and delete user', function() {
                cy.deleteUser(users.user_cypress)
            })
        })
    });
});
