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
    'ui/highlighter'
], function($, _, highlighterFactory) {
    'use strict';

    var highlightRangeData;

    QUnit.module('highlighterFactory');

    QUnit.test('module', function(assert) {
        assert.ok(typeof highlighterFactory === 'function', 'the module expose a function');
    });

    QUnit.module('highlighter');

    highlightRangeData = [

        // =============
        // Simple ranges
        // =============

        {
            title:      'fully highlights a plain text node',
            input:      'I should end up fully highlighted',
            selection:  'I should end up fully highlighted',
            output:     '<span class="hl" data-hl-group="1">I should end up fully highlighted</span>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'partially highlights a plain text node',
            input:      'I should end up partially highlighted',
            selection:                  'partially',
            output:     'I should end up <span class="hl" data-hl-group="1">partially</span> highlighted',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild, 'I should end up '.length);
                range.setEnd(fixtureContainer.firstChild, 'I should end up partially'.length);
            },
            highlightIndex: [
                { highlighted: true, inlineRanges: [
                    { groupId: '1', startOffset: 'I should end up '.length, endOffset: 'I should end up partially'.length }
                ]}
            ]
        },

        {
            title:      'partially highlights a plain text node selected from the start',
            input:      'I should end up partially highlighted',
            selection:  'I should end up partially',
            output:     '<span class="hl" data-hl-group="1">I should end up partially</span> highlighted',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild, 0);
                range.setEnd(fixtureContainer.firstChild, 'I should end up partially'.length);
            },
            highlightIndex: [
                { highlighted: true, inlineRanges: [
                    { groupId: '1', endOffset: 'I should end up partially'.length }
                ]}
            ]
        },

        {
            title:      'multiple partial highlights in a plain text node',
            input:      'How cool is that: <span class="hl" data-hl-group="1">Me</span>, ' +
                        '<span class="hl" data-hl-group="5">myself</span> and ' +
                        'I' +
                        ' are a bunch of <span class="hl" data-hl-group="3">highlighted</span> friends',
            selection:  'I',
            output:     'How cool is that: <span class="hl" data-hl-group="1">Me</span>, ' +
                        '<span class="hl" data-hl-group="2">myself</span> and ' +
                        '<span class="hl" data-hl-group="3">I</span>' +
                        ' are a bunch of <span class="hl" data-hl-group="4">highlighted</span> friends',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.childNodes[4], ' and '.length);
                range.setEnd(fixtureContainer.childNodes[4], ' and I'.length);
            },
            highlightIndex: [
                { highlighted: true, inlineRanges: [
                    { groupId: '1', startOffset: 'How cool is that: '.length, endOffset: 'How cool is that: Me'.length },
                    { groupId: '2', startOffset: 'How cool is that: Me, '.length, endOffset: 'How cool is that: Me, myself'.length },
                    { groupId: '3', startOffset: 'How cool is that: Me, myself and '.length, endOffset: 'How cool is that: Me, myself and I'.length },
                    { groupId: '4', startOffset: 'How cool is that: Me, myself and I are a bunch of '.length, endOffset: 'How cool is that: Me, myself and I are a bunch of highlighted'.length}
                ]}
            ]
        },

        {
            title:      'multiple partial highlights in a plain text node, with highlights at text node boundaries',
            input:      '<span class="hl" data-hl-group="8">How cool</span> is that: <span class="hl" data-hl-group="1">Me</span>, ' +
                        '<span class="hl" data-hl-group="5">myself</span> and ' +
                        'I' +
                        ' are a bunch of <span class="hl" data-hl-group="3">highlighted friends</span>',
            selection:  'I',
            output:     '<span class="hl" data-hl-group="1">How cool</span> is that: <span class="hl" data-hl-group="2">Me</span>, ' +
                        '<span class="hl" data-hl-group="3">myself</span> and ' +
                        '<span class="hl" data-hl-group="4">I</span>' +
                        ' are a bunch of <span class="hl" data-hl-group="5">highlighted friends</span>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.childNodes[5], ' and '.length);
                range.setEnd(fixtureContainer.childNodes[5], ' and I'.length);
            },
            highlightIndex: [
                { highlighted: true, inlineRanges: [
                    { groupId: '1', endOffset: 'How cool'.length },
                    { groupId: '2', startOffset: 'How cool is that: '.length, endOffset: 'How cool is that: Me'.length },
                    { groupId: '3', startOffset: 'How cool is that: Me, '.length, endOffset: 'How cool is that: Me, myself'.length },
                    { groupId: '4', startOffset: 'How cool is that: Me, myself and '.length, endOffset: 'How cool is that: Me, myself and I'.length },
                    { groupId: '5', startOffset: 'How cool is that: Me, myself and I are a bunch of '.length }
                ]}
            ]
        },

        {
            title:      'highlights the text content of a single dom element',
            input:      '<div>I should end up fully highlighted</div>',
            selection:  '<div>I should end up fully highlighted</div>',
            output:     '<div><span class="hl" data-hl-group="1">I should end up fully highlighted</span></div>',
            buildRange: function(range, fixtureContainer) {
                range.selectNode(fixtureContainer.firstChild);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'partially highlights the content of a dom element',
            input:      '<div>I should end up partially highlighted</div>',
            selection:                       'partially',
            output:     '<div>I should end up <span class="hl" data-hl-group="1">partially</span> highlighted</div>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild.firstChild, 'I should end up '.length);
                range.setEnd(fixtureContainer.firstChild.firstChild, 'I should end up partially'.length);
            },
            highlightIndex: [
                { highlighted: true, inlineRanges: [
                    { groupId: '1', startOffset: 'I should end up '.length, endOffset: 'I should end up partially'.length }
                ]}
            ]
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
                            '<li><span class="hl" data-hl-group="1">highlight me!</span></li>' +
                            '<li><span class="hl" data-hl-group="1">highlight me too!</span></li>' +
                            '<li>I am too shy to be highlighted</li>' +
                        '</ul>',
            buildRange: function(range) {
                var list = document.getElementById('list');
                range.setStart(list, 1);
                range.setEnd(list, 3);
            },
            highlightIndex: [
                { highlighted: false },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: false }
            ]
        },

        {
            title:      'highlights the text content of multiple dom elements with a selection ending in an ',
            input:      '<ul id="list">' +
                            '<li>leave me alone</li>' +
                            '<li>highlight me!</li>' +
                            '<li>highlight me too!</li>' +
                            '<li>I am too shy to be highlighted</li>' +
                        '</ul>',
            selection:      '<li></li>' +
                            '<li>highlight me!</li>' +
                            '<li>highlight me too!</li>' +
                            '<li></li>',
            output:     '<ul id="list">' +
                            '<li>leave me alone</li>' +
                            '<li><span class="hl" data-hl-group="1">highlight me!</span></li>' +
                            '<li><span class="hl" data-hl-group="1">highlight me too!</span></li>' +
                            '<li>I am too shy to be highlighted</li>' +
                        '</ul>',
            buildRange: function(range) {
                var list = document.getElementById('list');
                // this actually happens in real-world selection scenarios
                // instead of ending the selection at the end of the previous node,
                // it is ended in the current node with an end offset of 0
                range.setEnd(list.childNodes[3].firstChild, 0);
                // this hasn't been observed real-world, but we mirror the previous case to be on the safe side,
                // meaning we start the selection at the end of the previous node
                range.setStart(list.childNodes[0].firstChild, list.childNodes[0].firstChild.length);
            },
            highlightIndex: [
                { highlighted: false },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: false }
            ]
        },

        {
            title:      'highlights a fully selected text with a nested node',
            input:      'We, meaning <strong>me and my children</strong> should end up fully highlighted',
            selection:  'We, meaning <strong>me and my children</strong> should end up fully highlighted',
            output:     '<span class="hl" data-hl-group="1">We, meaning </span>' +
                        '<strong><span class="hl" data-hl-group="1">me and my children</span></strong>' +
                        '<span class="hl" data-hl-group="1"> should end up fully highlighted</span>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'highlights a partially selected text with a nested node',
            input:      'We, meaning <strong>me and my children</strong> should end up partially highlighted',
            selection:      'meaning <strong>me and my children</strong> should end up',
            output:     'We, <span class="hl" data-hl-group="1">meaning </span>' +
                        '<strong><span class="hl" data-hl-group="1">me and my children</span></strong>' +
                        '<span class="hl" data-hl-group="1"> should end up</span> partially highlighted',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild, 'We, '.length);
                range.setEnd(fixtureContainer.lastChild, ' should end up'.length);
            },
            highlightIndex: [
                { highlighted: true, inlineRanges: [{ groupId: '1', startOffset: 'We, '.length }] },
                { highlighted: true, groupId: '1' },
                { highlighted: true, inlineRanges: [{ groupId: '1', endOffset: ' should end up'.length }] }
            ]
        },

        {
            title:      'highlights a partially selected text containing non-nested node',
            input:      'My <strong>siblings</strong> should not bother me <strong>at all</strong>',
            selection:                                      'not bother ',
            output:     'My <strong>siblings</strong> should ' +
                        '<span class="hl" data-hl-group="1">not bother </span>' +
                        'me <strong>at all</strong>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.childNodes[2], ' should '.length);
                range.setEnd(fixtureContainer.childNodes[2], ' should not bother '.length);
            },
            highlightIndex: [
                { highlighted: false },
                { highlighted: false },
                { highlighted: true, inlineRanges: [{ groupId: '1', startOffset: ' should '.length, endOffset: ' should not bother '.length }] },
                { highlighted: false }
            ]
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
            output:     'I should be <span class="hl" data-hl-group="1">highlighted </span>' +
                        '<strong><span class="hl" data-hl-group="1">even if I was</span>' +
                        ' poorly selected...</strong>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild, 'I should be '.length);
                range.setEnd(fixtureContainer.lastChild.firstChild, 'even if I was'.length);
            },
            highlightIndex: [
                { highlighted: true, inlineRanges: [{ groupId: '1', startOffset: 'I should be '.length }] },
                { highlighted: true, inlineRanges: [{ groupId: '1', endOffset: 'even if I was'.length }] }
            ]
        },

        {
            title:      'highlights a selection starting in a partially selected node',
            input:      '<strong>I should be highlighted</strong> even if I was poorly selected...',
                                                                                 // added upon invalid range => HTML conversion
            selection:                                                           '<strong>' +
                                            'highlighted</strong> even if I was',
            output:     '<strong>I should be <span class="hl" data-hl-group="1">highlighted</span></strong>' +
                        '<span class="hl" data-hl-group="1"> even if I was</span>' +
                        ' poorly selected...',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild.firstChild, 'I should be '.length);
                range.setEnd(fixtureContainer.lastChild, ' even if I was'.length);
            },
            highlightIndex: [
                { highlighted: true, inlineRanges: [{ groupId: '1', startOffset: 'I should be '.length }] },
                { highlighted: true, inlineRanges: [{ groupId: '1', endOffset: ' even if I was'.length }] }
            ]
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
                        '<p>There is a ni<span class="hl" data-hl-group="1">ce view up here</span></p>' +
                        '<ul id="list">' +
                            '<li><span class="hl" data-hl-group="1">I am the first option</span></li>' +
                            '<li><span class="hl" data-hl-group="1">I am the second option</span></li>' +
                            '<li><span class="hl" data-hl-group="1">I am the </span><span class="some-class"><span class="hl" data-hl-group="1">third</span></span><span class="hl" data-hl-group="1"> option</span></li>' +
                            '<li><span class="hl" data-hl-group="1">Do not chose the fourth option !</span></li>' +
                        '</ul>' +
                        '<div><p><span>The list</span> is <strong>finished</strong>, see you soon !</p></div>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.childNodes[1].firstChild, 'There is a ni'.length);
                range.setEnd(fixtureContainer, 3);
            },
            highlightIndex: [
                { highlighted: false },
                { highlighted: true, inlineRanges: [{ groupId: '1', startOffset: 'There is a ni'.length }] },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: false },
                { highlighted: false },
                { highlighted: false },
                { highlighted: false }
            ]
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
                            '<li>I am the <span class="some-class">th<span class="hl" data-hl-group="1">ird</span></span><span class="hl" data-hl-group="1"> option</span></li>' +
                            '<li><span class="hl" data-hl-group="1">Do not chose the fourth option !</span></li>' +
                        '</ul>' +
                        '<div><p id="end-p"><span><span class="hl" data-hl-group="1">The list</span></span><span class="hl" data-hl-group="1"> is </span><strong><span class="hl" data-hl-group="1">finished</span></strong><span class="hl" data-hl-group="1">, see you</span> soon !</p></div>',
            buildRange: function(range) {
                var startNode = document.getElementsByClassName('some-class').item(0).firstChild;
                var endNode = document.getElementById('end-p').childNodes[3];
                range.setStart(startNode, 'th'.length);
                range.setEnd(endNode, ', see you'.length);
            },
            highlightIndex: [
                { highlighted: false },
                { highlighted: false },
                { highlighted: false },
                { highlighted: false },
                { highlighted: false },
                { highlighted: true, inlineRanges: [{ groupId: '1', startOffset: 'th'.length }] },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, inlineRanges: [{ groupId: '1', endOffset: ', see you'.length }] }
            ]
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
            output:     '<p><span class="hl" data-hl-group="1">I am on top of the list</span></p>' +
                        '<p><span class="hl" data-hl-group="1">There is a nice view up here</span></p>' +
                        '<ul id="list">' +
                            '<li><span class="hl" data-hl-group="1">I am the first option</span></li>' +
                            '<li><span class="hl" data-hl-group="1">I am the second option</span></li>' +
                            '<li><span class="hl" data-hl-group="1">I am</span> the <span class="some-class">third</span> option</li>' +
                            '<li>Do not chose the fourth option !</li>' +
                        '</ul>' +
                        '<div><p id="end-p"><span>The list</span> is <strong>finished</strong>, see you soon !</p></div>',
            buildRange: function(range, fixtureContainer) {
                var startNode = fixtureContainer.firstChild;
                var endNode = document.getElementById('list').childNodes[2].firstChild;
                range.setStart(startNode, 0);
                range.setEnd(endNode, 'I am'.length);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, inlineRanges: [{ groupId: '1', endOffset: 'I am'.length }] },
                { highlighted: false },
                { highlighted: false },
                { highlighted: false },
                { highlighted: false },
                { highlighted: false },
                { highlighted: false },
                { highlighted: false }
            ]
        },

        // ========================================
        // Ranges with exotic elements & edge cases
        // ========================================

        {
            title:      'highlights a node with an image inside',
            input:      'There is an image <img src="/tao/views/img/logo_tao.png"> in the middle of this selection',
            selection:  'There is an image <img src="/tao/views/img/logo_tao.png"> in the middle of this selection',
            output:     '<span class="hl" data-hl-group="1">There is an image </span><img src="/tao/views/img/logo_tao.png"><span class="hl" data-hl-group="1"> in the middle of this selection</span>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'do not highlight text in a selected textarea',
            input:      '<textarea>Leave me alone, I am inside a text area</textarea>',
            selection:  '<textarea>Leave me alone, I am inside a text area</textarea>',
            output:     '<textarea>Leave me alone, I am inside a text area</textarea>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            },
            highlightIndex: []
        },

        {
            title:      'do not highlight text fully selected in a textarea',
            input:      '<textarea>Leave me alone, I am inside a text area</textarea>',
            selection:            'Leave me alone, I am inside a text area',
            output:     '<textarea>Leave me alone, I am inside a text area</textarea>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer.firstChild);
            },
            highlightIndex: []
        },

        {
            title:      'do not highlight text partially selected in a textarea',
            input:      '<textarea>Leave me alone, I am inside a text area</textarea>',
            selection:                            'I am inside',
            output:     '<textarea>Leave me alone, I am inside a text area</textarea>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild.firstChild, 'Leave me alone, '.length);
                range.setEnd(fixtureContainer.firstChild.firstChild, 'Leave me alone, I am inside'.length);
            },
            highlightIndex: []
        },

        {
            title:      'do not highlight text in a nested textarea',
            input:      '<div>this selection <textarea>contains</textarea> a textarea</div>',
            selection:  '<div>this selection <textarea>contains</textarea> a textarea</div>',
            output:     '<div><span class="hl" data-hl-group="1">this selection </span>' +
                        '<textarea>contains</textarea>' +
                        '<span class="hl" data-hl-group="1"> a textarea</span></div>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'do not highlight text inside an input',
            input:      '<input value="Don\'t you dare highlighting me!!!" type="text">',
            selection:  '<input value="Don\'t you dare highlighting me!!!" type="text">',
            output:     '<input value="Don\'t you dare highlighting me!!!" type="text">',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            },
            highlightIndex: []
        },

        // ===========================
        // Groups & overlapping ranges
        // ===========================

        {
            title:      'adds a group id to the highlight wrapper',
            input:      'We <strong>all</strong> live in a <span class="yellow">yellow</span> highlighted group',
            selection:  'We <strong>all</strong> live in a <span class="yellow">yellow</span> highlighted group',
            output:     '<span class="hl" data-hl-group="1">We </span>' +
                        '<strong><span class="hl" data-hl-group="1">all</span></strong>' +
                        '<span class="hl" data-hl-group="1"> live in a </span>' +
                        '<span class="yellow"><span class="hl" data-hl-group="1">yellow</span></span>' +
                        '<span class="hl" data-hl-group="1"> highlighted group</span>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'select the next available group id',
            input:      '<span class="hl" data-hl-group="1">I am enlightened</span>, will you join me?',
            selection:                                                              'will you join me?',
            output:     '<span class="hl" data-hl-group="1">I am enlightened</span>, ' +
                        '<span class="hl" data-hl-group="2">will you join me?</span>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.childNodes[1], ', '.length);
                range.setEnd(fixtureContainer.childNodes[1], ', will you join me?'.length);
            },
            highlightIndex: [
                { highlighted: true, inlineRanges: [
                        { groupId: '1', endOffset: 'I am enlightened'.length },
                        { groupId: '2', startOffset: 'I am enlightened, '.length }
                ]}
            ]
        },

        {
            title:      'create a single group if two consecutive text node are highlighted',
            input:      '<span class="hl" data-hl-group="1">I already saw the light</span>, and so did you',
            selection:                                                                   ', and so did you',
            output:     '<span class="hl" data-hl-group="1">I already saw the light, and so did you</span>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer.childNodes[1]);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'create a single group if two text selections are joined',
            input:      '<span class="hl" data-hl-group="1">I already saw the light</span>, and soon, <span class="hl" data-hl-group="5">we will all had</span>',
            selection:                                                                   ', and soon, ',
            output:     '<span class="hl" data-hl-group="1">I already saw the light, and soon, we will all had</span>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer.childNodes[1]);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'create a single group if two node selections are joined',
            input:      '<ul id="list">' +
                            '<li><span class="hl" data-hl-group="5">For now</span></li>' +
                            '<li>We all belong</li>' +
                            '<li><span class="hl" data-hl-group="3">To a different group</span></li>' +
                        '</ul>',
            selection:      '<li>We all belong</li>',
            output:     '<ul id="list">' +
                            '<li><span class="hl" data-hl-group="1">For now</span></li>' +
                            '<li><span class="hl" data-hl-group="1">We all belong</span></li>' +
                            '<li><span class="hl" data-hl-group="1">To a different group</span></li>' +
                        '</ul>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild, 1);
                range.setEnd(fixtureContainer.firstChild, 2);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'does not highlight an already highlighted text',
            input:      '<span class="hl" data-hl-group="1">I already saw the light</span>',
            selection:                                     'I already saw the light',
            output:     '<span class="hl" data-hl-group="1">I already saw the light</span>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer.firstChild.firstChild);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'does not highlight an already highlighted portion of text',
            input:      '<span class="hl" data-hl-group="1">I already have more highlight that I need, leave me alone</span>',
            selection:                                                         'highlight',
            output:     '<span class="hl" data-hl-group="1">I already have more highlight that I need, leave me alone</span>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild.firstChild, 'I already have more '.length);
                range.setEnd(fixtureContainer.firstChild.firstChild, 'I already have more highlight'.length);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'does not highlight an already highlighted node',
            input:      '<span class="hl" data-hl-group="1">I already saw the light</span>',
            selection:  '<span class="hl" data-hl-group="1">I already saw the light</span>',
            output:     '<span class="hl" data-hl-group="1">I already saw the light</span>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'merge existing highlights when fully overlapped by a new plain text selection',
            input:      'This <span class="hl" data-hl-group="2">existing highlight</span> is about to be extended',
            selection:  'This <span class="hl" data-hl-group="2">existing highlight</span> is about to be extended',
            output:     '<span class="hl" data-hl-group="1">This existing highlight is about to be extended</span>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'merge existing highlights when fully overlapped by a new selection',
            input:      '<ul id="list">' +
                            '<li>I\'m too dark</li>' +
                            '<li><span class="hl" data-hl-group="3">I already saw the light</span></li>' +
                            '<li>So <span class="hl" data-hl-group="2">did</span> I!</li>' +
                            '<li>Can you please enlighten me?</li>' +
                        '</ul>',
            selection:      '<li>I\'m too dark</li>' +
                            '<li><span class="hl" data-hl-group="3">I already saw the light</span></li>' +
                            '<li>So <span class="hl" data-hl-group="2">did</span> I!</li>' +
                            '<li>Can you please enlighten me?</li>',
            output:      '<ul id="list">' +
                            '<li><span class="hl" data-hl-group="1">I\'m too dark</span></li>' +
                            '<li><span class="hl" data-hl-group="1">I already saw the light</span></li>' +
                            '<li><span class="hl" data-hl-group="1">So did I!</span></li>' +
                            '<li><span class="hl" data-hl-group="1">Can you please enlighten me?</span></li>' +
                        '</ul>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild, 0);
                range.setEnd(fixtureContainer.firstChild, 4);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' }
            ]
        },

        {
            title:      'extend existing highlight on the left',
            input:      'This <span class="hl" data-hl-group="2">existing highlight</span> is about to be extended',
            selection:  'This <span class="hl" data-hl-group="2">existing' +
                                                                                // added upon invalid range => HTML conversion,
                                                                                '</span>',
            output:     '<span class="hl" data-hl-group="1">This existing highlight</span> is about to be extended',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild, 0);
                range.setEnd(fixtureContainer.childNodes[1].firstChild, 'existing'.length);
            },
            highlightIndex: [
                { highlighted: true, inlineRanges: [{ groupId: '1', endOffset: 'This existing highlight'.length }] }
            ]
        },

        {
            title:      'extend existing highlight on the right',
            input:      'This <span class="hl" data-hl-group="2">existing highlight</span> is about to be extended',
            selection:  // added upon invalid range => HTML conversion
                        '<span class="hl" data-hl-group="2">' +
                                                                         'highlight</span> is about to',
            output:     'This <span class="hl" data-hl-group="1">existing highlight is about to</span> be extended',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.childNodes[1].firstChild, 'existing '.length);
                range.setEnd(fixtureContainer.childNodes[2], ' is about to'.length);
            },
            highlightIndex: [
                { highlighted: true, inlineRanges: [{ groupId: '1', startOffset: 'This '.length, endOffset: 'This existing highlight is about to'.length }] }
            ]
        },

        {
            title:      'join partially selected highlights, with another one in the middle',
            input:      '<strong><span class="hl" data-hl-group="2">Look what is about to happen</span></strong>' +
                        '<span class="hl" data-hl-group="2">This existing highlight</span>' +
                        ' will <span class="hl" data-hl-group="6">soon</span> be joined ' +
                        '<span class="hl" data-hl-group="4">by a new one</span>' +
                        '<strong><span class="hl" data-hl-group="4">how cool is that ?!</span></strong>',
            selection:  // added upon invalid range => HTML conversion
                        '<span class="hl" data-hl-group="2">' +
                                                                         'highlight</span>' +
                        ' will <span class="hl" data-hl-group="6">soon</span> be joined ' +
                        '<span class="hl" data-hl-group="4">by a ' +
                                                                        // added upon invalid range => HTML conversion
                                                                        '</span>',
            output:     '<strong><span class="hl" data-hl-group="1">Look what is about to happen</span></strong>' +
                        '<span class="hl" data-hl-group="1">This existing highlight will soon be joined by a new one</span>' +
                        '<strong><span class="hl" data-hl-group="1">how cool is that ?!</span></strong>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.childNodes[1].firstChild, 'This existing '.length);
                range.setEnd(fixtureContainer.childNodes[5].firstChild, 'by a '.length);
            },
            highlightIndex: [
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' },
                { highlighted: true, groupId: '1' }
            ]
        }

    ];

    QUnit
        .cases(highlightRangeData)
        .test('HighlightRange', function(data, assert) {
            // setup test
            var highlighter = highlighterFactory({
                className: 'hl',
                containerSelector: '#qunit-fixture'
            });
            var range = document.createRange();
            var highlightIndex;
            var rangeHtml;

            var fixtureContainer = document.getElementById('qunit-fixture');

            QUnit.expect(8);

            fixtureContainer.innerHTML = data.input;
            // the following assertion is just to provide a better visual feedback in QUnit UI
            assert.equal(fixtureContainer.innerHTML, data.input, 'input: ' + data.input);

            // create range, then make sure it is correctly built
            data.buildRange(range, fixtureContainer);
            rangeHtml = $('<div>').append(range.cloneContents()).html(); // this conversion to HTML will automatically close partially selected nodes, if any
            assert.equal(rangeHtml, data.selection, 'selection: ' + data.selection);

            // highlight
            highlighter.highlightRanges([range]);
            assert.equal(fixtureContainer.innerHTML, data.output, 'highlight: ' + data.output);

            // save highlight
            highlightIndex = highlighter.getHighlightIndex();
            assert.ok(_.isArray(highlightIndex), 'getHighlightIndex returns an array');
            assert.equal(highlightIndex.length, data.highlightIndex.length, 'array has the correct size');
            assert.deepEqual(highlightIndex, data.highlightIndex, 'array has the correct content');

            // reset markup
            fixtureContainer.innerHTML = '';
            assert.equal(fixtureContainer.innerHTML, '', 'markup has been deleted');

            // re-add markup and remove any existing highlight in fixture
            fixtureContainer.innerHTML = data.input ;
            highlighter.clearHighlights();

            // restore highlight
            highlighter.highlightFromIndex(highlightIndex);
            assert.equal(fixtureContainer.innerHTML, data.output, 'highlight has been restored');
        });

});
