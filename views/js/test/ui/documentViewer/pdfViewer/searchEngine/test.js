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
        {name: 'getPages', title: 'getPages'},
        {name: 'getMatches', title: 'getMatches'},
        {name: 'getMatchCount', title: 'getMatchCount'},
        {name: 'clearMatches', title: 'clearMatches'},
        {name: 'setTextManager', title: 'setTextManager'},
        {name: 'getTextManager', title: 'getTextManager'},
        {name: 'getQuery', title: 'getQuery'},
        {name: 'getCurrentMatch', title: 'getCurrentMatch'},
        {name: 'previousMatch', title: 'previousMatch'},
        {name: 'nextMatch', title: 'nextMatch'},
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

        QUnit.expect(10);

        assert.ok(instance.getMatches() instanceof Array, 'The list of matches is defined');
        assert.equal(instance.getMatches().length, 0, 'There is no matches');

        assert.ok(instance.getPages() instanceof Array, 'The list of pages has been defined');
        assert.equal(instance.getPages().length, 0, 'There is no pages');

        instance.getMatches().push([]);
        assert.equal(instance.getMatches().length, 1, 'There is something in the array of matches');

        instance.getPages().push(1);
        assert.equal(instance.getPages().length, 1, 'There is something in the array of pages');

        instance.clearMatches();

        assert.ok(instance.getMatches() instanceof Array, 'The list of matches has been reset');
        assert.equal(instance.getMatches().length, 0, 'There is no matches');

        assert.ok(instance.getPages() instanceof Array, 'The list of pages has been reset');
        assert.equal(instance.getPages().length, 0, 'There is no pages');

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
        count: 2,
        matches: [
            [],
            [[11, 15]],
            [[10, 14]],
            []
        ],
        pageNumbers: [2, 3],
        pages: [
            'This is a test document',
            'The search will match this page',
            'This Page Will Also Be Matched',
            'This page is the last'
        ]
    }, {
        title: 'search from page',
        config: {},
        query: 'will',
        currentPage: 3,
        firstPage: 3,
        count: 2,
        matches: [
            [],
            [[11, 15]],
            [[10, 14]],
            []
        ],
        pageNumbers: [2, 3],
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
        count: 1,
        matches: [
            [],
            [],
            [[10, 14]],
            []
        ],
        pageNumbers: [3],
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
        count: 2,
        matches: [
            [],
            [[12, 16]],
            [[11, 15]],
            []
        ],
        pageNumbers: [2, 3],
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
        count: 2,
        matches: [
            [],
            [[11, 17]],
            [[10, 16]],
            []
        ],
        pageNumbers: [2, 3],
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

            QUnit.expect(13);

            pdfjs.textContent = data.pages;
            pdfjs.pageCount = pdfjs.textContent.length;

            assert.deepEqual(instance.getMatches(), [], 'There is not search matches at this moment');
            assert.deepEqual(instance.getPages(), [], 'There is not search pages at this moment');
            assert.equal(instance.getQuery(), null, 'There is not search query at this moment');
            assert.equal(instance.getCurrentMatch(), null, 'There is no match at this moment');

            pdfjs.getDocument(pdfUrl).then(function (pdf) {
                textManager.setDocument(pdf);

                return instance.search(data.query, data.currentPage).then(function (pageNum) {
                    assert.equal(pageNum, data.firstPage, 'The search has found the terms and returned the right page number');
                    assert.equal(instance.getMatches().length, pdfjs.pageCount, 'The matches collection contains the same numbers than the amount of pages');
                    assert.deepEqual(instance.getMatches(), data.matches, 'The search has find the expected matches');
                    assert.deepEqual(instance.getPages(), data.pageNumbers, 'The search has find matches in the expected pages');
                    assert.equal(instance.getMatchCount(), data.count, 'There is the right number of matches');
                    assert.equal(instance.getQuery(), data.query, 'The current query is stored');
                    assert.equal(typeof instance.getCurrentMatch(), 'object', 'There is now a match');
                    assert.equal(instance.getCurrentMatch().page, data.firstPage, 'The current match target the right page');
                    assert.equal(instance.getCurrentMatch().index, 0, 'The current match target the right index');

                    instance.destroy();

                    QUnit.start();
                });
            }).catch(function () {
                assert.ok('false', 'No error should be triggered');
                QUnit.start();
            });
        });


    QUnit.asyncTest('navigating in search', function (assert) {
        var textManager = textManagerFactory({PDFJS: pdfjs});
        var config = {
            textManager: textManager
        };

        var query = 'page';
        var currentPage = 3;
        var count = 4;
        var matches = [
            [],
            [[27, 31], [46, 50]],
            [],
            [[5, 9]],
            [[5, 9]]
        ];
        var firstMatch = {
            overall: 3,
            page: 4,
            index: 0
        };
        var matchesPathPrevious = [{
            loop: false,
            overall: 2,
            page: 2,
            index: 1
        }, {
            loop: false,
            overall: 1,
            page: 2,
            index: 0
        }, {
            loop: true,
            overall: 4,
            page: 5,
            index: 0
        }, {
            loop: false,
            overall: 3,
            page: 4,
            index: 0
        }];
        var matchesPathNext = [{
            loop: false,
            overall: 4,
            page: 5,
            index: 0
        }, {
            loop: true,
            overall: 1,
            page: 2,
            index: 0
        }, {
            loop: false,
            overall: 2,
            page: 2,
            index: 1
        }, {
            loop: false,
            overall: 3,
            page: 4,
            index: 0
        }];
        var pageNumbers = [2, 4, 5];
        var pages = [
            ['This is a test document'],
            ['The search will match this page. ', 'Because this page contains the searched terms'],
            ['This is a test document'],
            ['This Page Will Also Be Matched.'],
            ['This page is the last']
        ];
        var instance = searchEngineFactory(config);
        var match, loop;

        QUnit.expect(14 + 6 * (matchesPathPrevious.length + matchesPathNext.length));

        pdfjs.textContent = pages;
        pdfjs.pageCount = pdfjs.textContent.length;

        assert.deepEqual(instance.getMatches(), [], 'There is not search matches at this moment');
        assert.deepEqual(instance.getPages(), [], 'There is not search pages at this moment');
        assert.equal(instance.getCurrentMatch(), null, 'There is no match at this moment');
        assert.equal(instance.getQuery(), null, 'There is not search query at this moment');

        pdfjs.getDocument(pdfUrl).then(function (pdf) {
            textManager.setDocument(pdf);

            return instance.search(query, currentPage).then(function (pageNum) {
                assert.equal(pageNum, firstMatch.page, 'The search has found the terms and returned the right page number');
                assert.equal(instance.getMatches().length, pdfjs.pageCount, 'The matches collection contains the same numbers than the amount of pages');
                assert.deepEqual(instance.getMatches(), matches, 'The search has find the expected matches');
                assert.deepEqual(instance.getPages(), pageNumbers, 'The search has find matches in the expected pages');
                assert.equal(instance.getMatchCount(), count, 'There is the right number of matches');

                match = instance.getCurrentMatch();
                assert.equal(typeof match, 'object', 'There is now a match');
                assert.equal(match.page, firstMatch.page, 'The current match target the right page');
                assert.equal(match.index, firstMatch.index, 'The current match target the right index');
                assert.equal(match.overall, firstMatch.overall, 'The current match target the right overall index');
                assert.equal(instance.getQuery(), query, 'The current query is stored');

                _.forEach(matchesPathPrevious, function (expectedMatch) {
                    loop = instance.previousMatch();
                    match = instance.getCurrentMatch();
                    assert.equal(typeof loop, 'boolean', 'The previousMatch() method returned a boolean');
                    assert.equal(typeof match, 'object', 'The previous match has been provided');
                    assert.equal(loop, expectedMatch.loop, 'We can navigate seamlessly across match all over the document');
                    assert.equal(match.page, expectedMatch.page, 'The current match target the right page');
                    assert.equal(match.index, expectedMatch.index, 'The current match target the right index');
                    assert.equal(match.overall, expectedMatch.overall, 'The current match target the right overall index');
                });

                _.forEach(matchesPathNext, function (expectedMatch) {
                    loop = instance.nextMatch();
                    match = instance.getCurrentMatch();
                    assert.equal(typeof loop, 'boolean', 'The nextMatch() method returned a boolean');
                    assert.equal(typeof match, 'object', 'The previous match has been provided');
                    assert.equal(loop, expectedMatch.loop, 'We can navigate seamlessly across match all over the document');
                    assert.equal(match.page, expectedMatch.page, 'The current match target the right page');
                    assert.equal(match.index, expectedMatch.index, 'The current match target the right index');
                    assert.equal(match.overall, expectedMatch.overall, 'The current match target the right overall index');
                });

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

        QUnit.expect(28);

        pdfjs.textContent = [
            ['The search should ', 'match this pa', 'ge because this p', 'ag', 'e contains', ' the word', ' \u201Cpage\u201D!']
        ];
        pdfjs.pageCount = pdfjs.textContent.length;

        assert.deepEqual(instance.getMatches(), [], 'There is not search matches at this moment');

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

                        return textManager.renderPage(pageNum, page.getViewport()).then(function (layer) {
                            return instance.updateMatches(pageNum).then(function (num) {
                                var $container = $('<div />').append(layer);
                                var $matches = $container.find('span');

                                assert.equal(num, pageNum, 'The page number has been provided');
                                assert.equal($matches.length, 6, 'There is the right number of highlighted matches');

                                assert.ok($matches.eq(0).hasClass('selected'), 'The first match is selected');
                                assert.equal($matches.eq(0).text(), 'pa', 'The highlighted span contains the right text');
                                assert.equal($matches.eq(0).data('match'), '0', 'The highlighted span is related to the right match');
                                assert.ok($matches.eq(1).hasClass('selected'), 'The first match is selected');
                                assert.equal($matches.eq(1).text(), 'ge', 'The highlighted span contains the right text');
                                assert.equal($matches.eq(1).data('match'), '0', 'The highlighted span is related to the right match');

                                assert.equal($matches.eq(2).text(), 'p', 'The highlighted span contains the right text');
                                assert.equal($matches.eq(2).data('match'), '1', 'The highlighted span is related to the right match');
                                assert.equal($matches.eq(3).text(), 'ag', 'The highlighted span contains the right text');
                                assert.equal($matches.eq(3).data('match'), '1', 'The highlighted span is related to the right match');
                                assert.equal($matches.eq(4).text(), 'e', 'The highlighted span contains the right text');
                                assert.equal($matches.eq(4).data('match'), '1', 'The highlighted span is related to the right match');

                                assert.equal($matches.eq(5).text(), 'page', 'The highlighted span contains the right text');
                                assert.equal($matches.eq(5).data('match'), '2', 'The highlighted span is related to the right match');
                            });
                        });
                    }).then(function () {
                        return instance.search('unknown').then(function (pageNum) {
                            assert.equal(pageNum, 0, 'The search has not found any terms');
                            assert.ok(instance.getMatches() instanceof Array, 'There is matches array');
                            assert.equal(instance.getMatches().length, pdfjs.pageCount, 'The matches collection contains the same numbers than the amount of pages');
                            assert.deepEqual(instance.getMatches(), [[]], 'The search has not find any matches, as expected');

                            return textManager.renderPage(pageNum, page.getViewport()).then(function (layer) {
                                return instance.updateMatches(pageNum).then(function (num) {
                                    var $container = $('<div />').append(layer);
                                    var $matches = $container.find('span');

                                    assert.equal(num, pageNum, 'The page number has been provided');
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
