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
    'ui/documentViewer/providers/pdfViewer/pdfjs/pageView'
], function ($, Promise, pageViewFactory) {
    'use strict';

    var pageViewApi;
    var pageRenderSets;


    QUnit.module('pdfViewer PageView factory');


    QUnit.test('module', function (assert) {
        var $container = $('#qunit-fixture');
        var pageNum = 1;
        var instance;

        QUnit.expect(2);

        assert.equal(typeof pageViewFactory, 'function', "The pdfViewer PageView module exposes a function");

        instance = pageViewFactory($container, pageNum);
        assert.equal(typeof instance, 'object', "The pdfViewer PageView factory provides an object");

        instance.destroy();
    });


    pageViewApi = [
        {name: 'isRendered', title: 'isRendered'},
        {name: 'getContainer', title: 'getContainer'},
        {name: 'getElement', title: 'getElement'},
        {name: 'getDrawLayer', title: 'getDrawLayer'},
        {name: 'getTextLayer', title: 'getTextLayer'},
        {name: 'getCanvas', title: 'getCanvas'},
        {name: 'getRenderingContext', title: 'getRenderingContext'},
        {name: 'render', title: 'render'},
        {name: 'show', title: 'show'},
        {name: 'hide', title: 'hide'},
        {name: 'destroy', title: 'destroy'}
    ];

    QUnit
        .cases(pageViewApi)
        .test('instance API ', function (data, assert) {
            var $container = $('#qunit-fixture');
            var pageNum = 1;
            var instance = pageViewFactory($container, pageNum);

            QUnit.expect(1);

            assert.equal(typeof instance[data.name], 'function', 'The pdfViewer PageView instance exposes a "' + data.name + '" function');

            instance.destroy();
        });


    QUnit.module('pdfViewer PageView implementation');


    QUnit.test('attributes', function (assert) {
        var $container = $('#qunit-fixture');
        var pageNum = 1;
        var instance = pageViewFactory($container, pageNum);

        QUnit.expect(2);

        assert.equal(instance.pageNum, pageNum, "The pdfViewer PageView instance contains the right page number");
        assert.equal(instance.isRendered(), false, "The pdfViewer PageView instance is not rendered");

        instance.destroy();
    });


    QUnit.test('getContainer', function (assert) {
        var $container = $('#qunit-fixture');
        var pageNum = 1;
        var instance = pageViewFactory($container, pageNum);

        QUnit.expect(3);

        assert.equal(typeof instance.getContainer(), "object", "The getContainer() method returns an object");
        assert.equal(instance.getContainer().length, 1, "The container exists");
        assert.equal(instance.getContainer(), $container, "The container is the provided one");

        instance.destroy();
    });


    QUnit.test('getElement', function (assert) {
        var $container = $('#qunit-fixture');
        var pageNum = 1;
        var instance = pageViewFactory($container, pageNum);

        QUnit.expect(3);

        assert.equal(typeof instance.getElement(), "object", "The getElement() method returns an object");
        assert.equal(instance.getElement().length, 1, "The element exists");
        assert.equal(instance.getElement().find('canvas').length, 1, "The element contains a canvas");

        instance.destroy();
    });


    QUnit.test('getDrawLayer', function (assert) {
        var $container = $('#qunit-fixture');
        var pageNum = 1;
        var instance = pageViewFactory($container, pageNum);

        QUnit.expect(4);

        assert.equal(typeof instance.getDrawLayer(), "object", "The getDrawLayer() method returns an object");
        assert.equal(instance.getDrawLayer().length, 1, "The draw layer exists");
        assert.ok(instance.getDrawLayer().is('canvas'), "The draw layer is a canvas");
        assert.ok(instance.getDrawLayer().parent().is(instance.getElement()), "The draw layer is contained by the page container");

        instance.destroy();
    });


    QUnit.test('getTextLayer', function (assert) {
        var $container = $('#qunit-fixture');
        var pageNum = 1;
        var instance = pageViewFactory($container, pageNum);

        QUnit.expect(4);

        assert.equal(typeof instance.getTextLayer(), "object", "The getTextLayer() method returns an object");
        assert.equal(instance.getTextLayer().length, 1, "The text layer exists");
        assert.ok(instance.getTextLayer().is('div'), "The text layer is a div");
        assert.ok(instance.getTextLayer().parent().is(instance.getElement()), "The text layer is contained by the page container");

        instance.destroy();
    });


    QUnit.test('getCanvas', function (assert) {
        var $container = $('#qunit-fixture');
        var pageNum = 1;
        var instance = pageViewFactory($container, pageNum);

        QUnit.expect(4);

        assert.equal(typeof instance.getCanvas(), "object", "The getCanvas() method returns an object");
        assert.ok($(instance.getCanvas()).is('canvas'), "This is a canvas");
        assert.ok($(instance.getCanvas()).is(instance.getDrawLayer()), "The canvas is the page panel");
        assert.ok($(instance.getCanvas()).parent().is(instance.getElement()), "The canvas is contained by the page container");

        instance.destroy();
    });


    QUnit.test('getRenderingContext', function (assert) {
        var $container = $('#qunit-fixture');
        var pageNum = 1;
        var instance = pageViewFactory($container, pageNum);

        QUnit.expect(2);

        assert.equal(typeof instance.getRenderingContext(), "object", "The getRenderingContext() method returns an object");
        assert.equal(instance.getRenderingContext(), instance.getCanvas().getContext('2d'), "This is the canvas rendering context");

        instance.destroy();
    });


    pageRenderSets = [{
        title: 'width > height',
        containerWidth: 200,
        containerHeight: 100,
        expectedWidth: 200,
        expectedHeight: 100,
        fitToWidth: false,
        viewport: {
            width: 400,
            height: 200
        }
    }, {
        title: 'width < height',
        containerWidth: 200,
        containerHeight: 100,
        expectedWidth: 50,
        expectedHeight: 100,
        fitToWidth: false,
        viewport: {
            width: 200,
            height: 400
        }
    }, {
        title: 'fitToWidth & width > height',
        containerWidth: 200,
        containerHeight: 100,
        expectedWidth: 200,
        expectedHeight: 100,
        fitToWidth: true,
        viewport: {
            width: 400,
            height: 200
        }
    }, {
        title: 'fitToWidth & width < height',
        containerWidth: 200,
        containerHeight: 100,
        expectedWidth: 200,
        expectedHeight: 400,
        fitToWidth: true,
        viewport: {
            width: 200,
            height: 400
        }
    }];

    QUnit
        .cases(pageRenderSets)
        .asyncTest('render ', function (data, assert) {
            var $container = $('#qunit-fixture');
            var pageNum = 1;
            var instance = pageViewFactory($container, pageNum);
            var mockPDFJSPage = {
                getViewport: function getViewport() {
                    return data.viewport;
                },

                render: function render() {
                    assert.ok(true, "The page is rendering");
                    return {
                        promise: new Promise(function (resolve) {
                            setTimeout(function () {
                                resolve();
                            }, 100);
                        })
                    };
                }
            };

            QUnit.expect(21);


            $container.width(data.containerWidth).height(data.containerHeight);
            assert.equal($container.width(), data.containerWidth, 'The container is ' + data.containerWidth + ' pixels width');
            assert.equal($container.height(), data.containerHeight, 'The container is ' + data.containerHeight + ' pixels height');

            instance.getElement().width(10).height(10);

            assert.notEqual(instance.getElement().width(), data.expectedWidth, "The page view is not " + data.expectedWidth + " pixels width");
            assert.notEqual(instance.getElement().height(), data.expectedHeight, "The page view is not " + data.expectedHeight + " pixels height");

            assert.notEqual(instance.getDrawLayer().width(), data.expectedWidth, "The draw layer is not " + data.expectedWidth + " pixels width");
            assert.notEqual(instance.getDrawLayer().height(), data.expectedHeight, "The draw layer is not " + data.expectedHeight + " pixels height");

            assert.notEqual(instance.getTextLayer().width(), data.expectedWidth, "The text layer is not " + data.expectedWidth + " pixels width");
            assert.notEqual(instance.getTextLayer().height(), data.expectedHeight, "The text layer is not " + data.expectedHeight + " pixels height");

            assert.notEqual(instance.getCanvas().width, data.viewport.width, "The canvas viewport is not " + data.viewport.width + " pixels width");
            assert.notEqual(instance.getCanvas().height, data.viewport.height, "The canvas viewport is not " + data.viewport.height + " pixels height");

            assert.ok(!instance.isRendered(), "The pdfViewer PageView instance is not rendered");

            instance.render(mockPDFJSPage, data.fitToWidth).then(function () {
                assert.ok(instance.isRendered(), "The pdfViewer PageView instance has been rendered");

                assert.equal(instance.getElement().width(), data.expectedWidth, "The page view is now " + data.expectedWidth + " pixels width");
                assert.equal(instance.getElement().height(), data.expectedHeight, "The page view is now " + data.expectedHeight + " pixels height");

                assert.equal(instance.getDrawLayer().width(), data.expectedWidth, "The draw layer is now " + data.expectedWidth + " pixels width");
                assert.equal(instance.getDrawLayer().height(), data.expectedHeight, "The draw layer is now " + data.expectedHeight + " pixels height");

                assert.equal(instance.getTextLayer().width(), data.expectedWidth, "The text layer is now " + data.expectedWidth + " pixels width");
                assert.equal(instance.getTextLayer().height(), data.expectedHeight, "The text layer is now " + data.expectedHeight + " pixels height");

                assert.equal(instance.getCanvas().width, data.viewport.width, "The canvas viewport is now " + data.viewport.width + " pixels width");
                assert.equal(instance.getCanvas().height, data.viewport.height, "The canvas viewport is now " + data.viewport.height + " pixels height");

                instance.destroy();

                QUnit.start();
            }).catch(function () {
                assert.ok('false', 'No error should be triggered');
                QUnit.start();
            });
        });


    QUnit.test('show/hide', function (assert) {
        var $container = $('#qunit-fixture');
        var pageNum = 1;
        var instance = pageViewFactory($container, pageNum);

        QUnit.expect(3);

        assert.ok(instance.getElement().hasClass('hidden'), "The page container is hidden by default");

        instance.show();

        assert.ok(!instance.getElement().hasClass('hidden'), "The page container is now visible");

        instance.hide();

        assert.ok(instance.getElement().hasClass('hidden'), "The page container is now hidden");
    });


    QUnit.test('destroy', function (assert) {
        var $container = $('#qunit-fixture');
        var pageNum = 1;
        var instance;

        QUnit.expect(15);

        assert.equal($container.children().length, 0, "The DOM container is empty");

        instance = pageViewFactory($container, pageNum);

        assert.equal(typeof instance.getContainer(), "object", "The getElement() method returns an object");
        assert.equal(typeof instance.getElement(), "object", "The getElement() method returns an object");
        assert.equal(typeof instance.getDrawLayer(), "object", "The getDrawLayer() method returns an object");
        assert.equal(typeof instance.getTextLayer(), "object", "The getTextLayer() method returns an object");
        assert.equal(typeof instance.getCanvas(), "object", "The getCanvas() method returns an object");
        assert.equal(typeof instance.getRenderingContext(), "object", "The getRenderingContext() method returns an object");

        assert.ok(instance.getElement().parent().is($container), "The page container has been added to the DOM");

        instance.destroy();

        assert.equal(instance.getContainer(), null, "The page container is destroyed");
        assert.equal(instance.getElement(), null, "The page container is destroyed");
        assert.equal(instance.getDrawLayer(), null, "The page panel is destroyed");
        assert.equal(instance.getTextLayer(), null, "The text layer is destroyed");
        assert.equal(instance.getCanvas(), null, "The canvas is destroyed");
        assert.equal(instance.getRenderingContext(), null, "The rendering context is destroyed");

        assert.equal($container.children().length, 0, "The page container has been removed from the DOM");
    });

});
