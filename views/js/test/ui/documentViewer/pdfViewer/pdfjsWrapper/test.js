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

    var wrapperApi;


    QUnit.module('PDF.js Wrapper factory');


    QUnit.test('module', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
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
        { name : 'load', title : 'load' },
        { name : 'renderPage', title : 'renderPage' },
        { name : 'getState', title : 'getState' },
        { name : 'getDocument', title : 'getDocument' },
        { name : 'getPageCount', title : 'getPageCount' },
        { name : 'getPage', title : 'getPage' },
        { name : 'setPage', title : 'setPage' },
        { name : 'getTextManager', title : 'getTextManager' },
        { name : 'getPagesManager', title : 'getPagesManager' },
        { name : 'refresh', title : 'refresh' },
        { name : 'destroy', title : 'destroy' }
    ];

    QUnit
        .cases(wrapperApi)
        .test('instance API ', function(data, assert) {
            var $container = $('#qunit-fixture');
            var config = {
                PDFJS: pdfjs
            };
            var instance = wrapperFactory($container, config);

            QUnit.expect(1);

            assert.equal(typeof instance[data.name], 'function', 'The PDF.js Wrapper instance exposes a "' + data.name + '" function');

            instance.destroy();
        });


    QUnit.module('PDF.js Wrapper implementation', {
        teardown: function () {
            pdfjs.removeAllListeners();
        }
    });


    QUnit.test('error', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {};

        QUnit.expect(1);

        assert.throws(function () {
            wrapperFactory($container, config);
        }, "The PDF.js Wrapper factory triggers an error if PDF.js is missing");
    });


    QUnit.test('attributes', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
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
            PDFJS: pdfjs
        };
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(7);

        pdfjs.pageCount = count;

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
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('load base64 content', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs
        };
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(7);

        pdfjs.pageCount = count;

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
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.test('getTextManager', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
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
            PDFJS: pdfjs
        };
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(12);

        pdfjs.pageCount = count;

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


    QUnit.asyncTest('renderPage', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs
        };
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(9);

        pdfjs.pageCount = count;

        instance = wrapperFactory($container, config);

        instance.renderPage(1).then(function () {
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

                        return instance.renderPage(1).then(function () {

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


    QUnit.asyncTest('refresh', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs
        };
        var instance;
        var page = 1;
        var count = 3;

        QUnit.expect(8);

        pdfjs.pageCount = count;

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
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('concurrency', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs
        };
        var instance;
        var page = 1;
        var count = 3;
        var promises = [];

        QUnit.expect(6);

        pdfjs.pageCount = count;

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
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });

});
