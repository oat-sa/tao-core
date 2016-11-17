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
    'ui/selector'
], function($, _, selector) {
    'use strict';

    QUnit.module('selector');

    QUnit.test('module', function(assert) {
        assert.ok(typeof selector === 'object', 'the module expose an object');
    });

    function isSameRange(rangeA, rangeB) {
        return rangeA && rangeB
            && rangeA.collapsed === rangeB.collapsed
            && rangeA.startContainer.isSameNode(rangeB.startContainer)
            && rangeA.endContainer.isSameNode(rangeB.endContainer)
            && rangeA.startOffset === rangeB.startOffset
            && rangeA.endOffset === rangeB.endOffset;
    }

    QUnit.module('getAllRanges()');

    QUnit.test('returns the only selected range', function(assert) {
        var selection = window.getSelection();
        var range = document.createRange();
        var allRanges;

        var fixtureContainer = document.getElementById('qunit-fixture');
        fixtureContainer.innerHTML = 'I am available for a selection';

        selection.removeAllRanges();
        selection.selectAllChildren(fixtureContainer.firstChild);
        selection.extend(fixtureContainer.firstChild, fixtureContainer.firstChild.length);
        range.selectNodeContents(fixtureContainer.firstChild);

        assert.ok(selector.hasRanges, 'ranges have been selected');

        allRanges = selector.getAllRanges();
        assert.equal(allRanges.length, 1, 'correct number of ranges has been retrieved');
        assert.ok(isSameRange(allRanges[0], range, 'correct range has been retrieved'));
    });

    // Multiple ranges handling in not fully cross browser compatible
    // see http://w3c.github.io/selection-api/#methods
    /*
    QUnit.test('returns multiple ranges', function(assert) {
        var selection = window.getSelection();
        var range1 = document.createRange();
        var range2 = document.createRange();
        var range3 = document.createRange();
        var allRanges;

        var fixtureContainer = document.getElementById('qunit-fixture');
        fixtureContainer.innerHTML =
            '<ul id="list">' +
                '<li>I am available for a selection</li>' +
                '<li>So am I</li>' +
                '<li>do not forget me please</li>' +
            '</ul>';

        range1.selectNodeContents(fixtureContainer.firstChild.childNodes[0]);
        range2.selectNodeContents(fixtureContainer.firstChild.childNodes[1]);
        range3.selectNodeContents(fixtureContainer.firstChild.childNodes[2]);

        selection.removeAllRanges();
        selection.addRange(range1);
        selection.addRange(range2);
        selection.addRange(range3);

        assert.ok(selector.hasNonEmptyRanges(), 'non empty ranges detected');
        assert.ok(selector.hasRanges, 'selection has ranges');

        allRanges = selector.getAllRanges();
        assert.equal(allRanges.length, 3, 'correct number of ranges has been retrieved');
        assert.ok(isSameRange(allRanges[0], range1, 'correct range has been retrieved'));
        assert.ok(isSameRange(allRanges[1], range2, 'correct range has been retrieved'));
        assert.ok(isSameRange(allRanges[2], range3, 'correct range has been retrieved'));

    });
    */

    QUnit.module('removeAllRanges()');

    QUnit.test('remove all existing ranges', function(assert) {
        var selection = window.getSelection();
        var range1 = document.createRange();
        var range2 = document.createRange();
        var range3 = document.createRange();
        var allRanges;

        var fixtureContainer = document.getElementById('qunit-fixture');
        fixtureContainer.innerHTML =
            '<ul id="list">' +
                '<li>I am available for a selection</li>' +
                '<li>So am I</li>' +
                '<li>do not forget me please</li>' +
            '</ul>';

        range1.selectNodeContents(fixtureContainer.firstChild.childNodes[0]);
        range2.selectNodeContents(fixtureContainer.firstChild.childNodes[1]);
        range3.selectNodeContents(fixtureContainer.firstChild.childNodes[2]);

        selection.removeAllRanges();
        selection.addRange(range1);
        selection.addRange(range2);
        selection.addRange(range3);

        assert.ok(selector.hasNonEmptyRanges(), 'non empty ranges detected');
        assert.ok(selector.hasRanges, 'selection has ranges');

        selector.removeAllRanges();
        assert.ok(selector.hasRanges() === false, 'selection has no more ranges');

        allRanges = selector.getAllRanges();
        assert.equal(allRanges.length, 0, 'all ranges has been removed');
    });

    QUnit.module('hasNonEmptyRanges()');

    QUnit.test('returns false if no selection exists', function(assert) {
        var selection = window.getSelection();

        selection.removeAllRanges();

        assert.ok(selector.hasNonEmptyRanges() === false, 'empty ranges have been detected');
    });

    QUnit.test('returns false if selection contains only empty range', function(assert) {
        var selection = window.getSelection();

        var fixtureContainer = document.getElementById('qunit-fixture');
        fixtureContainer.innerHTML = 'I am available for a selection';

        selection.removeAllRanges();
        selection.selectAllChildren(fixtureContainer.firstChild);
        selection.collapse(fixtureContainer.firstChild, 0);

        assert.ok(selector.hasNonEmptyRanges() === false, 'empty ranges have been detected');
    });

    QUnit.test('returns false if selection contains one empty range', function(assert) {
        var selection = window.getSelection();
        var range1 = document.createRange();
        var range2 = document.createRange();
        var range3 = document.createRange();

        var fixtureContainer = document.getElementById('qunit-fixture');
        fixtureContainer.innerHTML =
            '<ul id="list">' +
                '<li>I am available for a selection</li>' +
                '<li>So am I</li>' +
                '<li>do not forget me please</li>' +
            '</ul>';

        range1.collapse(); // has to be the first one in case browser does not support mutliple ranges
        range2.selectNodeContents(fixtureContainer.firstChild.childNodes[0]);
        range3.selectNodeContents(fixtureContainer.firstChild.childNodes[1]);

        selection.removeAllRanges();
        selection.addRange(range1);
        selection.addRange(range2);
        selection.addRange(range3);

        assert.ok(selector.hasRanges(), 'selection has ranges');
        assert.ok(selector.hasNonEmptyRanges() === false, 'empty ranges detected');
    });


});
