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

describe('Results', () => {
    const indexUrl = '/tao/Main/index';

    before(() => {
        cy.loginAsAdmin();
    });

    beforeEach(() => {
        Cypress.Cookies.preserveOnce('tao_community');
    });

    it('should forward to Results on click', function () {
        cy.visit(indexUrl);
        cy.get('ul.main-menu').children().contains('Results').click();
        cy.location('pathname').should('eq', indexUrl);
    });

    it('should contain Result menu active and contain text Results', function () {
        cy.get('ul.main-menu').children('.active').should('include.text', 'Results');
        cy.location('pathname').should('eq', indexUrl);
    });

    it('should contain search with placeholder value search Result', function () {
        cy.get('.horizontal-action-bar')
            .children('.search-area')
            .find('input')
            .should('have.attr', 'placeholder', 'Search Result');
        cy.location('pathname').should('eq', indexUrl);
    });

    it('should contain atleast one Result folder', function () {
        cy.get('#tree-manage_results').find('ul').children().should('not.to.have.length', 0);
        cy.location('pathname').should('eq', indexUrl);
        cy.wait(3000);
    });

    it('should have these menus on click', function () {
        const folder = cy.get('ul.ltr li', { timeout: 10000 }).find('a').contains('Assembled Delivery ').click();
        cy.location('pathname').should('eq', indexUrl);
    });

    it('should contain tree actions', function () {
        cy.get('ul.tree-action-bar')
            .children()
            .not('.hidden')
            .should($lis => {
                expect($lis.eq(0), 'Export ').to.contain('Export CSV');
            });
        cy.location('pathname').should('eq', indexUrl);
    });

    it('should contain horizontal action bar sub menu', function () {
        cy.get('ul.horizontal-action-bar')
            .children()
            .not('.hidden')
            .should($lis => {
                expect($lis.eq(0), 'first sub menu').to.contain('Results');
            });
        cy.location('pathname').should('eq', indexUrl);
    });
});
