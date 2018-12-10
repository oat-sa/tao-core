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
    'ui/documentViewer/providers/pdfViewer/fallback/viewer'
], function ($, Promise, fallbackFactory) {
    'use strict';

    var headless = /HeadlessChrome/.test(window.navigator.userAgent);
    var pdfUrl = location.href.replace('/pdfViewer/fallback/test.html', '/sample/demo.pdf');


    QUnit.module('pdfViewer Fallback factory');


    QUnit.test('module', function (assert) {
        var $container = $('#qunit-fixture');
        var instance;

        QUnit.expect(5);

        assert.equal(typeof fallbackFactory, 'function', "The pdfViewer Fallback module exposes a function");

        instance = fallbackFactory($container);

        assert.equal(typeof instance, 'object', "The pdfViewer Fallback factory provides an object");
        assert.equal(typeof instance.load, 'function', "The pdfViewer Fallback instance exposes a function load()");
        assert.equal(typeof instance.unload, 'function', "The pdfViewer Fallback instance exposes a function unload()");
        assert.equal(typeof instance.setSize, 'function', "The pdfViewer Fallback instance exposes a function setSize()");
    });


    QUnit.module('pdfViewer Fallback implementation');


    QUnit.asyncTest('render', function (assert) {
        var $container = $('#qunit-fixture');
        var expectedWidth = 256;
        var expectedHeight = 128;
        var instance;
        var promise;

        function checkRenderedStuff() {
            assert.equal($container.find('iframe').length, 1, 'An iframe has been added to the container');
            assert.equal($container.find('iframe').attr('src'), pdfUrl, 'The iframe targets the right file');
            assert.notEqual($container.children().length, 0, 'The container contains some children');

            assert.notEqual($container.find('iframe').width(), expectedWidth, 'The iframe is not ' + expectedWidth + ' pixels width');
            assert.notEqual($container.find('iframe').height(), expectedHeight, 'The iframe is not ' + expectedHeight + ' pixels height');

            instance.setSize(expectedWidth, expectedHeight);

            assert.equal($container.find('iframe').width(), expectedWidth, 'The iframe is now ' + expectedWidth + ' pixels width');
            assert.equal($container.find('iframe').height(), expectedHeight, 'The iframe is now ' + expectedHeight + ' pixels height');

            instance.unload();

            assert.equal($container.find('iframe').length, 0, 'The iframe has been removed from the container');
            assert.equal($container.children().length, 0, 'The container does not contain any children');
        }

        QUnit.expect(13);

        assert.equal($container.children().length, 0, 'The container does not contain any children');

        instance = fallbackFactory($container);
        promise = instance.load(pdfUrl);

        assert.equal(typeof promise, 'object', 'The load() function returns an object');
        assert.ok(promise instanceof Promise, 'The object returned by the load() function is a promise');

        if (headless) {
            assert.ok(true, 'Using a headless browser, the check for PDF load is disabled');
            checkRenderedStuff();
            QUnit.start();
        } else {
            promise.then(function () {
                assert.ok(true, 'The PDF file has been loaded');
                checkRenderedStuff();
                QUnit.start();
            }).catch(function() {
                assert.ok('false', 'The PDF file should be loaded');
                QUnit.start();
            });
        }
    });

});
