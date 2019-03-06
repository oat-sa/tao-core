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
 * Copyright (c) 2016-19 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 * @author Martin Nicholson <martin@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/promise',
    'core/tokenHandler',
    'lib/jquery.mockjax/jquery.mockjax'
], function($, _, Promise, tokenHandlerFactory) {
    'use strict';


    QUnit.module('tokenHandler');

    QUnit.test('module', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof tokenHandlerFactory, 'function', "The tokenHandler module exposes a function");
        assert.equal(typeof tokenHandlerFactory(), 'object', "The tokenHandler factory produces an object");
        assert.notStrictEqual(tokenHandlerFactory(), tokenHandlerFactory(), "The tokenHandler factory provides a different object on each call");
    });

    QUnit.cases([
        { name : 'getToken' },
        { name : 'setToken' },
        { name : 'getClientConfigTokens' },
        { name : 'clearStore' },
        { name : 'getQueueLength' },
        { name : 'setMaxSize' }
    ])
    .test('instance API ', function(data, assert) {
        var instance = tokenHandlerFactory();

        QUnit.expect(1);

        assert.equal(typeof instance[data.name], 'function', 'The tokenHandler instance exposes a "' + data.name + '" function');
    });

    function randomToken() {
        var d = Date.now() + Math.floor(5000 * Math.random());
        return {
            value: 'someToken' + ('' + d).slice(9),
            receivedAt: d
        };
    }

    QUnit.module('behaviour');

    QUnit.asyncTest('set/get single token', function(assert){
        var tokenHandler = tokenHandlerFactory();
        var expectedToken = { value: "e56fg1a3b9de2237f", receivedAt: Date.now() };

        QUnit.expect(2);

        tokenHandler.setToken(expectedToken.value)
            .then(function(result){
                assert.ok(result, 'The setToken method returns true');

                return tokenHandler.getToken();
            })
            .then(function(returnedToken){
                assert.equal(returnedToken, expectedToken.value, 'The getToken method returns the right token');

                return tokenHandler.clearStore();
            })
            .then(function() {
                QUnit.start();
            })
            .catch(function(err){
                assert.ok(false, err.message);
                QUnit.start();
            });
    });

    QUnit.asyncTest('getQueueLength', function(assert){
        var tokenHandler = tokenHandlerFactory({ maxSize: 5 });

        QUnit.expect(6);

        Promise.all([
            tokenHandler.setToken(randomToken()),
            tokenHandler.setToken(randomToken()),
            tokenHandler.setToken(randomToken()),
            tokenHandler.setToken(randomToken()),
            tokenHandler.setToken(randomToken())
        ])
        .then(function(){
            return tokenHandler.getQueueLength();
        })
        .then(function(length){
            assert.equal(length, 5, 'The queue size is correct: 5');
            return tokenHandler.getToken()
                .then(function(){
                    return tokenHandler.getQueueLength();
                });
        })
        .then(function(length){
            assert.equal(length, 4, 'The queue size is correct: 4');
            return tokenHandler.getToken()
                .then(function(){
                    return tokenHandler.getQueueLength();
                });
        })
        .then(function(length){
            assert.equal(length, 3, 'The queue size is correct: 3');
            return tokenHandler.getToken()
                .then(function(){
                    return tokenHandler.getQueueLength();
                });
        })
        .then(function(length){
            assert.equal(length, 2, 'The queue size is correct: 2');
            return tokenHandler.getToken()
                .then(function(){
                    return tokenHandler.getQueueLength();
                });
        })
        .then(function(length){
            assert.equal(length, 1, 'The queue size is correct: 1');
            return tokenHandler.getToken()
                .then(function(){
                    return tokenHandler.getQueueLength();
                });
        })
        .then(function(length){
            assert.equal(length, 0, 'The queue size is correct: 0');

            QUnit.start();
        })
        .catch(function(err){
            assert.ok(false, err.message);
            QUnit.start();
        });
    });

    QUnit.asyncTest('getClientConfigTokens', function(assert) {
        var tokenHandler = tokenHandlerFactory();

        QUnit.expect(3);

        tokenHandler.getClientConfigTokens()
            .then(function(result) {
                assert.ok(result, 'The method returned true');

                return tokenHandler.getQueueLength();
            })
            .then(function(length) {
                assert.equal(length, 5, 'The queue size is correct: 5');

                return tokenHandler.getToken();
            })
            .then(function(token){
                assert.equal(typeof token, 'string', 'A token string was fetched');

                return tokenHandler.clearStore();
            })
            .then(function() {
                QUnit.start();
            })
            .catch(function(err){
                assert.ok(false, err.message);
                QUnit.start();
            });
    });
});
