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
 * Command for programatically typing in a CKEditor
 * @param {propertyName} string - The name of the property to type in
 * @param {content} string - The content to type
 */
Cypress.Commands.add("typeInCKEditor", (propertyName, content) => {
    cy.window()
    .then(win => {
        const propertyUri = Cypress.$('[data-testid="' + propertyName + '"]').attr('id');
        win.CKEDITOR.instances[propertyUri].setData(content);
    });
});
