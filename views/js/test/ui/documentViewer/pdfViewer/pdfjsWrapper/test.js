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
    'core/eventifier',
    'pdfjs-dist/build/pdf',
    'ui/documentViewer/providers/pdfViewer/pdfjs/wrapper'
], function ($, eventifier, pdfjs, wrapperFactory) {
    'use strict';

    var pdfUrl = location.href.replace('/pdfViewer/pdfjsWrapper/test.html', '/sample/demo.pdf');
    var base64 = 'data:application/pdf;base64,' +
        'JVBERi0xLjEKJcKlwrHDqwoKMSAwIG9iagogIDw8IC9UeXBlIC9DYXRhbG9nCiAgICAgL1BhZ2Vz' +
        'IDIgMCBSCiAgPj4KZW5kb2JqCgoyIDAgb2JqCiAgPDwgL1R5cGUgL1BhZ2VzCiAgICAgL0tpZHMg' +
        'WzMgMCBSXQogICAgIC9Db3VudCAxCiAgICAgL01lZGlhQm94IFswIDAgMzAwIDE0NF0KICA+Pgpl' +
        'bmRvYmoKCjMgMCBvYmoKICA8PCAgL1R5cGUgL1BhZ2UKICAgICAgL1BhcmVudCAyIDAgUgogICAg' +
        'ICAvUmVzb3VyY2VzCiAgICAgICA8PCAvRm9udAogICAgICAgICAgIDw8IC9GMQogICAgICAgICAg' +
        'ICAgICA8PCAvVHlwZSAvRm9udAogICAgICAgICAgICAgICAgICAvU3VidHlwZSAvVHlwZTEKICAg' +
        'ICAgICAgICAgICAgICAgL0Jhc2VGb250IC9UaW1lcy1Sb21hbgogICAgICAgICAgICAgICA+Pgog' +
        'ICAgICAgICAgID4+CiAgICAgICA+PgogICAgICAvQ29udGVudHMgNCAwIFIKICA+PgplbmRvYmoK' +
        'CjQgMCBvYmoKICA8PCAvTGVuZ3RoIDU1ID4+CnN0cmVhbQogIEJUCiAgICAvRjEgMTggVGYKICAg' +
        'IDAgMCBUZAogICAgKEhlbGxvIFdvcmxkKSBUagogIEVUCmVuZHN0cmVhbQplbmRvYmoKCnhyZWYK' +
        'MCA1CjAwMDAwMDAwMDAgNjU1MzUgZiAKMDAwMDAwMDAxOCAwMDAwMCBuIAowMDAwMDAwMDc3IDAw' +
        'MDAwIG4gCjAwMDAwMDAxNzggMDAwMDAgbiAKMDAwMDAwMDQ1NyAwMDAwMCBuIAp0cmFpbGVyCiAg' +
        'PDwgIC9Sb290IDEgMCBSCiAgICAgIC9TaXplIDUKICA+PgpzdGFydHhyZWYKNTY1CiUlRU9GCg==';
    var events = eventifier();
    var wrapperApi;


    QUnit.module('PDF.js Wrapper factory', {
        teardown: function () {
            events.removeAllListeners();
            pdfjs.removeAllListeners();
        }
    });


    QUnit.test('module', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            events: events,
            PDFJS: pdfjs
        };
        var instance;

        QUnit.expect(2);

        assert.equal(typeof wrapperFactory, 'function', "The PDF.js Wrapper module exposes a function");

        instance = wrapperFactory($container, config);

        assert.equal(typeof instance, 'object', "The PDF.js Wrapper factory provides an object");

        instance.destroy();
    });


    wrapperApi = [
        {name: 'load', title: 'load'},
        {name: 'renderPage', title: 'renderPage'},
        {name: 'getState', title: 'getState'},
        {name: 'getDocument', title: 'getDocument'},
        {name: 'getPageCount', title: 'getPageCount'},
        {name: 'getPage', title: 'getPage'},
        {name: 'setPage', title: 'setPage'},
        {name: 'getTextManager', title: 'getTextManager'},
        {name: 'getPagesManager', title: 'getPagesManager'},
        {name: 'refresh', title: 'refresh'},
        {name: 'destroy', title: 'destroy'}
    ];

    QUnit
        .cases(wrapperApi)
        .test('instance API ', function (data, assert) {
            var $container = $('#qunit-fixture');
            var config = {
                events: events,
                PDFJS: pdfjs
            };
            var instance = wrapperFactory($container, config);

            QUnit.expect(1);

            assert.equal(typeof instance[data.name], 'function', 'The PDF.js Wrapper instance exposes a "' + data.name + '" function');

            instance.destroy();
        });


    QUnit.module('PDF.js Wrapper implementation', {
        teardown: function () {
            events.removeAllListeners();
            pdfjs.removeAllListeners();
        }
    });


    QUnit.test('error', function (assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(2);

        assert.throws(function () {
            wrapperFactory($container, {events: events});
        }, "The PDF.js Wrapper factory triggers an error if PDF.js is missing");

        assert.throws(function () {
            wrapperFactory($container, {PDFJS: pdfjs});
        }, "The PDF.js Wrapper factory triggers an error if the events hub is missing");
    });


    QUnit.test('attributes', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            events: events,
            PDFJS: pdfjs
        };
        var instance = wrapperFactory($container, config);

        QUnit.expect(1);

        assert.equal(instance.wrapped, pdfjs, "The PDF.js Wrapper instance gives access to the wrapped API");

        instance.destroy();
    });


    QUnit.asyncTest('load', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            events: events,
            PDFJS: pdfjs
        };
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(11);

        pdfjs.pageCount = count;

        events.on('init.wrapper', function () {
            assert.ok(true, 'The wrapper is initialized');
        }).on('loading', function () {
            assert.ok(true, 'The document is loading');
        }).on('loaded', function () {
            assert.ok(true, 'The document has been loaded');
        }).on('destroy.wrapper', function () {
            assert.ok(true, 'The destroy event has been triggered');
        });

        instance = wrapperFactory($container, config);

        assert.equal(instance.getState('loaded'), false, 'The PDF is not loaded at this time');

        instance.load(pdfUrl).then(function () {

            assert.ok(instance.getState('loaded'), 'The PDF is loaded');

            assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);
            assert.equal(instance.getPageCount(), count, 'The PDF has ' + count + ' pages');
            assert.equal(typeof instance.getDocument(), 'object', 'The PDF document is returned');

            instance.destroy();
            assert.equal(instance.getDocument(), null, 'The PDF document is destroyed');
            assert.ok(!instance.getState('loaded'), 'The PDF is not loaded');

            QUnit.start();
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('load base64 content', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            events: events,
            PDFJS: pdfjs
        };
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(10);

        pdfjs.pageCount = count;

        events.on('loading', function () {
            assert.ok(true, 'The document is loading');
        }).on('loaded', function () {
            assert.ok(true, 'The document has been loaded');
        }).on('destroy.wrapper', function () {
            assert.ok(true, 'The destroy event has been triggered');
        });

        instance = wrapperFactory($container, config);

        assert.equal(instance.getState('loaded'), false, 'The PDF is not loaded at this time');

        instance.load(base64).then(function () {

            assert.ok(instance.getState('loaded'), 'The PDF is loaded');

            assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);
            assert.equal(instance.getPageCount(), count, 'The PDF has ' + count + ' pages');
            assert.equal(typeof instance.getDocument(), 'object', 'The PDF document is returned');

            instance.destroy();
            assert.equal(instance.getDocument(), null, 'The PDF document is destroyed');
            assert.ok(!instance.getState('loaded'), 'The PDF is not loaded');

            QUnit.start();
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.test('getTextManager', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            events: events,
            PDFJS: pdfjs
        };
        var instance;

        QUnit.expect(1);

        instance = wrapperFactory($container, config);
        assert.equal(typeof instance.getTextManager(), 'object', "The text manager has been set");
    });


    QUnit.test('getPagesManager', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            events: events,
            PDFJS: pdfjs
        };
        var instance;

        QUnit.expect(1);

        instance = wrapperFactory($container, config);
        assert.equal(typeof instance.getPagesManager(), 'object', "The page manager has been set");
    });


    QUnit.asyncTest('setPage', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            events: events,
            PDFJS: pdfjs
        };
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(18);

        pdfjs.pageCount = count;

        events.on('pagechange', function (pageNum) {
            assert.ok(true, 'The pagechange event has been triggered');
            assert.equal(pageNum, page, 'The page number is provided with the event');
        });

        instance = wrapperFactory($container, config);

        instance.load(pdfUrl).then(function () {

            assert.ok(instance.getState('loaded'), 'The PDF is loaded');

            assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);
            assert.equal(instance.getPageCount(), count, 'The PDF has ' + count + ' pages');
            assert.equal(typeof instance.getDocument(), 'object', 'The PDF document is returned');

            page = count;
            instance.setPage(page).then(function () {
                assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);

                assert.equal(instance.getState('rendering'), false, 'The PDF is not rendering at this time');
                assert.equal(instance.getState('rendered'), true, 'The page has been rendered');

                return instance.setPage(page).then(function () {
                    assert.equal(instance.getPage(), page, 'The PDF is still set on the page ' + page);

                    return instance.setPage(page + 1).then(function () {
                        assert.equal(instance.getPage(), page, 'The PDF is still set on the page ' + page);

                        instance.destroy();
                        assert.equal(instance.getDocument(), null, 'The PDF document is destroyed');

                        QUnit.start();
                    });
                });
            }).catch(function () {
                assert.ok('false', 'No error should be triggered');
                QUnit.start();
            });

            assert.equal(instance.getState('rendering'), true, 'The PDF is rendering a page');
            assert.equal(instance.getState('rendered'), false, 'The page is not rendered at this time');
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('renderPage', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            events: events,
            PDFJS: pdfjs
        };
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(31);

        pdfjs.pageCount = count;

        events.on('pagechange', function (pageNum) {
            assert.ok(true, 'The pagechange event has been triggered');
            assert.equal(pageNum, page, 'The page number is provided with the event');
        }).on('rendering', function (pageNum) {
            assert.ok(true, 'The rendering event has been triggered');
            assert.equal(pageNum, page, 'The page number is provided with the event');
        }).on('rendered', function (pageNum) {
            assert.ok(true, 'The rendered event has been triggered');
            assert.equal(pageNum, page, 'The page number is provided with the event');
        }).on('allrendered', function (pageNum) {
            assert.ok(true, 'The allrendered event has been triggered');
            assert.equal(pageNum, page, 'The page number is provided with the event');
        });

        instance = wrapperFactory($container, config);

        instance.renderPage(page).then(function () {
            assert.ok(true, 'We can call renderPage without having loaded a document, and no error is thrown');

            return instance.load(pdfUrl).then(function () {

                assert.ok(instance.getState('loaded'), 'The PDF is loaded');

                assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);
                assert.equal(instance.getPageCount(), count, 'The PDF has ' + count + ' pages');
                assert.equal(typeof instance.getDocument(), 'object', 'The PDF document is returned');

                page++;
                return instance.setPage(page).then(function () {
                    assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);

                    return instance.setPage(page).then(function () {
                        assert.equal(instance.getPage(), page, 'The PDF is still set on the page ' + page);

                        pdfjs.on('pageRender', function () {
                            assert.ok(true, 'The page has been rendered');
                        });

                        return instance.renderPage(page).then(function () {

                            instance.destroy();
                            assert.equal(instance.getDocument(), null, 'The PDF document is destroyed');

                            QUnit.start();
                        });
                    });
                });
            });
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('refresh', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            events: events,
            PDFJS: pdfjs
        };
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(32);

        pdfjs.pageCount = count;

        events.on('pagechange', function (pageNum) {
            assert.ok(true, 'The pagechange event has been triggered');
            assert.equal(pageNum, page, 'The page number is provided with the event');
        }).on('rendering', function (pageNum) {
            assert.ok(true, 'The rendering event has been triggered');
            assert.equal(pageNum, page, 'The page number is provided with the event');
        }).on('rendered', function (pageNum) {
            assert.ok(true, 'The rendered event has been triggered');
            assert.equal(pageNum, page, 'The page number is provided with the event');
        }).on('allrendered', function (pageNum) {
            assert.ok(true, 'The allrendered event has been triggered');
            assert.equal(pageNum, page, 'The page number is provided with the event');
        }).on('refreshing', function (pageNum) {
            assert.ok(true, 'The refreshing event has been triggered');
            assert.equal(pageNum, page, 'The page number is provided with the event');
        });

        instance = wrapperFactory($container, config);

        instance.load(pdfUrl).then(function () {

            assert.ok(instance.getState('loaded'), 'The PDF is loaded');

            assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);
            assert.equal(instance.getPageCount(), count, 'The PDF has ' + count + ' pages');
            assert.equal(typeof instance.getDocument(), 'object', 'The PDF document is returned');

            page++;
            return instance.setPage(page).then(function () {
                assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);

                return instance.setPage(page).then(function () {
                    assert.equal(instance.getPage(), page, 'The PDF is still set on the page ' + page);

                    pdfjs.on('pageRender', function () {
                        assert.ok(true, 'The page has been refreshed');
                    });

                    return instance.refresh().then(function () {

                        instance.destroy();
                        assert.equal(instance.getDocument(), null, 'The PDF document is destroyed');

                        QUnit.start();
                    });
                });
            });
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('concurrency', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            events: events,
            PDFJS: pdfjs
        };
        var instance;
        var page = 1;
        var count = 3;
        var promises = [];

        QUnit.expect(15);

        pdfjs.pageCount = count;

        events.on('pagechange', function () {
            assert.ok(true, 'The pagechange event has been triggered');
        }).on('rendering', function () {
            assert.ok(true, 'The rendering event has been triggered');
        }).on('rendered', function () {
            assert.ok(true, 'The rendered event has been triggered');
        }).on('allrendered', function (pageNum) {
            assert.ok(true, 'The allrendered event has been triggered');
            assert.equal(pageNum, page, 'The page number is provided with the event');
        });

        instance = wrapperFactory($container, config);

        instance.load(pdfUrl).then(function () {

            assert.ok(instance.getState('loaded'), 'The PDF is loaded');

            assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);
            assert.equal(instance.getPageCount(), count, 'The PDF has ' + count + ' pages');
            assert.equal(typeof instance.getDocument(), 'object', 'The PDF document is returned');

            promises.push(instance.setPage(page++));
            promises.push(instance.setPage(page++));
            promises.push(instance.setPage(page));

            return Promise.all(promises).then(function () {
                assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);

                instance.destroy();
                assert.equal(instance.getDocument(), null, 'The PDF document is destroyed');

                QUnit.start();
            });
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('events', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            events: events,
            PDFJS: pdfjs
        };
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(12);

        pdfjs.pageCount = count;

        events.on('pagechange', function (pageNum) {
            assert.ok(true, 'The pagechange event has been triggered');
            assert.equal(pageNum, page, 'The page number is provided with the event');
            assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);
        }).on('allrendered', function () {
            assert.ok(true, 'The page has been rendered');

            events.off('allrendered').on('allrendered', function() {
                assert.ok(true, 'The page has been refreshed');

                instance.destroy();
                assert.equal(instance.getDocument(), null, 'The PDF document is destroyed');

                QUnit.start();
            });

            events.trigger('refresh');
        }).on('refreshing', function (pageNum) {
            assert.ok(true, 'The refreshing event has been triggered');
            assert.equal(pageNum, page, 'The page number is provided with the event');
        });

        instance = wrapperFactory($container, config);

        instance.load(pdfUrl).then(function () {

            assert.ok(instance.getState('loaded'), 'The PDF is loaded');

            assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);
            assert.equal(instance.getPageCount(), count, 'The PDF has ' + count + ' pages');
            assert.equal(typeof instance.getDocument(), 'object', 'The PDF document is returned');

            page++;
            events.trigger('setpage', page);
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });

});
