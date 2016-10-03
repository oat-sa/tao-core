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
    'ui/documentViewer/providers/pdfViewer/pdfjs/pageView'
], function ($, pageViewFactory) {
    'use strict';


    var pageViewApi = [
        { name : 'getContainer', title : 'getContainer' },
        { name : 'getPage', title : 'getPage' },
        { name : 'getTextLayer', title : 'getTextLayer' },
        { name : 'getCanvas', title : 'getCanvas' },
        { name : 'getRenderingContext', title : 'getRenderingContext' },
        { name : 'setSize', title : 'setSize' },
        { name : 'show', title : 'show' },
        { name : 'hide', title : 'hide' },
        { name : 'destroy', title : 'destroy' }
    ];


    QUnit.module('pdfViewer PageView factory');


    QUnit.test('module', function (assert) {
        var instance;

        QUnit.expect(3);

        assert.equal(typeof pageViewFactory, 'function', "The pdfViewer PageView module exposes a function");
        assert.equal(typeof pageViewFactory.normalizeScale, 'function', "The pdfViewer PageView factory exposes a normalizeScale helper");

        instance = pageViewFactory(1);
        assert.equal(typeof instance, 'object', "The pdfViewer PageView factory provides an object");
    });


    QUnit
        .cases(pageViewApi)
        .test('instance API ', function(data, assert) {
            var instance = pageViewFactory(1);
            QUnit.expect(1);
            assert.equal(typeof instance[data.name], 'function', 'The pdfViewer PageView instance exposes a "' + data.name + '" function');
        });



    QUnit.module('pdfViewer PageView implementation');


    QUnit.test('attributes', function (assert) {
        var instance;
        var pageNum = 1;

        QUnit.expect(4);

        instance = pageViewFactory(pageNum);
        assert.equal(instance.pageNum, pageNum, "The pdfViewer PageView instance contains the right page number");
        assert.equal(typeof instance.scale, "number", "The pdfViewer PageView instance has a default scale factor");
        assert.ok(instance.scale > 0, "The pdfViewer PageView instance has a correct scale factor");
        assert.equal(instance.rendered, false, "The pdfViewer PageView instance is not rendered");
    });


    QUnit.test('getContainer', function (assert) {
        var instance;
        var pageNum = 1;

        QUnit.expect(3);

        instance = pageViewFactory(pageNum);
        assert.equal(typeof instance.getContainer(), "object", "The getContainer() method returns an object");
        assert.equal(instance.getContainer().length, 1, "The container exists");
        assert.equal(instance.getContainer().find('canvas').length, 1, "The container contains a canvas");
    });


    QUnit.test('getPage', function (assert) {
        var instance;
        var pageNum = 1;

        QUnit.expect(4);

        instance = pageViewFactory(pageNum);
        assert.equal(typeof instance.getPage(), "object", "The getPage() method returns an object");
        assert.equal(instance.getPage().length, 1, "The page panel exists");
        assert.ok(instance.getPage().is('canvas'), "The page panel is a canvas");
        assert.ok(instance.getPage().parent().is(instance.getContainer()), "The page panel is contained by the page container");
    });


    QUnit.test('getTextLayer', function (assert) {
        var instance;
        var pageNum = 1;

        QUnit.expect(4);

        instance = pageViewFactory(pageNum);
        assert.equal(typeof instance.getTextLayer(), "object", "The getTextLayer() method returns an object");
        assert.equal(instance.getTextLayer().length, 1, "The text layer exists");
        assert.ok(instance.getTextLayer().is('div'), "The text layer is a div");
        assert.ok(instance.getTextLayer().parent().is(instance.getContainer()), "The text layer is contained by the page container");
    });


    QUnit.test('getCanvas', function (assert) {
        var instance;
        var pageNum = 1;

        QUnit.expect(4);

        instance = pageViewFactory(pageNum);
        assert.equal(typeof instance.getCanvas(), "object", "The getCanvas() method returns an object");
        assert.ok($(instance.getCanvas()).is('canvas'), "This is a canvas");
        assert.ok($(instance.getCanvas()).is(instance.getPage()), "The canvas is the page panel");
        assert.ok($(instance.getCanvas()).parent().is(instance.getContainer()), "The canvas is contained by the page container");
    });


    QUnit.test('getRenderingContext', function (assert) {
        var instance;
        var pageNum = 1;

        QUnit.expect(2);

        instance = pageViewFactory(pageNum);
        assert.equal(typeof instance.getRenderingContext(), "object", "The getRenderingContext() method returns an object");
        assert.equal(instance.getRenderingContext(), instance.getCanvas().getContext('2d'), "This is the canvas rendering context");
    });


    QUnit.test('setSize', function (assert) {
        var instance;
        var pageNum = 1;
        var expectedWidth = 320;
        var expectedHeight = 240;
        var viewport = {
            width: 640,
            height: 480
        };
        var oldViewport = {
            width: 640,
            height: 480
        };

        QUnit.expect(24);

        instance = pageViewFactory(pageNum);

        assert.notEqual(instance.getContainer().width(), expectedWidth, "The page container is not " + expectedWidth + " pixels width");
        assert.notEqual(instance.getContainer().height(), expectedHeight, "The page container is not " + expectedHeight + " pixels height");

        assert.notEqual(instance.getPage().width(), expectedWidth, "The page panel is not " + expectedWidth + " pixels width");
        assert.notEqual(instance.getPage().height(), expectedHeight, "The page panel is not " + expectedHeight + " pixels height");

        assert.notEqual(instance.getTextLayer().width(), expectedWidth, "The text layer is not " + expectedWidth + " pixels width");
        assert.notEqual(instance.getTextLayer().height(), expectedHeight, "The text layer is not " + expectedHeight + " pixels height");

        assert.notEqual(instance.getCanvas().width, viewport.width, "The canvas viewport is not " + viewport.width + " pixels width");
        assert.notEqual(instance.getCanvas().height, viewport.height, "The canvas viewport is not " + viewport.height + " pixels height");

        instance.setSize(expectedWidth, expectedHeight, viewport);

        assert.equal(instance.getContainer().width(), expectedWidth, "The page container is now " + expectedWidth + " pixels width");
        assert.equal(instance.getContainer().height(), expectedHeight, "The page container is now " + expectedHeight + " pixels height");

        assert.equal(instance.getPage().width(), expectedWidth, "The page panel is now " + expectedWidth + " pixels width");
        assert.equal(instance.getPage().height(), expectedHeight, "The page panel is now " + expectedHeight + " pixels height");

        assert.equal(instance.getTextLayer().width(), expectedWidth, "The text layer is now " + expectedWidth + " pixels width");
        assert.equal(instance.getTextLayer().height(), expectedHeight, "The text layer is now " + expectedHeight + " pixels height");

        assert.equal(instance.getCanvas().width, viewport.width, "The canvas viewport is now " + viewport.width + " pixels width");
        assert.equal(instance.getCanvas().height, viewport.height, "The canvas viewport is now " + viewport.height + " pixels height");

        expectedWidth = 300;
        expectedHeight = 200;
        viewport.width = 800;
        viewport.height = 600;

        instance.setSize(expectedWidth, expectedHeight);

        assert.equal(instance.getContainer().width(), expectedWidth, "The page container is now " + expectedWidth + " pixels width");
        assert.equal(instance.getContainer().height(), expectedHeight, "The page container is now " + expectedHeight + " pixels height");

        assert.equal(instance.getPage().width(), expectedWidth, "The page panel is now " + expectedWidth + " pixels width");
        assert.equal(instance.getPage().height(), expectedHeight, "The page panel is now " + expectedHeight + " pixels height");

        assert.equal(instance.getTextLayer().width(), expectedWidth, "The text layer is now " + expectedWidth + " pixels width");
        assert.equal(instance.getTextLayer().height(), expectedHeight, "The text layer is now " + expectedHeight + " pixels height");

        assert.equal(instance.getCanvas().width, oldViewport.width, "The canvas viewport is still " + oldViewport.width + " pixels width");
        assert.equal(instance.getCanvas().height, oldViewport.height, "The canvas viewport is still " + oldViewport.height + " pixels height");
    });


    QUnit.test('show/hide', function (assert) {
        var instance;
        var pageNum = 1;

        QUnit.expect(3);

        instance = pageViewFactory(pageNum);

        assert.ok(instance.getContainer().hasClass('hidden'), "The page container is hidden by default");

        instance.show();

        assert.ok(!instance.getContainer().hasClass('hidden'), "The page container is now visible");

        instance.hide();

        assert.ok(instance.getContainer().hasClass('hidden'), "The page container is now hidden");
    });


    QUnit.test('destroy', function (assert) {
        var $fixture = $('#qunit-fixture');
        var instance;
        var pageNum = 1;

        QUnit.expect(13);

        instance = pageViewFactory(pageNum);
        assert.equal(typeof instance.getContainer(), "object", "The getContainer() method returns an object");
        assert.equal(typeof instance.getPage(), "object", "The getPage() method returns an object");
        assert.equal(typeof instance.getTextLayer(), "object", "The getTextLayer() method returns an object");
        assert.equal(typeof instance.getCanvas(), "object", "The getCanvas() method returns an object");
        assert.equal(typeof instance.getRenderingContext(), "object", "The getRenderingContext() method returns an object");

        assert.equal($fixture.children().length, 0, "The DOM container is empty");
        $fixture.append(instance.getContainer());
        assert.ok(instance.getContainer().parent().is($fixture), "The page container has been added to the DOM");

        instance.destroy();
        assert.equal(instance.getContainer(), null, "The page container is destroyed");
        assert.equal(instance.getPage(), null, "The page panel is destroyed");
        assert.equal(instance.getTextLayer(), null, "The text layer is destroyed");
        assert.equal(instance.getCanvas(), null, "The canvas is destroyed");
        assert.equal(instance.getRenderingContext(), null, "The rendering context is destroyed");
        assert.equal($fixture.children().length, 0, "The page container has been removed from the DOM");
    });


    QUnit.test('normalizeScale', function (assert) {
        QUnit.expect(6);

        assert.equal(pageViewFactory.normalizeScale(-10), pageViewFactory.MIN_SCALE, "The normalizeScale() helper has corrected the negative scale");
        assert.equal(pageViewFactory.normalizeScale(0), pageViewFactory.DEFAULT_SCALE, "The normalizeScale() helper has corrected the empty scale");
        assert.equal(pageViewFactory.normalizeScale(null), pageViewFactory.DEFAULT_SCALE, "The normalizeScale() helper has corrected the null scale");
        assert.equal(pageViewFactory.normalizeScale("dd"), pageViewFactory.DEFAULT_SCALE, "The normalizeScale() helper has corrected the wrong scale");
        assert.equal(pageViewFactory.normalizeScale(5), 5, "The normalizeScale() helper has not corrected the correct scale");
        assert.equal(pageViewFactory.normalizeScale(100), pageViewFactory.MAX_SCALE, "The normalizeScale() helper has corrected the huge scale");
    });

});
