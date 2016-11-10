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
    'lodash',
    'core/promise',
    'ui/documentViewer'
], function (_, Promise, documentViewer) {
    'use strict';


    QUnit.module('documentViewer factory');


    QUnit.test('module', function (assert) {
        QUnit.expect(5);
        assert.equal(typeof documentViewer, 'function', "The documentViewer module exposes a function");
        assert.equal(typeof documentViewer(), 'object', "The documentViewer factory produces an object");
        assert.notStrictEqual(documentViewer(), documentViewer(), "The documentViewer factory provides a different object on each call");
        assert.equal(typeof documentViewer.registerProvider, 'function', "The instance module exposes a function registerProvider()");
        assert.equal(typeof documentViewer.clearProviders, 'function', "The instance module exposes a function clearProviders()");
    });


    var documentViewerApi = [
        {name: 'init', title: 'init'},
        {name: 'destroy', title: 'destroy'},
        {name: 'render', title: 'render'},
        {name: 'setSize', title: 'setSize'},
        {name: 'getType', title: 'getType'},
        {name: 'getUrl', title: 'getUrl'},
        {name: 'getViewer', title: 'getViewer'},
        {name: 'load', title: 'load'},
        {name: 'unload', title: 'unload'},
        {name: 'show', title: 'show'},
        {name: 'hide', title: 'hide'},
        {name: 'enable', title: 'enable'},
        {name: 'disable', title: 'disable'},
        {name: 'is', title: 'is'},
        {name: 'setState', title: 'setState'},
        {name: 'getContainer', title: 'getContainer'},
        {name: 'getElement', title: 'getElement'},
        {name: 'getTemplate', title: 'getTemplate'},
        {name: 'setTemplate', title: 'setTemplate'},
        {name: 'trigger', title: 'trigger'},
        {name: 'before', title: 'before'},
        {name: 'on', title: 'on'},
        {name: 'after', title: 'after'}
    ];

    QUnit
        .cases(documentViewerApi)
        .test('has API ', function (data, assert) {
            var instance = documentViewer('mock');
            assert.equal(typeof instance[data.name], 'function', 'The documentViewer instance exposes a "' + data.name + '" function');
        });


    QUnit.module('implementation', {
        setup: function () {
            documentViewer.clearProviders();
        }
    });


    QUnit.test('register error', function (assert) {
        QUnit.expect(4);

        assert.throws(function () {
            documentViewer.registerProvider('mock');
        }, 'An error is thrown when no provider is provided');

        assert.throws(function () {
            documentViewer.registerProvider('mock', {load: _.noop});
        }, 'An error is thrown when a provider without init method is provided');

        assert.throws(function () {
            documentViewer.registerProvider('mock', {init: _.noop});
        }, 'An error is thrown when a provider without load method is provided');

        documentViewer.registerProvider('mock', {init: _.noop, load: _.noop});
        assert.ok(true, 'No error is thrown when a well formatted provider is provided');
    });


    QUnit.test('load error', function (assert) {
        var viewer = documentViewer();

        QUnit.expect(6);

        assert.throws(function () {
            viewer.load();
        }, 'An error is thrown when no parameter is provided to the load() method');

        assert.throws(function () {
            viewer.load('', 'pdf');
        }, 'An error is thrown when an empty url is provided to the load() method');

        assert.throws(function () {
            viewer.load(null, 'pdf');
        }, 'An error is thrown when the url provided to the load() method is not a string');

        assert.throws(function () {
            viewer.load('/test.pdf', '');
        }, 'An error is thrown when an empty type is provided to the load() method');

        assert.throws(function () {
            viewer.load('/test.pdf', 10);
        }, 'An error is thrown when the type provided to the load() method is not a string');

        documentViewer.registerProvider('pdf', {init: _.noop, load: _.noop});

        viewer.load('/test.pdf', 'pdf');
        assert.ok(true, 'No error is thrown when the load() method is called with the right parameters');
    });


    QUnit.test('getType', function (assert) {
        var viewer = documentViewer();

        QUnit.expect(3);

        documentViewer.registerProvider('pdf', {init: _.noop, load: _.noop});

        assert.equal(viewer.getType(), null, 'No type is defined');

        viewer.load('/test.pdf', 'pdf');

        assert.equal(viewer.getType(), 'pdf', 'The type is defined');

        viewer.unload();

        assert.equal(viewer.getType(), null, 'No type is defined');
    });


    QUnit.test('getUrl', function (assert) {
        var viewer = documentViewer();

        QUnit.expect(3);

        documentViewer.registerProvider('pdf', {init: _.noop, load: _.noop});

        assert.equal(viewer.getUrl(), null, 'No url is defined');

        viewer.load('/test.pdf', 'pdf');

        assert.equal(viewer.getUrl(), '/test.pdf', 'The url is defined');

        viewer.unload();

        assert.equal(viewer.getUrl(), null, 'No url is defined');
    });


    QUnit.test('getViewer', function (assert) {
        var viewer = documentViewer();

        QUnit.expect(3);

        documentViewer.registerProvider('pdf', {init: _.noop, load: _.noop});

        assert.equal(viewer.getViewer(), null, 'No viewer is defined');

        viewer.load('/test.pdf', 'pdf');

        assert.equal(typeof viewer.getViewer(), 'object', 'The viewer is defined');

        viewer.unload();

        assert.equal(viewer.getViewer(), null, 'No viewer is defined');
    });


    QUnit.asyncTest('setSize', function (assert) {
        var viewer = documentViewer();
        var expectedWidth = 20;
        var expectedHeight = 10;

        QUnit.expect(16);

        documentViewer.registerProvider('pdf', {init: _.noop, load: _.noop});

        assert.equal(viewer.getViewer(), null, 'No viewer is defined');

        viewer.setSize(expectedWidth, expectedHeight);

        assert.equal(viewer.config.width, expectedWidth, 'The width has been recorded');
        assert.equal(viewer.config.height, expectedHeight, 'The height has been recorded');

        viewer.on('resized', function(width, height) {
            assert.ok('true', 'The document has been resized');
            assert.equal(width, expectedWidth, 'The right width has been provided');
            assert.equal(height, expectedHeight, 'The right height has been provided');
        }).on('loaded', function() {
            assert.equal(typeof viewer.getViewer(), 'object', 'The viewer is defined');
            assert.equal(viewer.getViewer().config.width, expectedWidth, 'The width has been forwarded');
            assert.equal(viewer.getViewer().config.height, expectedHeight, 'The height has been forwarded');

            expectedWidth = 200;
            expectedHeight = 100;
            viewer.setSize(expectedWidth, expectedHeight);

            assert.equal(viewer.config.width, expectedWidth, 'The width has been changed');
            assert.equal(viewer.config.height, expectedHeight, 'The height has been changed');
            assert.equal(viewer.getViewer().config.width, expectedWidth, 'The width has been forwarded');
            assert.equal(viewer.getViewer().config.height, expectedHeight, 'The height has been forwarded');

            viewer.unload();

            assert.equal(viewer.getViewer(), null, 'No viewer is defined');
            assert.equal(viewer.config.width, expectedWidth, 'The width is still recorded');
            assert.equal(viewer.config.height, expectedHeight, 'The height is still recorded');

            QUnit.start();
        });

        viewer.render().load('/test.pdf', 'pdf');
    });


    QUnit.asyncTest('error event', function (assert) {
        var viewer = documentViewer();

        QUnit.expect(2);

        documentViewer.registerProvider('pdf', {
            init: function() {
                return Promise.reject('test');
            },
            load: _.noop
        });

        assert.equal(viewer.getViewer(), null, 'No viewer is defined');

        viewer.on('error', function(err) {
            assert.ok('true', 'An error has been triggered');
            QUnit.start();
        });

        viewer.load('/test.pdf', 'pdf');
    });


    QUnit.asyncTest('load before render', function (assert) {
        var viewer = documentViewer();

        QUnit.expect(2);

        documentViewer.registerProvider('pdf', {init: _.noop, load: _.noop});

        viewer
            .on('load', function() {
                assert.ok(true, 'The document is loading');
            })
            .on('loaded', function() {
                assert.ok(true, 'The document has been loaded');
                QUnit.start();
            })
            .load('/test.pdf', 'pdf')
            .render();
    });


    QUnit.asyncTest('load after render', function (assert) {
        var viewer = documentViewer();

        QUnit.expect(2);

        documentViewer.registerProvider('pdf', {init: _.noop, load: _.noop});

        viewer
            .on('load', function() {
                assert.ok(true, 'The document is loading');
            })
            .on('loaded', function() {
                assert.ok(true, 'The document has been loaded');
                QUnit.start();
            })
            .render()
            .load('/test.pdf', 'pdf');
    });


    QUnit.asyncTest('unload', function (assert) {
        var viewer = documentViewer();

        QUnit.expect(4);

        documentViewer.registerProvider('pdf', {init: _.noop, load: _.noop});

        viewer
            .on('load', function() {
                assert.ok(true, 'The document is loading');
            })
            .on('loaded', function() {
                assert.ok(true, 'The document has been loaded');

                viewer.unload();
            })
            .on('unload', function() {
                assert.ok(true, 'The document is unloading');
            })
            .on('unloaded', function() {
                assert.ok(true, 'The document has been unloaded');
                QUnit.start();
            })
            .render()
            .load('/test.pdf', 'pdf');
    });


    QUnit.asyncTest('destroy', function (assert) {
        var viewer = documentViewer();
        var count = 0;

        QUnit.expect(7);

        documentViewer.registerProvider('pdf', {init: _.noop, load: _.noop});

        viewer
            .on('load', function() {
                assert.ok(true, 'The document is loading');
            })
            .on('loaded', function() {
                assert.ok(true, 'The document has been loaded');

                if (count ++) {
                    viewer.destroy();
                } else {
                    this.load('/test.pdf', 'pdf');
                }
            })
            .on('unload', function() {
                assert.ok(true, 'The document is unloading');
            })
            .on('unloaded', function() {
                assert.ok(true, 'The document has been unloaded');
                if (count > 1) {
                    QUnit.start();
                }
            })
            .render()
            .load('/test.pdf', 'pdf');
    });

});
