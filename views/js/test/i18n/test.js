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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'i18n'
], function(i18n) {
    'use strict';

    QUnit.module('i18n');


    QUnit.test('module', 4, function(assert) {
        var fake = 'my-dummy-text';
        assert.equal(typeof i18n, 'function', "The i18n module exposes a function");
        assert.equal(typeof i18n(fake), 'string', "The i18n function produces a string");
        assert.equal(i18n(fake), i18n(fake), "The i18n function always returns the same value for a particular context.");
        assert.equal(i18n(fake), fake, "The i18n function always returns the provided key when the translation is unknown.");
    });
});
