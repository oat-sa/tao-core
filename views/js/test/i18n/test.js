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
define(['i18n', 'i18ntr/messages'], function(i18n, i18nTr) {
    'use strict';

    const i18nApi = [
        {expected: 'translation mock 1', title: 'mock-1', params: []},
        {expected: 'translation mock 2', title: 'mock-2', params: []},
        {expected: 'parameterized text translation TAO', title: 'params text %s', params: ['TAO']},
        {expected: 'parameterized number translation 10', title: 'params number %d', params: [10]},
        {expected: 'parameterized json translation {id:1}', title: 'params json %j', params: [{id: 1}]}
    ];

    QUnit.module('i18n');

    QUnit.test('module', function(assert) {
        const fake = 'my-dummy-text';
        assert.equal(typeof i18n, 'function', 'The i18n module exposes a function');
        assert.equal(typeof i18n.p, 'function', 'The i18n module exposes a plural translation function');
        assert.equal(typeof i18n(fake), 'string', 'The i18n function produces a string');
        assert.equal(i18n(fake), i18n(fake), 'The i18n function always returns the same value for a particular context.');
        assert.equal(i18n(fake), fake, 'The i18n function always returns the provided key when the translation is unknown.');
    });

    QUnit
        .cases.init(i18nApi)
        .test('i18n translation ', function(data, assert) {
            const params = [data.title].concat(data.params);
            assert.equal(i18n.apply(i18n, params), data.expected, `The i18n translation of "${data.title}" must provide the text "${data.expected}"`);
        });

    QUnit.test('i18n plural translation', function(assert) {
        assert.equal(i18n.p('%d day', '%d days', 1), '1 singular-day', 'Singular form should use plural index 0');
        assert.equal(i18n.p('%d day', '%d days', 2), '2 few-days', 'Few count should use plural index 1');
        assert.notEqual(i18n.p('%d day', '%d days', 2), '2 many-days', 'Few count must not resolve to the many plural index');
        assert.equal(i18n.p('%d day', '%d days', 4), '4 many-days', 'Many count should preserve sparse plural index 3');
        assert.equal(i18n.p('%d day', '%d days', 21, 21), '21 many-days', 'Explicit format arguments should preserve the many plural index');
        assert.notEqual(i18n.p('%d day', '%d days', 4), '4 few-days', 'Many count must not resolve to the few plural index');
        assert.equal(i18n.p('%d day', '%d days', 3), '3 singular-day', 'Unavailable sparse plural indexes should fall back to index 0');
        assert.notEqual(i18n.p('%d day', '%d days', 3), '3 many-days', 'Unavailable plural index 2 must not be compressed to index 3');
        assert.equal(i18n.p('mock-1', 'mock-1 plural', 2), 'mock-1 plural', 'Plain string translations should use the provided plural fallback');
        assert.equal(i18n.p('missing plural key', 'missing plural fallback', 2), 'missing plural fallback', 'Missing translations should use the provided plural fallback');
        assert.equal(i18n.p('%d ruby day', '%d ruby days', 1), '<ruby>1</ruby> den', 'Plural translations should convert ruby placeholders after formatting');
    });

    QUnit.test('i18n plural object maps treat empty indexed values as untranslated', function(assert) {
        const pluralEntry = i18nTr.translations['%d empty plural day'];

        i18nTr.translations['%d empty plural day'] = {
            _plural: '%d empty plural days',
            _translations: {
                0: '%d singular-fallback',
                1: '',
                3: '%d many-translation'
            }
        };

        assert.equal(
            i18n.p('%d empty plural day', '%d empty plural days', 2),
            '2 singular-fallback',
            'Owned empty plural index should fall back to owned index 0'
        );
        assert.equal(
            i18n.p('%d empty plural day', '%d empty plural days', 4),
            '4 many-translation',
            'Non-empty owned plural indexes should still be preserved'
        );
        assert.equal(
            i18n.p('%d empty plural day', '%d empty plural days', 3),
            '3 singular-fallback',
            'Missing sparse plural indexes should still fall back to owned index 0'
        );

        i18nTr.translations['%d empty plural day'] = pluralEntry;
    });
});
