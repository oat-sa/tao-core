/**
 * Get popup instance, find a button with property of dataControl attribute and click on it
 * @param {String('ok'|'cancel'|'close')} dataControl - button to click, see data-control attributes in modal dialog
 */
const interactWithModal = (dataControl) => {
    cy.getSettled('[data-control="navigable-modal-body"]')
        .find(`button[data-control="${dataControl}"]`)
        .should('be.visible')
        .click();
}

/**
 * Close modal window with confirm
 */
Cypress.Commands.add('modalConfirm', () => {
    cy.log('COMMAND: modalConfirm');
    interactWithModal('ok');
});
