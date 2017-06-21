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
    'ui/documentViewer/providers/pdfViewer/pdfjs/pagesManager',
    'ui/documentViewer/providers/pdfViewer/pdfjs/textManager'
], function ($, pdfjs, pagesManagerFactory, textManagerFactory) {
    'use strict';

    var pdfUrl = location.href.replace('/pdfViewer/pagesManager/test.html', '/sample/demo.pdf');
    var pagesManagerApi;


    QUnit.module('pdfViewer PagesManager factory');


    QUnit.test('module', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {};
        var instance;

        QUnit.expect(2);

        assert.equal(typeof pagesManagerFactory, 'function', "The pdfViewer PagesManager module exposes a function");

        instance = pagesManagerFactory($container, config);
        assert.equal(typeof instance, 'object', "The pdfViewer PagesManager factory provides an object");

        instance.destroy();
    });


    pagesManagerApi = [
        { name : 'getContainer', title : 'getContainer' },
        { name : 'getTextManager', title : 'getTextManager' },
        { name : 'setTextManager', title : 'setTextManager' },
        { name : 'getView', title : 'getView' },
        { name : 'getActiveView', title : 'getActiveView' },
        { name : 'setActiveView', title : 'setActiveView' },
        { name : 'renderPage', title : 'renderPage' },
        { name : 'destroy', title : 'destroy' }
    ];

    QUnit
        .cases(pagesManagerApi)
        .test('instance API ', function(data, assert) {
            var $container = $('#qunit-fixture');
            var config = {};
            var instance = pagesManagerFactory($container, config);
            QUnit.expect(1);
            assert.equal(typeof instance[data.name], 'function', 'The pdfViewer PagesManager instance exposes a "' + data.name + '" function');

            instance.destroy();
        });



    QUnit.module('pdfViewer PagesManager implementation', {
        teardown: function () {
            pdfjs.removeAllListeners();
        }
    });


    QUnit.test('attributes', function (assert) {
        var $container = $('#qunit-fixture');
        var pageCount = 3;
        var config = {
            pageCount: pageCount
        };
        var instance = pagesManagerFactory($container, config);

        QUnit.expect(1);

        assert.equal(instance.pageCount, pageCount, "The pdfViewer PagesManager instance contains the number of pages");

        instance.destroy();
    });


    QUnit.test('getContainer', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {};
        var instance = pagesManagerFactory($container, config);

        QUnit.expect(3);

        assert.equal(typeof instance.getContainer(), "object", "The getContainer() method returns an object");
        assert.equal(instance.getContainer().length, 1, "The container exists");
        assert.ok(instance.getContainer().is($container), "This is the right container");

        instance.destroy();
    });


    QUnit.asyncTest('setTextManager', function (assert) {
        var $container = $('#qunit-fixture');
        var textManager1 = textManagerFactory({PDFJS: pdfjs});
        var textManager2 = textManagerFactory({PDFJS: pdfjs});
        var config = {
            pageNum: 1,
            textManager: textManager1
        };
        var instance = pagesManagerFactory($container, config);

        QUnit.expect(7);

        pdfjs.pageCount = 1;
        pdfjs.textContent = ['This is a test'];

        assert.equal(typeof instance.getTextManager(), "object", "The getTextManager() method returns an object");
        assert.equal(instance.getTextManager(), textManager1, "The getTextManager() method returns the right object");

        pdfjs.getDocument(pdfUrl).then(function (pdf) {
            textManager1.setDocument(pdf);
            textManager2.setDocument(pdf);

            instance.setActiveView(1);

            return pdf.getPage(1).then(function (page) {
                return instance.renderPage(page).then(function() {
                    assert.equal(instance.getActiveView().getTextManager(), textManager1, "The view uses the right text manager");

                    instance.setTextManager(textManager2);
                    assert.notEqual(instance.getTextManager(), textManager1, "The text manager has been changed");
                    assert.equal(instance.getTextManager(), textManager2, "The getTextManager() method returns the right object");

                    assert.notEqual(instance.getActiveView().getTextManager(), textManager1, "The text manager has been changed into the view");
                    assert.equal(instance.getActiveView().getTextManager(), textManager2, "The view uses the right text manager");

                    instance.destroy();
                    textManager1.destroy();
                    textManager2.destroy();

                    QUnit.start();
                });
            });
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.test('getView', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            pageCount: 2
        };
        var instance, view1, view2;

        QUnit.expect(6);

        assert.equal($container.children().length, 0, "The container is empty");

        instance = pagesManagerFactory($container, config);
        view1 = instance.getView(1);
        assert.equal(typeof view1, "object", "The returned view is an object");
        assert.equal($container.children().length, 1, "The container contains a child");
        assert.equal(instance.getView(1), view1, "The manager always returns the same view for a particular page");

        view2 = instance.getView(2);
        assert.notEqual(view1, view2, "The manager provides a view instance per page");
        assert.equal($container.children().length, 2, "The container contains 2 children");

        instance.destroy();
    });


    QUnit.test('activeView', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            pageCount: 2
        };
        var instance, view1, view2;

        QUnit.expect(24);

        assert.equal($container.children().length, 0, "The container is empty");

        instance = pagesManagerFactory($container, config);
        assert.equal(instance.getActiveView(), null, 'There is no active view for now');

        instance.setActiveView(1);
        assert.equal(typeof instance.getActiveView(), "object", "There is an active view");

        view1 = instance.getView(1);
        assert.equal(typeof view1, "object", "The returned view is an object");
        assert.equal(view1, instance.getActiveView(), 'The view 1 is the active view');
        assert.ok(!view1.getElement().hasClass('hidden'), "The view 1 is visible");
        assert.equal(instance.getActiveView().pageNum, 1, 'The page view has the right page number');


        instance.setActiveView(2);
        assert.equal(typeof instance.getActiveView(), "object", "There is an active view");

        view2 = instance.getView(2);
        assert.equal(typeof view2, "object", "The returned view is an object");
        assert.equal(view2, instance.getActiveView(), 'The view 2 is the active view');
        assert.ok(view1.getElement().hasClass('hidden'), "The view 1 is now hidden");
        assert.ok(!view2.getElement().hasClass('hidden'), "The view 2 is visible");
        assert.equal(instance.getActiveView().pageNum, 2, 'The page view has the right page number');

        instance.setActiveView(1);
        assert.equal(typeof instance.getActiveView(), "object", "There is an active view");

        assert.equal(view1, instance.getActiveView(), 'The view 1 is the active view');
        assert.ok(!view1.getElement().hasClass('hidden'), "The view 1 is now visible");
        assert.ok(view2.getElement().hasClass('hidden'), "The view 2 is now hidden");
        assert.equal(instance.getActiveView().pageNum, 1, 'The page view has the right page number');

        instance.setActiveView(3);
        assert.equal(typeof instance.getActiveView(), "object", "There is an active view");

        assert.equal(view2, instance.getActiveView(), 'The view 2 is the active view');
        assert.ok(view1.getElement().hasClass('hidden'), "The view 1 is now hidden");
        assert.ok(!view2.getElement().hasClass('hidden'), "The view 2 is now visible");
        assert.equal(instance.getActiveView().pageNum, 3, 'The page view has the right page number');

        assert.equal($container.children().length, 2, "The container contains 2 children");

        instance.destroy();
    });


    QUnit.asyncTest('renderPage', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {};
        var instance, view;
        var rendered = false;
        var requestedWidth = 200;
        var requestedHeight = 100;
        var expectedWidth = 200;
        var expectedHeight = 100;

        QUnit.expect(10);

        assert.equal($container.children().length, 0, "The container is empty");

        instance = pagesManagerFactory($container, config);

        pdfjs.on('pageRender', function() {
            assert.ok(true, "The page is rendering");
            rendered = true;
        });

        pdfjs.viewportWidth = 400;
        pdfjs.viewportHeight = 200;

        pdfjs.getDocument(pdfUrl).then(function (pdf) {
            return pdf.getPage(1).then(function (page) {
                return instance.renderPage(page).then(function() {
                    assert.ok(!rendered, 'The page is not rendered as there is not active view');
                    assert.equal($container.children().length, 0, "The container is empty");

                    instance.setActiveView(1);
                    assert.equal($container.children().length, 1, "The container contains a child");

                    view = instance.getActiveView();
                    assert.notEqual(view.getDrawLayerElement().width(), expectedWidth, 'The view is not ' + expectedWidth + ' pixels width');
                    assert.notEqual(view.getDrawLayerElement().height(), expectedHeight, 'The view is not ' + expectedHeight + ' pixels height');
                    $container.width(requestedWidth).height(requestedHeight);

                    return instance.renderPage(page).then(function() {
                        assert.ok(rendered, 'The page has been rendered in the active view');

                        assert.equal(view.getDrawLayerElement().width(), expectedWidth, 'The view is now ' + expectedWidth + ' pixels width');
                        assert.equal(view.getDrawLayerElement().height(), expectedHeight, 'The view is now ' + expectedHeight + ' pixels height');

                        instance.destroy();

                        QUnit.start();
                    });
                });
            });
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.test('destroy', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            pageCount: 2
        };
        var instance, view1, view2;

        QUnit.expect(8);

        assert.equal($container.children().length, 0, "The container is empty");

        instance = pagesManagerFactory($container, config);
        view1 = instance.getView(1);
        assert.equal($container.children().length, 1, "The container contains a child");

        view2 = instance.getView(2);
        assert.equal($container.children().length, 2, "The container contains 2 children");

        instance.setActiveView(1);
        assert.equal(typeof instance.getActiveView(), "object", "There is an active view");

        instance.destroy();

        assert.equal($container.children().length, 0, "The container is now empty");
        assert.equal(instance.getActiveView(), null, 'There is no active view');
        assert.equal(view1.getElement(), null, 'The view 1 is destroyed');
        assert.equal(view2.getElement(), null, 'The view 2 is destroyed');
    });

});
