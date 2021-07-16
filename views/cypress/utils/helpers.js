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
 * Copyright (c) 2021 Open Assessment Technologies SA ;
 */

/**
 * Checks a HTTP(S) url
 * @type {RegExp}
 */
const fullUrlRe = /^https?:\/\/\w+/;

/**
 * Makes sure an url is complete.
 * Adds the baseUrl if no root has been set.
 * @param {String} url - The URL to complete if needed
 * @param {String} trailing - An optional trailing substring to add
 * @returns {String}
 */
export function getFullUrl(url, trailing = '') {
    if (!fullUrlRe.test(url)) {
        url = `${new URL(url, Cypress.config().baseUrl)}`;
    }
    if (trailing && !url.endsWith(trailing)) {
        url = `${url}${trailing}`;
    }
    return url;
}
