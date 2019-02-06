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

    function randomToken() {
        var d = Date.now() + Math.floor(5000 * Math.random());
        return {
            value: 'someToken' + ('' + d).slice(9),
            receivedAt: d
        };
    }

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
        { name : 'fetchNewTokens' },
        { name : 'getQueueLength' }
    ])
    .test('instance API ', function(data, assert) {
        var instance = tokenHandlerFactory();

        QUnit.expect(1);

        assert.equal(typeof instance[data.name], 'function', 'The tokenHandler instance exposes a "' + data.name + '" function');
    });

    QUnit.module('behaviour');

    QUnit.asyncTest('set/get single token', function(assert){
        var tokenHandler = tokenHandlerFactory();
        var expectedToken = { value: "e56fg1a3b9de2237f", receivedAt: Date.now() };

        QUnit.expect(2);

        tokenHandler.setToken(expectedToken)
            .then(function(result){
                assert.ok(result, 'The setToken method returns true');

                return tokenHandler.getToken();
            })
            .then(function(returnedToken){
                assert.equal(returnedToken, expectedToken, 'The getToken method returns the right token');

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
            assert.equal(tokenHandler.getQueueLength(), 5, 'The queue size is correct: 5');
            return tokenHandler.getToken();
        })
        .then(function(){
            assert.equal(tokenHandler.getQueueLength(), 4, 'The queue size is correct: 4');
            return tokenHandler.getToken();
        })
        .then(function(){
            assert.equal(tokenHandler.getQueueLength(), 3, 'The queue size is correct: 3');
            return tokenHandler.getToken();
        })
        .then(function(){
            assert.equal(tokenHandler.getQueueLength(), 2, 'The queue size is correct: 2');
            return tokenHandler.getToken();
        })
        .then(function(){
            assert.equal(tokenHandler.getQueueLength(), 1, 'The queue size is correct: 1');
            return tokenHandler.getToken();
        })
        .then(function(){
            assert.equal(tokenHandler.getQueueLength(), 0, 'The queue size is correct: 0');

            QUnit.start();
        })
        .catch(function(err){
            assert.ok(false, err.message);
            QUnit.start();
        });
    });


    QUnit.module('request');

    // mock the token provider endpoint:
    $.mockjax({
        url: "/tao/ClientConfig/tokens",
        status: 200,
        response: function() {
            this.responseText = JSON.stringify([
                randomToken(),
                randomToken(),
                randomToken(),
                randomToken(),
                randomToken()
            ]);
        }
    });

    QUnit.asyncTest('fetchNewTokens', function(assert) {
        var tokenHandler = tokenHandlerFactory();

        QUnit.expect(5);

        tokenHandler.fetchNewTokens() // launches fetchNewTokens
            .then(function(tokens){
                assert.equal(typeof tokens, 'object', 'An object was fetched');
                assert.equal(tokens.length, 5, '5 tokens were fetched');
                assert.equal(typeof tokens[0].value, 'string', 'The first token has a value');
                assert.equal(typeof tokens[0].receivedAt, 'number', 'The first token has a timestamp');
                assert.notEqual(tokens[0].value, tokens[1].value, 'The tokens have different values');

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

    QUnit.asyncTest('get token when empty', function(assert) {
        var tokenHandler = tokenHandlerFactory({ maxSize: 5 });

        QUnit.expect(4);

        tokenHandler.getToken() // launches fetchNewTokens
            .then(function(token){
                assert.equal(typeof token, 'object', 'An object was fetched');
                assert.equal(typeof token.value, 'string', 'The first token has a value');
                assert.equal(typeof token.receivedAt, 'number', 'The first token has a timestamp');
                assert.equal(tokenHandler.getQueueLength(), 4, 'The queue size is correct: 4');

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
