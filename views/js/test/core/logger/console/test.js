define(['core/logger/console'], function(consoleLogger){
    'use strict';

    var cinfo = window.console.info;
    var clog = window.console.log;

    QUnit.module('API');

    QUnit.test("module", function(assert){
        QUnit.expect(2);

        assert.ok(typeof consoleLogger !== 'undefined', "The module exports something");
        assert.ok(typeof consoleLogger === 'function', "The module exposes a function");
    });

    QUnit.test("factory", function(assert){
        QUnit.expect(2);

        assert.ok(typeof consoleLogger() === 'object', "The factory creates an object");
        assert.notEqual(consoleLogger(), consoleLogger(), "The factory creates an new object");

    });

    QUnit.test("logger", function(assert){
        QUnit.expect(8);

        var logger = consoleLogger();
        assert.equal(typeof logger, 'object', 'The logger should be an object');
        assert.equal(typeof logger.log, 'function', 'The logger has a log method');
        assert.equal(typeof logger.fatal, 'function', 'The logger has a fatal method');
        assert.equal(typeof logger.error, 'function', 'The logger has an error method');
        assert.equal(typeof logger.warn, 'function', 'The logger has a warn method');
        assert.equal(typeof logger.info, 'function', 'The logger has an info method');
        assert.equal(typeof logger.debug, 'function', 'The logger has a debug method');
        assert.equal(typeof logger.trace, 'function', 'The logger has a trace method');
    });


    QUnit.module('console logger', {
        tearDown : function(){
             window.console.info = cinfo;
             window.console.log = clog;
        }
    });

    QUnit.asyncTest("info log", function(assert){
        QUnit.expect(1);

        //hack the window...
        window.console.info = function(message){
           assert.equal(message, 'foo', 'The message match');
           QUnit.start();
        };

        var logger = consoleLogger();
        logger.info('foo');
    });

    QUnit.asyncTest("fatal log", function(assert){
        QUnit.expect(2);

        //hack the window...
        window.console.log = function(level, message){
           assert.equal(level, 'FATAL', 'The level falls back to messages');
           assert.equal(message, 'baz', 'The message match');
           QUnit.start();
        };

        var logger = consoleLogger();
        logger.fatal('baz');
    });


    QUnit.asyncTest("info context log", function(assert){
        QUnit.expect(2);

        //hack the window...
        window.console.info = function(context, message){
           assert.equal(context, '[TEST]', 'The context match');
           assert.equal(message, 'bar', 'The message match');
           QUnit.start();
        };

        var logger = consoleLogger('TEST');
        logger.info('bar');
    });
});

