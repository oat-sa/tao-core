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

describe('Main Menu', () => {
    const indexUrl = '/tao/Main/index';

    beforeEach(() => {
        cy.loginAsAdmin();
    });

    it('should contain atleast 5 menu elements', function () {
        cy.visit(indexUrl);
        cy.get('ul.main-menu').find('li').its('length').should('be.gte', 5);
        cy.location('pathname').should('eq', indexUrl);
    });

    it('should contain these menu items', function () {
        cy.get('ul.main-menu')
            .children()
            .should($lis => {
                expect($lis.eq(0), 'first menu').to.contain('Items');
                expect($lis.eq(1), 'second menu').to.contain('Tests');
                expect($lis.eq(2), 'third menu').to.contain('Test-takers');
                expect($lis.eq(3), 'fourth menu').to.contain('Groups');
                expect($lis.eq(4), 'fifth menu').to.contain('Deliveries');
                expect($lis.eq(5), 'sixth menu').to.contain('Results');
            });

        cy.location('pathname').should('eq', indexUrl);
    });

    it('should contain atleast one active item', function () {
        cy.get('ul.main-menu').children().should('have.class', 'active');
        cy.location('pathname').should('eq', indexUrl);
    });
});
