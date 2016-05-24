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
define(['core/store', 'core/persistence'], function(store, persistence) {
    'use strict';

    QUnit.module('persistence');


    QUnit.test('module', function(assert) {
        QUnit.expect(1);
        
        assert.equal(typeof persistence, 'function', "The persistence module exposes a function");
    });


    QUnit.asyncTest('factory', function(assert) {
        QUnit.expect(3);

        var name = 'test1';

        persistence(name).then(function(storage1) {
            assert.equal(typeof storage1, 'object', 'An instance of the persistence accessor has been created');

            persistence(name).then(function(storage2) {
                assert.equal(typeof storage2, 'object', 'Another instance of the persistence accessor has been created');
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

        persistence(name).then(function(storage) {
            assert.equal(typeof storage, 'object', 'An instance of the persistence accessor has been created');

            storage.set(expectedName1, expectedValue1).then(function() {
                assert.ok(true, 'The value1 has been set');

                storage.set(expectedName2, expectedValue2).then(function() {
                    assert.ok(true, 'The value2 has been set');

                    var value1 = storage.get(expectedName1);
                    assert.equal(value1, expectedValue1, 'The got value1 is correct');

                    var value2 = storage.get(expectedName2);
                    assert.equal(value2, expectedValue2, 'The got value2 is correct');

                    storage.remove(expectedName1).then(function() {
                        assert.ok(true, 'The value1 has been removed');

                        var value1 = storage.get(expectedName1);
                        assert.equal(value1, undefined, 'The value1 is erased');

                        var value2 = storage.get(expectedName2);
                        assert.equal(value2, expectedValue2, 'The value2 is still there');

                        storage.clear().then(function() {
                            assert.ok(true, 'The data is erased');

                            var value1 = storage.get(expectedName1);
                            assert.equal(value1, undefined, 'The value1 is erased');

                            var value2 = storage.get(expectedName2);
                            assert.equal(value2, undefined, 'The value2 is erased');

                            QUnit.start();
                        });
                    });
                });
            });
        });
    });


    QUnit.asyncTest('persistence', function(assert) {
        QUnit.expect(4);

        var name = 'test3';
        var expectedName = 'foo';
        var expectedValue = 'bar';

        persistence(name).then(function(storage1) {
            assert.equal(typeof storage1, 'object', 'An instance of the persistence accessor has been created');

            storage1.set(expectedName, expectedValue).then(function() {
                assert.ok(true, 'The value has been set');

                persistence(name).then(function(storage2) {
                    assert.equal(typeof storage2, 'object', 'Another instance of the persistence accessor has been created');

                    var value = storage2.get(expectedName);
                    assert.equal(value, expectedValue, 'The got value is correct');

                    QUnit.start();
                });
            });
        });
    });


    QUnit.asyncTest('event #set', function(assert) {
        QUnit.expect(6);

        var name = 'test4';
        var expectedName = 'foo';
        var expectedValue = 'bar';

        persistence(name).then(function(storage) {
            assert.equal(typeof storage, 'object', 'An instance of the persistence accessor has been created');

            storage.on('set', function(name, value) {
                assert.equal(name, expectedName, 'The name of the value is correct');
                assert.equal(value, expectedValue, 'The set value is correct');
                assert.equal(storage.get(expectedName), value, 'The got value is correct');
            });

            storage.set(expectedName, expectedValue).then(function() {
                assert.ok(true, 'The value has been set');

                var value = storage.get(expectedName);
                assert.equal(value, expectedValue, 'The got value is correct');

                QUnit.start();
            });
        });
    });


    QUnit.asyncTest('event #remove', function(assert) {
        QUnit.expect(6);

        var name = 'test5';
        var expectedName = 'foo';
        var expectedValue = 'bar';

        persistence(name).then(function(storage) {
            assert.equal(typeof storage, 'object', 'An instance of the persistence accessor has been created');

            storage.on('remove', function(name) {
                assert.equal(name, expectedName, 'The name of the value is correct');
            });

            storage.set(expectedName, expectedValue).then(function() {
                assert.ok(true, 'The value has been set');

                var value = storage.get(expectedName);
                assert.equal(value, expectedValue, 'The got value is correct');

                storage.remove(expectedName).then(function() {
                    assert.ok(true, 'The value has been removed');

                    var value = storage.get(expectedName);
                    assert.equal(value, undefined, 'The value is erased');

                    QUnit.start();
                });
            });
        });
    });


    QUnit.asyncTest('event #clear', function(assert) {
        QUnit.expect(6);

        var name = 'test6';
        var expectedName = 'foo';
        var expectedValue = 'bar';

        persistence(name).then(function(storage) {
            assert.equal(typeof storage, 'object', 'An instance of the persistence accessor has been created');

            storage.on('clear', function() {
                assert.ok(true, 'The clear event has been triggered');
            });

            storage.set(expectedName, expectedValue).then(function() {
                assert.ok(true, 'The value has been set');

                var value = storage.get(expectedName);
                assert.equal(value, expectedValue, 'The got value is correct');

                storage.clear().then(function() {
                    assert.ok(true, 'The data has been erased');

                    var value = storage.get(expectedName);
                    assert.equal(value, undefined, 'The value is erased');

                    QUnit.start();
                });
            });
        });
    });
    
    
    QUnit.asyncTest('event #error', function(assert) {
        QUnit.expect(7);

        var name = 'test7';
        var expectedName = 'foo';
        var expectedValue = 'bar';

        store.setConfig(name, {
            failedSet: true,
            failedRemove: true,
            failedClear: true
        });
        persistence(name).then(function(storage) {
            assert.equal(typeof storage, 'object', 'An instance of the persistence accessor has been created');

            storage.on('error', function() {
                assert.ok(true, 'An error is thrown when storing the value');
            });

            storage.set(expectedName, expectedValue).catch(function() {
                assert.ok(true, 'The value has not been set');

                storage.remove(expectedName).catch(function() {
                    assert.ok(true, 'The value has not been removed');

                    storage.clear().catch(function() {
                        assert.ok(true, 'The data has not been erased');

                        QUnit.start();
                    });
                });
            });
        });
    });

});
