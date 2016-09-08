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
define(['core/store', 'core/cachedStore'], function(store, cachedStore) {
    'use strict';

    QUnit.module('cachedStore');


    QUnit.test('module', function(assert) {
        QUnit.expect(1);

        assert.equal(typeof cachedStore, 'function', "The cachedStore module exposes a function");
    });


    QUnit.asyncTest('factory', function(assert) {
        QUnit.expect(3);

        var name = 'test1';

        cachedStore(name).then(function(storage1) {
            assert.equal(typeof storage1, 'object', 'An instance of the cachedStore accessor has been created');

            cachedStore(name).then(function(storage2) {
                assert.equal(typeof storage2, 'object', 'Another instance of the cachedStore accessor has been created');
                assert.notEqual(storage1, storage2, 'The factory creates a new instance on each build');

                QUnit.start();
            });
        });
    });


    QUnit.asyncTest('data', function(assert) {
        QUnit.expect(11);

        var name = 'test2';
        var expectedName1 = 'foo';
        var expectedName2 = 'bob';
        var expectedValue1 = 'bar';
        var expectedValue2 = 'fake';

        cachedStore(name).then(function(storage) {
            assert.equal(typeof storage, 'object', 'An instance of the cachedStore accessor has been created');

            storage.setItem(expectedName1, expectedValue1).then(function() {
                assert.ok(true, 'The value1 has been set');

                storage.setItem(expectedName2, expectedValue2).then(function() {
                    assert.ok(true, 'The value2 has been set');

                    var value1 = storage.getItem(expectedName1);
                    assert.equal(value1, expectedValue1, 'The got value1 is correct');

                    var value2 = storage.getItem(expectedName2);
                    assert.equal(value2, expectedValue2, 'The got value2 is correct');

                    storage.removeItem(expectedName1).then(function() {
                        assert.ok(true, 'The value1 has been removed');

                        var value1 = storage.getItem(expectedName1);
                        assert.equal(value1, undefined, 'The value1 is erased');

                        var value2 = storage.getItem(expectedName2);
                        assert.equal(value2, expectedValue2, 'The value2 is still there');

                        storage.clear().then(function() {
                            assert.ok(true, 'The data is erased');

                            var value1 = storage.getItem(expectedName1);
                            assert.equal(value1, undefined, 'The value1 is erased');

                            var value2 = storage.getItem(expectedName2);
                            assert.equal(value2, undefined, 'The value2 is erased');

                            QUnit.start();
                        });
                    });
                });
            });
        });
    });


    QUnit.asyncTest('persistence', function(assert) {
        QUnit.expect(6);

        var name = 'test3';
        var expectedName = 'foo';
        var expectedValue = 'bar';

        cachedStore(name).then(function(storage1) {
            assert.equal(typeof storage1, 'object', 'An instance of the cachedStore accessor has been created');

            storage1.setItem(expectedName, expectedValue).then(function() {
                assert.ok(true, 'The value has been set');

                cachedStore(name).then(function(storage2) {
                    assert.equal(typeof storage2, 'object', 'Another instance of the cachedStore accessor has been created');

                    var value = storage2.getItem(expectedName);
                    assert.equal(value, expectedValue, 'The got value is correct');

                    storage2.removeStore().then(function() {
                        cachedStore(name).then(function(storage3) {
                            assert.equal(typeof storage3, 'object', 'Another instance of the cachedStore accessor has been created');

                            var value = storage3.getItem(expectedName);
                            assert.equal(typeof value, 'undefined', 'The got value is correct');

                            QUnit.start();
                        });
                    });
                });
            });
        });
    });

});
