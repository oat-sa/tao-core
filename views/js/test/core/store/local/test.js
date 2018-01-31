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
 * Test the localStorage store backend
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['core/store/localstorage', 'core/promise'], function(localStorageBackend, Promise){
    'use strict';

    QUnit.moduleDone(function(){
        window.localStorage.clear();
    });

    QUnit.module('API');

    QUnit.test("module", function(assert){
        QUnit.expect(2);

        assert.ok(typeof localStorageBackend !== 'undefined', "The module exports something");
        assert.ok(typeof localStorageBackend === 'function', "The module exposes a function");
    });

    QUnit.test("factory", function(assert){
        var store;
        QUnit.expect(4);

        assert.throws(function(){
            localStorageBackend();
        }, TypeError, 'The backend should be created with a store id');

        assert.throws(function(){
            localStorageBackend(false);
        }, TypeError, 'The backend should be created with a valid store id');

        store = localStorageBackend('foo');

        assert.equal(typeof store, 'object', 'The factory return an object');
        assert.notDeepEqual(localStorageBackend('foo'), store, 'The factory creates a new object');
    });

    QUnit.test("storage backend", function(assert){
        QUnit.expect(3);

        assert.equal(typeof localStorageBackend.removeAll, 'function', 'The backend exposes the removeAll method');
        assert.equal(typeof localStorageBackend.getAll, 'function', 'The backend exposes the getAll method');
        assert.equal(typeof localStorageBackend.getStoreIdentifier, 'function', 'The backend exposes the getStoreIdentifier method');
    });

    QUnit.test("store", function(assert){
        var store;
        QUnit.expect(7);

        store = localStorageBackend('foo');

        assert.equal(typeof store, 'object', 'The store is an object');
        assert.equal(typeof store.getItem, 'function', 'The store exposes the getItem method');
        assert.equal(typeof store.setItem, 'function', 'The store exposes the setItem method');
        assert.equal(typeof store.removeItem, 'function', 'The store exposes the removetItem method');
        assert.equal(typeof store.getItems, 'function', 'The store exposes the getItems method');
        assert.equal(typeof store.clear, 'function', 'The store exposes the clear method');
        assert.equal(typeof store.removeStore, 'function', 'The store exposes the removeStore method');
    });


    QUnit.module('CRUD');

    QUnit.asyncTest("setItem", function(assert){
        var store;
        var p;

        QUnit.expect(4);

        store = localStorageBackend('foo');
        assert.equal(typeof store, 'object', 'The store is an object');

        p = store.setItem('bar', 'boz');
        assert.ok(p instanceof Promise, 'setItem returns a Promise');

        p.then(function(result){

            assert.equal(typeof result, 'boolean', 'The result is a boolean');
            assert.ok(result, 'The item is added');

            QUnit.start();
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.asyncTest("getItem", function(assert){
        var store;
        var p;

        QUnit.expect(5);

        store = localStorageBackend('foo');
        assert.equal(typeof store, 'object', 'The store is an object');

        p = store.setItem('bar', 'noz');
        assert.ok(p instanceof Promise, 'setItem returns a Promise');

        p.then(function(result){
            assert.ok(result, 'The item is added');

            store.getItem('bar').then(function(value){

                assert.equal(typeof value, 'string', 'The result is a string');
                assert.equal(value, 'noz', 'The retrieved value is correct');

                QUnit.start();
            });
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.asyncTest("removeItem", function(assert){
        var store;

        QUnit.expect(5);

        store = localStorageBackend('foo');
        assert.equal(typeof store, 'object', 'The store is an object');

        store.setItem('moo', 'noob')
        .then(function(result){
            assert.ok(result, 'The item is added');

            return store.getItem('moo').then(function(value){
                assert.equal(value, 'noob', 'The retrieved value is correct');
            });
        }).then(function(){
            return store.removeItem('moo').then(function(rmResult){
                assert.ok(rmResult, 'The item is removed');
            });
        }).then(function(){
            return store.getItem('moo').then(function(value){
                assert.equal(typeof value, 'undefined', 'The value does not exists anymore');
                QUnit.start();
            });
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.asyncTest("object", function(assert){
        var store;
        var sample = {
            collection : [{
                item1: true,
                item2: 'false',
                item3: 12
            },{
                item4: { value : null }
            }]
        };
        QUnit.expect(3);

        store = localStorageBackend('foo');
        assert.equal(typeof store, 'object', 'The store is an object');

        store.setItem('sample', sample).then(function(added){
            assert.ok(added, 'The item is added');
            store.getItem('sample').then(function(result){
                assert.deepEqual(result, sample, 'Retrieving the sample');
                QUnit.start();
            });
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.asyncTest("clear", function(assert){
        var store;
        QUnit.expect(5);

        store = localStorageBackend('foo');
        assert.equal(typeof store, 'object', 'The store is an object');

        Promise.all([
            store.setItem('zoo', 'zoob'),
            store.setItem('too', 'toob')
        ])
        .then(function(){
            return store.getItem('too').then(function(value){
                assert.equal(value, 'toob', 'The retrieved value is correct');
            });
        }).then(function(){
            return store.clear().then(function(rmResult){
                assert.ok(rmResult, 'The item is removed');
            });
        }).then(function(){
            return store.getItem('too').then(function(value){
                assert.equal(typeof value, 'undefined', 'The value does not exists anymore');
                return store.getItem('zoo').then(function(newValue){
                    assert.equal(typeof newValue, 'undefined', 'The value does not exists anymore');
                    QUnit.start();
                });
            });
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.asyncTest("getItems", function(assert){
        var store;

        QUnit.expect(5);

        store = localStorageBackend('foo3');
        assert.equal(typeof store, 'object', 'The store is an object');

        Promise.all([
            store.setItem('zoo', 'zoob'),
            store.setItem('too', 'toob'),
            store.setItem('moo', 'moob'),
            store.setItem('joo', 'joob')
        ])
        .then(function(){
            return store.getItem('joo').then(function(value){
                assert.equal(value, 'joob', 'The retrieved value is correct');
            });
        }).then(function(){
            return store.getItems().then(function(entries){
                assert.equal(typeof entries, 'object', 'The entries is an object');
                assert.deepEqual(entries, {
                    zoo : 'zoob',
                    too : 'toob',
                    moo : 'moob',
                    joo : 'joob'
                }, 'The entries contains the store values');
            });
        })
        .then(function(){
            return store.setItem('yoo', 'yoob');
        })
        .then(function(){
            return store.removeItem('moo');
        })
        .then(function(){
            return store.getItems().then(function(entries){
                assert.deepEqual(entries, {
                    zoo : 'zoob',
                    too : 'toob',
                    yoo : 'yoob',
                    joo : 'joob'
                }, 'The entries contains the updated values');
            });
        }).then(function(){
            QUnit.start();
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });


    QUnit.module('Erase');

    QUnit.asyncTest("removeStore", function (assert) {
        var store;
        QUnit.expect(5);

        store = localStorageBackend('foo');
        assert.equal(typeof store, 'object', 'The store is an object');

        Promise.all([
            store.setItem('zoo', 'zoob'),
            store.setItem('too', 'toob')
        ])
        .then(function () {
            return store.getItem('too').then(function (value) {
                assert.equal(value, 'toob', 'The retrieved value is correct');
            });
        })
        .then(function () {
            return store.removeStore().then(function (rmResult) {
                assert.ok(rmResult, 'The store is removed');
            });
        })
        .then(function () {
            return store.getItem('too').then(function (value) {
                assert.equal(typeof value, 'undefined', 'The value does not exists anymore');
                return store.getItem('zoo').then(function (newValue) {
                    assert.equal(typeof newValue, 'undefined', 'The value does not exists anymore');
                    QUnit.start();
                });
            });
        })
        .catch(function (err) {
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.asyncTest("removeAll", function (assert) {
        var store1;
        var store2;

        QUnit.expect(19);

        store1 = localStorageBackend('foo1');
        store2 = localStorageBackend('foo2');

        assert.equal(typeof store1, 'object', 'The store1 is an object');
        assert.equal(typeof store2, 'object', 'The store2 is an object');

        Promise.all([
            store1.setItem('zoo', 'zooa'),
            store1.setItem('too', 'tooa'),

            store2.setItem('zoo', 'zoob'),
            store2.setItem('too', 'toob')
        ])
        .then(function () {
            return store1.getItem('too').then(function (value) {
                assert.equal(value, 'tooa', 'The value of too retrieved from store1 is correct');
                return store1.getItem('zoo').then(function (newValue) {
                    assert.equal(newValue, 'zooa', 'The value of zoo retrieved from store1 is correct');
                });
            });
        })
        .then(function () {
            return store2.getItem('too').then(function (value) {
                assert.equal(value, 'toob', 'The value of too retrieved from store2 is correct');
                return store2.getItem('zoo').then(function (newValue) {
                    assert.equal(newValue, 'zoob', 'The value of zoo retrieved from store2 is correct');
                });
            });
        })
        .then(function () {
            return localStorageBackend.removeAll();
        })
        .then(function (rmResult) {
            assert.ok(rmResult, 'The stores are removed');
        })
        .then(function () {
            return store1.getItem('too').then(function (value) {
                assert.equal(typeof value, 'undefined', 'The value too does not exist anymore in store1');
                return store1.getItem('zoo').then(function (newValue) {
                    assert.equal(typeof newValue, 'undefined', 'The value zoo does not exist anymore in store1');
                });
            });
        })
        .then(function () {
            return store2.getItem('too').then(function (value) {
                assert.equal(typeof value, 'undefined', 'The value too does not exist anymore in store2');
                return store2.getItem('zoo').then(function (newValue) {
                    assert.equal(typeof newValue, 'undefined', 'The value zoo does not exist anymore in store2');
                });
            });
        })
        .then(function() {
            store1 = localStorageBackend('foo1');
            store2 = localStorageBackend('foo2');

            assert.equal(typeof store1, 'object', 'The store1 is an object');
            assert.equal(typeof store2, 'object', 'The store2 is an object');

            Promise.all([
                store1.setItem('zoo', 'zoo1'),
                store2.setItem('zoo', 'zoo2')
            ])
                .then(function () {
                    return store1.getItem('zoo').then(function (value) {
                        assert.equal(value, 'zoo1', 'The value of zoo retrieved from store1 is correct');
                    });
                })
                .then(function () {
                    return store2.getItem('zoo').then(function (value) {
                        assert.equal(value, 'zoo2', 'The value of zoo retrieved from store2 is correct');
                    });
                })
                .then(function () {
                    return localStorageBackend.removeAll(function(storeName) {
                        return storeName === "foo2";
                    });
                })
                .then(function (rmResult) {
                    assert.ok(rmResult, 'The stores are removed');
                })
                .then(function () {
                    return store1.getItem('zoo').then(function (value) {
                        assert.equal(value, 'zoo1', 'The store1 is still there');
                    });
                })
                .then(function () {
                    return store2.getItem('zoo').then(function (value) {
                        assert.equal(typeof value, 'undefined', 'The store2 has been erased');
                    });
                })
                .then(function () {
                    return store1.removeStore().then(function (rmResult) {
                        assert.ok(rmResult, 'The store is removed');
                        QUnit.start();
                    });
                });
        })
        .catch(function (err) {
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.module('get stores');

    QUnit.asyncTest('get all stores', function(assert){
        var store1;
        var store2;
        var store3;

        QUnit.expect(8);

        assert.equal(typeof localStorageBackend.getAll, 'function', 'localstorage backend exposes the getAll method');

        store1 = localStorageBackend('test-store-1');
        store2 = localStorageBackend('test-store-2');
        store3 = localStorageBackend('bar3');

        assert.equal(typeof store1, 'object', 'The store1 is an object');
        assert.equal(typeof store2, 'object', 'The store2 is an object');
        assert.equal(typeof store3, 'object', 'The store2 is an object');

        Promise.all([
            store1.setItem('test', true),
            store2.setItem('test', true),
            store3.setItem('test', true)
        ]).then(function(){

            var validate = function(name){
                return name === 'test-store-1' || name === 'test-store-2';
            };

            return localStorageBackend.getAll(validate).then(function(storeNames){
                assert.equal(storeNames.length, 2, 'Two store names have been found');
                assert.ok(storeNames.indexOf('test-store-1') > -1, 'The 1st store is selected');
                assert.ok(storeNames.indexOf('test-store-2') > -1, 'The 2nd store is selected');
                assert.ok(storeNames.indexOf('bar3') === -1, 'The 3rd store is filtered');
            });
        })
        .then(function(){
            QUnit.start();
        })
        .catch(function (err) {
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.module('store id');

    QUnit.asyncTest('get store identifier', function(assert){
        QUnit.expect(4);

        assert.equal(typeof localStorageBackend.getStoreIdentifier, 'function', 'IndexedDB backend has the getStoreIdentifier method');

        localStorageBackend.getStoreIdentifier().then(function(id){

            assert.equal(typeof id, 'string', 'we have a store identifier');
            assert.ok(id.length > 0, 'the identifier is not empty');

            return localStorageBackend.getStoreIdentifier().then(function(idNextCall){

                assert.equal(id, idNextCall, 'The identifier should remain the same accross the store');
                QUnit.start();
            });

        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.asyncTest('get new store identifier', function(assert){
        QUnit.expect(3);

        localStorageBackend.getStoreIdentifier().then(function(id){

            assert.equal(typeof id, 'string', 'we have a store identifier');
            assert.ok(id.length > 0, 'the identifier is not empty');

            return localStorageBackend('id').removeStore().then(function(){
                return localStorageBackend.getStoreIdentifier().then(function(idNextCall){

                    assert.notEqual(id, idNextCall, 'The identifier should be different since the has been removed');
                    QUnit.start();
                });
            });
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });
});
