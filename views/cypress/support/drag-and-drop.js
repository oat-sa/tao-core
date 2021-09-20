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
 * Drag one element and drop it onto another, with mouse
 * Implementation is specific to 'jquery ui drag drop' library.
 * @param {String} dragSelector -  element to drag selector
 * @param {String} dropSelector  - element to drop to selector
 */
Cypress.Commands.add('dragAndDrop', (dragSelector, dropSelector) => {
    cy.get(dragSelector)
        .should('exist')
        .get(dropSelector)
        .should('exist');

    function getElementCenterCoords($el) {
        const rect = $el[0].getBoundingClientRect();
        const x = Math.round(rect.left + rect.width / 2);
        const y = Math.round(rect.top + rect.height / 2);
        return { x, y };
    }
    cy.get(dragSelector).then($draggable => {
        // Pick up this
        cy.get(dropSelector).then($droppable => {
            const { x: startX, y: startY } = getElementCenterCoords($draggable);
            const { x, y } = getElementCenterCoords($droppable);
            cy.get('#qti-block-element-placeholder').should('not.exist');

            cy.wrap($draggable)
                .trigger('mouseover', { force: true })
                .trigger('mousedown', {
                    which: 1,
                    pageX: startX,
                    pageX: startY
                })
                .trigger('mousemove', {
                    which: 1,
                    pageX: x,
                    pageY: y,
                    force: true
                });

            cy.wrap($droppable)
                .trigger('mouseover', { force: true })
                .trigger('mousemove', {
                    which: 1,
                    pageX: x,
                    pageY: y,
                    force: true
                });

            cy.wrap($draggable).trigger('mouseup', { force: true });
            cy.document().trigger('mouseup', { force: true });
        }); // Drop over this
    });
});
