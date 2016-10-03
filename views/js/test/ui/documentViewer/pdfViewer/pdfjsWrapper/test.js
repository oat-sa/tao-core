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
    'pdfjs-dist/build/pdf',
    'ui/documentViewer/providers/pdfViewer/pdfjs/wrapper'
], function ($, pdfjs, wrapperFactory) {
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

    QUnit.module('PDF.js Wrapper factory');


    QUnit.test('module', function (assert) {
        var $container = $('#qunit-fixture');
        var $canvas = $container.find('canvas');
        var config = {};
        var instance;

        QUnit.expect(11);

        assert.equal(typeof wrapperFactory, 'function', "The PDF.js Wrapper module exposes a function");

        instance = wrapperFactory(pdfjs, $canvas, config);

        assert.equal(typeof instance, 'object', "The PDF.js Wrapper factory provides an object");
        assert.equal(typeof instance.load, 'function', "The PDF.js Wrapper instance exposes a function load()");
        assert.equal(typeof instance.getState, 'function', "The PDF.js Wrapper instance exposes a function getState()");
        assert.equal(typeof instance.getDocument, 'function', "The PDF.js Wrapper instance exposes a function getDocument()");
        assert.equal(typeof instance.getPageCount, 'function', "The PDF.js Wrapper instance exposes a function getPageCount()");
        assert.equal(typeof instance.getPage, 'function', "The PDF.js Wrapper instance exposes a function getPage()");
        assert.equal(typeof instance.setPage, 'function', "The PDF.js Wrapper instance exposes a function setPage()");
        assert.equal(typeof instance.setSize, 'function', "The PDF.js Wrapper instance exposes a function setSize()");
        assert.equal(typeof instance.refresh, 'function', "The PDF.js Wrapper instance exposes a function refresh()");
        assert.equal(typeof instance.destroy, 'function', "The PDF.js Wrapper instance exposes a function destroy()");
    });


    QUnit.module('PDF.js Wrapper implementation', {
        teardown: function () {
            pdfjs.removeAllListeners();
        }
    });


    QUnit.asyncTest('load', function (assert) {
        var $container = $('#qunit-fixture');
        var $canvas = $container.find('canvas');
        var config = {};
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(6);

        pdfjs.pageCount = count;

        instance = wrapperFactory(pdfjs, $canvas, config);

        assert.equal(instance.getState('loaded'), false, 'The PDF is not loaded at this time');

        instance.load(pdfUrl).then(function () {

            assert.ok(instance.getState('loaded'), 'The PDF is loaded');

            assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);
            assert.equal(instance.getPageCount(), count, 'The PDF has ' + count + ' pages');
            assert.equal(typeof instance.getDocument(), 'object', 'The PDF document is returned');

            instance.destroy();
            assert.equal(instance.getDocument(), null, 'The PDF document is destroyed');

            QUnit.start();
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('load base64 content', function (assert) {
        var $container = $('#qunit-fixture');
        var $canvas = $container.find('canvas');
        var config = {};
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(5);

        pdfjs.pageCount = count;

        instance = wrapperFactory(pdfjs, $canvas, config);

        instance.load(base64).then(function () {

            assert.ok(instance.getState('loaded'), 'The PDF is loaded');

            assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);
            assert.equal(instance.getPageCount(), count, 'The PDF has ' + count + ' pages');
            assert.equal(typeof instance.getDocument(), 'object', 'The PDF document is returned');

            instance.destroy();
            assert.equal(instance.getDocument(), null, 'The PDF document is destroyed');

            QUnit.start();
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('setPage', function (assert) {
        var $container = $('#qunit-fixture');
        var $canvas = $container.find('canvas');
        var config = {};
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(12);

        pdfjs.pageCount = count;

        instance = wrapperFactory(pdfjs, $canvas, config);

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
            }).catch(function() {
                assert.ok('false', 'No error should be triggered');
                QUnit.start();
            });

            assert.equal(instance.getState('rendering'), true, 'The PDF is rendering a page');
            assert.equal(instance.getState('rendered'), false, 'The page is not rendered at this time');
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('setSize', function (assert) {
        var $container = $('#qunit-fixture');
        var $canvas = $container.find('canvas');
        var config = {};
        var instance;
        var requestedWidth = 200;
        var requestedHeight = 100;
        var expectedWidth = 200;
        var expectedHeight = 100;
        var page = 1;
        var count = 3;

        QUnit.expect(14);

        pdfjs.pageCount = count;
        pdfjs.viewportWidth = 400;
        pdfjs.viewportHeight = 200;

        instance = wrapperFactory(pdfjs, $canvas, config);

        assert.notEqual($canvas.width(), expectedWidth, 'The content panel is not ' + expectedWidth + ' pixels width');
        assert.notEqual($canvas.height(), expectedHeight, 'The content panel is not ' + expectedHeight + ' pixels width');

        instance.setSize(requestedWidth, requestedHeight).then(function () {
            assert.ok(true, 'The size can be set even if the PDF is not loaded');

            return instance.load(pdfUrl).then(function () {

                assert.ok(instance.getState('loaded'), 'The PDF is loaded');

                assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);
                assert.equal(instance.getPageCount(), count, 'The PDF has ' + count + ' pages');
                assert.equal(typeof instance.getDocument(), 'object', 'The PDF document is returned');

                return instance.setSize(requestedWidth, requestedHeight).then(function () {

                    assert.equal($canvas.width(), expectedWidth, 'The content panel is now ' + expectedWidth + ' pixels width');
                    assert.equal($canvas.height(), expectedHeight, 'The content panel is now ' + expectedHeight + ' pixels width');

                    return instance.setSize(requestedWidth, requestedHeight).then(function () {

                        assert.equal($canvas.width(), expectedWidth, 'The content panel is still ' + expectedWidth + ' pixels width');
                        assert.equal($canvas.height(), expectedHeight, 'The content panel is still ' + expectedHeight + ' pixels width');

                        pdfjs.viewportWidth = 200;
                        pdfjs.viewportHeight = 400;
                        requestedWidth = 300;
                        requestedHeight = 150;
                        expectedWidth = 75;
                        expectedHeight = 150;

                        return instance.setSize(requestedWidth, requestedHeight).then(function () {

                            assert.equal($canvas.width(), expectedWidth, 'The content panel is ' + expectedWidth + ' pixels width');
                            assert.equal($canvas.height(), expectedHeight, 'The content panel is ' + expectedHeight + ' pixels width');

                            instance.destroy();
                            assert.equal(instance.getDocument(), null, 'The PDF document is destroyed');

                            QUnit.start();
                        });
                    });
                });
            });
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('fitToWidth', function (assert) {
        var $container = $('#qunit-fixture');
        var $canvas = $container.find('canvas');
        var config = {
            fitToWidth: true
        };
        var instance;
        var requestedWidth = 200;
        var requestedHeight = 100;
        var expectedWidth = 200;
        var expectedHeight = 100;
        var page = 1;
        var count = 3;

        $container.width(expectedWidth).height(expectedHeight);

        QUnit.expect(11);

        pdfjs.pageCount = count;
        pdfjs.viewportWidth = 400;
        pdfjs.viewportHeight = 200;

        instance = wrapperFactory(pdfjs, $canvas, config);

        instance.load(pdfUrl).then(function () {

            assert.ok(instance.getState('loaded'), 'The PDF is loaded');

            assert.equal(instance.getPage(), page, 'The PDF is set on the page ' + page);
            assert.equal(instance.getPageCount(), count, 'The PDF has ' + count + ' pages');
            assert.equal(typeof instance.getDocument(), 'object', 'The PDF document is returned');

            assert.notEqual($canvas.width(), expectedWidth, 'The content panel is not ' + expectedWidth + ' pixels width');
            assert.notEqual($canvas.height(), expectedHeight, 'The content panel is not ' + expectedHeight + ' pixels width');

            return instance.setSize(requestedWidth, requestedHeight).then(function () {

                assert.equal($canvas.width(), expectedWidth, 'The content panel is now ' + expectedWidth + ' pixels width');
                assert.equal($canvas.height(), expectedHeight, 'The content panel is now ' + expectedHeight + ' pixels width');

                pdfjs.viewportWidth = 100;
                pdfjs.viewportHeight = 1000;
                requestedWidth = 300;
                requestedHeight = 150;
                expectedHeight = expectedWidth * pdfjs.viewportHeight / pdfjs.viewportWidth;

                return instance.setSize(requestedWidth, requestedHeight).then(function () {

                    assert.equal($canvas.width(), expectedWidth, 'The content panel is ' + expectedWidth + ' pixels width');
                    assert.equal($canvas.height(), expectedHeight, 'The content panel is ' + expectedHeight + ' pixels width');

                    instance.destroy();
                    assert.equal(instance.getDocument(), null, 'The PDF document is destroyed');

                    QUnit.start();
                });
            });
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('refresh', function (assert) {
        var $container = $('#qunit-fixture');
        var $canvas = $container.find('canvas');
        var config = {};
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(8);

        pdfjs.pageCount = count;

        instance = wrapperFactory(pdfjs, $canvas, config);

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
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('concurrency', function (assert) {
        var $container = $('#qunit-fixture');
        var $canvas = $container.find('canvas');
        var config = {};
        var instance;
        var page = 1;
        var count = 3;
        var promises = [];

        QUnit.expect(6);

        pdfjs.pageCount = count;

        instance = wrapperFactory(pdfjs, $canvas, config);

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
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });

});
