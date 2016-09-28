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
    'lib/simulator/jquery.keystroker',
    'ui/documentViewer/providers/pdfViewer/pdfjs/wrapper',
    'ui/documentViewer/providers/pdfViewer/pdfjs/viewer',
], function ($, keystroker, wrapperFactory, viewerFactory) {
    'use strict';

    var pdfUrl = location.href.replace('/pdfViewer/fallback/test.html', '/sample/demo.pdf');
    var pdfjsMock = {
        PDFJS: {}
    };


    QUnit.module('pdfViewer PDF.js factory');


    QUnit.test('module', function (assert) {
        var $container = $('#qunit-fixture');
        var instance;

        QUnit.expect(5);

        assert.equal(typeof viewerFactory, 'function', "The pdfViewer PDF.js module exposes a function");

        instance = viewerFactory($container, pdfjsMock, {});

        assert.equal(typeof instance, 'object', "The pdfViewer PDF.js factory provides an object");
        assert.equal(typeof instance.load, 'function', "The pdfViewer PDF.js instance exposes a function load()");
        assert.equal(typeof instance.unload, 'function', "The pdfViewer PDF.js instance exposes a function unload()");
        assert.equal(typeof instance.setSize, 'function', "The pdfViewer PDF.js instance exposes a function setSize()");
    });


    QUnit.module('implementation');


    QUnit.asyncTest('render', function (assert) {
        var $container = $('#qunit-fixture');
        var expectedWidth = 256;
        var expectedHeight = 128;
        var instance;
        var promise;

        QUnit.expect(19);

        assert.equal($container.children().length, 0, 'The container does not contain any children');

        instance = viewerFactory($container, pdfjsMock, {fitToWidth: true});
        promise = instance.load(pdfUrl);

        assert.equal(typeof promise, 'object', 'The load() function returns an object');
        assert.equal(typeof promise.then, 'function', 'The object returned by the load() function is a promise');

        promise.then(function () {
            var $pagePrev = $container.find('[data-control="pdf-page-prev"]'),
                $pageNext = $container.find('[data-control="pdf-page-next"]'),
                $pageNum = $container.find('[data-control="pdf-page-num"]'),
                $pageCount = $container.find('[data-control="pdf-page-count"]'),
                $fitToWidth = $container.find('[data-control="fit-to-width"]'),
                $content = $container.find('[data-control="pdf-content"]'),
                $pdfBar = $container.find('.pdf-bar'),
                $pdfContainer = $container.find('.pdf-container');

            assert.ok(true, 'The PDF file has been loaded');

            assert.notEqual($container.children().length, 0, 'The container contains some children');
            assert.equal($pagePrev.length, 1, 'The previous page button has been added');
            assert.equal($pageNext.length, 1, 'The next page button has been added');
            assert.equal($pageNum.length, 1, 'The page number input has been added');
            assert.equal($pageCount.length, 1, 'The page count info has been added');
            assert.equal($fitToWidth.length, 1, 'The fitToWidth option has been added');
            assert.equal($content.length, 1, 'The content panel has been added');
            assert.equal($pdfBar.length, 1, 'The PDF bar has been added');
            assert.equal($pdfContainer.length, 1, 'The PDF panel has been added');


            assert.notEqual($pdfContainer.width(), expectedWidth, 'The PDF panel is not ' + expectedWidth + ' pixels width');
            assert.notEqual($pdfContainer.height(), expectedHeight, 'The PDF panel is not ' + expectedHeight + ' pixels height');

            instance.setSize(expectedWidth, expectedHeight);

            assert.equal($pdfContainer.width(), expectedWidth, 'The PDF panel is now ' + expectedWidth + ' pixels width');
            assert.equal($pdfContainer.height(), expectedHeight - $pdfBar.height(), 'The PDF panel is now ' + expectedHeight + ' pixels height');

            instance.unload();

            assert.equal($container.find('.pdf-container').length, 0, 'The viewer has been removed from the container');
            assert.equal($container.children().length, 0, 'The container does not contain any children');

            QUnit.start();
        });
    });


    QUnit.asyncTest('navigation single page', function (assert) {
        var $container = $('#qunit-fixture');
        var instance;
        var promise;
        var page = 1;
        var count = 1;

        QUnit.expect(30);

        assert.equal($container.children().length, 0, 'The container does not contain any children');

        wrapperFactory.pageCount = count;
        instance = viewerFactory($container, pdfjsMock, {fitToWidth: true});
        promise = instance.load(pdfUrl);

        assert.equal(typeof promise, 'object', 'The load() function returns an object');
        assert.equal(typeof promise.then, 'function', 'The object returned by the load() function is a promise');

        promise.then(function () {
            var $pagePrev = $container.find('[data-control="pdf-page-prev"]'),
                $pageNext = $container.find('[data-control="pdf-page-next"]'),
                $pageNum = $container.find('[data-control="pdf-page-num"]'),
                $pageCount = $container.find('[data-control="pdf-page-count"]'),
                $fitToWidth = $container.find('[data-control="fit-to-width"]'),
                $content = $container.find('[data-control="pdf-content"]'),
                $pdfBar = $container.find('.pdf-bar'),
                $pdfContainer = $container.find('.pdf-container');

            assert.ok(true, 'The PDF file has been loaded');

            assert.equal($pagePrev.length, 1, 'The previous page button has been added');
            assert.equal($pageNext.length, 1, 'The next page button has been added');
            assert.equal($pageNum.length, 1, 'The page number input has been added');
            assert.equal($pageCount.length, 1, 'The page count info has been added');
            assert.equal($fitToWidth.length, 1, 'The fitToWidth option has been added');
            assert.equal($content.length, 1, 'The content panel has been added');
            assert.equal($pdfBar.length, 1, 'The PDF bar has been added');
            assert.equal($pdfContainer.length, 1, 'The PDF panel has been added');

            assert.equal($pageNum.val(), page, 'The current page is ' + page);
            assert.equal(Number($pageCount.text()), count, 'The page count is ' + count);
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
        });
    });


    QUnit.asyncTest('navigation multi pages', function (assert) {
        var $container = $('#qunit-fixture');
        var instance;
        var promise;
        var page = 1;
        var count = 10;

        QUnit.expect(28);

        assert.equal($container.children().length, 0, 'The container does not contain any children');

        wrapperFactory.pageCount = count;
        instance = viewerFactory($container, pdfjsMock, {fitToWidth: true});
        promise = instance.load(pdfUrl);

        assert.equal(typeof promise, 'object', 'The load() function returns an object');
        assert.equal(typeof promise.then, 'function', 'The object returned by the load() function is a promise');

        promise.then(function () {
            var $pagePrev = $container.find('[data-control="pdf-page-prev"]'),
                $pageNext = $container.find('[data-control="pdf-page-next"]'),
                $pageNum = $container.find('[data-control="pdf-page-num"]'),
                $pageCount = $container.find('[data-control="pdf-page-count"]'),
                $fitToWidth = $container.find('[data-control="fit-to-width"]'),
                $content = $container.find('[data-control="pdf-content"]'),
                $pdfBar = $container.find('.pdf-bar'),
                $pdfContainer = $container.find('.pdf-container');

            assert.ok(true, 'The PDF file has been loaded');

            assert.equal($pagePrev.length, 1, 'The previous page button has been added');
            assert.equal($pageNext.length, 1, 'The next page button has been added');
            assert.equal($pageNum.length, 1, 'The page number input has been added');
            assert.equal($pageCount.length, 1, 'The page count info has been added');
            assert.equal($fitToWidth.length, 1, 'The fitToWidth option has been added');
            assert.equal($content.length, 1, 'The content panel has been added');
            assert.equal($pdfBar.length, 1, 'The PDF bar has been added');
            assert.equal($pdfContainer.length, 1, 'The PDF panel has been added');

            assert.equal($pageNum.val(), page, 'The current page is ' + page);
            assert.equal(Number($pageCount.text()), count, 'The page count is ' + count);
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
        });
    });


    QUnit.asyncTest('options', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            fitToWidth: true
        };
        var instance;
        var promise;

        QUnit.expect(19);

        assert.equal($container.children().length, 0, 'The container does not contain any children');

        instance = viewerFactory($container, pdfjsMock, config);
        promise = instance.load(pdfUrl);

        assert.equal(typeof promise, 'object', 'The load() function returns an object');
        assert.equal(typeof promise.then, 'function', 'The object returned by the load() function is a promise');

        promise.then(function () {
            var $pagePrev = $container.find('[data-control="pdf-page-prev"]'),
                $pageNext = $container.find('[data-control="pdf-page-next"]'),
                $pageNum = $container.find('[data-control="pdf-page-num"]'),
                $pageCount = $container.find('[data-control="pdf-page-count"]'),
                $fitToWidth = $container.find('[data-control="fit-to-width"]'),
                $content = $container.find('[data-control="pdf-content"]'),
                $pdfBar = $container.find('.pdf-bar'),
                $pdfContainer = $container.find('.pdf-container');

            assert.ok(true, 'The PDF file has been loaded');

            assert.equal($pagePrev.length, 1, 'The previous page button has been added');
            assert.equal($pageNext.length, 1, 'The next page button has been added');
            assert.equal($pageNum.length, 1, 'The page number input has been added');
            assert.equal($pageCount.length, 1, 'The page count info has been added');
            assert.equal($fitToWidth.length, 1, 'The fitToWidth option has been added');
            assert.equal($content.length, 1, 'The content panel has been added');
            assert.equal($pdfBar.length, 1, 'The PDF bar has been added');
            assert.equal($pdfContainer.length, 1, 'The PDF panel has been added');

            assert.equal(config.fitToWidth, true, 'The fitToWidth config is set');
            assert.equal($fitToWidth.is(':checked'), true, 'The Fit to width option is checked');

            $fitToWidth.click();

            setTimeout(function() {
                assert.equal(config.fitToWidth, false, 'The fitToWidth config has been changed');
                assert.equal($fitToWidth.is(':checked'), false, 'The Fit to width option is not checked');

                $fitToWidth.click();

                setTimeout(function() {
                    assert.equal(config.fitToWidth, true, 'The fitToWidth config is set');
                    assert.equal($fitToWidth.is(':checked'), true, 'The Fit to width option is checked');

                    instance.unload();
                    assert.equal($container.children().length, 0, 'The viewer has been removed from the container');

                    QUnit.start();
                }, 100);
            }, 100);
        });
    });


});
