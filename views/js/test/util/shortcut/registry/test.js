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
    'util/shortcut/registry',
    'lib/simulator/jquery.simulate'
], function ($, Promise, shortcutRegistry) {
    'use strict';

    var shortcutApi = [
        {name: 'set', title: 'set'},
        {name: 'add', title: 'add'},
        {name: 'remove', title: 'remove'},
        {name: 'exists', title: 'exists'},
        {name: 'clear', title: 'clear'},
    ];


    QUnit.module('shortcut');


    QUnit.test('module', function (assert) {
        var $target = $('#qunit-fixture');

        QUnit.expect(3);

        assert.equal(typeof shortcutRegistry, 'function', "The shortcutRegistry module exposes a function");
        assert.equal(typeof shortcutRegistry($target.get(0)), 'object', "The shortcutRegistry factory produces an object");
        assert.notEqual(shortcutRegistry($target.get(0)), shortcutRegistry($target.get(0)), "The shortcutRegistry factory produces a different object at each call");

    });


    QUnit
        .cases(shortcutApi)
        .test('has API ', function (data, assert) {
            var $target = $('#qunit-fixture');
            var instance = shortcutRegistry($target.get(0));
            assert.equal(typeof instance[data.name], 'function', 'The shortcutRegistry instance exposes a "' + data.name + '" function');
        });


    QUnit.module('Keyboard');


    QUnit.asyncTest('add', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));
        var res;

        QUnit.expect(4);

        res = shortcuts.add('Meta+C', function (event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'meta+c', 'The keystroke is provided');
            shortcuts.remove('Meta+C');
            QUnit.start();
        });

        assert.equal(res, shortcuts, 'The helper returns itself');

        $target.simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: false,
            shiftKey: false,
            altKey: false,
            metaKey: true
        });
    });


    QUnit.asyncTest('remove', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));
        var res;

        QUnit.expect(5);

        shortcuts.add('Ctrl+C', function (event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'control+c', 'The keystroke is provided');

            res = shortcuts.remove('Ctrl+C');
            assert.equal(res, shortcuts, 'The helper returns itself');

            $target.on('keydown.test-remove', function () {
                $target.off('keydown.test-remove');
                assert.ok(true, 'The shortcut has been removed');
                QUnit.start();
            });

            $target.simulate('keydown', {
                charCode: 0,
                keyCode: 67,
                which: 67,
                code: 'KeyC',
                key: 'c',
                ctrlKey: true,
                shiftKey: false,
                altKey: false,
                metaKey: false
            });
        });

        $target.simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: true,
            shiftKey: false,
            altKey: false,
            metaKey: false
        });
    });


    QUnit.test('exists', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));

        QUnit.expect(3);

        shortcuts.set('Meta+C');

        assert.ok(shortcuts.exists('meta+c'), 'The registered shortcut must exists');
        assert.ok(!shortcuts.exists('shift+c'), 'An unregistered shortcut must not exists');

        shortcuts.remove('meta+c');

        assert.ok(!shortcuts.exists('meta+c'), 'The removed shortcut must not exists anymore');
    });


    QUnit.asyncTest('clear', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));
        var res;

        QUnit.expect(7);

        shortcuts.add('Shift+C', function (event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'shift+c', 'The keystroke is provided');

            assert.ok(shortcuts.exists('shift+c'), 'The registered shortcut must exists');
            res = shortcuts.clear();

            assert.equal(res, shortcuts, 'The helper returns itself');
            assert.ok(!shortcuts.exists('shift+c'), 'The removed shortcut must not exists anymore');

            $target.on('keydown.test-remove', function () {
                $target.off('keydown.test-remove');
                assert.ok(true, 'The shortcut has been removed');
                QUnit.start();
            });

            $target.simulate('keydown', {
                charCode: 0,
                keyCode: 67,
                which: 67,
                code: 'KeyC',
                key: 'c',
                ctrlKey: false,
                shiftKey: true,
                altKey: false,
                metaKey: false
            });
        });

        $target.simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: false,
            shiftKey: true,
            altKey: false,
            metaKey: false
        });
    });


    QUnit.asyncTest('modifiers', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));
        var res;

        QUnit.expect(4);

        res = shortcuts.add('Ctrl+Alt+Shift+Meta+C', function (event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'control+alt+shift+meta+c', 'The keystroke is provided');
            shortcuts.remove('Ctrl+Alt+Shift+Meta+C');
            QUnit.start();
        });

        assert.equal(res, shortcuts, 'The helper returns itself');

        $target.simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: true,
            shiftKey: true,
            altKey: true,
            metaKey: true
        });
    });


    QUnit.asyncTest('options.prevent', function (assert) {
        var $fixture = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($fixture);

        QUnit.expect(1);

        $('form', $fixture).on('submit', function(event) {
            assert.ok(false, 'The submit event should not be triggered!');
            event.preventDefault();
        });

        shortcuts.add('Enter', function () {
            assert.ok(true, 'The Enter shortcut has been caught');
            shortcuts.remove('Enter');
            QUnit.start();
        }, {
            prevent: true
        });

        $('input[type="text"]', $fixture).simulate('keydown', {
            charCode: 0,
            keyCode: $.simulate.keyCode.ENTER,
            which: $.simulate.keyCode.ENTER,
            code: 'Enter',
            key: $.simulate.keyCode.ENTER,
            ctrlKey: false,
            shiftKey: false,
            altKey: false,
            metaKey: false
        });
    });


    QUnit.asyncTest('options.propagate', function (assert) {
        var $fixture = $('#qunit-fixture');
        var $container = $('<div />').appendTo($fixture);
        var $target = $('<div />').appendTo($container);
        var shortcuts = shortcutRegistry($target);

        QUnit.expect(1);

        shortcuts.add('Alt+C', function () {
            assert.ok(true, 'The Alt+C shortcut has been caught');
            shortcuts.remove('Alt+C');
            QUnit.start();
        }, {
            propagate: false
        });

        $container.on('keydown', function() {
            assert.ok(false, 'The event should not be propagated!');
        });

        $target.simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: false,
            shiftKey: false,
            altKey: true,
            metaKey: false
        });
    });


    QUnit.asyncTest('options.avoidInput', function (assert) {
        var $fixture = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($fixture);

        QUnit.expect(2);

        shortcuts.add('Alt+C', function (event) {
            assert.ok(true, 'The Alt+C shortcut has been caught');
            assert.ok(!$(event.target).closest(':input').length, 'The shortcut does not come from an input')
            shortcuts.remove('Alt+C');
            QUnit.start();
        }, {
            avoidInput: true
        });

        $('input[type="text"]', $fixture).simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: false,
            shiftKey: false,
            altKey: true,
            metaKey: false
        });

        $('textarea', $fixture).simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: false,
            shiftKey: false,
            altKey: true,
            metaKey: false
        });

        $fixture.simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: false,
            shiftKey: false,
            altKey: true,
            metaKey: false
        });
    });


    QUnit.asyncTest('options.allowIn', function (assert) {
        var $fixture = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($fixture);
        var expected = 3;

        QUnit.expect(expected);

        $('form', $fixture).addClass('openbar');

        shortcuts.add('Alt+C', function () {
            assert.ok(true, 'The Alt+C shortcut has been caught');
            if (!--expected) {
                shortcuts.remove('Alt+C');
                QUnit.start();
            }
        }, {
            avoidInput: true,
            allowIn: '.openbar'
        });

        $('input[type="text"]', $fixture).simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: false,
            shiftKey: false,
            altKey: true,
            metaKey: false
        });

        $('textarea', $fixture).simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: false,
            shiftKey: false,
            altKey: true,
            metaKey: false
        });

        $fixture.simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: false,
            shiftKey: false,
            altKey: true,
            metaKey: false
        });
    });


    QUnit.module('Mouse');


    QUnit.asyncTest('add', function (assert) {
        var $target = $(document);
        var shortcuts = shortcutRegistry($target.get(0));

        QUnit.expect(3);

        shortcuts.add('Alt+LeftMouseClick', function (event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'alt+clickLeft', 'The keystroke is provided');
            shortcuts.remove('Alt+LeftMouseClick');
            QUnit.start();
        });

        $target.simulate('click', {
            ctrlKey: false,
            shiftKey: false,
            altKey: true,
            metaKey: false
        });
    });


    QUnit.asyncTest('remove', function (assert) {
        var $target = $(document);
        var shortcuts = shortcutRegistry($target.get(0));

        QUnit.expect(4);

        shortcuts.add('Ctrl+RightMouseClick', function (event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'control+clickRight', 'The keystroke is provided');

            shortcuts.remove('Ctrl+RightMouseClick');

            $target.on('click.test-remove', function () {
                $target.off('click.test-remove');
                assert.ok(true, 'The shortcut has been removed');
                QUnit.start();
            });

            $target.simulate('click', {
                button: 2,
                ctrlKey: true,
                shiftKey: false,
                altKey: false,
                metaKey: false
            });
        });

        $target.simulate('click', {
            button: 2,
            ctrlKey: true,
            shiftKey: false,
            altKey: false,
            metaKey: false
        });
    });


    QUnit.test('exists', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));

        QUnit.expect(3);

        shortcuts.set('Shift+MouseScrollUp');

        assert.ok(shortcuts.exists('shift+mouseScrollUp'), 'The registered shortcut must exists');
        assert.ok(!shortcuts.exists('shift+mouseScrollDown'), 'An unregistered shortcut must not exists');

        shortcuts.remove('shift+mousescrollup');

        assert.ok(!shortcuts.exists('shift+mouseScrollUp'), 'The removed shortcut must not exists anymore');
    });


    QUnit.asyncTest('clear', function (assert) {
        var $target = $(document);
        var shortcuts = shortcutRegistry($target.get(0));

        QUnit.expect(6);

        shortcuts.add('shift+mouseMiddleClick', function (event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'shift+clickMiddle', 'The keystroke is provided');

            assert.ok(shortcuts.exists('shift+mouseMiddleClick'), 'The registered shortcut must exists');
            shortcuts.clear();
            assert.ok(!shortcuts.exists('shift+mouseMiddleClick'), 'The removed shortcut must not exists anymore');

            $target.on('click.test-remove', function () {
                $target.off('click.test-remove');
                assert.ok(true, 'The shortcut has been removed');
                QUnit.start();
            });

            $target.simulate('click', {
                button: 1,
                ctrlKey: false,
                shiftKey: true,
                altKey: false,
                metaKey: false
            });
        });

        $target.simulate('click', {
            button: 1,
            ctrlKey: false,
            shiftKey: true,
            altKey: false,
            metaKey: false
        });
    });


    QUnit.asyncTest('all buttons', function (assert) {
        var $target = $(document);
        var shortcuts = shortcutRegistry($target.get(0));

        QUnit.expect(16);

        Promise.all([
            new Promise(function(resolve) {
                shortcuts.add('Alt+LeftMouseClick', function (event, keystroke) {
                    assert.ok(true, 'The shortcut has been caught');
                    assert.equal(typeof event, 'object', 'The event object is provided');
                    assert.equal(keystroke, 'alt+clickLeft', 'The keystroke is provided');
                    resolve();
                });
            }),
            new Promise(function(resolve) {
                shortcuts.add('Alt+RightMouseClick', function (event, keystroke) {
                    assert.ok(true, 'The shortcut has been caught');
                    assert.equal(typeof event, 'object', 'The event object is provided');
                    assert.equal(keystroke, 'alt+clickRight', 'The keystroke is provided');
                    resolve();
                });
            }),
            new Promise(function(resolve) {
                shortcuts.add('Alt+MiddleMouseClick', function (event, keystroke) {
                    assert.ok(true, 'The shortcut has been caught');
                    assert.equal(typeof event, 'object', 'The event object is provided');
                    assert.equal(keystroke, 'alt+clickMiddle', 'The keystroke is provided');
                    resolve();
                });
            }),
            new Promise(function(resolve) {
                shortcuts.add('Alt+BackMouseClick', function (event, keystroke) {
                    assert.ok(true, 'The shortcut has been caught');
                    assert.equal(typeof event, 'object', 'The event object is provided');
                    assert.equal(keystroke, 'alt+clickBack', 'The keystroke is provided');
                    resolve();
                });
            }),
            new Promise(function(resolve) {
                shortcuts.add('Alt+ForwardMouseClick', function (event, keystroke) {
                    assert.ok(true, 'The shortcut has been caught');
                    assert.equal(typeof event, 'object', 'The event object is provided');
                    assert.equal(keystroke, 'alt+clickForward', 'The keystroke is provided');
                    resolve();
                });
            })
        ]).then(function(){
            shortcuts.clear();
            assert.ok(true, 'All done!');
            QUnit.start();
        }).catch(function() {
            assert.ok(false, 'The promise should not fail!');
            QUnit.start();
        });

        $target.simulate('click', {
            button: 0,
            ctrlKey: false,
            shiftKey: false,
            altKey: true,
            metaKey: false
        });
        $target.simulate('click', {
            button: 1,
            ctrlKey: false,
            shiftKey: false,
            altKey: true,
            metaKey: false
        });
        $target.simulate('click', {
            button: 2,
            ctrlKey: false,
            shiftKey: false,
            altKey: true,
            metaKey: false
        });
        $target.simulate('click', {
            button: 3,
            ctrlKey: false,
            shiftKey: false,
            altKey: true,
            metaKey: false
        });
        $target.simulate('click', {
            button: 4,
            ctrlKey: false,
            shiftKey: false,
            altKey: true,
            metaKey: false
        });
    });


    QUnit.module('Error');


    QUnit.test('keyboard and mouse', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));

        QUnit.expect(2);

        assert.throws(function () {
            shortcuts.add('Ctrl+C+mouseLeftClick', $.noop);
        }, 'The helper refuses to register shortcut that mix keyboard and mouse');

        assert.throws(function () {
            shortcuts.add('mouseLeftClick+V', $.noop);
        }, 'The helper refuses to register shortcut that mix keyboard and mouse');
    });


    QUnit.module('Namespace');


    QUnit.asyncTest('add 2 namespaces, remove by using the full name', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));
        var resolved = false;

        QUnit.expect(4);

        Promise.all([
            new Promise(function(resolve) {
                shortcuts.add('Meta+C.first', function (event, keystroke) {
                    assert.ok(true, 'The first shortcut handler has been called');

                    if (resolved) {
                        assert.ok(false, 'The first shortcut handler should not be called a second time');

                        shortcuts.remove(keystroke);
                        QUnit.start();
                    } else {
                        resolve();
                    }
                });
            }),
            new Promise(function(resolve) {
                shortcuts.add('Meta+C.second', function (event, keystroke) {
                    assert.ok(true, 'The second shortcut handler has been called');

                    if (resolved) {
                        assert.ok(true, 'The second shortcut handler has been called a second time');

                        shortcuts.remove(keystroke);
                        QUnit.start();
                    } else {
                        resolve();
                    }
                });
            })
        ]).then(function(){
            resolved = true;
            shortcuts.remove('Meta+C.first');

            $target.simulate('keydown', {
                charCode: 0,
                keyCode: 67,
                which: 67,
                code: 'KeyC',
                key: 'c',
                ctrlKey: false,
                shiftKey: false,
                altKey: false,
                metaKey: true
            });
        }).catch(function() {
            assert.ok(false, 'The promise should not fail!');
            QUnit.start();
        });

        $target.simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: false,
            shiftKey: false,
            altKey: false,
            metaKey: true
        });
    });


    QUnit.asyncTest('add 2 namespaces, remove by using the namespace', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));
        var resolved = false;

        QUnit.expect(4);

        Promise.all([
            new Promise(function(resolve) {
                shortcuts.add('Meta+C.first', function (event, keystroke) {
                    assert.ok(true, 'The first shortcut handler has been called');

                    if (resolved) {
                        assert.ok(false, 'The first shortcut handler should not be called a second time');

                        shortcuts.remove(keystroke);
                        QUnit.start();
                    } else {
                        resolve();
                    }
                });
            }),
            new Promise(function(resolve) {
                shortcuts.add('Meta+C.second', function (event, keystroke) {
                    assert.ok(true, 'The second shortcut handler has been called');

                    if (resolved) {
                        assert.ok(true, 'The second shortcut handler has been called a second time');

                        shortcuts.remove(keystroke);
                        QUnit.start();
                    } else {
                        resolve();
                    }
                });
            })
        ]).then(function(){
            resolved = true;
            shortcuts.remove('.first');

            $target.simulate('keydown', {
                charCode: 0,
                keyCode: 67,
                which: 67,
                code: 'KeyC',
                key: 'c',
                ctrlKey: false,
                shiftKey: false,
                altKey: false,
                metaKey: true
            });
        }).catch(function() {
            assert.ok(false, 'The promise should not fail!');
            QUnit.start();
        });

        $target.simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: false,
            shiftKey: false,
            altKey: false,
            metaKey: true
        });
    });


    QUnit.test('exists', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));

        QUnit.expect(6);

        shortcuts.add('Meta+C.myShortcut', $.noop);

        assert.ok(shortcuts.exists('meta+c'), 'Check the registered shortcut without namespace');
        assert.ok(shortcuts.exists('meta+c.myShortcut'), 'Check the registered shortcut with its full name and namespace');
        assert.ok(shortcuts.exists('.myShortcut'), 'Check the registered shortcut only by the namespace');
        assert.ok(!shortcuts.exists('meta+c.not'), 'Check a shortcut with an unknown namespace');
        assert.ok(!shortcuts.exists('shift+c'), 'Check an unknown shortcut');

        shortcuts.remove('meta+c');

        assert.ok(!shortcuts.exists('meta+c'), 'The shortcut has been removed');
    });


    QUnit.asyncTest('clear', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));
        var res;

        QUnit.expect(7);

        shortcuts.add('Shift+C.first', function (event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'shift+c', 'The keystroke is provided');

            assert.ok(shortcuts.exists('shift+c'), 'The registered shortcut must exists');
            res = shortcuts.clear();

            assert.equal(res, shortcuts, 'The helper returns itself');
            assert.ok(!shortcuts.exists('shift+c'), 'The removed shortcut must not exists anymore');

            $target.on('keydown.test-remove', function () {
                $target.off('keydown.test-remove');
                assert.ok(true, 'The shortcut has been removed');
                QUnit.start();
            });

            $target.simulate('keydown', {
                charCode: 0,
                keyCode: 67,
                which: 67,
                code: 'KeyC',
                key: 'c',
                ctrlKey: false,
                shiftKey: true,
                altKey: false,
                metaKey: false
            });
        });

        $target.simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: false,
            shiftKey: true,
            altKey: false,
            metaKey: false
        });
    });


    QUnit.module('State');


    QUnit.test('setState/getState', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));
        var res;

        QUnit.expect(3);

        assert.ok(!shortcuts.getState('disabled'), 'The shortcuts registry is enabled');

        res = shortcuts.setState('disabled', true);
        assert.equal(res, shortcuts, 'The helper returns itself');

        assert.ok(shortcuts.getState('disabled'), 'The shortcuts registry is disabled');
    });


    QUnit.test('enable/disable', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));
        var res;

        QUnit.expect(5);

        assert.ok(!shortcuts.getState('disabled'), 'The shortcuts registry is enabled');

        res = shortcuts.disable();
        assert.equal(res, shortcuts, 'The helper returns itself');

        assert.ok(shortcuts.getState('disabled'), 'The shortcuts registry is disabled');

        res = shortcuts.enable();
        assert.equal(res, shortcuts, 'The helper returns itself');

        assert.ok(!shortcuts.getState('disabled'), 'The shortcuts registry is enabled');
    });


    QUnit.asyncTest('disable', function (assert) {
        var $target = $('#qunit-fixture');
        var shortcuts = shortcutRegistry($target.get(0));
        var res;

        QUnit.expect(6);

        res = shortcuts.add('Meta+C', function (event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'meta+c', 'The keystroke is provided');

            res = shortcuts.disable();
            assert.equal(res, shortcuts, 'The helper returns itself');

            $target.on('keydown.test-disabled', function () {
                $target.off('keydown.test-disabled');
                assert.ok(true, 'The shortcut has been disabled');
                QUnit.start();
            });

            $target.simulate('keydown', {
                charCode: 0,
                keyCode: 67,
                which: 67,
                code: 'KeyC',
                key: 'c',
                ctrlKey: false,
                shiftKey: false,
                altKey: false,
                metaKey: true
            });
        });

        assert.equal(res, shortcuts, 'The helper returns itself');

        $target.simulate('keydown', {
            charCode: 0,
            keyCode: 67,
            which: 67,
            code: 'KeyC',
            key: 'c',
            ctrlKey: false,
            shiftKey: false,
            altKey: false,
            metaKey: true
        });
    });
});
