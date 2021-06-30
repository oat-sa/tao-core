/* eslint-disable no-undef */
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

describe('Items', () => {
    const indexUrl = '/tao/Main/index';

    beforeEach(() => {
        cy.loginAsAdmin();
    });

    it('should forward to items on click', function () {
        cy.visit(indexUrl);
        cy.get('ul.main-menu').children().contains('Items').click();
        cy.location('pathname').should('eq', indexUrl);
    });

    it('should contain Item menu active and contain text Items', function () {
        cy.get('ul.main-menu').children().contains('Items').click();
        cy.get('ul.main-menu').children('.active').should('include.text', 'Items');
        cy.location('pathname').should('eq', indexUrl);
    });

    it('should contain search with placeholder value search item', function () {
        cy.get('.horizontal-action-bar')
            .children('.search-area')
            .find('input')
            .should('have.attr', 'placeholder', 'Search Item');
        cy.location('pathname').should('eq', indexUrl);
    });

    it('should contain atleast one item folder', function () {
        cy.get('#tree-manage_items').find('ul').children().should('not.to.have.length', 0);
        cy.location('pathname').should('eq', indexUrl);
    });

    it('should have these menus on click', function () {
        cy.get('ul.ltr li').find('a').contains('Item').click();
        cy.location('pathname').should('eq', indexUrl);
    });

    it('should contain tree actions', function () {
        cy.get('ul.tree-action-bar')
            .children()
            .not('.hidden')
            .should($lis => {
                expect($lis.eq(0), 'New class action').to.contain('New class');
                expect($lis.eq(1), 'Delete action').to.contain('Delete');
                expect($lis.eq(2), 'Import action').to.contain('Import');
                expect($lis.eq(3), 'Export action').to.contain('Export');
                expect($lis.eq(4), 'Move action').to.contain('Move To');
                expect($lis.eq(5), 'New item action').to.contain('New item');
            });
        cy.location('pathname').should('eq', indexUrl);
    });

    it('should contain horizontal action bar sub menu', function () {
        cy.get('ul.horizontal-action-bar')
            .children()
            .not('.hidden')
            .should($lis => {
                expect($lis.eq(0), 'first sub menu').to.contain('Properties');
                expect($lis.eq(1), 'second sub menu').to.contain('Manage Schema');
            });
        cy.location('pathname').should('eq', indexUrl);
    });

    it('should contain a form with text box value item', function () {
        cy.get('form#form_1').find('input[type=text]').should('have.attr', 'value', 'Item');
        cy.get('form#form_1').contains('Save');
        cy.get('.form-submitter').click();
        cy.location('pathname').should('eq', indexUrl);
    });
});
