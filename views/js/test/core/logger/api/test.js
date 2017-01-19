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

        assert.ok(typeof loggerFactory.levels.fatal === 'number', 'The module exposes a fatal level');
        assert.ok(typeof loggerFactory.levels.error === 'number', 'The module exposes a debug level');
        assert.ok(typeof loggerFactory.levels.warn === 'number', 'The module exposes a warn level');
        assert.ok(typeof loggerFactory.levels.info === 'number', 'The module exposes an info level');
        assert.ok(typeof loggerFactory.levels.debug === 'number', 'The module exposes a debug level');
        assert.ok(typeof loggerFactory.levels.trace === 'number', 'The module exposes a trace level');

        assert.ok(loggerFactory.levels.fatal > loggerFactory.levels.error, 'The fatal level is greater than error');
        assert.ok(loggerFactory.levels.error > loggerFactory.levels.warn, 'The error level is greater than warn');
        assert.ok(loggerFactory.levels.warn > loggerFactory.levels.info, 'The warn level is greater than info');
        assert.ok(loggerFactory.levels.info > loggerFactory.levels.debug, 'The info level is greater than debug');
        assert.ok(loggerFactory.levels.debug > loggerFactory.levels.trace, 'The debug level is greater than trace');
        assert.ok(loggerFactory.levels.trace > 0, 'The error level is greater than warn');
    });

    QUnit.test('factory', function(assert){
        QUnit.expect(2);

        assert.ok(typeof loggerFactory('foo') === 'object', 'The factory creates an object');
        assert.notEqual(loggerFactory('foo'), loggerFactory('foo'), 'The factory creates an new object');
    });

    QUnit.test('logger instance', function(assert){
        var logger;
        QUnit.expect(10);

        logger = loggerFactory('foo');

        assert.equal(typeof logger, 'object', 'The logger should be an object');
        assert.equal(typeof logger.log, 'function', 'The logger has a log method');
        assert.equal(typeof logger.fatal, 'function', 'The logger has a fatal method');
        assert.equal(typeof logger.error, 'function', 'The logger has an error method');
        assert.equal(typeof logger.warn, 'function', 'The logger has a warn method');
        assert.equal(typeof logger.info, 'function', 'The logger has an info method');
        assert.equal(typeof logger.debug, 'function', 'The logger has a debug method');
        assert.equal(typeof logger.trace, 'function', 'The logger has a trace method');

        assert.equal(typeof logger.level, 'function', 'The logger has a level method');
        assert.equal(typeof logger.child, 'function', 'The logger has a child method');
    });


    QUnit.module('behavior', {
        setup    : function(){
            loggerFactory.providers = [];
        }
    });

    QUnit.cases([
        { title: 'info text', name : 'foo', level : 'info', args : ['bar'], expected: { level : 'info', name : 'foo', msg : 'bar'} },
        { title: 'warn format', name : 'woo', level : 'warn', args : ['holy %s', 'foo'], expected: { level : 'warn', name : 'woo', msg : 'holy foo'} },
        { title: 'info num format', name : 'noo', level : 'info', args : [{a : true}, 'hello %d %s', 12, 'bar'], expected: { level : 'info', name : 'noo', msg : 'hello 12 bar'} },
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
                assert.equal(message.msg, data.expected.msg, 'The message name match');
            }
        });

        logger = loggerFactory(data.name);
        logger[data.level].apply(logger, data.args);
    });
/*

    QUnit.test('level number call', function(assert){
        QUnit.expect(5);

        loggerFactory.register({
            log : function log(message){
                assert.equal(typeof message, 'object', 'The message object is there');
                assert.equal(typeof message.time, 'number', 'The message has a time');
                assert.equal(message.level, 'warn', 'The level matches');
                assert.equal(message.messages.length, 1, 'There is one message');
                assert.equal(message.messages[0], 'bar', 'The message is correct');
            }
        });

        var logger = loggerFactory('foo');
        logger.log(40, 'bar');
    });

    QUnit.test('log default level call', function(assert){
       QUnit.expect(5);

       loggerFactory.register({
            log : function log(message){
                assert.equal(typeof message, 'object', 'the message object is there');
                assert.equal(typeof message.time, 'number', 'the message has a time');
                assert.equal(message.level, 'info', 'the level matches');
                assert.equal(message.messages.length, 1, 'there is one message');
                assert.equal(message.messages[0], 'foo', 'the message is correct');
            }
        });

        var logger = loggerFactory('foo');
        logger.log('foo');
    });

    QUnit.test('multiple messages call', function(assert){
        QUnit.expect(8);

       loggerFactory.register({
            log : function log(message){
                assert.equal(typeof message, 'object', 'the message object is there');
                assert.equal(typeof message.time, 'number', 'the message has a time');
                assert.equal(message.level, 'trace', 'The level matches');
                assert.equal(message.messages.length, 4, 'There is one message');
                assert.equal(message.messages[0], '10', 'The message is correct');
                assert.equal(message.messages[1], 'bar', 'The message is correct');
                assert.deepEqual(message.messages[2], { a: 'b'}, 'The message is correct');
                assert.deepEqual(message.messages[3], [1,2], 'The message is correct');
            }
        });

        var logger = loggerFactory('foo');
        logger.trace(10, 'bar', { a : 'b'}, [1, 2]);
    });

    QUnit.test('context', function(assert){
        QUnit.expect(3);
        var out = {};
        loggerFactory.register({
            log : function log(message){
                out[message.level] = message.context+'-'+message.messages.join('-');
            }
        });
        var logger = loggerFactory('TEST');
        logger.debug('foo');
        logger.warn('bar');
        logger.fatal('moo', 'nox');

        assert.equal(out.debug, 'TEST-foo', 'The contxtualized message is correct');
        assert.equal(out.warn, 'TEST-bar', 'The contxtualized message is correct');
        assert.equal(out.fatal, 'TEST-moo-nox', 'The contxtualized message is correct');
    });

    QUnit.test('fatal with a stack', function(assert){
        QUnit.expect(5);

        loggerFactory.register({
            log : function log(message){
                assert.equal(typeof message, 'object', 'the message object is there');
                assert.equal(typeof message.time, 'number', 'the message has a time');
                assert.equal(message.level, 'fatal', 'the level matches');
                assert.equal(typeof message.stack, 'string', 'a stack trace is present');
                assert.ok(message.stack.length > 0, 'the stack is not empty');
            }
        });

        var logger = loggerFactory('foo');
        logger.fatal('foo');
    });

    QUnit.asyncTest('late regsitration', function(assert){
        QUnit.expect(8);

        var counter = 0;
        var logger = loggerFactory('foo');

        assert.ok(typeof loggerFactory.providers === 'undefined', 'There is no provider registered');
        logger.fatal('foo');
        logger.fatal('$bar');
        logger.debug('baz');
        logger.warn('nox');
        assert.ok(typeof loggerFactory.providers === 'undefined', 'There is no provider registered');


        setTimeout(function(){
            loggerFactory.register({
                log : function log(msg){
                   assert.equal(msg.messages.length, 1, 'There is a message');
                   counter++;

                   if(counter === 5){
                        QUnit.start();
                   }
                }
            });
            assert.ok(typeof loggerFactory.providers !== 'undefined', 'There is a provider registered');

            //this one triggers the flush
            logger.debug('boo');
        }, 1);
    });*/
});
