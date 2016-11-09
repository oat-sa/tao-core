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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'ui/highlighter',
    'tao/test/ui/selector/mock'
], function($, _, highlighterFactory, selectorMock) {
    'use strict';

    QUnit.module('highlighterFactory');

    QUnit.test('module', function(assert) {
        assert.ok(typeof highlighterFactory === 'function', 'the module expose a function');
    });

    QUnit.module('highlighter');

    QUnit.test('fully highlights a text node', function(assert) {
        var input = 'I should end up fully highlighted';
        var output = '<span class="highlighted">I should end up fully highlighted</span>';

        // setup test
        var highlighter = highlighterFactory({
            selector: selectorMock,
            className: 'highlighted'
        });
        var range = document.createRange();

        var fixtureContainer = document.getElementById('qunit-fixture');

        fixtureContainer.innerHTML = input;

        // create selection
        range.setStart(fixtureContainer.firstChild, 0);
        range.setEnd(fixtureContainer.firstChild, fixtureContainer.firstChild.length);
        selectorMock.addRange(range);

        // highlight
        highlighter.highlightRanges();

        QUnit.expect(1);
        assert.equal(fixtureContainer.innerHTML, output);
    });

    QUnit.test('partially highlights a text node', function(assert) {
        var input = 'I should end up fully highlighted';
        var output = '<span class="highlighted">I should end up fully highlighted</span>';

        // setup test
        var highlighter = highlighterFactory({
            selector: selectorMock,
            className: 'highlighted'
        });
        var range = document.createRange();

        var fixtureContainer = document.getElementById('qunit-fixture');

        fixtureContainer.innerHTML = input;

        // create selection
        range.setStart(
            fixtureContainer.firstChild,
            fixtureContainer.firstChild.textContent.indexOf('partially')
        );
        range.setEnd(
            fixtureContainer.firstChild,
            fixtureContainer.firstChild.textContent.indexOf('partially') + 'partially'.length
        );
        selectorMock.addRange(range);

        // highlight
        highlighter.highlightRanges();

        QUnit.expect(1);
        assert.equal(fixtureContainer.innerHTML, output);
    });



});
