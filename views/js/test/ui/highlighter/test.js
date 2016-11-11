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

    var testData;
    var MathJax = getMathJaxData();

    QUnit.module('highlighterFactory');

    QUnit.test('module', function(assert) {
        assert.ok(typeof highlighterFactory === 'function', 'the module expose a function');
    });

    QUnit.module('highlighter');

    testData = [

        // =============
        // Simple ranges
        // =============

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
            selection:                  'partially',
            output:     'I should end up <span class="highlighted">partially</span> highlighted',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild, 'I should end up '.length);
                range.setEnd(fixtureContainer.firstChild, 'I should end up partially'.length);
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
                range.setStart(fixtureContainer.firstChild.firstChild, 'I should end up '.length);
                range.setEnd(fixtureContainer.firstChild.firstChild, 'I should end up partially'.length);
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
        },

        // ===========================
        // Ranges with exotic elements
        // ===========================

        {
            title:      'highlights a node with an image inside',
            input:      'There is an image <img src="/tao/views/img/logo_tao.png"> in the middle of this selection',
            selection:  'There is an image <img src="/tao/views/img/logo_tao.png"> in the middle of this selection',
            output:     '<span class="highlighted">There is an image </span><img src="/tao/views/img/logo_tao.png"><span class="highlighted"> in the middle of this selection</span>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            }
        },

        {
            title:      'do not highlight text in a selected textarea',
            input:      '<textarea>Leave me alone, I am inside a text area</textarea>',
            selection:  '<textarea>Leave me alone, I am inside a text area</textarea>',
            output:     '<textarea>Leave me alone, I am inside a text area</textarea>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            }
        },

        {
            title:      'do not highlight text fully selected in a textarea',
            input:      '<textarea>Leave me alone, I am inside a text area</textarea>',
            selection:  'Leave me alone, I am inside a text area',
            output:     '<textarea>Leave me alone, I am inside a text area</textarea>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer.firstChild);
            }
        },

        {
            title:      'do not highlight text partially selected in a textarea',
            input:      '<textarea>Leave me alone, I am inside a text area</textarea>',
            selection:                            'I am inside',
            output:     '<textarea>Leave me alone, I am inside a text area</textarea>',
            buildRange: function(range, fixtureContainer) {
                range.setStart(fixtureContainer.firstChild.firstChild, 'Leave me alone, '.length);
                range.setEnd(fixtureContainer.firstChild.firstChild, 'Leave me alone, I am inside'.length);
            }
        },

        {
            title:      'do not highlight text in a nested textarea',
            input:      '<div>this selection <textarea>contains</textarea> a textarea</div>',
            selection:  '<div>this selection <textarea>contains</textarea> a textarea</div>',
            output:     '<div><span class="highlighted">this selection </span>' +
                        '<textarea>contains</textarea>' +
                        '<span class="highlighted"> a textarea</span></div>',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            }
        },

        {
            title:      'do not highlight text inside an input',
            input:      '<input value="Don\'t you dare highlighting me!!!" type="text">',
            selection:  '<input value="Don\'t you dare highlighting me!!!" type="text">',
            output:     '<input value="Don\'t you dare highlighting me!!!" type="text">',
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            }
        },

        {
            title:      'highlight a Mathjax renderered text, but not the assistive MathMl',
            input:      MathJax.input,
            selection:  MathJax.selection,
            output:     MathJax.output,
            buildRange: function(range, fixtureContainer) {
                range.selectNodeContents(fixtureContainer);
            }
        }




    ];



    QUnit
        .cases(testData)
        .test('HighlightRange', function(data, assert) {
            // setup test
            var highlighter = highlighterFactory({
                $wrapper: $('<span>', {
                    class: 'highlighted'
                })
            });
            var range = document.createRange();
            var rangeHtml;

            var fixtureContainer = document.getElementById('qunit-fixture');

            QUnit.expect(3);

            fixtureContainer.innerHTML = data.input;
            // the following assertion is just to provide a better visual feedback in QUnit UI
            assert.equal(fixtureContainer.innerHTML, data.input, 'input: ' + data.input);

            // create range, then make sure the built selection is correct
            data.buildRange(range, fixtureContainer);
            rangeHtml = $('<div>').append(range.cloneContents()).html(); // this conversion to HTML will close any partially selected node
            assert.equal(rangeHtml, data.selection, 'selection: ' + data.selection);

            // highlight
            highlighter.highlightRanges([range]);
            assert.equal(fixtureContainer.innerHTML, data.output, 'highlight: ' + data.output);
        });


    function getMathJaxData() {
        return {
            input: '<span data-serial="math_5825d67ea3696998688184" data-qti-class="math"><span class="MathJax_Preview" style="color: inherit;"></span><span class="MathJax" id="MathJax-Element-1-Frame" tabindex="0" style="position: relative;" data-mathml="<math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;><semantics><mstyle displaystyle=&quot;true&quot; scriptlevel=&quot;0&quot;><mrow class=&quot;MJX-TeXAtom-ORD&quot;><mfrac><mn>1</mn><mn>2</mn></mfrac><mo>&amp;#xD7;</mo><msqrt><msup><mi>&amp;#x3C0;</mi><mn>2</mn></msup></msqrt></mrow></mstyle><annotation encoding=&quot;latex&quot;>      \frac{1}{2}\times\sqrt{\pi^2}</annotation></semantics></math>" role="presentation"><nobr aria-hidden="true"><span class="math" id="MathJax-Span-1" role="math" style="width: 4.446em; display: inline-block;"><span style="display: inline-block; position: relative; width: 3.99em; height: 0px; font-size: 111%;"><span style="position: absolute; clip: rect(1.028em, 1003.99em, 3.453em, -1000em); top: -2.574em; left: 0em;"><span class="mrow" id="MathJax-Span-2"><span class="semantics" id="MathJax-Span-3"><span class="mstyle" id="MathJax-Span-4"><span class="mrow" id="MathJax-Span-5"><span class="texatom" id="MathJax-Span-6"><span class="mrow" id="MathJax-Span-7"><span class="mfrac" id="MathJax-Span-8"><span style="display: inline-block; position: relative; width: 0.62em; height: 0px; margin-right: 0.12em; margin-left: 0.12em;"><span style="position: absolute; clip: rect(3.121em, 1000.39em, 4.183em, -1000em); top: -4.666em; left: 50%; margin-left: -0.25em;"><span class="mn" id="MathJax-Span-9" style="font-family: STIXGeneral;">1</span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; clip: rect(3.121em, 1000.47em, 4.183em, -1000em); top: -3.304em; left: 50%; margin-left: -0.25em;"><span class="mn" id="MathJax-Span-10" style="font-family: STIXGeneral;">2</span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; clip: rect(0.82em, 1000.62em, 1.287em, -1000em); top: -1.314em; left: 0em;"><span style="display: inline-block; overflow: hidden; vertical-align: 0em; border-top: 1.3px solid; width: 0.62em; height: 0px;"></span><span style="display: inline-block; width: 0px; height: 1.094em;"></span></span></span></span><span class="mo" id="MathJax-Span-11" style="font-family: STIXGeneral; padding-left: 0.25em;">×</span><span class="msqrt" id="MathJax-Span-12" style="padding-left: 0.25em;"><span style="display: inline-block; position: relative; width: 1.977em; height: 0px;"><span style="position: absolute; clip: rect(2.906em, 1001.02em, 4.201em, -1000em); top: -3.99em; left: 0.928em;"><span class="mrow" id="MathJax-Span-13"><span class="msup" id="MathJax-Span-14"><span style="display: inline-block; position: relative; width: 1.024em; height: 0px;"><span style="position: absolute; clip: rect(3.369em, 1000.54em, 4.201em, -1000em); top: -3.99em; left: 0em;"><span class="mi" id="MathJax-Span-15" style="font-family: STIXGeneral; font-style: italic;">π<span style="display: inline-block; overflow: hidden; height: 1px; width: 0.032em;"></span></span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; top: -4.403em; left: 0.596em;"><span class="mn" id="MathJax-Span-16" style="font-size: 70.7%; font-family: STIXGeneral;">2</span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span></span></span></span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; clip: rect(2.977em, 1001.05em, 3.413em, -1000em); top: -4.198em; left: 0.928em;"><span style="display: inline-block; position: relative; width: 1.049em; height: 0px;"><span style="position: absolute; font-family: STIXGeneral; top: -3.99em; left: 0em;">‾<span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; font-family: STIXGeneral; top: -3.99em; left: 0.549em;">‾<span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="font-family: STIXGeneral; position: absolute; top: -3.99em; left: 0.262em;">‾<span style="display: inline-block; width: 0px; height: 3.99em;"></span></span></span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; clip: rect(2.824em, 1000.96em, 4.442em, -1000em); top: -4.045em; left: 0em;"><span style="font-family: STIXGeneral;">√</span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span></span></span></span></span></span></span></span></span><span style="display: inline-block; width: 0px; height: 2.574em;"></span></span></span><span style="display: inline-block; overflow: hidden; vertical-align: -0.833em; border-left: 0px solid; width: 0px; height: 2.406em;"></span></span></nobr><span class="MJX_Assistive_MathML" role="presentation"><math xmlns="http://www.w3.org/1998/Math/MathML"><semantics><mstyle displaystyle="true" scriptlevel="0"><mrow class="MJX-TeXAtom-ORD"><mfrac><mn>1</mn><mn>2</mn></mfrac><mo>×</mo><msqrt><msup><mi>π</mi><mn>2</mn></msup></msqrt></mrow></mstyle><annotation encoding="latex">      \frac{1}{2}\times\sqrt{\pi^2}</annotation></semantics></math></span></span><script type="math/mml" id="MathJax-Element-1"><math><semantics><mstyle displaystyle="true" scriptlevel="0"><mrow class="MJX-TeXAtom-ORD"><mfrac><mn>1</mn><mn>2</mn></mfrac><mo>×</mo><msqrt><msup><mi>π</mi><mn>2</mn></msup></msqrt></mrow></mstyle><annotation encoding="latex">\frac{1}{2}\times\sqrt{\pi^2}</annotation></semantics></math></script></span>',
            selection: '<span data-serial="math_5825d67ea3696998688184" data-qti-class="math"><span class="MathJax_Preview" style="color: inherit;"></span><span class="MathJax" id="MathJax-Element-1-Frame" tabindex="0" style="position: relative;" data-mathml="<math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;><semantics><mstyle displaystyle=&quot;true&quot; scriptlevel=&quot;0&quot;><mrow class=&quot;MJX-TeXAtom-ORD&quot;><mfrac><mn>1</mn><mn>2</mn></mfrac><mo>&amp;#xD7;</mo><msqrt><msup><mi>&amp;#x3C0;</mi><mn>2</mn></msup></msqrt></mrow></mstyle><annotation encoding=&quot;latex&quot;>      \frac{1}{2}\times\sqrt{\pi^2}</annotation></semantics></math>" role="presentation"><nobr aria-hidden="true"><span class="math" id="MathJax-Span-1" role="math" style="width: 4.446em; display: inline-block;"><span style="display: inline-block; position: relative; width: 3.99em; height: 0px; font-size: 111%;"><span style="position: absolute; clip: rect(1.028em, 1003.99em, 3.453em, -1000em); top: -2.574em; left: 0em;"><span class="mrow" id="MathJax-Span-2"><span class="semantics" id="MathJax-Span-3"><span class="mstyle" id="MathJax-Span-4"><span class="mrow" id="MathJax-Span-5"><span class="texatom" id="MathJax-Span-6"><span class="mrow" id="MathJax-Span-7"><span class="mfrac" id="MathJax-Span-8"><span style="display: inline-block; position: relative; width: 0.62em; height: 0px; margin-right: 0.12em; margin-left: 0.12em;"><span style="position: absolute; clip: rect(3.121em, 1000.39em, 4.183em, -1000em); top: -4.666em; left: 50%; margin-left: -0.25em;"><span class="mn" id="MathJax-Span-9" style="font-family: STIXGeneral;">1</span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; clip: rect(3.121em, 1000.47em, 4.183em, -1000em); top: -3.304em; left: 50%; margin-left: -0.25em;"><span class="mn" id="MathJax-Span-10" style="font-family: STIXGeneral;">2</span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; clip: rect(0.82em, 1000.62em, 1.287em, -1000em); top: -1.314em; left: 0em;"><span style="display: inline-block; overflow: hidden; vertical-align: 0em; border-top: 1.3px solid; width: 0.62em; height: 0px;"></span><span style="display: inline-block; width: 0px; height: 1.094em;"></span></span></span></span><span class="mo" id="MathJax-Span-11" style="font-family: STIXGeneral; padding-left: 0.25em;">×</span><span class="msqrt" id="MathJax-Span-12" style="padding-left: 0.25em;"><span style="display: inline-block; position: relative; width: 1.977em; height: 0px;"><span style="position: absolute; clip: rect(2.906em, 1001.02em, 4.201em, -1000em); top: -3.99em; left: 0.928em;"><span class="mrow" id="MathJax-Span-13"><span class="msup" id="MathJax-Span-14"><span style="display: inline-block; position: relative; width: 1.024em; height: 0px;"><span style="position: absolute; clip: rect(3.369em, 1000.54em, 4.201em, -1000em); top: -3.99em; left: 0em;"><span class="mi" id="MathJax-Span-15" style="font-family: STIXGeneral; font-style: italic;">π<span style="display: inline-block; overflow: hidden; height: 1px; width: 0.032em;"></span></span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; top: -4.403em; left: 0.596em;"><span class="mn" id="MathJax-Span-16" style="font-size: 70.7%; font-family: STIXGeneral;">2</span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span></span></span></span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; clip: rect(2.977em, 1001.05em, 3.413em, -1000em); top: -4.198em; left: 0.928em;"><span style="display: inline-block; position: relative; width: 1.049em; height: 0px;"><span style="position: absolute; font-family: STIXGeneral; top: -3.99em; left: 0em;">‾<span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; font-family: STIXGeneral; top: -3.99em; left: 0.549em;">‾<span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="font-family: STIXGeneral; position: absolute; top: -3.99em; left: 0.262em;">‾<span style="display: inline-block; width: 0px; height: 3.99em;"></span></span></span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; clip: rect(2.824em, 1000.96em, 4.442em, -1000em); top: -4.045em; left: 0em;"><span style="font-family: STIXGeneral;">√</span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span></span></span></span></span></span></span></span></span><span style="display: inline-block; width: 0px; height: 2.574em;"></span></span></span><span style="display: inline-block; overflow: hidden; vertical-align: -0.833em; border-left: 0px solid; width: 0px; height: 2.406em;"></span></span></nobr><span class="MJX_Assistive_MathML" role="presentation"><math xmlns="http://www.w3.org/1998/Math/MathML"><semantics><mstyle displaystyle="true" scriptlevel="0"><mrow class="MJX-TeXAtom-ORD"><mfrac><mn>1</mn><mn>2</mn></mfrac><mo>×</mo><msqrt><msup><mi>π</mi><mn>2</mn></msup></msqrt></mrow></mstyle><annotation encoding="latex">      \frac{1}{2}\times\sqrt{\pi^2}</annotation></semantics></math></span></span><script type="math/mml" id="MathJax-Element-1"><math><semantics><mstyle displaystyle="true" scriptlevel="0"><mrow class="MJX-TeXAtom-ORD"><mfrac><mn>1</mn><mn>2</mn></mfrac><mo>×</mo><msqrt><msup><mi>π</mi><mn>2</mn></msup></msqrt></mrow></mstyle><annotation encoding="latex">\frac{1}{2}\times\sqrt{\pi^2}</annotation></semantics></math></script></span>',
            output: '<span data-serial="math_5825d67ea3696998688184" data-qti-class="math"><span class="MathJax_Preview" style="color: inherit;"></span><span class="MathJax" id="MathJax-Element-1-Frame" tabindex="0" style="position: relative;" data-mathml="<math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;><semantics><mstyle displaystyle=&quot;true&quot; scriptlevel=&quot;0&quot;><mrow class=&quot;MJX-TeXAtom-ORD&quot;><mfrac><mn>1</mn><mn>2</mn></mfrac><mo>&amp;#xD7;</mo><msqrt><msup><mi>&amp;#x3C0;</mi><mn>2</mn></msup></msqrt></mrow></mstyle><annotation encoding=&quot;latex&quot;>      rac{1}{2}	imessqrt{pi^2}</annotation></semantics></math>" role="presentation"><nobr aria-hidden="true"><span class="math" id="MathJax-Span-1" role="math" style="width: 4.446em; display: inline-block;"><span style="display: inline-block; position: relative; width: 3.99em; height: 0px; font-size: 111%;"><span style="position: absolute; clip: rect(1.028em, 1003.99em, 3.453em, -1000em); top: -2.574em; left: 0em;"><span class="mrow" id="MathJax-Span-2"><span class="semantics" id="MathJax-Span-3"><span class="mstyle" id="MathJax-Span-4"><span class="mrow" id="MathJax-Span-5"><span class="texatom" id="MathJax-Span-6"><span class="mrow" id="MathJax-Span-7"><span class="mfrac" id="MathJax-Span-8"><span style="display: inline-block; position: relative; width: 0.62em; height: 0px; margin-right: 0.12em; margin-left: 0.12em;"><span style="position: absolute; clip: rect(3.121em, 1000.39em, 4.183em, -1000em); top: -4.666em; left: 50%; margin-left: -0.25em;"><span class="mn" id="MathJax-Span-9" style="font-family: STIXGeneral;"><span class="highlighted">1</span></span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; clip: rect(3.121em, 1000.47em, 4.183em, -1000em); top: -3.304em; left: 50%; margin-left: -0.25em;"><span class="mn" id="MathJax-Span-10" style="font-family: STIXGeneral;"><span class="highlighted">2</span></span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; clip: rect(0.82em, 1000.62em, 1.287em, -1000em); top: -1.314em; left: 0em;"><span style="display: inline-block; overflow: hidden; vertical-align: 0em; border-top: 1.3px solid; width: 0.62em; height: 0px;"></span><span style="display: inline-block; width: 0px; height: 1.094em;"></span></span></span></span><span class="mo" id="MathJax-Span-11" style="font-family: STIXGeneral; padding-left: 0.25em;"><span class="highlighted">×</span></span><span class="msqrt" id="MathJax-Span-12" style="padding-left: 0.25em;"><span style="display: inline-block; position: relative; width: 1.977em; height: 0px;"><span style="position: absolute; clip: rect(2.906em, 1001.02em, 4.201em, -1000em); top: -3.99em; left: 0.928em;"><span class="mrow" id="MathJax-Span-13"><span class="msup" id="MathJax-Span-14"><span style="display: inline-block; position: relative; width: 1.024em; height: 0px;"><span style="position: absolute; clip: rect(3.369em, 1000.54em, 4.201em, -1000em); top: -3.99em; left: 0em;"><span class="mi" id="MathJax-Span-15" style="font-family: STIXGeneral; font-style: italic;"><span class="highlighted">π</span><span style="display: inline-block; overflow: hidden; height: 1px; width: 0.032em;"></span></span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; top: -4.403em; left: 0.596em;"><span class="mn" id="MathJax-Span-16" style="font-size: 70.7%; font-family: STIXGeneral;"><span class="highlighted">2</span></span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span></span></span></span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; clip: rect(2.977em, 1001.05em, 3.413em, -1000em); top: -4.198em; left: 0.928em;"><span style="display: inline-block; position: relative; width: 1.049em; height: 0px;"><span style="position: absolute; font-family: STIXGeneral; top: -3.99em; left: 0em;"><span class="highlighted">‾</span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; font-family: STIXGeneral; top: -3.99em; left: 0.549em;"><span class="highlighted">‾</span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="font-family: STIXGeneral; position: absolute; top: -3.99em; left: 0.262em;"><span class="highlighted">‾</span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span></span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span><span style="position: absolute; clip: rect(2.824em, 1000.96em, 4.442em, -1000em); top: -4.045em; left: 0em;"><span style="font-family: STIXGeneral;"><span class="highlighted">√</span></span><span style="display: inline-block; width: 0px; height: 3.99em;"></span></span></span></span></span></span></span></span></span></span><span style="display: inline-block; width: 0px; height: 2.574em;"></span></span></span><span style="display: inline-block; overflow: hidden; vertical-align: -0.833em; border-left: 0px solid; width: 0px; height: 2.406em;"></span></span></nobr><span class="MJX_Assistive_MathML" role="presentation"><math xmlns="http://www.w3.org/1998/Math/MathML"><semantics><mstyle displaystyle="true" scriptlevel="0"><mrow class="MJX-TeXAtom-ORD"><mfrac><mn>1</mn><mn>2</mn></mfrac><mo>×</mo><msqrt><msup><mi>π</mi><mn>2</mn></msup></msqrt></mrow></mstyle><annotation encoding="latex">      rac{1}{2}	imessqrt{pi^2}</annotation></semantics></math></span></span><script type="math/mml" id="MathJax-Element-1"><math><semantics><mstyle displaystyle="true" scriptlevel="0"><mrow class="MJX-TeXAtom-ORD"><mfrac><mn>1</mn><mn>2</mn></mfrac><mo>×</mo><msqrt><msup><mi>π</mi><mn>2</mn></msup></msqrt></mrow></mstyle><annotation encoding="latex">rac{1}{2}	imessqrt{pi^2}</annotation></semantics></math></script></span>'
        };
    }
});
