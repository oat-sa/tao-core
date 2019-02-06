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
 * Copyright (c) 2019 Open Assessment Technologies SA ;
 */
/**
 * Test of tao/core/tokenStore
 *
 * @author Martin Nicholson <martin@taotesting.com>
 */
define([
    'lodash',
    'core/promise',
    'core/tokenStore'
], function(_, Promise, tokenStoreFactory) {
    'use strict';

    // Define some dummy tokens with unique values & dates:
    var now = Date.now();
    var token1 = {
        value: "my1stcooltoken12345",
        receivedAt: now + 10
    };
    var key1 = token1.value;

    var token2 = {
        value: "my2ndcooltoken12345",
        receivedAt: now + 20
    };
    var key2 = token2.value;

    var token3 = {
        value: "my3rdcooltoken12345",
        receivedAt: now + 30
    };
    var key3 = token3.value;

    var token4 = {
        value: "my4thcooltoken12345",
        receivedAt: now + 40000000 // don't expire during test
    };
    var key4 = token4.value;


    QUnit.module('API');

    QUnit.test('module', function (assert){
        QUnit.expect(1);

        assert.equal(typeof tokenStoreFactory, 'function', "The module exposes a function");
    });

    QUnit.test('factory', function (assert){
        QUnit.expect(2);

        assert.equal(typeof tokenStoreFactory(), 'object', "The factory creates an object");
        assert.notDeepEqual(tokenStoreFactory(), tokenStoreFactory(), "The factory creates a new object");
    });

    QUnit.test('instance', function (assert){
        var tokenStore;
        QUnit.expect(7);

        tokenStore = tokenStoreFactory();

        assert.equal(typeof tokenStore.get, 'function', "The store exposes the method get");
        assert.equal(typeof tokenStore.has, 'function', "The store exposes the method has");
        assert.equal(typeof tokenStore.add, 'function', "The store exposes the method add");
        assert.equal(typeof tokenStore.remove, 'function', "The store exposes the method remove");
        assert.equal(typeof tokenStore.clear, 'function', "The store exposes the method clear");
        assert.equal(typeof tokenStore.getSize, 'function', "The store exposes the method getSize");
        assert.equal(typeof tokenStore.isEmpty, 'function', "The store exposes the method isEmpty");
    });


    QUnit.module('behavior');

    QUnit.asyncTest('basic access', function(assert){
        var tokenStore;
        QUnit.expect(6);

        tokenStore = tokenStoreFactory();
        assert.equal(typeof tokenStore, 'object', "The store is an object");

        assert.equal(tokenStore.has(key1), false, 'The store does not contain token 1');
        tokenStore.get(key1)
            .then(function(value){
                assert.equal(typeof value, 'undefined', 'The store does not contain undefined token');
            })
            .then(function(){
                return tokenStore.add(token1);
            })
            .then(function(assigned){
                tokenStore.log();
                assert.ok(assigned, 'The value assignment is done'); //
                assert.ok( tokenStore.has(key1) , 'The store contains token 1');
                return tokenStore.get(key1);
            })
            .then(function(value){
                assert.deepEqual(value, token1, 'The store gives the correct token');

                QUnit.start();
            })
            .catch(function(err){
                assert.ok(false, err.message);
                QUnit.start();
            });
    });

    QUnit.asyncTest('limited size', function(assert){
        var tokenStore;
        QUnit.expect(11);

        tokenStore = tokenStoreFactory({ maxSize: 3 });
        assert.equal(typeof tokenStore, 'object', "The store is an object");

        tokenStore.add(token1)
            .then(function(){
                assert.ok(tokenStore.has(key1), 'The store contains token 1');
            })
            .then(function(){
                return tokenStore.add(token2);
            })
            .then(function(){
                assert.ok(tokenStore.has(key1), 'The store contains token 1');
                assert.ok(tokenStore.has(key2), 'The store contains token 2');
            })
            .then(function(){
                return tokenStore.add(token3);
            })
            .then(function(){
                assert.ok(tokenStore.has(key1), 'The store contains token 1');
                assert.ok(tokenStore.has(key2), 'The store contains token 2');
                assert.ok(tokenStore.has(key3), 'The store contains token 3');
            })
            .then(function(){
                return tokenStore.add(token4);
            })
            .then(function(){
                assert.ok(! tokenStore.has(key1), 'The store no longer contains token 1');
                assert.ok(tokenStore.has(key2), 'The store contains token 2');
                assert.ok(tokenStore.has(key3), 'The store contains token 3');
                assert.ok(tokenStore.has(key4), 'The store contains token 4');

                QUnit.start();
            })
            .catch(function(err){
                assert.ok(false, err.message);
                QUnit.start();
            });
    });

    QUnit.asyncTest('remove', function(assert){
        var tokenStore;
        QUnit.expect(10);

        tokenStore = tokenStoreFactory({ maxSize: 3 });

        Promise
            .all([
                tokenStore.add(token1),
                tokenStore.add(token2),
                tokenStore.add(token3)
            ])
            .then(function(){
                assert.ok(tokenStore.has(key1), 'The store contains the given token');
                assert.ok(tokenStore.has(key2), 'The store contains the given token');
                assert.ok(tokenStore.has(key3), 'The store contains the given token');
            })
            .then(function(){
                return tokenStore.remove(key3);
            })
            .then(function(removed){
                assert.ok(removed, 'The removal went well');
                assert.ok( ! tokenStore.has(key3), 'The token was removed from the store');
            })
            .then(function(){
                assert.ok( ! tokenStore.has('zoobizoob'), 'The token does not exists');
                return tokenStore.remove('zoobizoob');
            })
            .then(function(removed){
                assert.ok(!removed, 'Nothing to remove');
            })
            .then(function(){
                return tokenStore.add(token4);
            })
            .then(function(){
                assert.ok(tokenStore.has(key1), 'The store contains the given token');
                assert.ok(tokenStore.has(key2), 'The store contains the given token');
                assert.ok(tokenStore.has(key4), 'The store contains the given token');

                QUnit.start();
            })
            .catch(function(err){
                assert.ok(false, err.message);
                QUnit.start();
            });
    });

    QUnit.asyncTest('clear', function(assert){
        var tokenStore;
        QUnit.expect(7);

        tokenStore = tokenStoreFactory({});

        Promise
            .all([
                tokenStore.add(token1),
                tokenStore.add(token2),
                tokenStore.add(token3)
            ])
            .then(function(){
                assert.ok(tokenStore.has(key1), 'The store contains the given token');
                assert.ok(tokenStore.has(key2), 'The store contains the given token');
                assert.ok(tokenStore.has(key3), 'The store contains the given token');
            })
            .then(function(){
                return tokenStore.clear();
            })
            .then(function(cleared){
                assert.ok(cleared, 'The clear went well');
                assert.ok(! tokenStore.has(key1), 'The store does not contain the given token');
                assert.ok(! tokenStore.has(key2), 'The store does not contain the given token');
                assert.ok(! tokenStore.has(key3), 'The store does not contain the given token');

                QUnit.start();
            })
            .catch(function(err){
                assert.ok(false, err.message);
                QUnit.start();
            });
    });

    QUnit.asyncTest('getSize', function(assert){
        var tokenStore;
        QUnit.expect(3);

        tokenStore = tokenStoreFactory({});

        Promise
            .all([
                tokenStore.add(token1),
                tokenStore.add(token2),
                tokenStore.add(token3)
            ])
            .then(function(){
                assert.equal(tokenStore.getSize(), 3, 'The store size is correct: 3');
            })
            .then(function(){
                return tokenStore.clear();
            })
            .then(function(cleared){
                assert.ok(cleared, 'The clear went well');
                assert.equal(tokenStore.getSize(), 0, 'The store size is correct: 0');

                QUnit.start();
            })
            .catch(function(err){
                assert.ok(false, err.message);
                QUnit.start();
            });
    });

    QUnit.asyncTest('getTokens', function(assert){
        var tokenStore;
        QUnit.expect(3);

        tokenStore = tokenStoreFactory({});

        Promise
            .all([
                tokenStore.add(token1),
                tokenStore.add(token2),
                tokenStore.add(token3)
            ])
            .then(function(){
                return tokenStore.getTokens();
            })
            .then(function(tokens){
                assert.equal(typeof tokens, 'object', 'An object is retrieved');
                assert.deepEqual(_.values(tokens), [token1, token2, token3], 'The correct set of tokens are retrieved');
            })
            .then(function(){
                tokenStore.clear();
            })
            .then(function(){
                return tokenStore.getTokens();
            })
            .then(function(tokens){
                assert.deepEqual(tokens, {}, 'Empty set of tokens is retrieved');

                QUnit.start();
            })
            .catch(function(err){
                assert.ok(false, err.message);
                QUnit.start();
            });
    });

    QUnit.asyncTest('expireOldTokens', function(assert){
        var tokenStore;
        QUnit.expect(2);

        tokenStore = tokenStoreFactory({ tokenTimeLimit: 100 });

        Promise
            .all([
                tokenStore.add(token1),
                tokenStore.add(token2),
                tokenStore.add(token3),
                tokenStore.add(token4)
            ])
            .then(function(){
                assert.equal(tokenStore.getSize(), 4, 'The store size is correct: 4');
            })
            .then(function() {
                return new Promise(function(resolve) {
                    setTimeout(resolve, 200);
                });
            })
            .then(function(){
                return tokenStore.expireOldTokens();
            })
            .then(function(){
                assert.equal(tokenStore.getSize(), 1, 'A single token remains after small time delay');

                QUnit.start();
            })
            .catch(function(err){
                assert.ok(false, err.message);
                QUnit.start();
            });
    });

});
