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

describe('Item Author Role', () => {
    const userLogin = users.user_item_author.login;
    const userPassword = users.user_item_author.password;

    before(() => {
        cy.loginAsAdmin();
        tryToDeleteUser(users.user_item_author);
        cy.intercept('GET', '**/add*').as('add');
        cy.visit(urls.addUser);
        cy.wait('@add');
        cy.addUser(selectors.addUserForm, users.user_item_author, userRoles.itemAuthor);
        cy.intercept('GET', '**/logout*').as('logout');
        cy.logoutAttempt();
        cy.wait('@logout');
    });

    describe('Login', () => {
        it('Logged in successfully', function() {
            cy.loginAttempt(userLogin, userPassword);
            cy.get('#user_settings .username').should('have.text', users.user_item_author.login);
            cy.get('#logout');
        });

        it('Pass splash-screen', function() {
            cy.get('#splash-screen');
            cy.get(':nth-child(1) > :nth-child(1) > .block').click();
            cy.get('#splash-close-btn').click();
        });
    });

    describe('Check Item tab', () => {
        it('Has access to items tab', function() {
            cy.get('.lft.main-menu > :nth-child(1) > a .icon-item');
        });

        it('Has access to resource tree', function() {
            cy.intercept('GET', '**/taoItems/Items/*').as('treeItems');
            cy.wait('@treeItems');
            cy.get('#tree-manage_items').should('be.visible');
            cy.get('#tree-manage_items > ul > li')
        });

        it('Has access to resource actions', function() {
            cy.get('.tree-action-bar-box > .plain')
                .should('be.visible')
                .children().its('length').should('be.gt', 0);
        });
    });

    describe('Check Assets tab', () => {
        it('Has access to Assets tab', function() {
            cy.get('.lft.main-menu > li > a .icon-media');
            cy.intercept('GET', '**/taoMediaManager/MediaManager/*').as('mediaData');
            cy.visit(urls.mediaManager);
            cy.wait('@mediaData');
        });

        it('Has access to resource tree', function() {
            cy.get('#tree-media_manager').should('be.visible');
            cy.get('#tree-media_manager > ul > li');
        });

        it('Has access to resource actions', function() {
            cy.get('.tree-action-bar-box > .plain')
                .should('be.visible')
                .children().its('length').should('be.gt', 0);
        });
    });
});
