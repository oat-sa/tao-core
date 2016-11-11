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

    var testData;

    QUnit.module('highlighterFactory');

    QUnit.test('module', function(assert) {
        assert.ok(typeof highlighterFactory === 'function', 'the module expose a function');
    });

    QUnit.module('highlightRange()');

    function getRangeHtml(range) {
        return $('<div>').append(range.cloneContents()).html();
    }

    testData = [

        // =============
        // Simple ranges
        // =============

        {
            title:      'fully highlights a plain text node',
            input:      'I should end up fully highlighted',
            //todo: rename to range
            selection:  'I should end up fully highlighted',
            output:     '<span class="highlighted">I should end up fully highlighted</span>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            }
        },

        {
            title:      'partially highlights a plain text node',
            input:      'I should end up partially highlighted',
            selection:                  'partially',
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
            title:      'partially highlights a plain text node selected from the start',
            input:      'I should end up partially highlighted',
            selection:  'I should end up partially',
            output:     '<span class="highlighted">I should end up partially</span> highlighted',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild, 0);
                range.setEnd(fixtureContainer.firstChild, 'I should end up partially'.length
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
            title:      'partially highlights the content of a dom element',
            input:      '<div>I should end up partially highlighted</div>',
            selection:                       'partially',
            output:     '<div>I should end up <span class="highlighted">partially</span> highlighted</div>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(
                    fixtureContainer.firstChild.firstChild,
                    fixtureContainer.firstChild.firstChild.textContent.indexOf('partially')
                );
                range.setEnd(
                    fixtureContainer.firstChild.firstChild,
                    fixtureContainer.firstChild.firstChild.textContent.indexOf('partially') + 'partially'.length
                );
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
                range.selectNodeContents(fixtureContainer);
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
                range.setStart(fixtureContainer.firstChild, 'We, '.length);
                range.setEnd(fixtureContainer.lastChild, ' should end up'.length);
            }
        },

        {
            title:      'highlights a partially selected text containing non-nested node',
            input:      'My <strong>siblings</strong> should not bother me <strong>at all</strong>',
            selection:                                      'not bother ',
            output:     'My <strong>siblings</strong> should ' +
                        '<span class="highlighted">not bother </span>' +
                        'me <strong>at all</strong>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.childNodes[2], ' should '.length);
                range.setEnd(fixtureContainer.childNodes[2], ' should not bother '.length);
            }
        },

        // ====================================
        // Ranges with partially selected nodes
        // ====================================

        {
            title:      'highlights a selection ending in a partially selected node',
            input:      'I should be highlighted <strong>even if I was poorly selected...</strong>',
            selection:              'highlighted <strong>even if I was' +
                                                                            // added upon invalid range => HTML conversion
                                                                            '</strong>',
            output:     'I should be <span class="highlighted">highlighted </span>' +
                        '<strong><span class="highlighted">even if I was</span>' +
                        ' poorly selected...</strong>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild, 'I should be '.length);
                range.setEnd(fixtureContainer.lastChild.firstChild, 'even if I was'.length);
            }
        },

        {
            title:      'highlights a selection starting in a partially selected node',
            input:      '<strong>I should be highlighted</strong> even if I was poorly selected...',
                                                                                 // added upon invalid range => HTML conversion
            selection:                                                           '<strong>' +
                                            'highlighted</strong> even if I was',
            output:     '<strong>I should be <span class="highlighted">highlighted</span></strong>' +
                        '<span class="highlighted"> even if I was</span>' +
                        ' poorly selected...',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild.firstChild, 'I should be '.length);
                range.setEnd(fixtureContainer.lastChild, ' even if I was'.length);
            }
        },

        {
            title:      'Highlights a range containing multiples nodes, 1',
            input:      '<p>I am on top of the list</p>' +
                        '<p>There is a nice view up here</p>' +
                        '<ul id="list">' +
                            '<li>I am the first option</li>' +
                            '<li>I am the second option</li>' +
                            '<li>I am the <span class="some-class">third</span> option</li>' +
                            '<li>Do not chose the fourth option !</li>' +
                        '</ul>' +
                        '<div><p><span>The list</span> is <strong>finished</strong>, see you soon !</p></div>',
            selection:                                          // added upon invalid range => HTML conversion
                                                                '<p>' +
                                        'ce view up here</p>' +
                        '<ul id="list">' +
                            '<li>I am the first option</li>' +
                            '<li>I am the second option</li>' +
                            '<li>I am the <span class="some-class">third</span> option</li>' +
                            '<li>Do not chose the fourth option !</li>' +
                        '</ul>',
            output:     '<p>I am on top of the list</p>' +
                        '<p>There is a ni<span class="highlighted">ce view up here</span></p>' +
                        '<ul id="list">' +
                            '<li><span class="highlighted">I am the first option</span></li>' +
                            '<li><span class="highlighted">I am the second option</span></li>' +
                            '<li><span class="highlighted">I am the </span><span class="some-class"><span class="highlighted">third</span></span><span class="highlighted"> option</span></li>' +
                            '<li><span class="highlighted">Do not chose the fourth option !</span></li>' +
                        '</ul>' +
                        '<div><p><span>The list</span> is <strong>finished</strong>, see you soon !</p></div>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.childNodes[1].firstChild, 'There is a ni'.length);
                range.setEnd(fixtureContainer, 3);
            }
        },

        {
            title:      'Highlights a range containing multiples nodes, 2',
            input:      '<p>I am on top of the list</p>' +
                        '<p>There is a nice view up here</p>' +
                        '<ul id="list">' +
                            '<li>I am the first option</li>' +
                            '<li>I am the second option</li>' +
                            '<li>I am the <span class="some-class">third</span> option</li>' +
                            '<li>Do not chose the fourth option !</li>' +
                        '</ul>' +
                        '<div><p id="end-p"><span>The list</span> is <strong>finished</strong>, see you soon !</p></div>',
            selection:  // added upon invalid range => HTML conversion
                        '<ul id="list"><li><span class="some-class">' +
                                                                    'ird</span> option</li>' +
                            '<li>Do not chose the fourth option !</li>' +
                        '</ul>' +
                        '<div><p id="end-p"><span>The list</span> is <strong>finished</strong>, see you' +
                        // added upon invalid range => HTML conversion
                        '</p></div>',
            output:     '<p>I am on top of the list</p>' +
                        '<p>There is a nice view up here</p>' +
                        '<ul id="list">' +
                            '<li>I am the first option</li>' +
                            '<li>I am the second option</li>' +
                            '<li>I am the <span class="some-class">th<span class="highlighted">ird</span></span><span class="highlighted"> option</span></li>' +
                            '<li><span class="highlighted">Do not chose the fourth option !</span></li>' +
                        '</ul>' +
                        '<div><p id="end-p"><span><span class="highlighted">The list</span></span><span class="highlighted"> is </span><strong><span class="highlighted">finished</span></strong><span class="highlighted">, see you</span> soon !</p></div>',
            buildRange: function(range) {
                var startNode = document.getElementsByClassName('some-class').item(0).firstChild;
                var endNode = document.getElementById('end-p').childNodes[3];
                range.setStart(startNode, 'th'.length);
                range.setEnd(endNode, ', see you'.length);
            }
        },

        {
            title:      'Highlights a range containing multiples nodes, 3',
            input:      '<p>I am on top of the list</p>' +
                        '<p>There is a nice view up here</p>' +
                        '<ul id="list">' +
                            '<li>I am the first option</li>' +
                            '<li>I am the second option</li>' +
                            '<li>I am the <span class="some-class">third</span> option</li>' +
                            '<li>Do not chose the fourth option !</li>' +
                        '</ul>' +
                        '<div><p id="end-p"><span>The list</span> is <strong>finished</strong>, see you soon !</p></div>',
            selection:  '<p>I am on top of the list</p>' +
                        '<p>There is a nice view up here</p>' +
                        '<ul id="list">' +
                            '<li>I am the first option</li>' +
                            '<li>I am the second option</li>' +
                            '<li>I am' +
                        // added upon invalid range => HTML conversion
                        '</li></ul>',
            output:     '<p><span class="highlighted">I am on top of the list</span></p>' +
                        '<p><span class="highlighted">There is a nice view up here</span></p>' +
                        '<ul id="list">' +
                            '<li><span class="highlighted">I am the first option</span></li>' +
                            '<li><span class="highlighted">I am the second option</span></li>' +
                            '<li><span class="highlighted">I am</span> the <span class="some-class">third</span> option</li>' +
                            '<li>Do not chose the fourth option !</li>' +
                        '</ul>' +
                        '<div><p id="end-p"><span>The list</span> is <strong>finished</strong>, see you soon !</p></div>',
            buildRange: function(range, fixtureContainer) {
                var startNode = fixtureContainer.firstChild;
                var endNode = document.getElementById('list').childNodes[2].firstChild;
                range.setStart(startNode, 0);
                range.setEnd(endNode, 'I am'.length);
            }
        }



    ];

    QUnit
        .cases(testData)
        .test('Highlight', function(data, assert) {
            // setup test
            var highlighter = highlighterFactory({
                $wrapper: $('<span>', {
                    class: 'highlighted'
                })
            });
            var range = document.createRange();

            var fixtureContainer = document.getElementById('qunit-fixture');

            QUnit.expect(3);

            fixtureContainer.innerHTML = data.input;
            // the following assertion is just to provide a better visual feedback in QUnit UI
            assert.equal(fixtureContainer.innerHTML, data.input, 'input: ' + data.input);

            // create range, then make sure the built selection is correct
            data.buildRange(range, fixtureContainer);
            assert.equal(getRangeHtml(range), data.selection, 'selection: ' + data.selection);

            // highlight
            highlighter.highlightRanges([range]);
            assert.equal(fixtureContainer.innerHTML, data.output, 'highlight: ' + data.output);
        });

});
