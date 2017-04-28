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
define(['jquery', 'util/shortcut', 'lib/simulator/jquery.simulate'], function($, shortcutHelper) {
    'use strict';


    QUnit.module('shortcut');


    QUnit.test('module', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof shortcutHelper, 'object', "The shortcutHelper module exposes an object");
        assert.equal(typeof shortcutHelper.add, 'function', "The shortcutHelper module exposes a add() function");
        assert.equal(typeof shortcutHelper.remove, 'function', "The shortcutHelper module exposes a remove() function");
    });


    QUnit.module('Keyboard');


    QUnit.asyncTest('add', function(assert) {
        QUnit.expect(4);

        var res = shortcutHelper.add('Meta+C', function(event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'meta+c', 'The keystroke is provided');
            shortcutHelper.remove('Meta+C');
            QUnit.start();
        });

        assert.equal(res, shortcutHelper, 'The helper returns itself');

        $(document).simulate('keydown', {
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


    QUnit.asyncTest('remove', function(assert) {
        QUnit.expect(5);

        shortcutHelper.add('Ctrl+C', function(event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'control+c', 'The keystroke is provided');

            var res = shortcutHelper.remove('Ctrl+C');
            assert.equal(res, shortcutHelper, 'The helper returns itself');

            $(document).on('keydown.test-remove', function() {
                $(document).off('keydown.test-remove');
                assert.ok(true, 'The shortcut has been removed');
                QUnit.start();
            });

            $(document).simulate('keydown', {
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

        $(document).simulate('keydown', {
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


    QUnit.test('exists', function(assert) {
        QUnit.expect(3);

        shortcutHelper.add('Meta+C', _.noop);

        assert.ok(shortcutHelper.exists('meta+c'), 'The registered shortcut must exists');
        assert.ok(!shortcutHelper.exists('shift+c'), 'An unregistered shortcut must not exists');

        shortcutHelper.remove('meta+c');

        assert.ok(!shortcutHelper.exists('meta+c'), 'The removed shortcut must not exists anymore');
    });


    QUnit.asyncTest('clear', function(assert) {
        QUnit.expect(7);

        shortcutHelper.add('Shift+C', function(event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'shift+c', 'The keystroke is provided');

            assert.ok(shortcutHelper.exists('shift+c'), 'The registered shortcut must exists');
            var res = shortcutHelper.clear();

            assert.equal(res, shortcutHelper, 'The helper returns itself');
            assert.ok(!shortcutHelper.exists('shift+c'), 'The removed shortcut must not exists anymore');

            $(document).on('keydown.test-remove', function() {
                $(document).off('keydown.test-remove');
                assert.ok(true, 'The shortcut has been removed');
                QUnit.start();
            });

            $(document).simulate('keydown', {
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

        $(document).simulate('keydown', {
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


    QUnit.module('Mouse');


    QUnit.asyncTest('add', function(assert) {
        QUnit.expect(3);

        shortcutHelper.add('Meta+LeftMouseClick', function(event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'meta+clickLeft', 'The keystroke is provided');
            shortcutHelper.remove('Meta+LeftMouseClick');
            QUnit.start();
        });

        $(document).simulate('click', {
            ctrlKey: false,
            shiftKey: false,
            altKey: false,
            metaKey: true
        });
    });


    QUnit.asyncTest('remove', function(assert) {
        QUnit.expect(4);

        shortcutHelper.add('Ctrl+RightMouseClick', function(event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'control+clickRight', 'The keystroke is provided');

            shortcutHelper.remove('Ctrl+RightMouseClick');

            $(document).on('click.test-remove', function() {
                $(document).off('click.test-remove');
                assert.ok(true, 'The shortcut has been removed');
                QUnit.start();
            });

            $(document).simulate('click', {
                button: 2,
                ctrlKey: true,
                shiftKey: false,
                altKey: false,
                metaKey: false
            });
        });

        $(document).simulate('click', {
            button: 2,
            ctrlKey: true,
            shiftKey: false,
            altKey: false,
            metaKey: false
        });
    });


    QUnit.test('exists', function(assert) {
        QUnit.expect(3);

        shortcutHelper.add('Shift+MouseScrollUp', $.noop);

        assert.ok(shortcutHelper.exists('shift+mouseScrollUp'), 'The registered shortcut must exists');
        assert.ok(!shortcutHelper.exists('shift+mouseScrollDown'), 'An unregistered shortcut must not exists');

        shortcutHelper.remove('shift+mousescrollup');

        assert.ok(!shortcutHelper.exists('shift+mouseScrollUp'), 'The removed shortcut must not exists anymore');
    });


    QUnit.asyncTest('clear', function(assert) {
        QUnit.expect(6);

        shortcutHelper.add('shift+mouseMiddleClick', function(event, keystroke) {
            assert.ok(true, 'The shortcut has been caught');
            assert.equal(typeof event, 'object', 'The event object is provided');
            assert.equal(keystroke, 'shift+clickMiddle', 'The keystroke is provided');

            assert.ok(shortcutHelper.exists('shift+mouseMiddleClick'), 'The registered shortcut must exists');
            shortcutHelper.clear();
            assert.ok(!shortcutHelper.exists('shift+mouseMiddleClick'), 'The removed shortcut must not exists anymore');

            $(document).on('click.test-remove', function() {
                $(document).off('click.test-remove');
                assert.ok(true, 'The shortcut has been removed');
                QUnit.start();
            });

            $(document).simulate('click', {
                button: 1,
                ctrlKey: false,
                shiftKey: true,
                altKey: false,
                metaKey: false
            });
        });

        $(document).simulate('click', {
            button: 1,
            ctrlKey: false,
            shiftKey: true,
            altKey: false,
            metaKey: false
        });
    });


    QUnit.module('Error');


    QUnit.test('keyboard and mouse', function(assert) {
        QUnit.expect(2);

        assert.throws(function() {
            shortcutHelper.add('Ctrl+C+mouseLeftClick', $.noop);
        }, 'The helper refuses to register shortcut that mix keyboard and mouse');

        assert.throws(function() {
            shortcutHelper.add('mouseLeftClick+V', $.noop);
        }, 'The helper refuses to register shortcut that mix keyboard and mouse');
    });

});
