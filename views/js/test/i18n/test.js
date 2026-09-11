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
    'i18n',
    'i18n/plural',
    'i18n/ruby',
    'json!i18ntr/messages.json'
], function(i18n, pluralHelper, rubyHelper, i18nTr) {
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
        assert.equal(typeof i18n.p, 'function', 'The i18n module exposes a plural helper');
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
        assert.equal(i18n.p('mock-1', 'mock-1 plural', 2), 'mock-1 plural', 'Plain string translations should fall back to the provided plural source');
        assert.equal(i18n.p('%d ruby day', '%d ruby days', 1), '<ruby>1</ruby> den', 'Plural translations should convert ruby placeholders after formatting');
    });

    QUnit.test('i18n preserves regular formatting for non-plural 3-argument calls', function(assert) {
        assert.equal(
            i18n('Value %s %d', 'TAO', 3),
            'Value TAO 3',
            'Three-argument formatting calls should not be reinterpreted as plural when the source is not pluralized'
        );
    });

    QUnit.test('plural helper parses pluralForms independently from i18n module wiring', function(assert) {
        const pluralRule = pluralHelper.getPluralRule('nplurals=4; plural=(n==1) ? 0 : (n>=2 && n<=4) ? 1 : 3;');

        assert.equal(pluralRule(1), 0, 'Singular counts should resolve to index 0');
        assert.equal(pluralRule(2), 1, 'Few counts should resolve to index 1');
        assert.equal(pluralRule(7), 3, 'Higher counts should resolve to the configured terminal plural index');
    });

    QUnit.test('plural helper falls back for oversized or implausible plural headers', function(assert) {
        const tooManyPluralsRule = pluralHelper.getPluralRule('nplurals=99; plural=n != 1;');
        const tooLongExpressionRule = pluralHelper.getPluralRule(`nplurals=2; plural=${'n'.concat('+0'.repeat(70))};`);

        assert.equal(tooManyPluralsRule(2), 1, 'Oversized nplurals declarations should fall back to the default rule');
        assert.equal(tooLongExpressionRule(2), 1, 'Overlong plural expressions should fall back to the default rule');
    });

    QUnit.test('ruby helper keeps ruby formatting concerns isolated from i18n module wiring', function(assert) {
        assert.equal(
            rubyHelper.convertRubyTags('{ruby}7{rt}sedem{/rt}{/ruby} dni'),
            '<ruby>7<rt>sedem</rt></ruby> dni',
            'Ruby placeholder tags should be converted into HTML tags'
        );
        assert.equal(
            rubyHelper.plainTextFromRuby('{ruby}7{rt}sedem{/rt}{/ruby} dni'),
            '7 dni',
            'Ruby annotations should be removable for plain-text-only contexts'
        );
    });

    QUnit.test('i18n plural object maps treat empty indexed values as untranslated', function(assert) {
        const pluralEntry = i18nTr.translations['%d empty plural day'];

        i18nTr.translations['%d empty plural day'] = {
            0: '%d singular-fallback',
            1: '',
            3: '%d many-translation'
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

    QUnit.test('i18n plural translation falls back to default rule for unsafe plural expressions', function(assert) {
        const originalPluralForms = i18nTr.pluralForms;

        i18nTr.pluralForms = 'nplurals=2; plural=n/*comment*/2;';

        assert.equal(
            i18n.p('%d day', '%d days', 2),
            '2 few-days',
            'Unsafe plural expressions should fall back to the default English-style rule'
        );

        i18nTr.pluralForms = originalPluralForms;
    });

    QUnit.test('i18n plural translation evaluates division-based plural expressions from pluralForms', function(assert) {
        const originalPluralForms = i18nTr.pluralForms;

        i18nTr.pluralForms = 'nplurals=3; plural=n/2;';

        assert.equal(
            i18n.p('%d day', '%d days', 4),
            '4 singular-day',
            'Parsed plural expressions should still respect sparse-index fallback behavior'
        );
        assert.equal(
            i18n.p('%d day', '%d days', 2),
            '2 few-days',
            'Division-based plural expressions should be parsed in the frontend'
        );

        i18nTr.pluralForms = originalPluralForms;
    });
});
