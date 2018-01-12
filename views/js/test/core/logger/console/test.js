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
 * Test the console logger provider
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['core/logger/console'], function(consoleLogger) {
    'use strict';

    //keep a ref of the global functions
    var cerr   = window.console.error;
    var cwarn  = window.console.warn;
    var cinfo  = window.console.info;
    var clog   = window.console.log;
    var cdebug = window.console.debug;

    //mock checkMinLevel function which should be propogated by core/logger/api module
    consoleLogger.checkMinLevel = function () {
        return true;
    };

    QUnit.module('API');

    QUnit.test('module', function(assert){
        QUnit.expect(3);

        assert.ok(typeof consoleLogger !== 'undefined', "The module exports something");
        assert.ok(typeof consoleLogger === 'object', "The module exposes an object");
        assert.equal(typeof consoleLogger.log, 'function', 'The logger has a log method');
    });


    QUnit.module('basic logging', {
        teardown: function() {
            window.console.error = cerr;
            window.console.warn  = cwarn;
            window.console.info  = cinfo;
            window.console.log   = clog;
            window.console.debug = cdebug;
        }
    });

    QUnit.asyncTest("trace log", function(assert) {
        QUnit.expect(4);

        window.console.debug = function(name, message, record) {
            assert.equal(name, 'foo', 'The logger name matches');
            assert.equal(message, 'hello', 'The logger name matches');
            assert.equal(typeof record, 'object', 'the record is an object');
            assert.equal(record.level, 'trace', 'The record level is correct');
            QUnit.start();
        };

        consoleLogger.log({
            level: 'trace',
            name : 'foo',
            msg: 'hello'
        });
    });

    QUnit.asyncTest("debug log", function(assert) {

        var field = {
            array : ['a', 'b', 'c'],
            obj : {
                prop: true,
                time : new Date()
            },
            bool : false
        };
        QUnit.expect(5);

        window.console.debug = function(name, message, record) {
            assert.equal(name, 'foo', 'The logger name matches');
            assert.equal(message, 'hello', 'The logger name matches');
            assert.equal(typeof record, 'object', 'the record is an object');
            assert.equal(record.level, 'debug', 'The record level is correct');
            assert.deepEqual(record.field, field, 'The addtionnal field is kept');
            QUnit.start();
        };

        consoleLogger.log({
            level: 'debug',
            name : 'foo',
            msg: 'hello',
            field : field
        });
    });

    QUnit.asyncTest("info log", function(assert) {
        QUnit.expect(5);

        window.console.info = function(name, message, record) {
            assert.equal(name, 'foo', 'The logger name matches');
            assert.equal(message, 'hello', 'The logger name matches');
            assert.equal(typeof record, 'object', 'the record is an object');
            assert.equal(record.level, 'info', 'The record level is correct');
            assert.equal(record.field, true, 'The record field is available');
            QUnit.start();
        };

        consoleLogger.log({
            level: 'info',
            name : 'foo',
            msg: 'hello',
            field : true
        });
    });

    QUnit.asyncTest("warn log", function(assert) {
        QUnit.expect(4);

        window.console.warn = function(name, message, record) {
            assert.equal(name, 'foo', 'The logger name matches');
            assert.equal(message, 'oops', 'The logger name matches');
            assert.equal(typeof record, 'object', 'the record is an object');
            assert.equal(record.level, 'warn', 'The record level is correct');
            QUnit.start();
        };

        consoleLogger.log({
            level: 'warn',
            name : 'foo',
            msg: 'oops'
        });
    });

    QUnit.asyncTest("error log", function(assert) {
        QUnit.expect(5);

        window.console.error = function(name, message, err, record) {
            assert.equal(name, 'foo', 'The logger name matches');
            assert.equal(message, 'oops', 'The logger name matches');
            assert.equal(typeof record, 'object', 'the record is an object');
            assert.equal(record.level, 'error', 'The record level is correct');
            assert.ok(record.err instanceof Error, 'The record contains an error');
            QUnit.start();
        };

        consoleLogger.log({
            level: 'error',
            name : 'foo',
            msg: 'oops',
            err: new Error('oops')
        });
    });

    QUnit.asyncTest("fatal log", function(assert) {
        QUnit.expect(5);

        window.console.error = function(name, message, err, record) {
            assert.equal(name, 'foo', 'The logger name matches');
            assert.equal(message, 'oops', 'The logger name matches');
            assert.equal(typeof record, 'object', 'the record is an object');
            assert.equal(record.level, 'fatal', 'The record level is correct');
            assert.ok(record.err instanceof Error, 'The record contains an error');
            QUnit.start();
        };

        consoleLogger.log({
            level: 'fatal',
            name : 'foo',
            msg: 'oops',
            err: new Error('oops')
        });
    });


    QUnit.module('fallback logging', {
        setup : function(){
            window.console.error = undefined;
            window.console.warn  = undefined;
            window.console.info = undefined;
            window.console.log = undefined;
            window.console.debug = undefined;
        },
        teardown: function() {
            window.console.error = cerr;
            window.console.warn  = cwarn;
            window.console.info  = cinfo;
            window.console.log   = clog;
            window.console.debug = cdebug;
        }
    });

    QUnit.asyncTest('no native warn', function(assert){
        QUnit.expect(5);

        window.console.log = function(level, name, message, record) {
            assert.equal(level, '[WARN]', 'The level is displayed');
            assert.equal(name, 'foo', 'The logger name matches');
            assert.equal(message, 'oops', 'The logger name matches');
            assert.equal(typeof record, 'object', 'the record is an object');
            assert.equal(record.level, 'warn', 'The record level is correct');
            QUnit.start();
        };

        consoleLogger.log({
            level: 'warn',
            name : 'foo',
            msg: 'oops'
        });
    });

    QUnit.asyncTest('no native debug', function(assert){
        QUnit.expect(5);

        window.console.log = function(level, name, message, record) {
            assert.equal(level, '[DEBUG]', 'The level is displayed');
            assert.equal(name, 'foo', 'The logger name matches');
            assert.equal(message, 'oops', 'The logger name matches');
            assert.equal(typeof record, 'object', 'the record is an object');
            assert.equal(record.level, 'debug', 'The record level is correct');
            QUnit.start();
        };

        consoleLogger.log({
            level: 'debug',
            name : 'foo',
            msg: 'oops'
        });
    });
});
