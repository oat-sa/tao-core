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
    'core/promise',
    'lib/simulator/jquery.keystroker',
    'ui/documentViewer/providers/pdfViewer/pdfjs/wrapper',
    'ui/documentViewer/providers/pdfViewer/pdfjs/viewer',
    'pdfjs-dist/build/pdf'
], function ($, Promise, keystroker, wrapperFactory, viewerFactory, pdfjs) {
    'use strict';

    var pdfUrl = location.href.replace('/pdfViewer/pdfjsViewer/test.html', '/sample/demo.pdf');


    QUnit.module('pdfViewer PDF.js factory');


    QUnit.test('module', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs
        };
        var instance;

        QUnit.expect(5);

        assert.equal(typeof viewerFactory, 'function', "The pdfViewer PDF.js module exposes a function");

        instance = viewerFactory($container, config);

        assert.equal(typeof instance, 'object', "The pdfViewer PDF.js factory provides an object");
        assert.equal(typeof instance.load, 'function', "The pdfViewer PDF.js instance exposes a function load()");
        assert.equal(typeof instance.unload, 'function', "The pdfViewer PDF.js instance exposes a function unload()");
        assert.equal(typeof instance.setSize, 'function', "The pdfViewer PDF.js instance exposes a function setSize()");
    });


    QUnit.module('pdfViewer PDF.js implementation', {
        teardown: function () {
            pdfjs.removeAllListeners();
        }
    });


    QUnit.test('error', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {};

        QUnit.expect(1);

        assert.throws(function () {
            viewerFactory($container, config);
        }, "The pdfViewer PDF.js factory triggers an error if PDF.js is missing");
    });


    QUnit.asyncTest('render', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs,
            fitToWidth: true
        };
        var requestedWidth = 320;
        var requestedHeight = 240;
        var expectedWidth;
        var expectedHeight;
        var instance;
        var promise;

        QUnit.expect(18);

        assert.equal($container.children().length, 0, 'The container does not contain any children');

        instance = viewerFactory($container, config);
        promise = instance.load(pdfUrl);

        assert.equal(typeof promise, 'object', 'The load() function returns an object');
        assert.ok(promise instanceof Promise, 'The object returned by the load() function is a promise');

        promise.then(function () {
            var $pagePrev = $container.find('[data-control="pdf-page-prev"]'),
                $pageNext = $container.find('[data-control="pdf-page-next"]'),
                $pageNum = $container.find('[data-control="pdf-page-num"]'),
                $pageCount = $container.find('[data-control="pdf-page-count"]'),
                $fitToWidth = $container.find('[data-control="fit-to-width"]'),
                $pdfBar = $container.find('.pdf-bar'),
                $pdfContainer = $container.find('.pdf-container');

            assert.ok(true, 'The PDF file has been loaded');

            assert.notEqual($container.children().length, 0, 'The container contains some children');
            assert.equal($pagePrev.length, 1, 'The previous page button has been added');
            assert.equal($pageNext.length, 1, 'The next page button has been added');
            assert.equal($pageNum.length, 1, 'The page number input has been added');
            assert.equal($pageCount.length, 1, 'The page count info has been added');
            assert.equal($fitToWidth.length, 1, 'The fitToWidth option has been added');
            assert.equal($pdfBar.length, 1, 'The PDF bar has been added');
            assert.equal($pdfContainer.length, 1, 'The PDF panel has been added');

            $pdfBar.height(20);
            assert.notEqual($pdfContainer.width(), requestedWidth, 'The PDF panel is not ' + expectedWidth + ' pixels width');
            assert.notEqual($pdfContainer.height(), requestedHeight, 'The PDF panel is not ' + expectedHeight + ' pixels height');

            instance.setSize(requestedWidth, requestedHeight);

            expectedWidth = requestedWidth;
            expectedHeight = requestedHeight - $pdfBar.outerHeight();

            assert.equal($pdfContainer.width(), expectedWidth, 'The PDF panel is now ' + expectedWidth + ' pixels width');
            assert.equal($pdfContainer.height(), expectedHeight, 'The PDF panel is now ' + expectedHeight + ' pixels height');

            instance.unload();

            assert.equal($container.find('.pdf-container').length, 0, 'The viewer has been removed from the container');
            assert.equal($container.children().length, 0, 'The container does not contain any children');

            QUnit.start();
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('navigation single page', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs,
            fitToWidth: true
        };
        var instance;
        var promise;
        var page = 1;
        var count = 1;

        QUnit.expect(29);

        assert.equal($container.children().length, 0, 'The container does not contain any children');

        pdfjs.pageCount = count;
        wrapperFactory.pageCount = count;
        instance = viewerFactory($container, config);
        promise = instance.load(pdfUrl);

        assert.equal(typeof promise, 'object', 'The load() function returns an object');
        assert.ok(promise instanceof Promise, 'The object returned by the load() function is a promise');

        promise.then(function () {
            var $pagePrev = $container.find('[data-control="pdf-page-prev"]'),
                $pageNext = $container.find('[data-control="pdf-page-next"]'),
                $pageNum = $container.find('[data-control="pdf-page-num"]'),
                $pageCount = $container.find('[data-control="pdf-page-count"]'),
                $fitToWidth = $container.find('[data-control="fit-to-width"]'),
                $pdfBar = $container.find('.pdf-bar'),
                $pdfContainer = $container.find('.pdf-container');

            assert.ok(true, 'The PDF file has been loaded');

            assert.equal($pagePrev.length, 1, 'The previous page button has been added');
            assert.equal($pageNext.length, 1, 'The next page button has been added');
            assert.equal($pageNum.length, 1, 'The page number input has been added');
            assert.equal($pageCount.length, 1, 'The page count info has been added');
            assert.equal($fitToWidth.length, 1, 'The fitToWidth option has been added');
            assert.equal($pdfBar.length, 1, 'The PDF bar has been added');
            assert.equal($pdfContainer.length, 1, 'The PDF panel has been added');

            assert.equal($pageNum.val(), page, 'The current page is ' + page);
            assert.equal(parseInt($pageCount.text(), 10), count, 'The page count is ' + count);
            assert.equal($pageNum.is(':disabled'), true, 'The page number input is disabled');
            assert.equal($pagePrev.is(':disabled'), true, 'The previous page button is disabled');
            assert.equal($pageNext.is(':disabled'), true, 'The next page button is disabled');

            $pageNext.click();

            setTimeout(function() {
                assert.equal($pageNum.val(), page, 'The current page is ' + page);
                assert.equal($pageNum.is(':disabled'), true, 'The page number input is disabled');
                assert.equal($pagePrev.is(':disabled'), true, 'The previous page button is disabled');
                assert.equal($pageNext.is(':disabled'), true, 'The next page button is disabled');

                $pagePrev.click();

                setTimeout(function() {
                    assert.equal($pageNum.val(), page, 'The current page is ' + page);
                    assert.equal($pageNum.is(':disabled'), true, 'The page number input is disabled');
                    assert.equal($pagePrev.is(':disabled'), true, 'The previous page button is disabled');
                    assert.equal($pageNext.is(':disabled'), true, 'The next page button is disabled');

                    $pageNum.val(count + 1);
                    $pageNum.change();

                    setTimeout(function() {
                        assert.equal($pageNum.val(), page, 'The current page is ' + page);
                        assert.equal($pageNum.is(':disabled'), true, 'The page number input is disabled');
                        assert.equal($pagePrev.is(':disabled'), true, 'The previous page button is disabled');
                        assert.equal($pageNext.is(':disabled'), true, 'The next page button is disabled');

                        instance.unload();
                        assert.equal($container.children().length, 0, 'The viewer has been removed from the container');

                        QUnit.start();
                    }, 100);
                }, 100);
            }, 100);
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('navigation multi pages', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs,
            fitToWidth: true
        };
        var instance;
        var promise;
        var page = 1;
        var count = 10;

        QUnit.expect(27);

        assert.equal($container.children().length, 0, 'The container does not contain any children');

        pdfjs.pageCount = count;
        wrapperFactory.pageCount = count;
        instance = viewerFactory($container, config);
        promise = instance.load(pdfUrl);

        assert.equal(typeof promise, 'object', 'The load() function returns an object');
        assert.ok(promise instanceof Promise, 'The object returned by the load() function is a promise');

        promise.then(function () {
            var $pagePrev = $container.find('[data-control="pdf-page-prev"]'),
                $pageNext = $container.find('[data-control="pdf-page-next"]'),
                $pageNum = $container.find('[data-control="pdf-page-num"]'),
                $pageCount = $container.find('[data-control="pdf-page-count"]'),
                $fitToWidth = $container.find('[data-control="fit-to-width"]'),
                $pdfBar = $container.find('.pdf-bar'),
                $pdfContainer = $container.find('.pdf-container');

            assert.ok(true, 'The PDF file has been loaded');

            assert.equal($pagePrev.length, 1, 'The previous page button has been added');
            assert.equal($pageNext.length, 1, 'The next page button has been added');
            assert.equal($pageNum.length, 1, 'The page number input has been added');
            assert.equal($pageCount.length, 1, 'The page count info has been added');
            assert.equal($fitToWidth.length, 1, 'The fitToWidth option has been added');
            assert.equal($pdfBar.length, 1, 'The PDF bar has been added');
            assert.equal($pdfContainer.length, 1, 'The PDF panel has been added');

            assert.equal($pageNum.val(), page, 'The current page is ' + page);
            assert.equal(parseInt($pageCount.text(), 10), count, 'The page count is ' + count);
            assert.equal($pagePrev.is(':disabled'), true, 'The previous page button is disabled');

            $pageNext.click();
            page ++;

            setTimeout(function() {
                assert.equal($pageNum.val(), page, 'The current page is ' + page);
                assert.equal($pagePrev.is(':disabled'), false, 'The previous page button is enabled');

                $pagePrev.click();
                page --;

                setTimeout(function() {
                    assert.equal($pageNum.val(), page, 'The current page is ' + page);
                    assert.equal($pageNext.is(':disabled'), false, 'The next page button is enabled');

                    page = count;
                    $pageNum.val(page);
                    $pageNum.change();

                    setTimeout(function() {
                        assert.equal($pageNum.val(), page, 'The current page is ' + page);
                        assert.equal($pageNext.is(':disabled'), true, 'The next page button is disabled');

                        keystroker.keystroke($pageNum, keystroker.keyCode.DOWN);
                        page --;

                        setTimeout(function() {
                            assert.equal($pageNum.val(), page, 'The current page is ' + page);
                            assert.equal($pagePrev.is(':disabled'), false, 'The previous page button is enabled');
                            assert.equal($pageNext.is(':disabled'), false, 'The next page button is enabled');

                            keystroker.keystroke($pageNum, keystroker.keyCode.UP);
                            page ++;

                            setTimeout(function() {
                                assert.equal($pageNum.val(), page, 'The current page is ' + page);
                                assert.equal($pagePrev.is(':disabled'), false, 'The previous page button is enabled');
                                assert.equal($pageNext.is(':disabled'), true, 'The next page button is disabled');

                                instance.unload();
                                assert.equal($container.children().length, 0, 'The viewer has been removed from the container');

                                QUnit.start();
                            }, 100);
                        }, 100);
                    }, 100);
                }, 100);
            }, 100);
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('options', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs,
            fitToWidth: true
        };
        var instance;
        var promise;

        QUnit.expect(15);

        assert.equal($container.children().length, 0, 'The container does not contain any children');

        instance = viewerFactory($container, config);
        promise = instance.load(pdfUrl);

        assert.equal(typeof promise, 'object', 'The load() function returns an object');
        assert.ok(promise instanceof Promise, 'The object returned by the load() function is a promise');

        promise.then(function () {
            var $pagePrev = $container.find('[data-control="pdf-page-prev"]'),
                $pageNext = $container.find('[data-control="pdf-page-next"]'),
                $pageNum = $container.find('[data-control="pdf-page-num"]'),
                $pageCount = $container.find('[data-control="pdf-page-count"]'),
                $fitToWidth = $container.find('[data-control="fit-to-width"]'),
                $pdfBar = $container.find('.pdf-bar'),
                $pdfContainer = $container.find('.pdf-container');

            assert.ok(true, 'The PDF file has been loaded');

            assert.equal($pagePrev.length, 1, 'The previous page button has been added');
            assert.equal($pageNext.length, 1, 'The next page button has been added');
            assert.equal($pageNum.length, 1, 'The page number input has been added');
            assert.equal($pageCount.length, 1, 'The page count info has been added');
            assert.equal($fitToWidth.length, 1, 'The fitToWidth option has been added');
            assert.equal($pdfBar.length, 1, 'The PDF bar has been added');
            assert.equal($pdfContainer.length, 1, 'The PDF panel has been added');

            assert.equal($fitToWidth.is(':checked'), true, 'The Fit to width option is checked');

            $fitToWidth.click();

            setTimeout(function() {
                assert.equal($fitToWidth.is(':checked'), false, 'The Fit to width option is not checked');

                $fitToWidth.click();

                setTimeout(function() {
                    assert.equal($fitToWidth.is(':checked'), true, 'The Fit to width option is checked');

                    instance.unload();
                    assert.equal($container.children().length, 0, 'The viewer has been removed from the container');

                    QUnit.start();
                }, 100);
            }, 100);
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('findBar', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs
        };
        var instance;

        QUnit.expect(9);

        assert.equal($container.children().length, 0, 'The container does not contain any children');

        instance = viewerFactory($container, config);
        instance.load(pdfUrl).then(function () {
            assert.ok(true, 'The PDF file has been loaded');

            assert.equal($container.find('[data-control="pdf-search"]').length, 0, 'There is no search button');
            assert.equal($container.find('.pdf-find-bar').length, 0, 'There is no find bar');

            instance.unload();

            assert.equal($container.children().length, 0, 'The viewer has been removed from the container');

            config.allowSearch= true;
            instance = viewerFactory($container, config);
            return instance.load(pdfUrl).then(function () {
                assert.ok(true, 'The PDF file has been loaded');

                assert.equal($container.find('[data-control="pdf-search"]').length, 1, 'The search button has been added');
                assert.equal($container.find('.pdf-find-bar').length, 1, 'The find bar has been added');

                instance.unload();

                assert.equal($container.children().length, 0, 'The viewer has been removed from the container');
                QUnit.start();
            });
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


});
