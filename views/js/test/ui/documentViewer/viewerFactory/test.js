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
    'tpl!test/ui/documentViewer/viewerFactory/mock'
], function (_, Promise, viewerFactory, mockTpl) {
    'use strict';


    QUnit.module('viewerFactory factory', {
        setup: function () {
            viewerFactory.registerProvider('mock', {init: _.noop, load: _.noop});
        },
        teardown: function () {
            viewerFactory.clearProviders();
        }
    });


    QUnit.test('module', function (assert) {
        QUnit.expect(5);

        assert.equal(typeof viewerFactory, 'function', "The viewerFactory module exposes a function");
        assert.equal(typeof viewerFactory.registerProvider, 'function', "The instance module exposes a function registerProvider()");
        assert.equal(typeof viewerFactory.getProvider, 'function', "The instance module exposes a function getProvider()");
        assert.equal(typeof viewerFactory('mock'), 'object', "The viewerFactory factory produces an object");
        assert.notStrictEqual(viewerFactory('mock'), viewerFactory('mock'), "The viewerFactory factory provides a different object on each call");
    });


    var viewerFactoryApi = [
        {name: 'init', title: 'init'},
        {name: 'destroy', title: 'destroy'},
        {name: 'render', title: 'render'},
        {name: 'setSize', title: 'setSize'},
        {name: 'show', title: 'show'},
        {name: 'hide', title: 'hide'},
        {name: 'enable', title: 'enable'},
        {name: 'disable', title: 'disable'},
        {name: 'is', title: 'is'},
        {name: 'setState', title: 'setState'},
        {name: 'getContainer', title: 'getContainer'},
        {name: 'getElement', title: 'getElement'},
        {name: 'getType', title: 'getType'},
        {name: 'getUrl', title: 'getUrl'},
        {name: 'getTemplate', title: 'getTemplate'},
        {name: 'setTemplate', title: 'setTemplate'},
        {name: 'trigger', title: 'trigger'},
        {name: 'before', title: 'before'},
        {name: 'on', title: 'on'},
        {name: 'after', title: 'after'}
    ];

    QUnit
        .cases(viewerFactoryApi)
        .test('has API ', function (data, assert) {
            var instance = viewerFactory('mock');
            assert.equal(typeof instance[data.name], 'function', 'The viewerFactory instance exposes a "' + data.name + '" function');
        });


    QUnit.module('provider', {
        setup: function () {
            viewerFactory.clearProviders();
        }
    });


    QUnit.test('register error', function (assert) {
        QUnit.expect(4);

        assert.throws(function () {
            viewerFactory.registerProvider('mock');
        }, 'An error is thrown when no provider is provided');

        assert.throws(function () {
            viewerFactory.registerProvider('mock', {load: _.noop});
        }, 'An error is thrown when a provider without init method is provided');

        assert.throws(function () {
            viewerFactory.registerProvider('mock', {init: _.noop});
        }, 'An error is thrown when a provider without load method is provided');

        viewerFactory.registerProvider('mock', {init: _.noop, load: _.noop});
        assert.ok(true, 'No error is thrown when a well formatted provider is provided');
    });


    QUnit.asyncTest('init()', function (assert) {
        var expectedConfig = {
            url: 'an/url/to/test',
            width: 200,
            height: 100,
            fitToWidth: false,
            allowSearch: false,
            caseSensitiveSearch: false,
            highlightAllMatches: false
        };

        QUnit.expect(3);

        viewerFactory.registerProvider('mock', {
            init: function() {
                assert.ok(true, 'The init method has been delegated');
                assert.deepEqual(this.config, expectedConfig, 'The config has been loaded');
            },
            load: _.noop
        });

        viewerFactory('mock', expectedConfig)
            .on('initialized', function() {
                assert.ok(true, 'The viewer is initialized');
                QUnit.start();
            });

    });


    QUnit.asyncTest('destroy()', function (assert) {
        var expectedConfig = {
            url: 'an/url/to/test',
            width: 200,
            height: 100,
            fitToWidth: false,
            allowSearch: false,
            caseSensitiveSearch: false,
            highlightAllMatches: false
        };

        QUnit.expect(5);

        viewerFactory.registerProvider('mock', {
            init: function() {
                assert.ok(true, 'The init method has been delegated');
                assert.deepEqual(this.config, expectedConfig, 'The config has been loaded');
            },
            unload: function() {
                assert.ok(true, 'The destroy method has been delegated');
            },
            load: _.noop
        });

        viewerFactory('mock', expectedConfig)
            .on('initialized', function() {
                assert.ok(true, 'The viewer is initialized');

                this.destroy();
            })
            .on('unloaded', function() {
                assert.ok(true, 'The viewer is destroyed');

                QUnit.start();
            });

    });


    QUnit.asyncTest('render()', function (assert) {
        var expectedConfig = {
            url: 'an/url/to/test',
            width: 200,
            height: 100,
            fitToWidth: false,
            allowSearch: false,
            caseSensitiveSearch: false,
            highlightAllMatches: false
        };

        QUnit.expect(7);

        viewerFactory.registerProvider('mock', {
            init: function() {
                assert.ok(true, 'The init method has been delegated');
                assert.deepEqual(this.config, expectedConfig, 'The config has been loaded');
            },
            load: function() {
                assert.ok(true, 'The load method has been delegated');
            },
            unload: function() {
                assert.ok(true, 'The destroy method has been delegated');
            }
        });

        viewerFactory('mock', expectedConfig)
            .on('initialized', function() {
                assert.ok(true, 'The viewer is initialized');

                this.render();
            })
            .on('loaded', function() {
                assert.ok(true, 'The viewer has loaded the document');

                this.destroy();
            })
            .on('unloaded', function() {
                assert.ok(true, 'The viewer is destroyed');

                QUnit.start();
            });

    });


    QUnit.asyncTest('setSize()', function (assert) {
        var expectedWidth = 200;
        var expectedHeight = 100;
        var expectedConfig = {
            url: 'an/url/to/test',
            width: 'auto',
            height: 'auto',
            fitToWidth: false,
            allowSearch: false,
            caseSensitiveSearch: false,
            highlightAllMatches: false
        };

        QUnit.expect(13);

        viewerFactory.registerProvider('mock', {
            init: function() {
                assert.ok(true, 'The init method has been delegated');
                assert.deepEqual(this.config, expectedConfig, 'The config has been loaded');
            },
            setSize: function(width, height) {
                assert.ok(true, 'The setSize method has been delegated');
                assert.equal(width, expectedWidth, 'The expected width has been provided');
                assert.equal(height, expectedHeight, 'The expected height has been provided');
            },
            load: function() {
                assert.ok(true, 'The load method has been delegated');
            },
            unload: function() {
                assert.ok(true, 'The destroy method has been delegated');
            }
        });

        viewerFactory('mock', expectedConfig)
            .on('initialized', function() {
                assert.ok(true, 'The viewer is initialized');

                this.render();
            })
            .on('loaded', function() {
                assert.ok(true, 'The viewer has loaded the document');

                this.setSize(expectedWidth, expectedHeight);
            })
            .on('resized', function(width, height) {
                assert.ok(true, 'The viewer has resized the document');
                assert.equal(width, expectedWidth, 'The expected width has been provided');
                assert.equal(height, expectedHeight, 'The expected height has been provided');

                this.destroy();
            })
            .on('unloaded', function() {
                assert.ok(true, 'The viewer is destroyed');

                QUnit.start();
            });

    });

    QUnit.asyncTest('init error', function (assert) {
        QUnit.expect(2);

        viewerFactory.registerProvider('mock', {
            init: function() {
                assert.ok(true, 'The init method has been delegated');
                return Promise.reject(new Error('test'));
            },
            load: _.noop
        });

        viewerFactory('mock')
            .on('error', function() {
                assert.ok(true, 'The viewer has thrown an error when initializing');

                QUnit.start();
            });
    });


    QUnit.asyncTest('load error', function (assert) {
        QUnit.expect(3);

        viewerFactory.registerProvider('mock', {
            init: function() {
                assert.ok(true, 'The init method has been delegated');
                this.render();
            },
            load: function() {
                assert.ok(true, 'The load method has been delegated');
                return Promise.reject(new Error('test'));
            }
        });

        viewerFactory('mock')
            .on('error', function() {
                assert.ok(true, 'The viewer has thrown an error when loading');

                QUnit.start();
            });
    });


    QUnit.asyncTest('setSize error', function (assert) {
        QUnit.expect(4);

        viewerFactory.registerProvider('mock', {
            init: function() {
                assert.ok(true, 'The init method has been delegated');
                this.render();
            },
            load: function() {
                assert.ok(true, 'The load method has been delegated');
                this.setSize(10, 10);
            },
            setSize: function() {
                assert.ok(true, 'The setSize method has been delegated');
                return Promise.reject(new Error('test'));
            }
        });

        viewerFactory('mock')
            .on('error', function() {
                assert.ok(true, 'The viewer has thrown an error when resizing');

                QUnit.start();
            });
    });


    QUnit.asyncTest('unload error', function (assert) {
        QUnit.expect(4);

        viewerFactory.registerProvider('mock', {
            init: function() {
                assert.ok(true, 'The init method has been delegated');
                this.render();
            },
            load: function() {
                assert.ok(true, 'The load method has been delegated');
                this.destroy();
            },
            unload: function() {
                assert.ok(true, 'The unload method has been delegated');
                return Promise.reject(new Error('test'));
            }
        });

        viewerFactory('mock')
            .on('error', function() {
                assert.ok(true, 'The viewer has thrown an error when unloading');

                QUnit.start();
            });
    });


    QUnit.test('getType', function (assert) {
        var viewer;

        QUnit.expect(1);

        viewerFactory.registerProvider('pdf', {init: _.noop, load: _.noop});
        viewer = viewerFactory('pdf', {type: 'pdf', url: '/test.pdf'});
        assert.equal(viewer.getType(), 'pdf', 'The type is defined');
    });


    QUnit.test('getUrl', function (assert) {
        var viewer;

        QUnit.expect(1);

        viewerFactory.registerProvider('pdf', {init: _.noop, load: _.noop});
        viewer = viewerFactory('pdf', {type: 'pdf', url: '/test.pdf'});
        assert.equal(viewer.getUrl(), '/test.pdf', 'The url is defined');
    });


    QUnit.asyncTest('getTemplate()', function (assert) {
        var expectedConfig = {
            url: 'an/url/to/test',
            width: 200,
            height: 100,
            fitToWidth: false,
            allowSearch: false,
            caseSensitiveSearch: false,
            highlightAllMatches: false
        };

        QUnit.expect(8);

        viewerFactory.registerProvider('mock', {
            init: function() {
                assert.ok(true, 'The init method has been delegated');
                assert.deepEqual(this.config, expectedConfig, 'The config has been loaded');
            },
            getTemplate: function() {
                assert.ok(true, 'The getTemplate method has been called');
                return mockTpl;
            },
            unload: function() {
                assert.ok(true, 'The destroy method has been delegated');
            },
            load: function() {
                assert.ok(true, 'The load method has been delegated');
            }
        });

        viewerFactory('mock', expectedConfig)
            .on('initialized', function() {
                assert.ok(true, 'The viewer is initialized');

                this.render();
            })
            .on('loaded', function() {
                assert.ok(true, 'The viewer has loaded the document');

                this.destroy();
            })
            .on('unloaded', function() {
                assert.ok(true, 'The viewer is destroyed');

                QUnit.start();
            });

    });
});
