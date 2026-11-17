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
define(['jquery', 'uri', 'layout/tree/provider/jstreeInstanceLoadSkip'], function ($, uri, skip) {
    'use strict';

    var itemUri = 'http://example.test/Ontologies/TAOItem.rdf#i123';
    var encodedUri = uri.encode(itemUri);
    var propertiesAction = {
        id: 'item-properties',
        url: 'https://backoffice.example/taoItems/Items/editItem'
    };

    QUnit.module('API');

    QUnit.test('module', function (assert) {
        assert.expect(6);
        assert.equal(typeof skip, 'object', 'exposes an object');
        assert.equal(typeof skip.getInstanceUriFromNodeContext, 'function', 'getInstanceUriFromNodeContext');
        assert.equal(typeof skip.contentShowsSameInstanceForm, 'function', 'contentShowsSameInstanceForm');
        assert.equal(typeof skip.shouldSkipTreeDrivenInstanceLoad, 'function', 'shouldSkipTreeDrivenInstanceLoad');
        assert.equal(typeof skip.readContentInstanceUri, 'function', 'readContentInstanceUri');
        assert.equal(typeof skip.formActionMatchesLoadAction, 'function', 'formActionMatchesLoadAction');
    });

    QUnit.module('getInstanceUriFromNodeContext');

    QUnit.test('prefers plain id on context', function (assert) {
        assert.strictEqual(
            skip.getInstanceUriFromNodeContext({ id: itemUri, uri: 'other' }),
            itemUri,
            'uses id when set'
        );
    });

    QUnit.test('decodes encoded uri on context', function (assert) {
        assert.strictEqual(
            skip.getInstanceUriFromNodeContext({ uri: encodedUri }),
            itemUri,
            'decodes uri when id is missing'
        );
    });

    QUnit.module('contentShowsSameInstanceForm');

    QUnit.test('matches plain #id and form action', function (assert) {
        var $block = $(
            '<div class="content-block">' +
                '<div class="xhtml_form">' +
                    '<form action="/taoItems/Items/editItem" method="post">' +
                        '<input type="hidden" id="id" name="id" />' +
                    '</form>' +
                '</div>' +
            '</div>'
        );
        $block.find('#id').val(itemUri);
        $('#qunit-fixture').empty().append($block);

        assert.ok(
            skip.contentShowsSameInstanceForm({ id: itemUri }, propertiesAction, $block),
            'same instance and action URL'
        );
    });

    QUnit.test('matches encoded #uri when #id is empty', function (assert) {
        var $block = $(
            '<div class="content-block">' +
                '<form action="/taoItems/Items/editItem">' +
                    '<input type="hidden" id="uri" name="uri" />' +
                '</form>' +
            '</div>'
        );
        $block.find('#uri').val(encodedUri);
        $('#qunit-fixture').empty().append($block);

        assert.ok(
            skip.contentShowsSameInstanceForm({ id: itemUri }, propertiesAction, $block),
            'decodes #uri'
        );
    });

    QUnit.test('rejects different instance uri', function (assert) {
        var $block = $(
            '<div class="content-block">' +
                '<form action="/taoItems/Items/editItem">' +
                    '<input type="hidden" id="id" value="http://example.test/other" />' +
                '</form>' +
            '</div>'
        );

        assert.notOk(
            skip.contentShowsSameInstanceForm({ id: itemUri }, propertiesAction, $block),
            'different resource'
        );
    });

    QUnit.test('rejects mismatched form action', function (assert) {
        var $block = $(
            '<div class="content-block">' +
                '<form action="/taoTests/Tests/editTest">' +
                    '<input type="hidden" id="id" />' +
                '</form>' +
            '</div>'
        );
        $block.find('#id').val(itemUri);

        assert.notOk(
            skip.contentShowsSameInstanceForm({ id: itemUri }, propertiesAction, $block),
            'wrong edit endpoint'
        );
    });

    QUnit.module('shouldSkipTreeDrivenInstanceLoad');

    QUnit.test('skips post-save re-select of same uri', function (assert) {
        assert.ok(
            skip.shouldSkipTreeDrivenInstanceLoad({
                nodeContext: { id: itemUri },
                selectInstanceAction: propertiesAction,
                postSaveRefresh: true,
                postSaveUri: itemUri,
                activeContentActionId: 'item-properties',
                $contentBlock: null
            }),
            'post-save refresh for same resource'
        );
    });

    QUnit.test('skips when another content action is active during post-save refresh', function (assert) {
        assert.ok(
            skip.shouldSkipTreeDrivenInstanceLoad({
                nodeContext: { id: itemUri },
                selectInstanceAction: propertiesAction,
                postSaveRefresh: true,
                postSaveUri: itemUri,
                activeContentActionId: 'item-authoring',
                $contentBlock: null
            }),
            'authoring race guard'
        );
    });

    QUnit.test('does not skip navigation to another instance', function (assert) {
        assert.notOk(
            skip.shouldSkipTreeDrivenInstanceLoad({
                nodeContext: { id: 'http://example.test/Ontologies/TAOItem.rdf#other' },
                selectInstanceAction: propertiesAction,
                postSaveRefresh: true,
                postSaveUri: itemUri,
                activeContentActionId: 'item-properties',
                $contentBlock: null
            }),
            'different node after save'
        );
    });

    QUnit.test('does not skip when post-save refresh flag is false and form differs', function (assert) {
        assert.notOk(
            skip.shouldSkipTreeDrivenInstanceLoad({
                nodeContext: { id: itemUri },
                selectInstanceAction: propertiesAction,
                postSaveRefresh: false,
                postSaveUri: '',
                activeContentActionId: 'item-authoring',
                $contentBlock: null
            }),
            'explicit tree selection while on authoring'
        );
    });
});