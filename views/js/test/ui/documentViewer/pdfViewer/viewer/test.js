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
    'ui/documentViewer/viewerFactory',
    'ui/documentViewer/providers/pdfViewer',
    'test/ui/documentViewer/pdfViewer/mocks/mockPdfjs'
], function (_, Promise, viewerFactory, pdfViewer, mockPdfjs) {
    'use strict';

    var headless = /HeadlessChrome/.test(window.navigator.userAgent);
    var pdfUrl = location.href.replace('/pdfViewer/viewer/test.html', '/sample/demo.pdf');
    var PDFjsId = 'pdfjs-dist/build/pdf';
    var contexts = [{
        title: 'fallback',
        module: null
    }, {
        title: 'PDF.js',
        module: mockPdfjs
    }];

    QUnit.module('pdfViewer factory', {
        teardown: function () {
            viewerFactory.clearProviders();
        }
    });


    QUnit.test('module', function (assert) {
        QUnit.expect(7);

        assert.equal(typeof pdfViewer, 'object', "The pdfViewer module exposes an object");
        assert.equal(typeof pdfViewer.getTemplate, 'function', "The pdfViewer module exposes a function getTemplate()");
        assert.equal(typeof pdfViewer.init, 'function', "The pdfViewer module exposes a function init()");
        assert.equal(typeof pdfViewer.load, 'function', "The pdfViewer module exposes a function load()");

        viewerFactory.registerProvider('pdf', pdfViewer);
        assert.ok(true, "The pdfViewer provider can be registered without triggering an error");
        assert.equal(typeof viewerFactory('pdf'), 'object', "An instance of pdfViewer can be created");
        assert.notStrictEqual(viewerFactory('pdf'), viewerFactory('pdf'), "A different instance of pdfViewer is created on each call");

        viewerFactory.clearProviders();
    });


    QUnit.module('pdfViewer implementation', {
        setup: function () {
            viewerFactory.registerProvider('pdf', pdfViewer);
        },
        teardown: function () {
            viewerFactory.clearProviders();
        }
    });


    QUnit
        .cases(contexts)
        .asyncTest('render ', function(data, assert) {
            QUnit.expect(headless ? 2 : 3);

            requirejs.undef(PDFjsId);
            define(PDFjsId, data.module);

            viewerFactory('pdf', {
                type: 'pdf',
                url: pdfUrl
            })
                .on('initialized', function () {
                    assert.ok(true, 'The viewer is initialized');

                    if (headless) {
                        this.destroy();
                    } else {
                        this.render('#qunit-fixture');
                    }
                })
                .on('loaded', function () {
                    var self = this;
                    assert.ok(true, 'The PDF file has been loaded');
                    setTimeout(function() {
                        self.destroy();
                    }, 250);
                })
                .on('unloaded', function () {
                    assert.ok(true, 'The viewer is destroyed');
                    QUnit.start();
                });
        });

});
