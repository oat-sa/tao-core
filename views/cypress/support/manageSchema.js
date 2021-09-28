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

/**
 * Adds new property to class (list with single selection of boolean values)
 * @param {String} className
 * @param {String} editClass - css selector for the edit class button
 * @param {String} classOptions - css selector for the class options form
 * @param {String} newPropertyName
 * @param {String} propertyEdit - css selector for the property edition form
 * @param {String} editClassUrl - url for the editing class POST request
 */
 Cypress.Commands.add('addPropertyToClass', (
    className,
    editClass,
    classOptions,
    newPropertyName,
    propertyEdit,
    editClassUrl) => {

    cy.log('COMMAND: addPropertyToClass', newPropertyName);

    cy.getSettled(`li [title ="${className}"]`).last().click();
    cy.getSettled(editClass).click();
    cy.getSettled(classOptions).find('a[class="btn-info property-adder small"]').click();

    cy.getSettled('span[class="icon-edit"]').last().click();
    cy.get(propertyEdit).find('input').first().clear('input').type(newPropertyName);
    cy.get(propertyEdit).find('select[class="property-type property"]').select('list');
    cy.get(propertyEdit).find('select[class="property-listvalues property"]').select('Boolean');
    cy.intercept('POST', `**/${editClassUrl}`).as('editClass');
    cy.get('button[type="submit"]').click();
    cy.wait('@editClass');
});
