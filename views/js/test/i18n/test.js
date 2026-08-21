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
define(['i18n'], function(i18n) {
    'use strict';

    QUnit.module('i18n');

    QUnit.test('module', function(assert) {
        var fake = 'my-dummy-text';
        assert.equal(typeof i18n, 'function', 'The i18n module exposes a function');
        assert.equal(typeof i18n.p, 'function', 'The i18n module exposes a plural translation function');
        assert.equal(typeof i18n(fake), 'string', 'The i18n function produces a string');
        assert.equal(i18n(fake), i18n(fake), 'The i18n function always returns the same value for a particular context.');
        assert.equal(i18n(fake), fake, 'The i18n function always returns the provided key when the translation is unknown.');
    });

    var i18nApi = [
        {expected: 'translation mock 1', title: 'mock-1', params: []},
        {expected: 'translation mock 2', title: 'mock-2', params: []},
        {expected: 'parameterized text translation TAO', title: 'params text %s', params: ['TAO']},
        {expected: 'parameterized number translation 10', title: 'params number %d', params: [10]},
        {expected: 'parameterized json translation {id:1}', title: 'params json %j', params: [{id: 1}]}
    ];

    QUnit
        .cases.init(i18nApi)
        .test('i18n translation ', function(data, assert) {
            var params = [data.title].concat(data.params);
            assert.equal(i18n.apply(i18n, params), data.expected, 'The i18n translation of "' + data.title + '" must provide the text "' + data.expected + '"');
        });

    QUnit.test('i18n plural translation', function(assert) {
        assert.equal(i18n.p('%d day', '%d days', 1), '1 den', 'Singular form should use plural index 0');
        assert.equal(i18n.p('%d day', '%d days', 3), '3 dni', 'Few form should use plural index 1');
        assert.equal(i18n.p('%d day', '%d days', 5), '5 dni', 'Many form should use the locale plural index');
        assert.equal(i18n.p('%d day', '%d days', 21, 21), '21 dni', 'Plural translations still accept explicit format arguments');
    });
});
