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
    'core/eventifier',
    'lib/simulator/jquery.keystroker',
    'pdfjs-dist/build/pdf',
    'ui/documentViewer/providers/pdfViewer/pdfjs/areaBroker',
    'ui/documentViewer/providers/pdfViewer/pdfjs/textManager',
    'ui/documentViewer/providers/pdfViewer/pdfjs/wrapper',
    'ui/documentViewer/providers/pdfViewer/pdfjs/findBar',
    'tpl!ui/documentViewer/providers/pdfViewer/pdfjs/viewer'
], function ($, _, eventifier, keystroker, pdfjs, areaBroker, textManagerFactory, wrapperFactory, findBarFactory, viewerTpl) {
    'use strict';

    var pdfUrl = location.href.replace('/pdfViewer/findBar/test.html', '/sample/demo.pdf');
    var pdfjsBackup = {};
    var findBarApi;


    QUnit.module('pdfViewer FindBar factory', {
        teardown: function () {
            pdfjs.removeAllListeners();
        }
    });


    QUnit.test('module', function (assert) {
        var textManager = textManagerFactory({PDFJS: pdfjs});
        var events = eventifier();
        var $container = $('<div />').append(viewerTpl());
        var broker = areaBroker($container, {
            bar: $('.pdf-bar', $container),
            actions: $('.pdf-actions', $container),
            info: $('.pdf-info', $container),
            content: $('.pdf-container', $container)
        });
        var config = {
            events: events,
            areaBroker: broker,
            textManager: textManager
        };
        var instance;

        QUnit.expect(2);

        assert.equal(typeof findBarFactory, 'function', "The pdfViewer FindBar module exposes a function");

        instance = findBarFactory(config);
        assert.equal(typeof instance, 'object', "The pdfViewer FindBar factory provides an object");

        instance.destroy();
        textManager.destroy();
    });


    findBarApi = [
        {name: 'getSearchEngine', title: 'getSearchEngine'},
        {name: 'destroy', title: 'destroy'}
    ];

    QUnit
        .cases(findBarApi)
        .test('instance API ', function (data, assert) {
            var textManager = textManagerFactory({PDFJS: pdfjs});
            var events = eventifier();
            var $container = $('<div />').append(viewerTpl());
            var broker = areaBroker($container, {
                bar: $('.pdf-bar', $container),
                actions: $('.pdf-actions', $container),
                info: $('.pdf-info', $container),
                content: $('.pdf-container', $container)
            });
            var config = {
                events: events,
                areaBroker: broker,
                textManager: textManager
            };
            var instance = findBarFactory(config);
            QUnit.expect(1);
            assert.equal(typeof instance[data.name], 'function', 'The pdfViewer FindBar instance exposes a "' + data.name + '" function');

            instance.destroy();
            textManager.destroy();
        });


    QUnit.module('pdfViewer FindBar implementation', {
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
        var textManager = textManagerFactory({PDFJS: pdfjs});
        var events = eventifier();
        var $container = $('<div />').append(viewerTpl());
        var broker = areaBroker($container, {
            bar: $('.pdf-bar', $container),
            actions: $('.pdf-actions', $container),
            info: $('.pdf-info', $container),
            content: $('.pdf-container', $container)
        });

        QUnit.expect(3);

        assert.throws(function () {
            findBarFactory({events: events, areaBroker: broker});
        }, "The pdfViewer FindBar factory triggers an error if the text manager is missing");

        assert.throws(function () {
            findBarFactory({events: events, textManager: textManager});
        }, "The pdfViewer FindBar factory triggers an error if the area broker is missing");

        assert.throws(function () {
            findBarFactory({areaBroker: broker, textManager: textManager});
        }, "The pdfViewer FindBar factory triggers an error if the events hub is missing");

        textManager.destroy();
    });


    QUnit.test('search button', function (assert) {
        var textManager = textManagerFactory({PDFJS: pdfjs});
        var events = eventifier();
        var $container = $('<div />').append(viewerTpl());
        var broker = areaBroker($container, {
            bar: $('.pdf-bar', $container),
            actions: $('.pdf-actions', $container),
            info: $('.pdf-info', $container),
            content: $('.pdf-container', $container)
        });
        var config = {
            events: events,
            areaBroker: broker,
            textManager: textManager
        };
        var instance = findBarFactory(config);

        QUnit.expect(7);

        assert.equal($('[data-control="pdf-search"]', $container).length, 1, 'The search button has been added');
        assert.equal($('.pdf-find-bar', $container).length, 1, 'The find bar has been added');
        assert.ok($('.pdf-find-bar', $container).hasClass('hidden'), 'The find bar is hidden');

        $('[data-control="pdf-search"]', $container).click();

        assert.ok(!$('.pdf-find-bar', $container).hasClass('hidden'), 'The find bar is shown');

        $('[data-control="pdf-search"]', $container).click();

        assert.ok($('.pdf-find-bar', $container).hasClass('hidden'), 'The find bar is hidden');

        $('[data-control="pdf-search"]', $container).click();

        assert.ok(!$('.pdf-find-bar', $container).hasClass('hidden'), 'The find bar is shown');

        keystroker.keystroke($('[data-control="pdf-search-query"]', $container), keystroker.keyCode.ESCAPE);

        assert.ok($('.pdf-find-bar', $container).hasClass('hidden'), 'The find bar is hidden');

        instance.destroy();
        textManager.destroy();
    });


    QUnit.test('enable/disable', function (assert) {
        var textManager = textManagerFactory({PDFJS: pdfjs});
        var events = eventifier();
        var $container = $('<div />').append(viewerTpl());
        var broker = areaBroker($container, {
            bar: $('.pdf-bar', $container),
            actions: $('.pdf-actions', $container),
            info: $('.pdf-info', $container),
            content: $('.pdf-container', $container)
        });
        var config = {
            events: events,
            areaBroker: broker,
            textManager: textManager
        };
        var instance = findBarFactory(config);

        QUnit.expect(9);

        assert.equal($('[data-control="pdf-search"]', $container).length, 1, 'The search button has been added');
        assert.equal($('.pdf-find-bar', $container).length, 1, 'The find bar has been added');
        assert.ok($('.pdf-find-bar', $container).hasClass('hidden'), 'The find bar is hidden');
        assert.ok(!$('[data-control="pdf-search"]', $container).attr('disabled'), 'The search button is enabled');

        $('[data-control="pdf-search"]', $container).click();

        assert.ok(!$('.pdf-find-bar', $container).hasClass('hidden'), 'The find bar is shown');

        events.trigger('disable');

        assert.ok(!!$('[data-control="pdf-search"]', $container).attr('disabled'), 'The search button is disabled');
        assert.ok($('.pdf-find-bar', $container).hasClass('hidden'), 'The find bar is hidden');

        events.trigger('enable');

        assert.ok(!$('[data-control="pdf-search"]', $container).attr('disabled'), 'The search button is enabled');
        assert.ok($('.pdf-find-bar', $container).hasClass('hidden'), 'The find bar is hidden');

        instance.destroy();
        textManager.destroy();
    });


    QUnit.test('highlight all', function (assert) {
        var textManager = textManagerFactory({PDFJS: pdfjs});
        var events = eventifier();
        var $container = $('<div />').append(viewerTpl());
        var broker = areaBroker($container, {
            bar: $('.pdf-bar', $container),
            actions: $('.pdf-actions', $container),
            info: $('.pdf-info', $container),
            content: $('.pdf-container', $container)
        });
        var config = {
            events: events,
            areaBroker: broker,
            textManager: textManager
        };
        var instance = findBarFactory(config);

        QUnit.expect(14);

        assert.equal($('[data-control="pdf-search"]', $container).length, 1, 'The search button has been added');
        assert.equal($('.pdf-find-bar', $container).length, 1, 'The find bar has been added');

        assert.ok(!$('[data-control="highlight-all"]', $container).is(':checked'), 'The highlightAll option is not checked');
        assert.ok(!broker.getContentArea().hasClass('highlight-all'), 'The highlightAll option is not activated');

        $('[data-control="highlight-all"]', $container).prop('checked', true).change();

        assert.ok($('[data-control="highlight-all"]', $container).is(':checked'), 'The highlightAll option is checked');
        assert.ok(broker.getContentArea().hasClass('highlight-all'), 'The highlightAll option is activated');

        instance.destroy();

        assert.equal($('[data-control="pdf-search"]', $container).length, 0, 'The search button has been removed');
        assert.equal($('.pdf-find-bar', $container).length, 0, 'The find bar has been removed');

        config.highlightAll = true;
        instance = findBarFactory(config);

        assert.equal($('[data-control="pdf-search"]', $container).length, 1, 'The search button has been added');
        assert.equal($('.pdf-find-bar', $container).length, 1, 'The find bar has been added');

        assert.ok($('[data-control="highlight-all"]', $container).is(':checked'), 'The highlightAll option is checked');
        assert.ok(broker.getContentArea().hasClass('highlight-all'), 'The highlightAll option is activated');

        $('[data-control="highlight-all"]', $container).prop('checked', false).change();

        assert.ok(!$('[data-control="highlight-all"]', $container).is(':checked'), 'The highlightAll option is not checked');
        assert.ok(!broker.getContentArea().hasClass('highlight-all'), 'The highlightAll option is not activated');

        instance.destroy();
        textManager.destroy();
    });


    QUnit.asyncTest('search', function (assert) {
        var $container = $('<div />').append(viewerTpl());
        var broker = areaBroker($container, {
            bar: $('.pdf-bar', $container),
            actions: $('.pdf-actions', $container),
            info: $('.pdf-info', $container),
            content: $('.pdf-container', $container)
        });
        var events = eventifier();
        var pdf = wrapperFactory(broker.getContentArea(), {PDFJS: pdfjs, events: events});
        var config = {
            events: events,
            areaBroker: broker,
            textManager: pdf.getTextManager()
        };
        var instance = findBarFactory(config);
        var expectedQuery = 'page';
        var expectedPage = 2;
        var expectedPageEmpty = 0;
        var expectedCount = 4;
        var pages = [
            ['This is a test document'],
            ['The search will match this page. ', 'Because this page contains the searched terms'],
            ['This is a test document'],
            ['This Page Will Also Be Matched.'],
            ['This page is the last']
        ];

        QUnit.expect(32);

        pdfjs.textContent = pages;
        pdfjs.pageCount = pdfjs.textContent.length;

        assert.equal($('[data-control="pdf-search"]', $container).length, 1, 'The search button has been added');
        assert.equal($('.pdf-find-bar', $container).length, 1, 'The find bar has been added');

        pdf.load(pdfUrl).then(function () {
            assert.ok(pdf.getState('loaded'), 'The PDF is loaded');

            events.on('searching.searchSomething', function (query) {
                assert.ok(true, 'The search is running');
                assert.equal(query, expectedQuery, 'The search engine is searching for the expected query');
            }).on('searchdone.searchSomething', function (query, page) {
                assert.ok(true, 'The search is done');
                assert.equal(query, expectedQuery, 'The search engine has searched for the expected query');
                assert.equal(page, expectedPage, 'The search engine has found a match on the expected page');
                assert.equal(instance.getSearchEngine().getMatchCount(), expectedCount, 'The search has found the expected matches');
            }).on('setpage.searchSomething', function (page) {
                assert.equal(page, expectedPage, 'The find bar has set the right page');
            }).on('pagechange.searchSomething', function (page) {
                assert.equal(page, expectedPage, 'The page has been changed');
            }).on('allrendered.searchSomething', function (page) {
                assert.equal(page, expectedPage, 'The page has been rendered');
            }).on('matchesupdating.searchSomething', function (page) {
                assert.equal(page, expectedPage, 'The find bar is displaying the matches');
            }).on('matchesupdated.searchSomething', function (page) {
                assert.equal(page, expectedPage, 'The find bar has displayed the matches');

                assert.equal($('.selected', broker.getContentArea()).length, 1, 'There is a selected match');

                events.off('.searchSomething').on('searching.emptySearch', function () {
                    assert.ok(false, 'The search must not be running');
                }).on('searchdone.emptySearch', function () {
                    assert.ok(false, 'The search must not be done');
                }).on('refresh.emptySearch', function () {
                    assert.ok(true, 'The find bar has refreshed the page');
                }).on('allrendered.emptySearch', function (page) {
                    assert.equal(page, expectedPage, 'The page has been rendered');
                }).on('matchesupdating.emptySearch', function (page) {
                    assert.equal(page, expectedPage, 'The find bar is displaying the matches');
                }).on('matchesupdated.emptySearch', function (page) {
                    assert.equal(page, expectedPage, 'The find bar has displayed the matches');
                    assert.equal(instance.getSearchEngine().getMatchCount(), 0, 'The search has not found any matches');

                    assert.equal($('.selected', broker.getContentArea()).length, 0, 'There is no selected match');

                    events.off('.emptySearch').on('searching.searchUnknown', function (query) {
                        assert.ok(true, 'The search is running');
                        assert.equal(query, expectedQuery, 'The search engine is searching for the expected query');
                    }).on('searchdone.searchUnknown', function (query, page) {
                        assert.ok(true, 'The search is done');
                        assert.equal(query, expectedQuery, 'The search engine has searched for the expected query');
                        assert.equal(page, expectedPageEmpty, 'The search engine has found a match on the expected page');
                        assert.equal(instance.getSearchEngine().getMatchCount(), 0, 'The search has not found any matches');
                    }).on('refresh.searchUnknown', function () {
                        assert.ok(true, 'The find bar has refreshed the page');
                    }).on('allrendered.searchUnknown', function (page) {
                        assert.equal(page, expectedPage, 'The page has been rendered');
                    }).on('matchesupdating.searchUnknown', function (page) {
                        assert.equal(page, expectedPage, 'The find bar is displaying the matches');
                    }).on('matchesupdated.searchUnknown', function (page) {
                        assert.equal(page, expectedPage, 'The find bar has displayed the matches');

                        assert.equal($('.selected', broker.getContentArea()).length, 0, 'There is a selected match');

                        instance.destroy();
                        pdf.destroy();
                        QUnit.start();
                    });

                    expectedQuery = 'unknown';
                    $('[data-control="pdf-search-query"]', $container).val(expectedQuery).keypress();
                });

                expectedQuery = '';
                $('[data-control="pdf-search-query"]', $container).val(expectedQuery).keypress();
            });

            $('[data-control="pdf-search-query"]', $container).val(expectedQuery).keypress();
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('search case sensitive', function (assert) {
        var $container = $('<div />').append(viewerTpl());
        var broker = areaBroker($container, {
            bar: $('.pdf-bar', $container),
            actions: $('.pdf-actions', $container),
            info: $('.pdf-info', $container),
            content: $('.pdf-container', $container)
        });
        var events = eventifier();
        var pdf = wrapperFactory(broker.getContentArea(), {PDFJS: pdfjs, events: events});
        var config = {
            events: events,
            areaBroker: broker,
            textManager: pdf.getTextManager()
        };
        var instance = findBarFactory(config);
        var expectedQuery = 'Page';
        var expectedPage = 2;
        var expectedPageCaseSensitive = 4;
        var expectedCount = 3;
        var pages = [
            ['This is a test document'],
            ['The search will match this page first. '],
            ['This is a test document'],
            ['Once the case sensitive option will be set, this Page will be matched first.'],
            ['This page is the last']
        ];

        QUnit.expect(26);

        pdfjs.textContent = pages;
        pdfjs.pageCount = pdfjs.textContent.length;

        assert.equal($('[data-control="pdf-search"]', $container).length, 1, 'The search button has been added');
        assert.equal($('.pdf-find-bar', $container).length, 1, 'The find bar has been added');
        assert.ok(!$('[data-control="case-sensitive-search"]', $container).is(':checked'), 'The caseSensitive option is not checked');

        pdf.load(pdfUrl).then(function () {
            assert.ok(pdf.getState('loaded'), 'The PDF is loaded');

            events.on('searching.notCaseSensitive', function (query) {
                assert.ok(true, 'The search is running');
                assert.equal(query, expectedQuery, 'The search engine is searching for the expected query');
            }).on('searchdone.notCaseSensitive', function (query, page) {
                assert.ok(true, 'The search is done');
                assert.equal(query, expectedQuery, 'The search engine has searched for the expected query');
                assert.equal(page, expectedPage, 'The search engine has found a match on the expected page');
                assert.equal(instance.getSearchEngine().getMatchCount(), expectedCount, 'The search has found the expected matches');
            }).on('setpage.notCaseSensitive', function (page) {
                assert.equal(page, expectedPage, 'The find bar has set the right page');
            }).on('pagechange.notCaseSensitive', function (page) {
                assert.equal(page, expectedPage, 'The page has been changed');
            }).on('allrendered.notCaseSensitive', function (page) {
                assert.equal(page, expectedPage, 'The page has been rendered');
            }).on('matchesupdating.notCaseSensitive', function (page) {
                assert.equal(page, expectedPage, 'The find bar is displaying the matches');
            }).on('matchesupdated.notCaseSensitive', function (page) {
                assert.equal(page, expectedPage, 'The find bar has displayed the matches');

                events.off('.notCaseSensitive').on('searching', function (query) {
                    assert.ok(true, 'The search is running');
                    assert.equal(query, expectedQuery, 'The search engine is searching for the expected query');
                }).on('searchdone', function (query, page) {
                    assert.ok(true, 'The search is done');
                    assert.equal(query, expectedQuery, 'The search engine has searched for the expected query');
                    assert.equal(page, expectedPageCaseSensitive, 'The search engine has found a match on the expected page');
                    assert.equal(instance.getSearchEngine().getMatchCount(), expectedCount, 'The search has found the expected matches');
                }).on('setpage', function (page) {
                    assert.equal(page, expectedPageCaseSensitive, 'The find bar has set the right page');
                }).on('pagechange', function (page) {
                    assert.equal(page, expectedPageCaseSensitive, 'The page has been changed');
                }).on('allrendered', function (page) {
                    assert.equal(page, expectedPageCaseSensitive, 'The page has been rendered');
                }).on('matchesupdating', function (page) {
                    assert.equal(page, expectedPageCaseSensitive, 'The find bar is displaying the matches');
                }).on('matchesupdated', function (page) {
                    assert.equal(page, expectedPageCaseSensitive, 'The find bar has displayed the matches');

                    instance.destroy();
                    pdf.destroy();
                    QUnit.start();
                });

                expectedCount = 1;
                $('[data-control="case-sensitive-search"]', $container).prop('checked', true).change();
            });

            $('[data-control="pdf-search-query"]', $container).val(expectedQuery).keypress();
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('navigating in search', function (assert) {
        var $container = $('<div />').append(viewerTpl());
        var broker = areaBroker($container, {
            bar: $('.pdf-bar', $container),
            actions: $('.pdf-actions', $container),
            info: $('.pdf-info', $container),
            content: $('.pdf-container', $container)
        });
        var events = eventifier();
        var pdf = wrapperFactory(broker.getContentArea(), {PDFJS: pdfjs, events: events});
        var config = {
            events: events,
            areaBroker: broker,
            textManager: pdf.getTextManager()
        };
        var instance = findBarFactory(config);
        var expectedQuery = 'page';
        var expectedPage = 2;
        var expectedCount = 4;
        var expectedMatches = [
            [],
            [[27, 31], [46, 50]],
            [],
            [[5, 9]],
            [[5, 9]]
        ];
        var pages = [
            ['This is a test document'],
            ['The search will match this page. ', 'Because this page contains the searched terms'],
            ['This is a test document'],
            ['This Page Will Also Be Matched.'],
            ['This page is the last']
        ];
        var cursor = 0;
        var current = null;
        var navigationPath = [{
            loopBegin: false,
            loopEnd: false,
            overall: 1,
            page: 2,
            index: 0,
            clickOn: '[data-control="pdf-search-next"]'
        },{
            loopBegin: false,
            loopEnd: false,
            overall: 2,
            page: 2,
            index: 1,
            clickOn: '[data-control="pdf-search-next"]'
        },{
            loopBegin: false,
            loopEnd: false,
            overall: 3,
            page: 4,
            index: 0,
            clickOn: '[data-control="pdf-search-next"]'
        },{
            loopBegin: false,
            loopEnd: false,
            overall: 4,
            page: 5,
            index: 0,
            clickOn: '[data-control="pdf-search-next"]'
        },{
            loopBegin: true,
            loopEnd: false,
            overall: 1,
            page: 2,
            index: 0,
            clickOn: '[data-control="pdf-search-prev"]'
        },{
            loopBegin: false,
            loopEnd: true,
            overall: 4,
            page: 5,
            index: 0,
            clickOn: '[data-control="pdf-search-prev"]'
        },{
            loopBegin: false,
            loopEnd: false,
            overall: 3,
            page: 4,
            index: 0,
            clickOn: '[data-control="pdf-search-prev"]'
        },{
            loopBegin: false,
            loopEnd: false,
            overall: 2,
            page: 2,
            index: 1,
            clickOn: '[data-control="pdf-search-prev"]'
        },{
            loopBegin: false,
            loopEnd: false,
            overall: 1,
            page: 2,
            index: 0,
            clickOn: '[data-control="pdf-search-prev"]'
        },{
            loopBegin: false,
            loopEnd: true,
            overall: 4,
            page: 5,
            index: 0,
            clickOn: false
        }];

        QUnit.expect(
            14 +                        // the first asserts till we reach the navigation start
            7 * navigationPath.length + // the asserts processed while navigating
            7 * 4                       // the asserts processed on page changes while navigating
        );

        pdfjs.textContent = pages;
        pdfjs.pageCount = pdfjs.textContent.length;

        assert.equal($('[data-control="pdf-search"]', $container).length, 1, 'The search button has been added');
        assert.equal($('.pdf-find-bar', $container).length, 1, 'The find bar has been added');

        pdf.load(pdfUrl).then(function () {
            assert.ok(pdf.getState('loaded'), 'The PDF is loaded');

            events.on('searching', function (query) {
                assert.ok(true, 'The search is running');
                assert.equal(query, expectedQuery, 'The search engine is searching for the expected query');
            }).on('searchdone', function (query, page) {
                assert.ok(true, 'The search is done');
                assert.equal(query, expectedQuery, 'The search engine has searched for the expected query');
                assert.equal(page, expectedPage, 'The search engine has found a match on the expected page');
                assert.equal(instance.getSearchEngine().getMatchCount(), expectedCount, 'The search has found the expected matches');
                assert.deepEqual(instance.getSearchEngine().getMatches(), expectedMatches, 'The search has found the expected matches');
            }).on('setpage', function (page) {
                assert.equal(page, expectedPage, 'The find bar has set the right page');
            }).on('pagechange', function (page) {
                assert.equal(page, expectedPage, 'The page has been changed');
            }).on('allrendered', function (page) {
                assert.equal(page, expectedPage, 'The page has been rendered');
            }).on('matchesupdating', function (page) {
                assert.equal(page, expectedPage, 'The find bar is displaying the matches');
            }).on('matchesupdated', function (page) {
                assert.equal(page, expectedPage, 'The find bar has displayed the matches');

                current = navigationPath[cursor ++];

                assert.equal(!$('[data-control="pdf-search-loop-begin"]', $container).hasClass('hidden'), current.loopBegin, 'The loop to begin message is correctly set');
                assert.equal(!$('[data-control="pdf-search-loop-end"]', $container).hasClass('hidden'), current.loopEnd, 'The loop to end message is correctly set');
                assert.equal($('[data-control="pdf-search-position"]', $container).text().trim(), current.overall + '/' + expectedCount, 'The position message is correctly set');
                assert.equal(instance.getSearchEngine().getCurrentMatch().page, current.page, 'The right page is set');
                assert.equal(instance.getSearchEngine().getCurrentMatch().index, current.index, 'The right page match is selected');
                assert.equal(instance.getSearchEngine().getCurrentMatch().overall, current.overall, 'The right match is selected');

                if (current.clickOn) {
                    expectedPage = navigationPath[cursor].page;
                    $(current.clickOn, $container).click();
                } else {
                    instance.destroy();
                    pdf.destroy();
                    QUnit.start();
                }
            });

            $('[data-control="pdf-search-query"]', $container).val(expectedQuery).keypress();
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.test('destroy', function (assert) {
        var textManager = textManagerFactory({PDFJS: pdfjs});
        var events = eventifier();
        var $container = $('<div />').append(viewerTpl());
        var broker = areaBroker($container, {
            bar: $('.pdf-bar', $container),
            actions: $('.pdf-actions', $container),
            info: $('.pdf-info', $container),
            content: $('.pdf-container', $container)
        });
        var config = {
            events: events,
            areaBroker: broker,
            textManager: textManager
        };
        var instance = findBarFactory(config);

        QUnit.expect(6);

        assert.ok(_.isPlainObject(instance.getSearchEngine()), "The getSearchEngine() method returns the search engine instance");
        assert.equal($('[data-control="pdf-search"]', $container).length, 1, 'The search button has been added');
        assert.equal($('.pdf-find-bar', $container).length, 1, 'The find bar has been added');

        instance.destroy();
        textManager.destroy();

        assert.equal(instance.getSearchEngine(), null, "The findBar has been destroyed");
        assert.equal($('[data-control="pdf-search"]', $container).length, 0, 'The search button has been removed');
        assert.equal($('.pdf-find-bar', $container).length, 0, 'The find bar has been removed');
    });

});
