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
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'lodash',
    'pdfjs-dist/build/pdf',
    'ui/documentViewer/providers/pdfViewer/pdfjs/textManager',
    'ui/documentViewer/providers/pdfViewer/pdfjs/searchEngine'
], function ($, _, pdfjs, textManagerFactory, searchEngineFactory) {
    'use strict';

    var pdfUrl = location.href.replace('/pdfViewer/searchEngine/test.html', '/sample/demo.pdf');
    var pdfjsBackup = {};
    var searchEngineApi;
    var searchCases;


    QUnit.module('pdfViewer SearchEngine factory');


    QUnit.test('module', function (assert) {
        var textManager = textManagerFactory({PDFJS: pdfjs});
        var config = {
            textManager: textManager
        };
        var instance;

        QUnit.expect(2);

        assert.equal(typeof searchEngineFactory, 'function', "The pdfViewer SearchEngine module exposes a function");

        instance = searchEngineFactory(config);
        assert.equal(typeof instance, 'object', "The pdfViewer SearchEngine factory provides an object");

        instance.destroy();
    });


    searchEngineApi = [
        {name: 'getMatches', title: 'getMatches'},
        {name: 'search', title: 'search'},
        {name: 'updateMatches', title: 'updateMatches'},
        {name: 'destroy', title: 'destroy'}
    ];

    QUnit
        .cases(searchEngineApi)
        .test('instance API ', function (data, assert) {
            var textManager = textManagerFactory({PDFJS: pdfjs});
            var config = {
                textManager: textManager
            };
            var instance = searchEngineFactory(config);
            QUnit.expect(1);
            assert.equal(typeof instance[data.name], 'function', 'The pdfViewer SearchEngine instance exposes a "' + data.name + '" function');

            instance.destroy();
        });


    QUnit.module('pdfViewer SearchEngine implementation', {
        setup: function () {
            pdfjsBackup.pageCount = pdfjs.pageCount;
            pdfjsBackup.textContent = pdfjs.textContent;
        },
        teardown: function () {
            pdfjs.removeAllListeners();
            pdfjs.pageCount = pdfjsBackup.pageCount;
            pdfjs.textContent = pdfjsBackup.textContent;
        }
    });


    QUnit.test('error', function (assert) {
        var config = {};

        QUnit.expect(1);

        assert.throws(function () {
            searchEngineFactory(config);
        }, "The pdfViewer SearchEngine factory triggers an error if the text manager is missing");
    });


    QUnit.test('clearMatches', function (assert) {
        var textManager = textManagerFactory({PDFJS: pdfjs});
        var config = {
            textManager: textManager
        };
        var instance = searchEngineFactory(config);

        QUnit.expect(6);

        assert.equal(instance.getMatches(), null, 'There is not search matches at this moment');

        instance.clearMatches();

        assert.ok(instance.getMatches() instanceof Array, 'The list matches has been reset');
        assert.equal(instance.getMatches().length, 0, 'There is no matches');

        instance.getMatches().push([]);
        assert.equal(instance.getMatches().length, 1, 'There is something in the array of matches');

        instance.clearMatches();

        assert.ok(instance.getMatches() instanceof Array, 'The list matches has been reset');
        assert.equal(instance.getMatches().length, 0, 'There is no matches');

        instance.destroy();
        textManager.destroy();
    });


    QUnit.test('setTextManager', function (assert) {
        var textManager1 = textManagerFactory({PDFJS: pdfjs});
        var textManager2 = textManagerFactory({PDFJS: pdfjs});
        var config = {
            textManager: textManager1
        };
        var instance = searchEngineFactory(config);

        QUnit.expect(4);

        assert.equal(typeof instance.getTextManager(), "object", "The getTextManager() method returns an object");
        assert.equal(instance.getTextManager(), textManager1, "The getTextManager() method returns the right object");

        instance.setTextManager(textManager2);
        assert.notEqual(instance.getTextManager(), textManager1, "The text manager has been changed");
        assert.equal(instance.getTextManager(), textManager2, "The getTextManager() method returns the right object");

        instance.destroy();
        textManager1.destroy();
        textManager2.destroy();
    });


    searchCases = [{
        title: 'no case sensitive',
        config: {},
        query: 'will',
        firstPage: 2,
        matches: [
            [],
            [[11, 15]],
            [[10, 14]],
            []
        ],
        pages: [
            'This is a test document',
            'The search will match this page',
            'This Page Will Also Be Matched',
            'This page is the last'
        ]
    }, {
        title: 'case sensitive',
        config: {caseSensitive: true},
        query: 'Will',
        firstPage: 3,
        matches: [
            [],
            [],
            [[10, 14]],
            []
        ],
        pages: [
            'This is a test document',
            'The search will not match this page',
            'This Page Will Be Matched',
            'This page is the last'
        ]
    }, {
        title: 'content special chars',
        config: {},
        query: 'Will',
        firstPage: 2,
        matches: [
            [],
            [[12, 16]],
            [[11, 15]],
            []
        ],
        pages: [
            'This is a test document',
            'The search \u201Cwill\u201D match this page',
            'This Page \u201CWill\u201D Also Be Matched',
            'This page is the last'
        ]
    }, {
        title: 'using special chars',
        config: {},
        query: '\u201Cwill\u201D',
        firstPage: 2,
        matches: [
            [],
            [[11, 17]],
            [[10, 16]],
            []
        ],
        pages: [
            'This is a test document',
            'The search \u201Cwill\u201D match this page',
            'This Page \u201CWill\u201D Also Be Matched',
            'This page is the last'
        ]
    }];

    QUnit
        .cases(searchCases)
        .asyncTest('search', function (data, assert) {
            var textManager = textManagerFactory({PDFJS: pdfjs});
            var config = _.merge({
                textManager: textManager
            }, data.config);
            var instance = searchEngineFactory(config);

            QUnit.expect(5);

            pdfjs.textContent = data.pages;
            pdfjs.pageCount = pdfjs.textContent.length;

            assert.equal(instance.getMatches(), null, 'There is not search matches at this moment');

            pdfjs.getDocument(pdfUrl).then(function (pdf) {
                textManager.setDocument(pdf);

                return instance.search(data.query).then(function (pageNum) {
                    assert.equal(pageNum, data.firstPage, 'The search has found the terms and returned the right page number');
                    assert.ok(instance.getMatches() instanceof Array, 'There is now some search matches');
                    assert.equal(instance.getMatches().length, pdfjs.pageCount, 'The matches collection contains the same numbers than the amount of pages');
                    assert.deepEqual(instance.getMatches(), data.matches, 'The search has find the expected matches');

                    instance.destroy();

                    QUnit.start();
                });
            }).catch(function () {
                assert.ok('false', 'No error should be triggered');
                QUnit.start();
            });
        });


    QUnit.asyncTest('updateMatches', function (assert) {
        var textManager = textManagerFactory({PDFJS: pdfjs});
        var config = {
            textManager: textManager
        };
        var instance = searchEngineFactory(config);
        var searchQuery = 'page';
        var expectedPage = 1;
        var expectedMatches = [
            [[29, 33], [47, 51], [71, 75]]
        ];

        QUnit.expect(18);

        pdfjs.textContent = [
            ['The search should ','match this pa','ge because this p','ag','e contains',' the word',' \u201Cpage\u201D!']
        ];
        pdfjs.pageCount = pdfjs.textContent.length;

        assert.equal(instance.getMatches(), null, 'There is not search matches at this moment');

        pdfjs.getDocument(pdfUrl).then(function (pdf) {
            textManager.setDocument(pdf);

            return pdf.getPage(1).then(function (page) {
                return instance.updateMatches(1).then(function () {
                    assert.ok(true, 'There is no matches, but the updateMatches has resolved the promise');

                    return instance.search(searchQuery).then(function (pageNum) {
                        assert.equal(pageNum, expectedPage, 'The search has found the terms and returned the right page number');
                        assert.ok(instance.getMatches() instanceof Array, 'There is now some search matches');
                        assert.equal(instance.getMatches().length, pdfjs.pageCount, 'The matches collection contains the same numbers than the amount of pages');
                        assert.deepEqual(instance.getMatches(), expectedMatches, 'The search has find the expected matches');

                        return textManager.renderPage(pageNum, page.getViewport()).then(function(layer) {
                            return instance.updateMatches(pageNum).then(function () {
                                var $container = $('<div />').append(layer);
                                var $matches = $container.find('span');

                                assert.equal($matches.length, 6, 'There is the right number of highlighted matches');
                                assert.equal($matches.eq(0).text(), 'pa', 'The highlighted span contains the right text');
                                assert.equal($matches.eq(1).text(), 'ge', 'The highlighted span contains the right text');
                                assert.equal($matches.eq(2).text(), 'p', 'The highlighted span contains the right text');
                                assert.equal($matches.eq(3).text(), 'ag', 'The highlighted span contains the right text');
                                assert.equal($matches.eq(4).text(), 'e', 'The highlighted span contains the right text');
                                assert.equal($matches.eq(5).text(), 'page', 'The highlighted span contains the right text');
                            });
                        });
                    }).then(function() {
                        return instance.search('unknown').then(function (pageNum) {
                            assert.equal(pageNum, 0, 'The search has not found any terms');
                            assert.ok(instance.getMatches() instanceof Array, 'There is matches array');
                            assert.equal(instance.getMatches().length, pdfjs.pageCount, 'The matches collection contains the same numbers than the amount of pages');
                            assert.deepEqual(instance.getMatches(), [[]], 'The search has not find any matches, as expected');

                            return textManager.renderPage(pageNum, page.getViewport()).then(function(layer) {
                                return instance.updateMatches(pageNum).then(function () {
                                    var $container = $('<div />').append(layer);
                                    var $matches = $container.find('span');

                                    assert.equal($matches.length, 0, 'There is no highlighted matches');

                                    instance.destroy();

                                    QUnit.start();
                                });
                            });
                        });
                    });
                });
            });
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.test('destroy', function (assert) {
        var textManager = textManagerFactory({PDFJS: pdfjs});
        var config = {
            textManager: textManager
        };
        var instance = searchEngineFactory(config);

        QUnit.expect(2);

        assert.equal(instance.getTextManager(), textManager, "The getTextManager() method returns the right object");

        instance.destroy();

        assert.equal(instance.getTextManager(), null, "The searchEngine instance has forgotten the text manager");
    });

});
