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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

/**
 * Test the request provider for the communicator
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'core/communicator',
    'core/communicator/request'
], function ($, communicator, requestProvider) {
    'use strict';


    QUnit.module('API');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.equal(typeof requestProvider, 'object', "The communicator/request module exposes an object");
    });

    QUnit.cases([
        {name: 'init', title: 'init'},
        {name: 'destroy', title: 'destroy'},
        {name: 'open', title: 'open'},
        {name: 'close', title: 'close'},
        {name: 'send', title: 'send'}
    ]).test('communicator api', function (data, assert) {
            assert.equal(typeof requestProvider[data.name], 'function', 'The communicator/request api exposes a "' + data.name + '" function');
        });


    QUnit.module('provider');

    QUnit.asyncTest('missing configuration', function (assert) {
        QUnit.expect(1);

        communicator.registerProvider('request', requestProvider);

        var instance = communicator('request');

        instance.init().catch(function () {
            assert.ok(true, 'The provider needs the a service config');
            QUnit.start();
        });
    });

    QUnit.asyncTest('lifecyle', function (assert) {
        QUnit.expect(8);

        communicator.registerProvider('request', requestProvider);

        var instance = communicator('request', {service: 'service.url'})
            .on('init', function () {
                assert.ok(true, 'The communicator has fired the "init" event');
            })
            .on('ready', function () {
                assert.ok(true, 'The communicator has fired the "ready" event');
            })
            .on('open', function () {
                assert.ok(true, 'The communicator has fired the "open" event');
            })
            .on('opened', function () {
                assert.ok(true, 'The communicator has fired the "opened" event');
            })
            .on('close', function () {
                assert.ok(true, 'The communicator has fired the "close" event');
            })
            .on('closed', function () {
                assert.ok(true, 'The communicator has fired the "closed" event');
            })
            .on('destroy', function () {
                assert.ok(true, 'The communicator has fired the "destroy" event');
            })
            .on('destroyed', function () {
                assert.ok(true, 'The communicator has fired the "destroyed" event');
            });

        instance.init()
            .then(function () {
                return instance.open();
            })
            .then(function () {
                return instance.close();
            })
            .then(function () {
                return instance.destroy();
            })
            .then(function(){
                QUnit.start();
            }).catch(function(err){
                assert.ok(false, err.message);
                QUnit.start();
            });
    });

    QUnit.asyncTest('send', function (assert) {
        QUnit.expect(2);

        communicator.registerProvider('request', requestProvider);

        var instance = communicator('request', {service: '/tao/views/js/test/core/communicator/request/messages.json'})
            .on('receive', function (data) {
               assert.equal(typeof data, 'object', 'We got a response');
               assert.equal( data.responses[0], 'ok', 'The correct response is received');
            });

        instance.init()
            .then(function () {
                return instance.open();
            })
            .then(function () {
                return instance.send('foo', 'bar');
            })
            .then(function () {
                return instance.close();
            })
            .then(function () {
                return instance.destroy();
            })
            .then(function(){
                //ensure only on call is done in 1 second
                setTimeout(function(){
                    QUnit.start();
                }, 1000);
            }).catch(function(err){
                assert.ok(false, err.message);
                QUnit.start();
            });
    });


});

