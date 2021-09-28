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

require('cypress-downloadfile/lib/downloadFileCommand');
import '@oat-sa/e2e-runner/support/fileupload';
import '@oat-sa/e2e-runner/support/auth';
import '@oat-sa/e2e-runner/support/lti.js';
import './resourceTree';
import './userManagement';
import './manageSchema';
import './resourceProperties';
import urls from '../utils/urls';

/**
 * Sends login request using the format of the TAO login form
 * @param {String} username
 * @param {String} password
 */
Cypress.Commands.add('loginAsUser', (username, password) => {
    cy.login({ url: urls.login, username, password });
});

/**
 * Sends login request with the credentials defined via cypress variables 'adminUser' and 'adminPass'
 */
Cypress.Commands.add('loginAsAdmin', () => {
    const username = Cypress.env('adminUser');
    const password = Cypress.env('adminPass');

    cy.loginAsUser(username, password);
});

/**
 * Logs out using the UI
 */
Cypress.Commands.add('logoutAttempt', () => {
    cy.get('#logout').click();
});

/**
 * Creates a UI login attempt with provided data
 * @param {String} username
 * @param {String} password
 */
Cypress.Commands.add('loginAttempt', (username, password) => {
    cy.intercept('POST', '**/login*').as('login');
    cy.get('#login', { timeout: 10000 }).type(username);
    cy.get('#password').type(password);
    cy.get('#connect').click();
    cy.wait('@login');
});

/**
 * Recursively gets an element, returning only after it's determined to be attached to the DOM for good
 * @param {String} selector - css selector of the element
 * @param {Object} opts
 * @param {String} opts.retries
 * @param {String} opts.delay
 * @return {JQuery}
 */
Cypress.Commands.add('getSettled', (selector, opts = {}) => {
    const retries = opts.retries || 3;
    const delay = opts.delay || 500;

    const isAttached = (resolve, count = 0) => {
        const el = Cypress.$(selector);

        // is element attached to the DOM?
        count = Cypress.dom.isAttached(el) ? count + 1 : 0;

        // hit our base case, return the element
        if (count >= retries) {
            return resolve(el);
        }

        // retry after a bit of a delay
        setTimeout(() => isAttached(resolve, count), delay);
    };

    // wrap, so we can chain cypress commands off the result
    return cy.wrap(null).then(() => {
        return new Cypress.Promise((resolve) => {
            return isAttached(resolve, 0);
        }).then((el) => {
            return cy.wrap(el);
        });
    });
});

/**
 * Tries to find an element, returning only after it's present on the page or after timeout
 * @param {String} selector - css selector of the element
 * @param {Object} opts
 * @param {String} opts.timeout
 * @param {String} opts.delay
 * @return {Boolean}
 */
Cypress.Commands.add('isElementPresent', (selector, opts = {}) => {
    const timeout = opts.timeout || 5000;
    const delay = opts.delay || 500;

    const isPresent = (resolve) => {
        const $el = Cypress.$(selector);
        if ($el.length) {
            resolve(true);
        } else {
            setTimeout(() => isPresent(resolve), delay);
        }
    };

    return new Cypress.Promise(resolve => {
        setTimeout(
            () => resolve(false),
            timeout
        );
        return isPresent(resolve);
    });
});

/**
 * Preserve session cookies to stay logged in to TAO during tests
 */
Cypress.Cookies.defaults({
    preserve: (cookie) => {
        return cookie.name.startsWith('tao_');
    }
});
