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
 * Run the setup of the platform
 * @param {String} treeRenderUrl - url for resource tree data GET request
 * @param {String} editClassLabelUrl - url for resource edit class data POST request
 * @param {String} urlsItems - url to visit related to the part of TAO we want to move
 * @param {String} rootSelector - root selector of the part of TAO we are
 */
Cypress.Commands.add('setup', (
    treeRenderUrl,
    editClassLabelUrl,
    urlVisit,
    rootSelector
) => {
    cy.log('COMMAND: setup')
        .loginAsAdmin()
        .intercept('GET', `**/${ treeRenderUrl }/getOntologyData**`).as('treeRender')
        .intercept('POST', `**/${ editClassLabelUrl }`).as('editClassLabel')
        .visit(urlVisit)
        .wait('@treeRender')
        .getSettled(`${ rootSelector } a`)
        .first()
        .click()
        .wait('@editClassLabel');
});

/**
 * Run the setup in page files of the platform
 * @param {String} urlVisit - url to visit related to the part of TAO we want to move
 */
Cypress.Commands.add('setupPage', (
    urlVisit
) => {
    cy.log('COMMAND: setupPage')
        .loginAsAdmin()
        .intercept('POST', '**/edit*').as('edit')
        .visit(urlVisit)
        .wait('@edit');
});

/**
 * Creates new resource subclass
 * @param {String} formSelector - css selector for the class edition form
 * @param {String} treeRenderUrl - url for resource tree data GET request
 * @param {String} addSubClassUrl - url for adding subclass POST request
 */
Cypress.Commands.add('addClass', (
    formSelector,
    treeRenderUrl,
    addSubClassUrl
) => {
    cy.log('COMMAND: addClass')
        .intercept('GET', `**/${treeRenderUrl}/getOntologyData**`).as('treeRender')
        .intercept('POST', `**/${addSubClassUrl}`).as('addSubClass')
        .get('[data-context=resource][data-action=subClass]')
        .click()
        .wait('@addSubClass')
        .wait('@treeRender')
        .wait('@editClassLabel')
        .get(formSelector).should('exist');
});

/**
 * Creates new resource class in the tree root
 * @param {String} formSelector - css selector for the class edition form
 * @param {String} rootSelector - css selector for the tree root element
 * @param {String} name - new class name
 * @param {String} editClassLabelUrl - url for editing subclass POST request
 * @param {String} treeRenderUrl - url for the resource tree data GET request
 * @param {String} addSubClassUrl - url for the adding subclass POST request
 */
Cypress.Commands.add('addClassToRoot', (
    rootSelector,
    formSelector,
    name,
    editClassLabelUrl,
    treeRenderUrl,
    addSubClassUrl
) => {
    cy.log('COMMAND: addClassToRoot', name)
        .getSettled(`${rootSelector} a:nth(0)`)
        .click()
        .intercept('POST', `**/${editClassLabelUrl}`).as('editClassLabel')
        .addClass(formSelector, treeRenderUrl, addSubClassUrl)
        .renameSelectedClass(formSelector, name);
});

/**
 * Moves class to another place
 * @param {String} moveSelector - css selector for the move button
 * @param {String} moveConfirmSelector - css selector for the element to confirm action
 * @param {String} name - name of the class which will be moved
 * @param {String} nameWhereMove - name of the class to move to
 * @param {String} restResourceGetAll - url for the rest resource GET request
 */
Cypress.Commands.add('moveClass', (
    moveSelector,
    moveConfirmSelector,
    name,
    nameWhereMove,
    restResourceGetAll
) => {
    cy.log('COMMAND: moveClass', name)
        .getSettled(`li[title="${name}"] a:nth(0)`)
        .click()
        .wait('@editClassLabel')
        .intercept('GET', `**/${restResourceGetAll}**`).as('classToMove')
        .get('#feedback-2, #feedback-1').should('not.exist')
        .getSettled(moveSelector)
        .click()
        .wait('@classToMove')
        .getSettled(`.destination-selector a[title="${nameWhereMove}"]`)
        .click()
        .get('.actions button')
        .click()
        .get(moveConfirmSelector)
        .click()
        .get(`li[title="${name}"] a`).should('not.exist');
});

/**
 * Moves class to the tree root
 * @param {String} rootSelector - css selector for the tree root element
 * @param {String} moveSelector - css selector for the move button
 * @param {String} moveConfirmSelector - css selector for the element to confirm action
 * @param {String} name - name of the class which will be moved
 * @param {String} nameWhereMove - name of the class to move to
 * @param {String} restResourceGetAll - url for the rest resource GET request
 */
Cypress.Commands.add('moveClassFromRoot', (
    rootSelector,
    moveSelector,
    moveConfirmSelector,
    name,
    nameWhereMove,
    restResourceGetAll
) => {
    cy.log('COMMAND: moveClassFromRoot', name)
        .get('#feedback-1, #feedback-2').should('not.exist')
        .getSettled(`${rootSelector} a:nth(0)`)
        .click()
        .get(`${rootSelector} li[title="${name}"] a`)
        .moveClass(moveSelector, moveConfirmSelector, name, nameWhereMove, restResourceGetAll)
});

/**
 * Deletes class
 * @param {String} rootSelector - css selector for the tree root element
 * @param {String} formSelector - css selector for the class edition form
 * @param {String} deleteSelector - css selector for the delete button
 * @param {String} confirmSelector - css selector for the element to confirm action
 * @param {String} deleteClassUrl - url for the deleting class POST request
 * @param {String} name - name of the class which will be deleted
 * @param {Boolean} isConfirmCheckbox = false - if true also checks confirmation checkbox
 */
Cypress.Commands.add('deleteClass', (
    rootSelector,
    formSelector,
    deleteSelector,
    confirmSelector,
    deleteClassUrl,
    name,
    isConfirmCheckbox = false
) => {
    cy.log('COMMAND: deleteClass', name)
        .getSettled(`${rootSelector} a`)
        .contains('a', name).click()
        .get(formSelector)
        .should('exist')
    cy.get(deleteSelector).click();

    if (isConfirmCheckbox) {
        cy.get('.modal-body label[for=confirm]')
            .click();
    }

    cy.intercept('POST', `**/${deleteClassUrl}`).as('deleteClass')
    cy.get(confirmSelector)
        .click();
    cy.wait('@deleteClass');
});

/**
 * Deletes class from the tree root
 * @param {String} rootSelector - css selector for the tree root element
 * @param {String} formSelector - css selector for the class edition form
 * @param {String} deleteSelector - css selector for the delete button
 * @param {String} confirmSelector - css selector for the element to confirm action
 * @param {String} deleteClassUrl - url for the deleting class POST request
 * @param {String} name - name of the class which will be deleted
 * @param {Boolean} isConfirmCheckbox = false - if true also checks confirmation checkbox
 */
Cypress.Commands.add('deleteClassFromRoot', (
    rootSelector,
    formSelector,
    deleteSelector,
    confirmSelector,
    name,
    deleteClassUrl,
    isConfirmCheckbox
) => {

    cy.log('COMMAND: deleteClassFromRoot', name)
        .getSettled(`${rootSelector} a:nth(0)`)
        .click()
        .get(`li[title="${name}"] a`)
        .deleteClass(rootSelector, formSelector, deleteSelector, confirmSelector, deleteClassUrl, name, isConfirmCheckbox)
});

/**
 * Creates new resource node
 * @param {String} formSelector - css selector for the class edition form
 * @param {String} addSelector - css selector for the adding class button
 */
Cypress.Commands.add('addNode', (formSelector, addSelector) => {
    cy.log('COMMAND: addNode');
    cy.intercept('GET', `**/getOntologyData**`).as('treeRender');
    cy.getSettled(addSelector).click();
    cy.get(formSelector).should('exist');
    cy.wait('@treeRender');
});

/**
 * Selects resource node with the given name (opens subtree associated with this node)
 * @param {String} rootSelector - css selector for the tree root element
 * @param {String} formSelector - css selector for the class edition form
 * @param {String} name - name of the node which will be selected
 */
Cypress.Commands.add('selectNode', (rootSelector, formSelector, name) => {
    cy.log('COMMAND: selectNode', name);
    cy.getSettled(`${rootSelector} a:nth(0)`).click();
    cy.contains('a', name).click();
    cy.get(formSelector).should('exist');
});

/**
 * Deletes resource node with the given name
 * @param {String} rootSelector - css selector for the tree root element
 * @param {String} deleteSelector - css selector for the delete button
 * @param {String} editUrl - url for the editing node POST request
 * @param {String} name - name of the node which will be deleted
 */
Cypress.Commands.add('deleteNode', (
    rootSelector,
    deleteSelector,
    editUrl,
    name,
) => {
    cy.log('COMMAND: deleteNode', name)
        .intercept('POST', `**/${editUrl}`).as('editUrl')
        .getSettled(`${rootSelector} a`)
        .contains('a', name).click()
        .wait('@editUrl')
        .getSettled(deleteSelector).click()
        .getSettled('[data-control="ok"]').click()
        .getSettled(`${rootSelector} a`)
        .contains('a', name).should('not.exist');
});

/**
 * Imports resource in class (class should already be selected before running this command)
 * @param {String} importSelector - css selector for the import button
 * @param {String} importFilePath - path to the file to import
 * @param {String} importUrl - url for the resource import POST request
 * @param {String} className
 */
Cypress.Commands.add('importToSelectedClass', (
    importSelector,
    importFilePath,
    importUrl,
    className) => {

    cy.log('COMMAND: import', importUrl);
    cy.get(importSelector).click();

    cy.readFile(importFilePath, 'binary')
        .then(fileContent => {
            cy.get('input[type="file"][name="content"]')
                .attachFile({
                        fileContent,
                        filePath: importFilePath,
                        encoding: 'binary',
                        lastModified: new Date().getTime()
                    }
                );

            cy.get('.progressbar.success').should('exist');

            cy.intercept('POST', `**/${importUrl}**`).as('import').get('.form-toolbar button')
                .click()
                .wait('@import')

            return cy.isElementPresent('.task-report-container')
                .then(isTaskStatus => {
                    if (isTaskStatus) {
                        cy.get('.feedback-success.hierarchical').should('exist');
                    } else {
                        // task was moved to the task queue (background)
                        cy.get('.badge-component').click();
                        cy.get('.task-element.completed').first().contains(className);
                        // close the task manager
                        cy.get('.badge-component').click();
                    }
                })
        });
});

/**
 * Exports resource in class (class should already be selected before running this command)
 * @param {String} exportSelector - css selector for the export button
 * @param {String} exportUrl - url for the resource export POST request
 * @param {String} className
 */
Cypress.Commands.add('exportFromSelectedClass', (
    exportSelector,
    exportUrl,
    className) => {

    cy.log('COMMAND: export', exportUrl);

    cy.get(exportSelector).click();
    cy.get('#exportChooser .form-toolbar button').click();

    cy.task('getDownloads').then(
        files => {
            expect(files.length).to.equal(1);

            cy.task('readDownload', files[0]).then(fileContent => {
                expect(files[0]).to.contain(className.replaceAll(' ', '_').toLowerCase());

                cy.wrap(fileContent.length).should('be.gt', 0);

                // remove file as cypress doesn't remove downloads in the open mode
                cy.task('removeDownload', files[0]);
            });
        }
    );
});
