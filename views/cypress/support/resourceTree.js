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

const labelSelector = '[data-testid=Label]';

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
    restResourceGetAll,
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
    cy.wait('@deleteClass')
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

    cy.getSettled(addSelector).click();
    cy.get(formSelector).should('exist');
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
 * Renames class to the given name (class should already be selected before running this command)
 * @param {String} formSelector - css selector for the class edition form
 * @param {String} newName
 */
Cypress.Commands.add('renameSelectedClass', (formSelector, newName) => {
    cy.log('COMMAND: renameSelectedClass', newName)
        .getSettled(`${formSelector} ${labelSelector}`)
        .clear()
        .type(newName)
        .click()
        .getSettled('button[id="Save"]')
        .click()
        .wait('@editClassLabel')
        .get('#feedback-1, #feedback-2').should('not.exist')
        .get(formSelector).should('exist')
        .get(`${formSelector} ${labelSelector}`).should('have.value', newName)
        .wait('@treeRender');
});

/**
 * Renames node to the given name (node should already be selected before running this command)
 * @param {String} formSelector - css selector for the class edition form
 * @param {String} editUrl - url for the editing node POST request
 * @param {String} newName
 */
Cypress.Commands.add('renameSelectedNode', (formSelector, editUrl, newName) => {
    cy.log('COMMAND: renameSelectedNode', newName)
        .intercept('POST', `**${editUrl}`).as('edit')
        .getSettled(`${formSelector} ${labelSelector}`)
        .clear()
        .type(newName)
        .getSettled('button[id="Save"]')
        .click()
        .wait('@edit')
        .get('#feedback-1, #feedback-2').should('not.exist')
        .get(formSelector).should('exist')
        .get(`${formSelector} ${labelSelector}`).should('have.value', newName)
});

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

/**
 * Assigns value to the class property (works for the list with single selection of boolean values)
 * @param {String} nodeName
 * @param {String} nodePropertiesForm - css selector for the node properties edition form
 * @param {String} selectOption - css selector for the option to select
 * @param {String} treeRenderUrl - url for resource tree data GET request
 */
Cypress.Commands.add('assignValueToProperty', (
    nodeName,
    nodePropertiesForm,
    selectOption,
    treeRenderUrl) => {

    cy.log('COMMAND: assignValueToProperty', nodeName, nodePropertiesForm);
    cy.getSettled(`li [title ="${nodeName}"] a`).last().click();
    cy.getSettled(nodePropertiesForm).find(selectOption).check();
    cy.intercept('GET', `**/${treeRenderUrl}/getOntologyData**`).as('treeRender')
    cy.getSettled('button[type="submit"]').click();
    cy.wait('@treeRender');
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
