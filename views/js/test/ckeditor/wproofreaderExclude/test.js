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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */
define([
    'tao/ckeditor/wproofreaderExclude',
    'tao/ckeditor/wproofreaderBootstrap'
], function (wproofreaderExclude, wproofreaderBootstrap) {
    'use strict';

    QUnit.module('wproofreaderExclude');

    QUnit.test('uses native spellcheck without a premium key', function (assert) {
        assert.strictEqual(wproofreaderBootstrap.enabled, false, 'premium provider is disabled');
        assert.strictEqual(
            wproofreaderBootstrap.getCkeditorConfig().disableNativeSpellChecker,
            false,
            'native spellcheck remains enabled'
        );
    });

    QUnit.test('defaults exclude math, QTI widgets, code and source mode', function (assert) {
        var options = wproofreaderExclude.buildWscExcludeOptions({});

        assert.expect(6);

        assert.ok(
            options.ignoreClasses.indexOf('math-tex') !== -1,
            'math-tex spans are ignored'
        );
        assert.ok(
            options.ignoreClasses.indexOf('cke_widget_wrapper') !== -1,
            'CKEditor widget wrappers are ignored'
        );
        assert.ok(
            options.ignoreClasses.indexOf('widget-box') !== -1,
            'QTI widgets are ignored'
        );
        assert.ok(
            options.ignoreElements.indexOf('pre') !== -1 && options.ignoreElements.indexOf('code') !== -1,
            'pre/code elements are ignored'
        );
        assert.ok(
            options.enableAutoSearchIn.indexOf('.cke_editable') !== -1,
            'inline editables stay eligible for auto-search'
        );
        assert.ok(
            options.disableAutoSearchIn.indexOf('.cke_source') !== -1,
            'source mode stays out of auto-search'
        );
    });

    QUnit.test('custom ignoreElements merge with defaults instead of replacing them', function (assert) {
        var options = wproofreaderExclude.buildWscExcludeOptions({ ignoreElements: ['table'] });

        assert.expect(2);

        assert.ok(
            options.ignoreElements.indexOf('table') !== -1,
            'the custom element is added'
        );
        assert.ok(
            options.ignoreElements.indexOf('pre') !== -1 && options.ignoreElements.indexOf('math') !== -1,
            'the defaults survive alongside the custom entry'
        );
    });

    QUnit.test('custom string and single-value entries are normalised', function (assert) {
        var fromString = wproofreaderExclude.buildWscExcludeOptions({ ignoreElements: 'table, figure' });
        var fromSingle = wproofreaderExclude.buildWscExcludeOptions({ ignoreClasses: 'custom-math' });

        assert.expect(4);

        assert.ok(
            fromString.ignoreElements.indexOf('table') !== -1
                && fromString.ignoreElements.indexOf('figure') !== -1,
            'comma-separated string entries are added'
        );
        assert.ok(
            fromString.ignoreElements.indexOf('pre') !== -1,
            'defaults survive string input'
        );
        assert.ok(
            fromSingle.ignoreClasses.indexOf('custom-math') !== -1,
            'a single string entry is added'
        );
        assert.ok(
            fromSingle.ignoreClasses.indexOf('math-tex') !== -1,
            'defaults survive single-value input'
        );
    });

    QUnit.test('QTI structural attributes are never ignored by default', function (assert) {
        var options = wproofreaderExclude.buildWscExcludeOptions({});

        assert.expect(1);

        assert.ok(
            options.ignoreAttributes.indexOf('data-qti-class') === -1
                && options.ignoreAttributes.indexOf('data-serial') === -1,
            'attributes sitting on prose containers do not prune whole subtrees'
        );
    });
});
