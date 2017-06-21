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
    'lodash',
    'core/promise',
    'pdfjs-dist/build/pdf',
    'ui/documentViewer/providers/pdfViewer/pdfjs/textManager'
], function ($, _, Promise, pdfjs, textManagerFactory) {
    'use strict';

    var pdfUrl = location.href.replace('/pdfViewer/textManager/test.html', '/sample/demo.pdf');
    var pdfjsBackup = {};
    var textLayerApi;


    QUnit.module('pdfViewer TextManager factory');


    QUnit.test('module', function (assert) {
        var config = {
            PDFJS: pdfjs
        };
        var instance;

        QUnit.expect(2);

        assert.equal(typeof textManagerFactory, 'function', "The pdfViewer TextManager module exposes a function");

        instance = textManagerFactory(config);
        assert.equal(typeof instance, 'object', "The pdfViewer TextManager factory provides an object");

        instance.destroy();
    });


    textLayerApi = [
        {name: 'setDocument', title: 'setDocument'},
        {name: 'getDocument', title: 'getDocument'},
        {name: 'getContents', title: 'getContents'},
        {name: 'getText', title: 'getText'},
        {name: 'getFullText', title: 'getFullText'},
        {name: 'getPageContent', title: 'getPageContent'},
        {name: 'getPageText', title: 'getPageText'},
        {name: 'renderPage', title: 'renderPage'},
        {name: 'destroy', title: 'destroy'}
    ];

    QUnit
        .cases(textLayerApi)
        .test('instance API ', function (data, assert) {
            var config = {
                PDFJS: pdfjs
            };
            var instance = textManagerFactory(config);

            QUnit.expect(1);

            assert.equal(typeof instance[data.name], 'function', 'The pdfViewer TextManager instance exposes a "' + data.name + '" function');

            instance.destroy();
        });


    QUnit.module('pdfViewer TextManager implementation', {
        setup: function () {
            pdfjsBackup.pageCount = pdfjs.pageCount;
            pdfjsBackup.textContent = pdfjs.textContent;
        },
        teardown: function () {
            pdfjs.removeAllListeners();
            pdfjs.pageCount = pdfjsBackup.pageCount;
            pdfjs.textContent = pdfjsBackup.textContent;
        }
    });


    QUnit.test('error', function (assert) {
        var config = {};

        QUnit.expect(1);

        assert.throws(function () {
            textManagerFactory(config);
        }, "The pdfViewer TextManager factory triggers an error if PDF.js is missing");
    });


    QUnit.asyncTest('setDocument', function (assert) {
        var config = {
            PDFJS: pdfjs
        };
        var instance = textManagerFactory(config);

        QUnit.expect(3);

        assert.equal(instance.getDocument(), null, "The getDocument() method returns null when no document have been set");

        pdfjs.getDocument(pdfUrl).then(function (pdf) {
            instance.setDocument(pdf);

            assert.equal(instance.getDocument(), pdf, "The document has been set");
            assert.equal(typeof instance.getDocument(), "object", "The document is an object");

            instance.destroy();

            QUnit.start();
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('getContents', function (assert) {
        var pageCount = 2;
        var config = {
            PDFJS: pdfjs
        };
        var instance = textManagerFactory(config);
        var textContent = [
            ['Page 1', ' ', 'foo'],
            ['Page 2', ' ', 'bar']
        ];

        QUnit.expect(5 + 7 * pageCount);

        assert.equal(instance.getDocument(), null, "The getDocument() method returns null when no document have been set");

        pdfjs.pageCount = pageCount;
        pdfjs.textContent = textContent;

        instance.getContents().then(function () {
            assert.ok(false, 'The getContents() method should fail when not document is set');
            QUnit.start();
        }).catch(function () {
            assert.ok(true, 'The getContents() method fails when not document is set');

            pdfjs.getDocument(pdfUrl).then(function (pdf) {
                instance.setDocument(pdf);

                return instance.getContents().then(function (contents) {
                    assert.ok(contents instanceof Array, "Received a collection");
                    assert.equal(contents.length, pageCount, "The collection contains the right number of pages contents");

                    _.forEach(contents, function (pageContent, index) {
                        assert.equal(typeof pageContent, 'object', 'The page content is an object');
                        assert.equal(typeof pageContent.content, 'object', 'The page content contains a content entry');
                        assert.equal(typeof pageContent.strings, 'object', 'The page content contains a strings entry');
                        assert.equal(typeof pageContent.text, 'string', 'The page content contains a text entry');
                        assert.ok(pageContent.nodes instanceof Array, 'The page content contains a nodes entry');

                        assert.deepEqual(pageContent.strings, textContent[index], 'The page content contains the right strings');
                        assert.equal(pageContent.text, textContent[index].join(''), 'The page content contains the right text');
                    });

                    return instance.getContents().then(function (_contents) {
                        assert.equal(contents, _contents, "The getContents() method always returns the same object unless the document has been changed");

                        instance.destroy();

                        QUnit.start();
                    });
                });
            }).catch(function () {
                assert.ok('false', 'No error should be triggered');
                QUnit.start();
            });
        });
    });


    QUnit.asyncTest('getText', function (assert) {
        var pageCount = 2;
        var config = {
            PDFJS: pdfjs
        };
        var instance = textManagerFactory(config);
        var textContent = [
            ['Page 1', ' ', 'foo'],
            ['Page 2', ' ', 'bar']
        ];

        QUnit.expect(4 + 2 * pageCount);

        assert.equal(instance.getDocument(), null, "The getDocument() method returns null when no document have been set");

        pdfjs.pageCount = pageCount;
        pdfjs.textContent = textContent;

        instance.getText().then(function () {
            assert.ok(false, 'The getText() method should fail when not document is set');
            QUnit.start();
        }).catch(function () {
            assert.ok(true, 'The getText() method fails when not document is set');

            pdfjs.getDocument(pdfUrl).then(function (pdf) {
                instance.setDocument(pdf);

                return instance.getText().then(function (texts) {
                    assert.ok(texts instanceof Array, "Received a collection");
                    assert.equal(texts.length, pageCount, "The collection contains the right number of pages texts");

                    _.forEach(texts, function (text, index) {
                        assert.equal(typeof text, 'string', 'This is a text entry');
                        assert.equal(text, textContent[index].join(''), 'This is contains the right text');
                    });

                    instance.destroy();

                    QUnit.start();
                });
            }).catch(function () {
                assert.ok('false', 'No error should be triggered');
                QUnit.start();
            });
        });
    });


    QUnit.asyncTest('getFullText', function (assert) {
        var pageCount = 2;
        var config = {
            PDFJS: pdfjs
        };
        var instance = textManagerFactory(config);
        var textContent = [
            'Page 1',
            'Page 2'
        ];

        QUnit.expect(4);

        assert.equal(instance.getDocument(), null, "The getDocument() method returns null when no document have been set");

        pdfjs.pageCount = pageCount;
        pdfjs.textContent = textContent;

        instance.getFullText().then(function () {
            assert.ok(false, 'The getFullText() method should fail when not document is set');
            QUnit.start();
        }).catch(function () {
            assert.ok(true, 'The getFullText() method fails when not document is set');

            pdfjs.getDocument(pdfUrl).then(function (pdf) {
                instance.setDocument(pdf);

                return instance.getFullText().then(function (text) {
                    assert.equal(typeof text, 'string', "Received a string");
                    assert.equal(text, textContent.join(' '), "This is the right text");

                    instance.destroy();

                    QUnit.start();
                });
            }).catch(function () {
                assert.ok('false', 'No error should be triggered');
                QUnit.start();
            });
        });
    });


    QUnit.asyncTest('getPageContent', function (assert) {
        var pageCount = 2;
        var config = {
            PDFJS: pdfjs
        };
        var instance = textManagerFactory(config);
        var textContent = [
            ['Page 1', ' ', 'foo'],
            ['Page 2', ' ', 'bar']
        ];

        QUnit.expect(17);

        assert.equal(instance.getDocument(), null, "The getDocument() method returns null when no document have been set");

        pdfjs.pageCount = pageCount;
        pdfjs.textContent = textContent;

        instance.getPageContent(1).then(function () {
            assert.ok(false, 'The getPageContent() method should fail when not document is set');
            QUnit.start();
        }).catch(function () {
            assert.ok(true, 'The getPageContent() method fails when not document is set');

            pdfjs.getDocument(pdfUrl).then(function (pdf) {
                instance.setDocument(pdf);

                return instance.getPageContent(2).then(function (pageContent2) {
                    assert.equal(typeof pageContent2, 'object', "Received an object");
                    assert.equal(typeof pageContent2.content, 'object', 'The page content contains a content entry');
                    assert.equal(typeof pageContent2.strings, 'object', 'The page content contains a strings entry');
                    assert.equal(typeof pageContent2.text, 'string', 'The page content contains a text entry');
                    assert.ok(pageContent2.nodes instanceof Array, 'The page content contains a nodes entry');

                    assert.deepEqual(pageContent2.strings, textContent[1], 'The page content contains the right strings');
                    assert.equal(pageContent2.text, textContent[1].join(''), 'The page content contains the right text');

                    return instance.getPageContent(1).then(function (pageContent1) {
                        assert.equal(typeof pageContent1, 'object', "Received an object");
                        assert.equal(typeof pageContent1.content, 'object', 'The page content contains a content entry');
                        assert.equal(typeof pageContent1.strings, 'object', 'The page content contains a strings entry');
                        assert.equal(typeof pageContent1.text, 'string', 'The page content contains a text entry');
                        assert.ok(pageContent1.nodes instanceof Array, 'The page content contains a nodes entry');

                        assert.deepEqual(pageContent1.strings, textContent[0], 'The page content contains the right strings');
                        assert.equal(pageContent1.text, textContent[0].join(''), 'The page content contains the right text');

                        assert.notEqual(pageContent1, pageContent2, "Content of page 1 and 2 is different");

                        instance.destroy();

                        QUnit.start();
                    });
                });
            }).catch(function () {
                assert.ok('false', 'No error should be triggered');
                QUnit.start();
            });
        });
    });


    QUnit.asyncTest('getPageText', function (assert) {
        var pageCount = 2;
        var config = {
            PDFJS: pdfjs
        };
        var instance = textManagerFactory(config);
        var textContent = [
            'Page 1',
            'Page 2'
        ];

        QUnit.expect(7);

        assert.equal(instance.getDocument(), null, "The getDocument() method returns null when no document have been set");

        pdfjs.pageCount = pageCount;
        pdfjs.textContent = textContent;

        instance.getPageText(1).then(function () {
            assert.ok(false, 'The getPageText() method should fail when not document is set');
            QUnit.start();
        }).catch(function () {
            assert.ok(true, 'The getPageText() method fails when not document is set');

            pdfjs.getDocument(pdfUrl).then(function (pdf) {
                instance.setDocument(pdf);

                return instance.getPageText(2).then(function (pageText2) {
                    assert.equal(typeof pageText2, 'string', "Received a string");
                    assert.equal(pageText2, textContent[1], 'This is the right text');

                    return instance.getPageText(1).then(function (pageText1) {
                        assert.equal(typeof pageText1, 'string', "Received a string");
                        assert.equal(pageText1, textContent[0], 'This is the right text');

                        assert.notEqual(pageText1, pageText2, "Content of page 1 and 2 is different");

                        instance.destroy();

                        QUnit.start();
                    });
                });
            }).catch(function () {
                assert.ok('false', 'No error should be triggered');
                QUnit.start();
            });
        });
    });


    QUnit.asyncTest('renderPage', function (assert) {
        var config = {
            PDFJS: pdfjs
        };
        var instance = textManagerFactory(config);
        var expectedFullText = 'This is a test!';

        pdfjs.pageCount = 1;
        pdfjs.textContent = [
            ['This is a test', '!']
        ];

        QUnit.expect(3);

        pdfjs.on('textLayer', function () {
            assert.ok(true, 'The text layer is rendering');
        });

        pdfjs.getDocument(pdfUrl).then(function (pdf) {
            instance.setDocument(pdf);

            return pdf.getPage(1).then(function (page) {
                return instance.renderPage(1, page.getViewport()).then(function (layer) {
                    assert.equal(typeof layer, 'object', "The result of the rendering process is an object");
                    assert.equal($(layer).text(), expectedFullText, "The layer contains the right text");

                    instance.destroy();
                    QUnit.start();
                });
            });
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('renderPage multi', function (assert) {
        var config = {
            PDFJS: pdfjs
        };
        var instance = textManagerFactory(config);
        var expectedFullText = 'This is a test!';

        pdfjs.pageCount = 1;
        pdfjs.textContent = [
            ['This is a test', '!']
        ];

        QUnit.expect(5);

        pdfjs.on('textLayer', function () {
            assert.ok(true, 'The text layer is rendering');
        });

        pdfjs.getDocument(pdfUrl).then(function (pdf) {
            instance.setDocument(pdf);

            return pdf.getPage(1).then(function (page) {

                return Promise.all([
                    instance.renderPage(1, page.getViewport()),
                    instance.renderPage(1, page.getViewport())
                ]).then(function (results) {
                    assert.equal(typeof results[0], 'undefined', 'The first rendering process has been halted');
                    assert.equal(typeof results[1], 'object', "The result of the second rendering process is an object");
                    assert.equal($(results[1]).text(), expectedFullText, "The layer contains the right text");

                    instance.destroy();
                    QUnit.start();
                });
            });
        }).catch(function () {
            assert.ok('false', 'No error should be triggered');
            QUnit.start();
        });
    });


    QUnit.asyncTest('destroy', function (assert) {
        var pageCount = 2;
        var config = {
            PDFJS: pdfjs
        };
        var instance = textManagerFactory(config);
        var textContent = [
            ['Page 1', ' ', 'foo'],
            ['Page 2', ' ', 'bar']
        ];

        QUnit.expect(8 + 7 * pageCount);

        assert.equal(instance.getDocument(), null, "The getDocument() method returns null when no document have been set");

        pdfjs.pageCount = pageCount;
        pdfjs.textContent = textContent;

        pdfjs.getDocument(pdfUrl).then(function (pdf) {
            instance.setDocument(pdf);

            assert.equal(instance.getDocument(), pdf, "The document has been set");
            assert.equal(typeof instance.getDocument(), "object", "The document is an object");

            return instance.getContents().then(function (contents) {
                assert.ok(contents instanceof Array, "Received a collection");
                assert.equal(contents.length, pageCount, "The collection contains the right number of pages contents");

                _.forEach(contents, function (pageContent, index) {
                    assert.equal(typeof pageContent, 'object', 'The page content is an object');
                    assert.equal(typeof pageContent.content, 'object', 'The page content contains a content entry');
                    assert.equal(typeof pageContent.strings, 'object', 'The page content contains a strings entry');
                    assert.equal(typeof pageContent.text, 'string', 'The page content contains a text entry');
                    assert.ok(pageContent.nodes instanceof Array, 'The page content contains a nodes entry');

                    assert.deepEqual(pageContent.strings, textContent[index], 'The page content contains the right strings');
                    assert.equal(pageContent.text, textContent[index].join(''), 'The page content contains the right text');
                });

                return instance.getContents().then(function (_contents) {
                    assert.equal(contents, _contents, "The getContents() method always returns the same object unless the document has been changed");

                    instance.destroy();

                    assert.equal(instance.getDocument(), null, "The document has been removed");

                    instance.getContents().then(function () {
                        assert.ok(false, 'The getContents() method should fail when not document is set');
                        QUnit.start();
                    }).catch(function () {
                        assert.ok(true, 'The getContents() method fails when not document is set');
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
