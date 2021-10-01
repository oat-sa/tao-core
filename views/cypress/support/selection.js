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
 * Select text within text element
 * @param {String} selector for text element
 */
Cypress.Commands.add('selectTextWithin', (selector) => {
    cy.document().then(doc => {
        cy.window().then(win => {
            cy.get(selector).then(textElement => {
                if (win.getSelection) {
                    const selection = win.getSelection();
                    const range = doc.createRange();
                    range.selectNodeContents(textElement.get(0));
                    selection.removeAllRanges();
                    selection.addRange(range);
                } else {
                    throw new Error("Can't select text.");
                }
            });
        });
    });
});
