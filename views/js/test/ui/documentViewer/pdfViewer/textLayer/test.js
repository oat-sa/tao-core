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
    'pdfjs-dist/build/pdf',
    'ui/documentViewer/providers/pdfViewer/pdfjs/textLayer'
], function ($, Promise, pdfjs, textLayerFactory) {
    'use strict';

    var pdfUrl = location.href.replace('/pdfViewer/textLayer/test.html', '/sample/demo.pdf');
    var textLayerApi;


    QUnit.module('pdfViewer TextLayer factory');


    QUnit.test('module', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs
        };
        var instance;

        QUnit.expect(2);

        assert.equal(typeof textLayerFactory, 'function', "The pdfViewer TextLayer module exposes a function");

        instance = textLayerFactory($container, config);
        assert.equal(typeof instance, 'object', "The pdfViewer TextLayer factory provides an object");

        instance.destroy();
    });


    textLayerApi = [
        {name: 'getContainer', title: 'getContainer'},
        {name: 'getFullText', title: 'getFullText'},
        {name: 'getTextContent', title: 'getTextContent'},
        {name: 'setTextContent', title: 'setTextContent'},
        {name: 'setTextContentFromPage', title: 'setTextContentFromPage'},
        {name: 'render', title: 'render'},
        {name: 'destroy', title: 'destroy'}
    ];

    QUnit
        .cases(textLayerApi)
        .test('instance API ', function (data, assert) {
            var $container = $('#qunit-fixture');
            var config = {
                PDFJS: pdfjs
            };
            var instance = textLayerFactory($container, config);

            QUnit.expect(1);

            assert.equal(typeof instance[data.name], 'function', 'The pdfViewer TextLayer instance exposes a "' + data.name + '" function');

            instance.destroy();
        });


    QUnit.module('pdfViewer TextLayer implementation', {
        teardown: function () {
            pdfjs.removeAllListeners();
        }
    });


    QUnit.test('error', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {};

        QUnit.expect(1);

        assert.throws(function () {
            textLayerFactory($container, config);
        }, "The pdfViewer TextLayer factory triggers an error if PDF.js is missing");
    });


    QUnit.test('getContainer', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs
        };
        var instance = textLayerFactory($container, config);

        QUnit.expect(3);

        assert.equal(typeof instance.getContainer(), "object", "The getContainer() method returns an object");
        assert.equal(instance.getContainer().length, 1, "The container exists");
        assert.equal(instance.getContainer(), $container, "The container is the provided one");

        instance.destroy();
    });


    QUnit.test('textContent', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs
        };
        var textContent = {
            items: [{str: 'This'}, {str: 'is'}, {str: 'a'}, {str: 'test'}]
        };
        var expectedFullText = 'This is a test';
        var instance = textLayerFactory($container, config);

        pdfjs.textContent = [expectedFullText];

        QUnit.expect(7);

        assert.equal(instance.getTextContent(), null, "There is no textContent at this moment");
        assert.equal(instance.getFullText(), null, "There is no full text at this moment");

        instance.setTextContent(textContent);
        assert.equal(instance.getTextContent(), textContent, "The textContent has been set");
        assert.equal(instance.getFullText(), expectedFullText, "The full text has been set");

        textContent.items.push({str: '!'});
        assert.equal(instance.getFullText(), expectedFullText, "The full text has not been changed");

        expectedFullText += ' !';
        instance.setTextContent(textContent);
        assert.equal(instance.getTextContent(), textContent, "The textContent has been set");
        assert.equal(instance.getFullText(), expectedFullText, "The full text has been set");

        instance.destroy();
    });


    QUnit.asyncTest('textContent from page', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs
        };
        var instance = textLayerFactory($container, config);
        var expectedFullText = 'This is a test';

        pdfjs.textContent = [expectedFullText];

        QUnit.expect(4);

        assert.equal(instance.getTextContent(), null, "There is no textContent at this moment");
        assert.equal(instance.getFullText(), null, "There is no full text at this moment");

        pdfjs.getDocument(pdfUrl).then(function (pdf) {
            return pdf.getPage(1).then(function (page) {
                return instance.setTextContentFromPage(page).then(function (textContent) {
                    assert.equal(instance.getTextContent(), textContent, "The textContent has been set");
                    assert.equal(instance.getFullText(), expectedFullText, "The full text has been set");

                    instance.destroy();

                    QUnit.start();
                });
            });
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('render', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs
        };
        var instance = textLayerFactory($container, config);
        var expectedFullText = 'Thisisatest';

        pdfjs.textContent = ['This is a test'];

        QUnit.expect(4);

        assert.equal($container.children().length, 0, "The DOM container is empty");

        pdfjs.on('textLayer', function () {
            assert.ok(true, 'The text layer is rendering');
        });

        pdfjs.getDocument(pdfUrl).then(function (pdf) {
            return pdf.getPage(1).then(function (page) {
                return instance.setTextContentFromPage(page).then(function () {
                    return instance.render(page.getViewport()).then(function () {
                        assert.notEqual($container.children().length, 0, "The layer contains text");
                        assert.equal($container.text(), expectedFullText, "The layer contains the right text");

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


    QUnit.asyncTest('render multi', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs
        };
        var instance = textLayerFactory($container, config);
        var expectedFullText = 'Thisisatest';

        pdfjs.textContent = ['This is a test'];

        QUnit.expect(5);

        assert.equal($container.children().length, 0, "The DOM container is empty");

        pdfjs.on('textLayer', function () {
            assert.ok(true, 'The text layer is rendering');
        });

        pdfjs.getDocument(pdfUrl).then(function (pdf) {
            return pdf.getPage(1).then(function (page) {
                return instance.setTextContentFromPage(page).then(function () {
                    return Promise.all([
                        instance.render(page.getViewport()),
                        instance.render(page.getViewport())
                    ]).then(function () {
                        assert.notEqual($container.children().length, 0, "The layer contains text");
                        assert.equal($container.text(), expectedFullText, "The layer contains the right text");

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


    QUnit.asyncTest('destroy', function (assert) {
        var $container = $('#qunit-fixture');
        var config = {
            PDFJS: pdfjs
        };
        var instance = textLayerFactory($container, config);

        QUnit.expect(9);

        assert.equal($container.children().length, 0, "The DOM container is empty");

        pdfjs.getDocument(pdfUrl).then(function (pdf) {
            return pdf.getPage(1).then(function (page) {
                return instance.setTextContentFromPage(page).then(function () {
                    return instance.render(page.getViewport()).then(function () {
                        assert.equal(typeof instance.getContainer(), "object", "The getContainer() method returns an object");
                        assert.equal(typeof instance.getTextContent(), "object", "The getTextContent() method returns an object");
                        assert.equal(typeof instance.getFullText(), "string", "The getFullText() method returns a string");
                        assert.notEqual($container.children().length, 0, "The layer contains text");

                        instance.destroy();

                        assert.equal(instance.getContainer(), null, "The container has been forgotten");
                        assert.equal(instance.getTextContent(), null, "The text content has been forgotten");
                        assert.equal(instance.getFullText(), null, "The full text has been forgotten");
                        assert.equal($container.children().length, 0, "The layer has been removed from the DOM");

                        QUnit.start();
                    });
                });
            });
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });

});
