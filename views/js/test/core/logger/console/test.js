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
define( [  "core/logger/console" ], function(  consoleLogger ) {
    "use strict";

    //Keep a ref of the global functions
    var cerr   = window.console.error;
    var cwarn  = window.console.warn;
    var cinfo  = window.console.info;
    var clog   = window.console.log;
    var cdebug = window.console.debug;

    //Mock checkMinLevel function which should be propogated by core/logger/api module
    consoleLogger.checkMinLevel = function() {
        return true;
    };

    QUnit.module( "API" );

    QUnit.test( "module", function( assert ) {
        assert.expect( 3 );

        assert.ok( typeof consoleLogger !== "undefined", "The module exports something" );
        assert.ok( typeof consoleLogger === "object", "The module exposes an object" );
        assert.equal( typeof consoleLogger.log, "function", "The logger has a log method" );
    } );

    QUnit.module( "basic logging", {
        afterEach: function( assert ) {
            window.console.error = cerr;
            window.console.warn  = cwarn;
            window.console.info  = cinfo;
            window.console.log   = clog;
            window.console.debug = cdebug;
        }
    } );

    QUnit.test( "trace log", function( assert ) {
        var ready = assert.async();
        assert.expect( 4 );

        window.console.debug = function( name, message, record ) {
            assert.equal( name, "foo", "The logger name matches" );
            assert.equal( message, "hello", "The logger name matches" );
            assert.equal( typeof record, "object", "the record is an object" );
            assert.equal( record.level, "trace", "The record level is correct" );
            ready();
        };

        consoleLogger.log( {
            level: "trace",
            name: "foo",
            msg: "hello"
        } );
    } );

    QUnit.test( "debug log", function( assert ) {
        var ready = assert.async();

        var field = {
            array: [ "a", "b", "c" ],
            obj: {
                prop: true,
                time: new Date()
            },
            bool: false
        };
        assert.expect( 5 );

        window.console.debug = function( name, message, record ) {
            assert.equal( name, "foo", "The logger name matches" );
            assert.equal( message, "hello", "The logger name matches" );
            assert.equal( typeof record, "object", "the record is an object" );
            assert.equal( record.level, "debug", "The record level is correct" );
            assert.deepEqual( record.field, field, "The addtionnal field is kept" );
            ready();
        };

        consoleLogger.log( {
            level: "debug",
            name: "foo",
            msg: "hello",
            field: field
        } );
    } );

    QUnit.test( "info log", function( assert ) {
        var ready = assert.async();
        assert.expect( 5 );

        window.console.info = function( name, message, record ) {
            assert.equal( name, "foo", "The logger name matches" );
            assert.equal( message, "hello", "The logger name matches" );
            assert.equal( typeof record, "object", "the record is an object" );
            assert.equal( record.level, "info", "The record level is correct" );
            assert.equal( record.field, true, "The record field is available" );
            ready();
        };

        consoleLogger.log( {
            level: "info",
            name: "foo",
            msg: "hello",
            field: true
        } );
    } );

    QUnit.test( "warn log", function( assert ) {
        var ready = assert.async();
        assert.expect( 4 );

        window.console.warn = function( name, message, record ) {
            assert.equal( name, "foo", "The logger name matches" );
            assert.equal( message, "oops", "The logger name matches" );
            assert.equal( typeof record, "object", "the record is an object" );
            assert.equal( record.level, "warn", "The record level is correct" );
            ready();
        };

        consoleLogger.log( {
            level: "warn",
            name: "foo",
            msg: "oops"
        } );
    } );

    QUnit.test( "error log", function( assert ) {
        var ready = assert.async();
        assert.expect( 5 );

        window.console.error = function( name, message, err, record ) {
            assert.equal( name, "foo", "The logger name matches" );
            assert.equal( message, "oops", "The logger name matches" );
            assert.equal( typeof record, "object", "the record is an object" );
            assert.equal( record.level, "error", "The record level is correct" );
            assert.ok( record.err instanceof Error, "The record contains an error" );
            ready();
        };

        consoleLogger.log( {
            level: "error",
            name: "foo",
            msg: "oops",
            err: new Error( "oops" )
        } );
    } );

    QUnit.test( "fatal log", function( assert ) {
        var ready = assert.async();
        assert.expect( 5 );

        window.console.error = function( name, message, err, record ) {
            assert.equal( name, "foo", "The logger name matches" );
            assert.equal( message, "oops", "The logger name matches" );
            assert.equal( typeof record, "object", "the record is an object" );
            assert.equal( record.level, "fatal", "The record level is correct" );
            assert.ok( record.err instanceof Error, "The record contains an error" );
            ready();
        };

        consoleLogger.log( {
            level: "fatal",
            name: "foo",
            msg: "oops",
            err: new Error( "oops" )
        } );
    } );

    QUnit.module( "fallback logging", {
        beforeEach: function( assert ) {
            window.console.error = undefined;
            window.console.warn  = undefined;
            window.console.info = undefined;
            window.console.log = undefined;
            window.console.debug = undefined;
        },
        afterEach: function( assert ) {
            window.console.error = cerr;
            window.console.warn  = cwarn;
            window.console.info  = cinfo;
            window.console.log   = clog;
            window.console.debug = cdebug;
        }
    } );

    QUnit.test( "no native warn", function( assert ) {
        var ready = assert.async();
        assert.expect( 5 );

        window.console.log = function( level, name, message, record ) {
            assert.equal( level, "[WARN]", "The level is displayed" );
            assert.equal( name, "foo", "The logger name matches" );
            assert.equal( message, "oops", "The logger name matches" );
            assert.equal( typeof record, "object", "the record is an object" );
            assert.equal( record.level, "warn", "The record level is correct" );
            ready();
        };

        consoleLogger.log( {
            level: "warn",
            name: "foo",
            msg: "oops"
        } );
    } );

    QUnit.test( "no native debug", function( assert ) {
        var ready = assert.async();
        assert.expect( 5 );

        window.console.log = function( level, name, message, record ) {
            assert.equal( level, "[DEBUG]", "The level is displayed" );
            assert.equal( name, "foo", "The logger name matches" );
            assert.equal( message, "oops", "The logger name matches" );
            assert.equal( typeof record, "object", "the record is an object" );
            assert.equal( record.level, "debug", "The record level is correct" );
            ready();
        };

        consoleLogger.log( {
            level: "debug",
            name: "foo",
            msg: "oops"
        } );
    } );
} );
