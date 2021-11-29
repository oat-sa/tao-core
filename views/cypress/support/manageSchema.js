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

const propertiesWithListValues = [
   'list',
   'multiplenodetree',
   'longlist',
   'multilist',
   'multisearchlist',
   'singlesearchlist'
];

/**
 * Adds new property to class (list with single selection of boolean values)
 * @param {Object} options - Configuration object containing all target variables
 * @param {String} options.className
 * @param {String} options.manageSchemaSelector - css selector for the edit class button
 * @param {String} options.classOptions - css selector for the class options form
 * @param {String} options.propertyName
 * @param {String} options.propertyAlias
 * @param {String} options.propertyEditSelector - css selector for the property edition form
 * @param {String} options.editUrl - url for the editing class POST request
 */
 Cypress.Commands.add('addPropertyToClass', (options) => {
   options.propertyType = options.propertyType || 'list';
   options.propertyListValue = options.propertyListValue || 'Boolean';

    cy.log('COMMAND: addPropertyToClass', options.propertyName);

    cy.getSettled(`li [title ="${options.className}"]`).last().click();
    cy.getSettled(options.manageSchemaSelector).click();
    cy.getSettled(options.classOptions).find('a[class="btn-info property-adder small"]').click();

    cy.getSettled('span[class="icon-edit"]').last().click();
    cy.get(options.propertyEditSelector).find('input[data-testid="Label"]').clear().type(options.propertyName);

    if (options.propertyAlias) {
      cy.get(options.propertyEditSelector).find('input[data-testid="Alias"]').clear().type(options.propertyAlias);
    }

    cy.get(options.propertyEditSelector).find('select[class="property-type property"]').select(options.propertyType);

    if (propertiesWithListValues.includes(options.propertyType)) {
      cy.get(options.propertyEditSelector).find('select[class="property-listvalues property"]').select(options.propertyListValue);
    }

    cy.intercept('POST', `**/${options.editUrl}`).as('editClass');
    cy.get('button[type="submit"]').click();
    cy.wait('@editClass');
});

/**
 * Validates a property in a class
 * @param {Object} options - Configuration object containing all target variables
 * @param {String} options.className - name of the class
 * @param {String} options.classOptions - css selector for the class options form
 * @param {Object} property - property to validate
 * @param {String} property.name - name of the property
 * @param {String} property.type - type of the property
 * @param {String} property.listValue - list value of the property
 */

Cypress.Commands.add('validateClassProperty', (options, property) => {
   cy.log('COMMAND: validateClassProperty', property.name);

   cy.getSettled(options.classOptions)
   .contains('.property-heading-label', property.name)
   .siblings('.property-heading-toolbar')
   .contains(options.className)
   .within(() => {
      cy.get('.icon-edit').click();
   });
   cy.getSettled('.property-edit-container-open [data-testid="Label"]').should('have.value', property.name);
   cy.getSettled('.property-edit-container-open [data-testid="Type"]').should('have.value', property.type);

   if (property.listValue) {
      cy.getSettled('.property-edit-container-open [data-testid="List values"]').should('have.value', property.listValue);
   }
   cy.getSettled('.property-edit-container-open .icon-edit').click();
})