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

    var validSelectionsData;

    QUnit.module('highlighterFactory');

    QUnit.test('module', function(assert) {
        assert.ok(typeof highlighterFactory === 'function', 'the module expose a function');
    });

    QUnit.module('highlighter');

    function logTest(input) {
        console.log('=================');
        console.log(input);
    }

    function getRangeHtml(range) {
        return $('<div>').append(range.extractContents()).html();
    }

    validSelectionsData = [
        {
            title:      'fully highlights a plain text node',
            input:      'I should end up fully highlighted',
            selection:  'I should end up fully highlighted',
            output:     '<span class="highlighted">I should end up fully highlighted</span>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            }
        },

        {
            title:      'partially highlights a plain text node',
            input:      'I should end up partially highlighted',
            selection:  'partially',
            output:     'I should end up <span class="highlighted">partially</span> highlighted',
            buildRange: function(range, fixtureContainer) {
                range.setStart(
                    fixtureContainer.firstChild,
                    fixtureContainer.firstChild.textContent.indexOf('partially')
                );
                range.setEnd(
                    fixtureContainer.firstChild,
                    fixtureContainer.firstChild.textContent.indexOf('partially') + 'partially'.length
                );
            }
        },

        {
            title:      'highlights the text content of a single dom element',
            input:      '<div>I should end up fully highlighted</div>',
            selection:  '<div>I should end up fully highlighted</div>',
            output:     '<div><span class="highlighted">I should end up fully highlighted</span></div>',
            buildRange: function(range, fixtureContainer) {
                range.selectNode(fixtureContainer.firstChild);
            }
        },

        {
            title:      'highlights the text content of multiple dom elements',
            input:      '<ul id="list">' +
                            '<li>leave me alone</li>' +
                            '<li>highlight me!</li>' +
                            '<li>highlight me too!</li>' +
                            '<li>I am too shy to be highlighted</li>' +
                        '</ul>',
            selection:      '<li>highlight me!</li>' +
                            '<li>highlight me too!</li>',
            output:     '<ul id="list">' +
                            '<li>leave me alone</li>' +
                            '<li><span class="highlighted">highlight me!</span></li>' +
                            '<li><span class="highlighted">highlight me too!</span></li>' +
                            '<li>I am too shy to be highlighted</li>' +
                        '</ul>',
            buildRange: function(range) {
                var list = document.getElementById('list');
                range.setStart(list, 1);
                range.setEnd(list, 3);
            }
        },

        {
            title:      'highlights a fully selected text with a nested node',
            input:      'We, meaning <strong>me and my children</strong> should end up fully highlighted',
            selection:  'We, meaning <strong>me and my children</strong> should end up fully highlighted',
            output:     '<span class="highlighted">We, meaning </span>' +
                        '<strong><span class="highlighted">me and my children</span></strong>' +
                        '<span class="highlighted"> should end up fully highlighted</span>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer.firstChild);
            }
        },

        {
            title:      'highlights a partially selected text with a nested node',
            input:      'We, meaning <strong>me and my children</strong> should end up partially highlighted',
            selection:      'meaning <strong>me and my children</strong> should end up',
            output:     'We, <span class="highlighted">meaning </span>' +
                        '<strong><span class="highlighted">me and my children</span></strong>' +
                        '<span class="highlighted"> should end up</span> partially highlighted',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer.firstChild);
            }
        }
    ];

    QUnit
        .cases(validSelectionsData)
        .test('Valid selections', function(data, assert) {
            // setup test
            var highlighter = highlighterFactory({
                selector: selectorMock,
                className: 'highlighted'
            });
            var range = document.createRange();

            var fixtureContainer = document.getElementById('qunit-fixture');

            logTest(data.input);

            QUnit.expect(2);

            fixtureContainer.innerHTML = data.input;

            // create and verify range, to make sure buildRange() is implemented correctly
            data.buildRange(range, fixtureContainer);
            assert.equal(getRangeHtml(range), data.selection, 'selection is correct');

            selectorMock.removeAllRanges();
            selectorMock.addRange(range);

            // highlight
            highlighter.highlightRanges();
            assert.equal(fixtureContainer.innerHTML, data.output);
        });



});
