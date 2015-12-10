define(['core/logger/api'], function(loggerFactory){
    'use strict';

    var mockProvider = {
        log : function() { }
    };

    QUnit.module('API');

    QUnit.test("module", function(assert){
        QUnit.expect(2);

        assert.ok(typeof loggerFactory !== 'undefined', "The module exports something");
        assert.ok(typeof loggerFactory === 'function', "The module exposes a function");
    });

    QUnit.test("factory", function(assert){
        QUnit.expect(3);

        assert.ok(typeof loggerFactory(mockProvider) === 'object', "The factory creates an object");
        assert.notEqual(loggerFactory(mockProvider), loggerFactory(mockProvider), "The factory creates an new object");

        assert.throws(function(){
            loggerFactory();
        }, TypeError, 'The fact');

    });

    QUnit.test("logger", function(assert){
        QUnit.expect(8);

        var logger = loggerFactory(mockProvider);
        assert.equal(typeof logger, 'object', 'The logger should be an object');
        assert.equal(typeof logger.log, 'function', 'The logger has a log method');
        assert.equal(typeof logger.fatal, 'function', 'The logger has a fatal method');
        assert.equal(typeof logger.error, 'function', 'The logger has an error method');
        assert.equal(typeof logger.warn, 'function', 'The logger has a warn method');
        assert.equal(typeof logger.info, 'function', 'The logger has an info method');
        assert.equal(typeof logger.debug, 'function', 'The logger has a debug method');
        assert.equal(typeof logger.trace, 'function', 'The logger has a trace method');
    });


    QUnit.module('provider');

    QUnit.test("level name call", function(assert){
        QUnit.expect(3);

        var logger = loggerFactory({
            log : function log(level, messages){
                assert.equal(level, 'info', 'The level matches');
                assert.equal(messages.length, 1, 'There is one message');
                assert.equal(messages[0], 'foo', 'The message is correct');
            }
        });
        logger.info('foo');
    });


    QUnit.test("level number call", function(assert){
        QUnit.expect(3);

        var logger = loggerFactory({
            log : function log(level, messages){
                assert.equal(level, 'warn', 'The level matches');
                assert.equal(messages.length, 1, 'There is one message');
                assert.equal(messages[0], 'bar', 'The message is correct');
            }
        });
        logger.log(40, 'bar');
    });

    QUnit.test("log default level call", function(assert){
        QUnit.expect(3);

        var logger = loggerFactory({
            log : function log(level, messages){
                assert.equal(level, 'info', 'The level matches');
                assert.equal(messages.length, 1, 'There is one message');
                assert.equal(messages[0], 'foo', 'The message is correct');
            }
        });
        logger.log('foo');
    });

    QUnit.test("multiple messages call", function(assert){
        QUnit.expect(6);

        var logger = loggerFactory({
            log : function log(level, messages){
                assert.equal(level, 'trace', 'The level matches');
                assert.equal(messages.length, 4, 'There is one message');
                assert.equal(messages[0], '10', 'The message is correct');
                assert.equal(messages[1], 'bar', 'The message is correct');
                assert.equal(messages[2], '{"a":"b"}', 'The message is correct');
                assert.equal(messages[3], '[1,2]', 'The message is correct');
            }
        });
        logger.trace(10, 'bar', { a : 'b'}, [1, 2]);
    });

    QUnit.test("context", function(assert){
        QUnit.expect(3);
        var out = {};
        var logger = loggerFactory({
            log : function log(level, messages){
                out[level] = messages.join('-');
            }
        }, 'TEST');
        logger.debug('foo');
        logger.warn('bar');
        logger.fatal('moo', 'nox');

        assert.equal(out.debug, '[TEST]-foo', 'The contxtualized message is correct');
        assert.equal(out.warn, '[TEST]-bar', 'The contxtualized message is correct');
        assert.equal(out.fatal, '[TEST]-moo-nox', 'The contxtualized message is correct');
    });

});
