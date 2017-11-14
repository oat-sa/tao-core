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
 * Test the logger API
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['core/logger/api'], function(loggerFactory){
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert){
        QUnit.expect(2);

        assert.ok(typeof loggerFactory !== 'undefined', 'The module exports something');
        assert.ok(typeof loggerFactory === 'function', 'The module exposes a function');
    });

    QUnit.test('register', function(assert){
        QUnit.expect(3);

        assert.ok(typeof loggerFactory.register === 'function', 'The module exposes also a register method');

        assert.throws(function(){
            loggerFactory.register('foo');
        }, TypeError, 'A provider is an object');

        assert.throws(function(){
            loggerFactory.register({ foo : function(){} });
        }, TypeError, 'A provider is an object with a log method');

        loggerFactory.register({ log : function(){} });
    });

    QUnit.test('levels', function(assert){
        QUnit.expect(13);

        assert.ok(typeof loggerFactory.levels === 'object', 'The module exposes the available levels');

        assert.ok(typeof loggerFactory.levels.emergency === 'number', 'The module exposes a emergency level');
        assert.ok(typeof loggerFactory.levels.alert === 'number', 'The module exposes a alert level');
        assert.ok(typeof loggerFactory.levels.critical === 'number', 'The module exposes a critical level');
        assert.ok(typeof loggerFactory.levels.error === 'number', 'The module exposes an error level');
        assert.ok(typeof loggerFactory.levels.warning === 'number', 'The module exposes a warning level');
        assert.ok(typeof loggerFactory.levels.notice === 'number', 'The module exposes a notice level');
        assert.ok(typeof loggerFactory.levels.info === 'number', 'The module exposes a info level');
        assert.ok(typeof loggerFactory.levels.debug === 'number', 'The module exposes a debug level');

        assert.ok(loggerFactory.levels.emergency > loggerFactory.levels.alert, 'The emergency level is greater than alert');
        assert.ok(loggerFactory.levels.alert > loggerFactory.levels.critical, 'The alert level is greater than critical');
        assert.ok(loggerFactory.levels.critical > loggerFactory.levels.error, 'The critical level is greater than error');
        assert.ok(loggerFactory.levels.error > loggerFactory.levels.warning, 'The error level is greater than warning');
        assert.ok(loggerFactory.levels.warning > loggerFactory.levels.notice, 'The warning level is greater than notice');
        assert.ok(loggerFactory.levels.notice > loggerFactory.levels.info, 'The notice level is greater than info');
        assert.ok(loggerFactory.levels.info > loggerFactory.levels.debug, 'The info level is greater than debug');
        assert.ok(loggerFactory.levels.debug > 0, 'The debug level is greater than zero');
    });

    QUnit.test('factory', function(assert){
        QUnit.expect(4);

        assert.throws(function(){
            loggerFactory();
        }, TypeError, 'A logger needs a name');

        assert.throws(function(){
            loggerFactory({});
        }, TypeError, 'A logger needs a name');

        assert.ok(typeof loggerFactory('foo') === 'object', 'The factory creates an object');
        assert.notEqual(loggerFactory('foo'), loggerFactory('foo'), 'The factory creates an new object');
    });

    QUnit.test('logger instance', function(assert){
        var logger;
        QUnit.expect(10);

        logger = loggerFactory('foo');

        assert.equal(typeof logger, 'object', 'The logger should be an object');
        assert.equal(typeof logger.log, 'function', 'The logger has a log method');
        assert.equal(typeof logger.emergency, 'function', 'The logger has a emergency method');
        assert.equal(typeof logger.alert, 'function', 'The logger has a alert method');
        assert.equal(typeof logger.critical, 'function', 'The logger has an critical method');
        assert.equal(typeof logger.error, 'function', 'The logger has a error method');
        assert.equal(typeof logger.warning, 'function', 'The logger has an warning method');
        assert.equal(typeof logger.notice, 'function', 'The logger has a notice method');
        assert.equal(typeof logger.info, 'function', 'The logger has a info method');
        assert.equal(typeof logger.debug, 'function', 'The logger has a debug method');

        assert.equal(typeof logger.level, 'function', 'The logger has a level method');
        assert.equal(typeof logger.child, 'function', 'The logger has a child method');
    });


    QUnit.module('providers', {
        setup    : function(){
            loggerFactory.providers = false;
        }
    });


    QUnit.asyncTest('load providers', function(assert){
        var p;
        QUnit.expect(5);

        assert.equal(loggerFactory.providers, false, 'No providers');

        p = loggerFactory.load(['test/core/logger/api/mlogck']);
        assert.ok(p instanceof Promise, 'the load method returns a Promise');

        p.then(function(){
            assert.equal(loggerFactory.providers.length, 1, 'A provider is registered');
            assert.equal(typeof loggerFactory.providers[0], 'object', 'The registered provider is an object');
            assert.equal(typeof loggerFactory.providers[0].log, 'function', 'The registered provider has a log function');
            QUnit.start();
        }).catch(function(err){
            assert.ok(false, err.message);
        });
    });

    QUnit.asyncTest('wrong providers', function(assert){
        var p;
        QUnit.expect(3);

        assert.equal(loggerFactory.providers, false, 'No providers');

        p = loggerFactory.load(['test/core/logger/api/test']);
        assert.ok(p instanceof Promise, 'the load method returns a Promise');

        p.then(function(){
            assert.ok(false, 'The method should not resolve');
            QUnit.start();
        }).catch(function(err){
            assert.ok(err instanceof TypeError, 'The given provider is not a logger');
            QUnit.start();
        });
    });


    QUnit.module('logger behavior', {
        setup    : function(){
            loggerFactory.providers = [];
        }
    });




    QUnit.cases([
        { title: 'info text', name : 'foo', level : 'info', args : ['bar'], expected: { level : 'info', name : 'foo', msg : 'bar'} },
        { title: 'warning format', name : 'woo', level : 'warning', args : ['holy %s', 'foo'], expected: { level : 'warning', name : 'woo', msg : 'holy foo'} },
        { title: 'info num format', name : 'noo', level : 'info', args : [{a : true}, 'hello %d %s', 12, 'bar'], expected: { level : 'info', name : 'noo', msg : 'hello 12 bar'} },
        { title: 'error', name : 'eoo', level : 'error', args : [new Error('oops')], expected: { level : 'error', name : 'eoo', msg : 'oops'} },
        { title: 'error', name : 'eoo', level : 'error', args : [{a : true}, new TypeError('oops')], expected: { level : 'error', name : 'eoo', msg : 'oops'} },
    ])
    .test('message logs ', function(data, assert){
        var logger;
        QUnit.expect(8);

        loggerFactory.register({
            log : function log(message){
                assert.equal(typeof message, 'object', 'the message object is there');
                assert.equal(typeof message.time, 'string', 'the message has a time');
                assert.equal(message.v, 0, 'The format version is consistent');
                assert.equal(message.pid, 1, 'The pid is consistent');

                assert.equal(message.hostname, navigator.userAgent, 'The hostname matches the UA');
                assert.equal(message.level, data.expected.level, 'the level match');
                assert.equal(message.name, data.expected.name, 'The message name match');
                assert.equal(message.msg, data.expected.msg, 'The message match');
            }
        });

        logger = loggerFactory(data.name);
        logger[data.level].apply(logger, data.args);
    });

    QUnit.test('minimum level', function(assert){
        var logger;
        QUnit.expect(3);

        loggerFactory.register({
            log : function log(message){
                assert.equal(message.level, 'warning', 'the level match');
                assert.equal(message.msg, 'something', 'the message match');
            }
        });

        logger = loggerFactory('foo', 'warning');
        logger.debug('nothing');
        logger.info('nothing');
        logger.notice('nothing');
        logger.warning('something');

        assert.equal(logger.level(), 'warning', 'The current level match');
    });

    QUnit.test('default minimum level', function(assert){
        var logger;
        QUnit.expect(3);

        loggerFactory.register({
            log : function log(message){
                assert.equal(message.level, 'warning', 'the level match');
                assert.equal(message.msg, 'something', 'the message match');
            }
        });

        loggerFactory.setDefaultLevel('warning');
        logger = loggerFactory('foo');
        logger.debug('nothing');
        logger.info('nothing');
        logger.notice('nothing');
        logger.warning('something');

        assert.equal(logger.level(), 'warning', 'The current level match');
    });

    QUnit.test('change minimum level', function(assert){
        var logger;
        QUnit.expect(5);

        loggerFactory.register({
            log : function log(message){
                assert.equal(message.level, 'trace', 'the level match');
                assert.equal(message.msg, 'something', 'the message match');
            }
        });

        logger = loggerFactory('foo', 'warning');
        logger.info('nothing');
        assert.equal(logger.level(), 'warning', 'The current level match');

        logger.level('info');
        assert.equal(logger.level(), 'info', 'The current level match');
        logger.debug('nothing');

        logger.level(loggerFactory.levels.debug);
        assert.equal(logger.level(), 'debug', 'The current level match');
        logger.trace('something');
    });

    QUnit.test('base fields', function(assert){
        var logger;
        var moo = {
            a : true,
            b : [1, 12],
            c : {
                borz : new Date()
            }
        };
        QUnit.expect(3);

        loggerFactory.register({
            log : function log(message){
                assert.equal(message.foo, 'bar', 'the foo field match');
                assert.equal(message.msg, 'something', 'the level match');
                assert.deepEqual(message.moo, moo, 'the moo field match');
            }
        });

        logger = loggerFactory('foo', loggerFactory.levels.debug, {
            foo : 'bar',
            moo : moo
        });
        logger.debug('nothing');
        logger.info('something');
    });

    QUnit.test('optional min level', function(assert){
        var logger;
        var moo = {
            a : false,
            b : [-1, -12]
        };
        QUnit.expect(3);

        loggerFactory.register({
            log : function log(message){
                assert.equal(message.foo, 'bar', 'the foo field match');
                assert.equal(message.msg, 'something', 'the level match');
                assert.deepEqual(message.moo, moo, 'the moo field match');
            }
        });

        logger = loggerFactory('foo', {
            foo : 'bar',
            moo : moo
        });
        logger.debug('nothing');
        logger.warning('something');
    });

    QUnit.test('fields override', function(assert){
        var logger;
        QUnit.expect(4);

        loggerFactory.register({
            log : function log(message){
                assert.equal(message.msg, 'something', 'the level match');
                assert.equal(message.foo, 'norz', 'the foo field match');
                assert.deepEqual(message.moo, [12], 'the moo field match');
                assert.equal(message.other, 'yeah', 'the other field match');
            }
        });

        logger = loggerFactory('foo', loggerFactory.levels.debug, {
            foo : 'bar',
            moo : {
                a : 24
            }
        });
        logger.debug('nothing');
        logger.info({
            other: 'yeah',
            foo : 'norz',
            moo : [12]
        }, 'something');
    });

    QUnit.test('child logger', function(assert){
        var logger;
        var child;
        QUnit.expect(13);

        loggerFactory.register({
            log : function log(message){
                assert.equal(message.msg, 'something', 'the level match');
                assert.equal(message.foo, 'bar', 'the foo field match');
                assert.equal(message.bar, true, 'the bar field match');
            }
        });

        logger = loggerFactory('foo', loggerFactory.levels.debug, {
            foo : 'bar',
        });

        logger.trace('nothing');

        child = logger.child({bar : true});

        assert.equal(typeof child, 'object', 'The child should be an object');
        assert.equal(typeof child.log, 'function', 'The child has a log method');
        assert.equal(typeof child.emergency, 'function', 'The child has a emergency method');
        assert.equal(typeof child.alert, 'function', 'The child has an alert method');
        assert.equal(typeof child.critical, 'function', 'The child has a critical method');
        assert.equal(typeof child.error, 'function', 'The child has an error method');
        assert.equal(typeof child.warning, 'function', 'The child has a warning method');
        assert.equal(typeof child.notice, 'function', 'The child has a notice method');
        assert.equal(typeof child.info, 'function', 'The child has a info method');
        assert.equal(typeof child.debug, 'function', 'The child has a debug method');
        assert.equal(typeof child.level, 'function', 'The child has a level method');
        assert.equal(typeof child.child, 'function', 'The child has a child method');
        child.debug('something');
    });

});
