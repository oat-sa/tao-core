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

import '@oat-sa/e2e-runner/support/auth';
import './resourceTree'

Cypress.Commands.add('setupServer', () => {
    cy.server({
        preserve: (xhr) => {
            // this function receives the xhr object in question and
            // will whitelist if it's a GET that appears to be a static resource

            // TAO custom logic for whitelisting: add .tpl and .rdf files to default 'mute' list
            return xhr.method === 'GET' && /\.(jsx?|html|css|tpl|rdf)(\?.*)?$/.test(xhr.url);
        }
    });

    Cypress.Cookies.defaults({
        preserve: (cookie) => {
            // if the function returns truthy
            // then the cookie will not be cleared
            // before each test runs

            // Basically we want to stay logged in to TAO while we run our tests
            // Unfortunately the session cookie name is dynamic
            return cookie.name.startsWith('tao_');
        }
    });
});

Cypress.Commands.add('loginAsAdmin', () => {
    cy.fixture('urls').as('urls').then(urls => {
            const username = Cypress.env('adminUser');
            const password = Cypress.env('adminPass');

            cy.login({url: urls.login, username, password});
        });
    }
);
