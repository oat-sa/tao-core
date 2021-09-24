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

import '@oat-sa/e2e-runner/support/fileupload';

/**
 * Upload file to input
 * @param {String} importSelector -  selector for input type="file"
 * @param {String} importFilePath  - parh to file
 */
Cypress.Commands.add('fileUpload', (importSelector, importFilePath) => {
    cy.log('COMMAND: fileUpload', importSelector, importFilePath);

    cy.readFile(importFilePath, 'binary').then(fileContent => {
        cy.get(importSelector).attachFile({
            fileContent,
            filePath: importFilePath,
            encoding: 'binary',
            lastModified: new Date().getTime()
        });
    });
});
