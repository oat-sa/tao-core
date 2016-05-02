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
define(['lodash', 'core/communicator'], function(_, communicator) {
    'use strict';


    var mockProvider = {
        init : _.noop,
        destroy  : _.noop
    };

    QUnit.module('communicator factory', {
        setup: function(){
            communicator.registerProvider('mock', mockProvider);
        },
        teardown: function() {
            communicator.clearProviders();
        }
    });

    
    QUnit.test('module', 5, function(assert){
        assert.equal(typeof communicator, 'function', "The communicator module exposes a function");
        assert.equal(typeof communicator(), 'object', "The communicator factory produces an object");
        assert.notStrictEqual(communicator(), communicator(), "The communicator factory provides a different object on each call");
        assert.equal(typeof communicator.registerProvider, 'function', "The instance module exposes a function registerProvider()");
        assert.equal(typeof communicator.getProvider, 'function', "The instance module exposes a function getProvider()");
    });


    var communicatorApi = [
        {name : 'init', title : 'init'},
        {name : 'destroy', title : 'destroy'},

        {name : 'send', title : 'send'},
        {name : 'channel', title : 'channel'},

        {name : 'getConfig', title : 'getConfig'},

        {name : 'trigger', title : 'trigger'},
        {name : 'before', title : 'before'},
        {name : 'on', title : 'on'},
        {name : 'after', title : 'after'}
    ];

    QUnit
        .cases(communicatorApi)
        .test('api', function(data, assert){
            var instance = communicator();
            assert.equal(typeof instance[data.name], 'function', 'The communicator instance exposes a "' + data.name + '" function');
        });


    QUnit.module('provider', {
        setup: function(){
            communicator.clearProviders();
        }
    });


    QUnit.asyncTest('init()', function(assert){
        QUnit.expect(2);

        communicator.registerProvider('foo', {
            init : function(){
                assert.equal(this.bar, 'baz', 'The provider is executed on the instance context');
                QUnit.start();
                return this;
            }
        });

        var instance = communicator('foo');
        instance.bar = 'baz';
        assert.equal(instance.init(), instance, 'The provider has returned the expected response');
    });


    QUnit.asyncTest('destroy()', function(assert){
        QUnit.expect(2);

        communicator.registerProvider('foo', {
            init: _.noop,
            destroy : function(){
                assert.ok(true, 'The provider has delegated the destroy');
                QUnit.start();
                return this;
            }
        });

        var instance = communicator('foo');
        assert.equal(instance.destroy(), instance, 'The provider has returned the expected response');
    });


    QUnit.asyncTest('send()', function(assert){
        var expectedChannel = 'foo';
        var expectedMessage = 'bar';

        QUnit.expect(4);

        communicator.registerProvider('foo', {
            init: _.noop,
            send: function(channel, message) {
                assert.ok(true, 'The provider has delegated the send');
                assert.equal(channel, expectedChannel, 'The right channel has been used');
                assert.equal(message, expectedMessage, 'The right message has been sent');
                QUnit.start();
                return this;
            }
        });

        var instance = communicator('foo');
        assert.equal(instance.send(expectedChannel, expectedMessage), instance, 'The provider has returned the expected response');
    });


    QUnit.asyncTest('channel()', function(assert) {
        var expectedMessage = 'Hello';

        QUnit.expect(2);

        communicator.registerProvider('foo', {init: _.noop});

        var instance = communicator('foo');

        instance.channel('bar', function(message) {
            assert.ok('The message has been received');
            assert.equal(message, expectedMessage, 'The right message has been received');
            QUnit.start();
        });

        instance.trigger('message', 'bar', expectedMessage);
    });


    QUnit.test('channel() error', function(assert) {

        QUnit.expect(2);

        communicator.registerProvider('foo', {init: _.noop});

        var instance = communicator('foo');

        assert.throws(function() {
            instance.channel(null, function(message) {});
        }, 'A channel must have a name');

        assert.throws(function() {
            instance.channel('foo', null);
        }, 'A channel must have a handler');
    });


    QUnit.asyncTest('getConfig()', function(assert){
        QUnit.expect(1);

        var config = {
            'timeout': 15000,
            'moo' : 'norz'
        };

        communicator.registerProvider('foo', {
            init : function(){
                var myConfig = this.getConfig();
                assert.deepEqual(myConfig, config, 'The retrieved config is the right one');
                QUnit.start();
            }
        });

        var instance = communicator('foo', config);
        instance.init();
    });
});
