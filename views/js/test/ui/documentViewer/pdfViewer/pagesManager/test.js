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
    'ui/documentViewer/providers/pdfViewer/pdfjs/pagesManager'
], function ($, pagesManagerFactory) {
    'use strict';


    var pagesManagerApi = [
        { name : 'getContainer', title : 'getContainer' },
        { name : 'getViewsCount', title : 'getViewsCount' },
        { name : 'getView', title : 'getView' },
        { name : 'getActiveView', title : 'getActiveView' },
        { name : 'setActiveView', title : 'setActiveView' },
        { name : 'renderPage', title : 'renderPage' },
        { name : 'destroy', title : 'destroy' }
    ];


    QUnit.module('pdfViewer PagesManager factory');


    QUnit.test('module', function (assert) {
        var $container = $('#qunit-fixture');
        var instance;

        QUnit.expect(2);

        assert.equal(typeof pagesManagerFactory, 'function', "The pdfViewer PagesManager module exposes a function");

        instance = pagesManagerFactory(1, $container, {});
        assert.equal(typeof instance, 'object', "The pdfViewer PagesManager factory provides an object");
    });


    QUnit
        .cases(pagesManagerApi)
        .test('instance API ', function(data, assert) {
            var instance = pagesManagerFactory(1);
            QUnit.expect(1);
            assert.equal(typeof instance[data.name], 'function', 'The pdfViewer PagesManager instance exposes a "' + data.name + '" function');
        });



    QUnit.module('pdfViewer PagesManager implementation');


    QUnit.test('getContainer', function (assert) {
        var $container = $('#qunit-fixture');
        var instance;

        QUnit.expect(3);

        instance = pagesManagerFactory(1, $container, {});
        assert.equal(typeof instance.getContainer(), "object", "The getContainer() method returns an object");
        assert.equal(instance.getContainer().length, 1, "The container exists");
        assert.ok(instance.getContainer().is($container), "This is the right container");
    });


    QUnit.test('getViewsCount', function (assert) {
        var $container = $('#qunit-fixture');
        var instance;
        var pageCount = 5;

        QUnit.expect(1);

        instance = pagesManagerFactory(pageCount, $container, {});
        assert.equal(instance.getViewsCount(), pageCount, "The manager has the right number of views");
    });


    QUnit.test('getView', function (assert) {
        var $container = $('#qunit-fixture');
        var instance, view1, view2;
        var pageCount = 2;

        QUnit.expect(6);

        assert.equal($container.children().length, 0, "The container is empty");

        instance = pagesManagerFactory(pageCount, $container, {});
        view1 = instance.getView(1);
        assert.equal(typeof view1, "object", "The returned view is an object");
        assert.equal($container.children().length, 1, "The container contains a child");
        assert.equal(instance.getView(1), view1, "The manager always returns the same view for a particular page");

        view2 = instance.getView(2);
        assert.notEqual(view1, view2, "The manager provides a view instance per page");
        assert.equal($container.children().length, 2, "The container contains 2 children");
    });


    QUnit.test('activeView', function (assert) {
        var $container = $('#qunit-fixture');
        var instance, view1, view2;
        var pageCount = 2;

        QUnit.expect(16);

        assert.equal($container.children().length, 0, "The container is empty");

        instance = pagesManagerFactory(pageCount, $container, {});
        assert.equal(instance.getActiveView(), null, 'There is no active view for now');

        instance.setActiveView(1);
        assert.equal(typeof instance.getActiveView(), "object", "There is an active view");

        view1 = instance.getView(1);
        assert.equal(typeof view1, "object", "The returned view is an object");
        assert.equal(view1, instance.getActiveView(), 'The view 1 is the active view');
        assert.ok(!view1.getElement().hasClass('hidden'), "The view 1 is visible");


        instance.setActiveView(2);
        assert.equal(typeof instance.getActiveView(), "object", "There is an active view");

        view2 = instance.getView(2);
        assert.equal(typeof view2, "object", "The returned view is an object");
        assert.equal(view2, instance.getActiveView(), 'The view 2 is the active view');
        assert.ok(view1.getElement().hasClass('hidden'), "The view 1 is now hidden");
        assert.ok(!view2.getElement().hasClass('hidden'), "The view 2 is visible");

        instance.setActiveView(1);
        assert.equal(typeof instance.getActiveView(), "object", "There is an active view");

        assert.equal(view1, instance.getActiveView(), 'The view 1 is the active view');
        assert.ok(!view1.getElement().hasClass('hidden'), "The view 1 is now visible");
        assert.ok(view2.getElement().hasClass('hidden'), "The view 2 is now hidden");

        assert.equal($container.children().length, 2, "The container contains 2 children");
    });


    QUnit.asyncTest('renderPage', function (assert) {
        var $container = $('#qunit-fixture');
        var instance, view;
        var rendered = false;
        var requestedWidth = 200;
        var requestedHeight = 100;
        var expectedWidth = 200;
        var expectedHeight = 100;
        var mockPage = {
            getViewport: function getViewport() {
                return {
                    width: 400,
                    height: 200
                };
            },

            render: function render() {
                assert.ok(true, "The page is rendering");
                return {
                    promise: new Promise(function(resolve) {
                        setTimeout(function() {
                            rendered = true;
                            resolve();
                        }, 100);
                    })
                };
            }
        };

        QUnit.expect(10);

        assert.equal($container.children().length, 0, "The container is empty");

        instance = pagesManagerFactory(1, $container, {});

        instance.renderPage(mockPage).then(function() {
            assert.ok(!rendered, 'The page is not rendered as there is not active view');
            assert.equal($container.children().length, 0, "The container is empty");

            instance.setActiveView(1);
            assert.equal($container.children().length, 1, "The container contains a child");

            view = instance.getActiveView();
            assert.notEqual(view.getDrawLayer().width(), expectedWidth, 'The view is not ' + expectedWidth + ' pixels width');
            assert.notEqual(view.getDrawLayer().height(), expectedHeight, 'The view is not ' + expectedHeight + ' pixels height');
            $container.width(requestedWidth).height(requestedHeight);

            instance.renderPage(mockPage).then(function() {
                assert.ok(rendered, 'The page has been rendered in the active view');

                assert.equal(view.getDrawLayer().width(), expectedWidth, 'The view is now ' + expectedWidth + ' pixels width');
                assert.equal(view.getDrawLayer().height(), expectedHeight, 'The view is now ' + expectedHeight + ' pixels height');

                QUnit.start();
            }).catch(function() {
                assert.ok('false', 'No error should be triggered');
                QUnit.start();
            });
        }).catch(function() {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.test('destroy', function (assert) {
        var $container = $('#qunit-fixture');
        var instance, view1, view2;
        var pageCount = 2;

        QUnit.expect(8);

        assert.equal($container.children().length, 0, "The container is empty");

        instance = pagesManagerFactory(pageCount, $container, {});
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
