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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

define( [  "core/promise", "core/promiseQueue" ], function(  Promise, promiseQueue ) {
    "use strict";

    QUnit.module( "API" );

    QUnit.test( "factory", function( assert ) {
        assert.expect( 4 );

        assert.ok( typeof promiseQueue !== "undefined", "The module exports something" );
        assert.ok( typeof promiseQueue === "function", "The module exposes a function" );
        assert.ok( typeof promiseQueue() === "object", "The module is a factory" );
        assert.notDeepEqual( promiseQueue(), promiseQueue(), "The factory creates a new object" );
    } );

    QUnit.test( "queue methods", function( assert ) {

        var queue = promiseQueue();

        assert.expect( 4 );

        assert.equal( typeof queue.add, "function", 'the queue has a "add" method' );
        assert.equal( typeof queue.getValues, "function", 'the queue has a "getValues" method' );
        assert.equal( typeof queue.serie, "function", 'the queue has a "serie" method' );
        assert.equal( typeof queue.clear, "function", 'the queue has a "clear" method' );
    } );

    QUnit.module( "queue" );

    QUnit.test( "add", function( assert ) {

        var queue = promiseQueue();

        assert.expect( 6 );

        assert.equal( queue.add( Promise.resolve() ), queue, "the add method chains" );
        queue.add( Promise.resolve() );
        queue.add( new Promise( function( resolve ) {
            setTimeout( resolve, 50 );
        } ) );
        queue.add( new Promise( function( reject ) {
            setTimeout( reject, 50 );
        } ) );
        assert.equal( queue.getValues().length, 4, "The queue has 4 entries" );
        assert.ok( queue.getValues()[ 0 ] instanceof Promise, "The queue contains promises" );
        assert.ok( queue.getValues()[ 1 ] instanceof Promise, "The queue contains promises" );
        assert.ok( queue.getValues()[ 2 ] instanceof Promise, "The queue contains promises" );
        assert.ok( queue.getValues()[ 3 ] instanceof Promise, "The queue contains promises" );
    } );

    QUnit.test( "clear", function( assert ) {

        var queue = promiseQueue();

        assert.expect( 3 );

        queue.add( Promise.resolve() );
        queue.add( Promise.resolve() );
        queue.add( new Promise( function( resolve ) {
            setTimeout( resolve, 50 );
        } ) );
        queue.add( new Promise( function( reject ) {
            setTimeout( reject, 50 );
        } ) );
        assert.equal( queue.getValues().length, 4, "The queue has 4 entries" );

        assert.equal( queue.clear(), queue, "the clear method chains" );

        assert.equal( queue.getValues().length, 0, "The queue is now empty" );
    } );
    QUnit.test( "serie", function( assert ) {
        var ready = assert.async();

        var states = {
            a: "waiting",
            b: "waiting"
        };
        var queue = promiseQueue();

        assert.expect( 4 );

        queue.serie( function a() {
            return new Promise( function( resolve ) {
                setTimeout( function() {
                    assert.equal( states.b, "waiting", "The second promise function is still waiting" );
                    states.a = "done";
                    resolve();
                }, 100 );
            } );
        } )
        .catch( function( err ) {
            assert.ok( false, err.message );
            ready();
        } );
        queue.serie( function b() {
            assert.equal( states.a, "done", "The 1st promise function is finished" );
            return new Promise( function( resolve ) {
                setTimeout( function() {
                    states.b = "done";
                    resolve();
                }, 50 );
            } );
        } ).then( function() {
            assert.equal( states.a, "done", "The 1st promise function is finished" );
            assert.equal( states.b, "done", "The 2nd promise function is finished" );
            ready();
        } )
        .catch( function( err ) {
            assert.ok( false, err.message );
            ready();
        } );
    } );

    QUnit.test( "3 in serie", function( assert ) {
        var ready = assert.async();

        var states = {
            a: "waiting",
            b: "waiting",
            c: "waiting"
        };
        var queue = promiseQueue();

        assert.expect( 4 );

        queue.serie( function a() {
            states.a = "started";
            return new Promise( function( resolve ) {
                setTimeout( function() {
                    states.a = "done";
                    resolve();
                }, 100 );
            } );
        } );
        queue.serie( function b() {
            states.b = "started";
            return new Promise( function( resolve ) {
                setTimeout( function a() {
                    states.b = "done";
                    resolve();
                }, 100 );
            } );
        } );
        queue.serie( function c() {
            states.c = "started";
            return new Promise( function( resolve ) {
                setTimeout( function a() {
                    states.c = "done";
                    resolve();
                }, 100 );
            } );
        } );

        setTimeout( function() {
            assert.deepEqual( states, { a: "started", b: "waiting", c: "waiting" } );
        }, 10 );
        setTimeout( function() {
            assert.deepEqual( states, { a: "done", b: "started", c: "waiting" } );
        }, 110 );
        setTimeout( function() {
            assert.deepEqual( states, { a: "done", b: "done", c: "started" } );
        }, 210 );
        setTimeout( function() {
            assert.deepEqual( states, { a: "done", b: "done", c: "done" } );
            ready();
        }, 310 );
    } );

    QUnit.test( "serie resolved data and reject", function( assert ) {
        var ready = assert.async();

        var queue = promiseQueue();

        assert.expect( 4 );

        queue.serie( function a() {
            return new Promise( function( resolve ) {
                setTimeout( function() {
                    resolve( "a" );
                }, 100 );
            } );
        } ).then( function( data ) {
            assert.equal( data, "a" );
        } )
        .catch( function( err ) {
            assert.ok( false, err.message );
            ready();
        } );
        queue.serie( function b() {
            return new Promise( function( resolve ) {
                setTimeout( function() {
                    resolve( "b" );
                }, 100 );
            } );
        } ).then( function( data ) {
            assert.equal( data, "b" );
        } )
        .catch( function( err ) {
            assert.ok( false, err.message );
            ready();
        } );
        queue.serie( function c() {
            return new Promise( function( resolve, reject ) {
                setTimeout( function() {
                    reject( new TypeError( "c" ) );
                }, 100 );
            } );
        } ).then( function() {
            assert.ok( false, "rejected must not resolve" );
            ready();
        } )
        .catch( function( err ) {
            assert.ok( err instanceof TypeError, "The correct error is rejected" );
            assert.equal( err.message, "c", "The correct error is rejected" );
            ready();
        } );
    } );

    QUnit.test( "early reject", function( assert ) {

        var ready = assert.async();

        var states = {
            a: "waiting",
            b: "waiting",
            c: "waiting",
            d: "waiting"
        };
        var queue = promiseQueue();

        assert.expect( 5 );

        queue.serie( function a() {
            states.a = "started";
            return new Promise( function( resolve ) {
                setTimeout( function() {
                    states.a = "done";
                    resolve();
                }, 100 );
            } );
        } );
        queue.serie( function b() {
            states.b = "started";
            return new Promise( function( resolve, reject ) {
                setTimeout( function a() {
                    states.b = "error";
                    reject( new Error( "b" ) );
                }, 100 );
            } );
        } ).catch( function( err ) {
            assert.equal( err.message, "b" );

            //D must be called, it is added after the rejection
            queue.serie( function d() {
                states.d = "started";
                return new Promise( function( resolve ) {
                    setTimeout( function a() {
                        states.d = "done";
                        resolve();
                    }, 100 );
                } );
            } );
        } );

        //C must never be called, the rejection was done before
        queue.serie( function c() {
            states.c = "started";
            return new Promise( function( resolve ) {
                setTimeout( function a() {
                    states.c = "done";
                    resolve();
                }, 100 );
            } );
        } ).then( function() {
            assert.ok( false, "must not be called" );
            ready();
        } )
        .catch( function() {
            assert.ok( false, "must not be called" );
            ready();
        } );

        setTimeout( function() {
            assert.deepEqual( states, { a: "started", b: "waiting", c: "waiting", d: "waiting" } );
        }, 10 );
        setTimeout( function() {
            assert.deepEqual( states, { a: "done", b: "started", c: "waiting", d: "waiting" } );
        }, 110 );
        setTimeout( function() {
            assert.deepEqual( states, { a: "done", b: "error", c: "waiting", d: "started" } );
        }, 210 );
        setTimeout( function() {
            assert.deepEqual( states, { a: "done", b: "error", c: "waiting", d: "done" } );
            ready();
        }, 310 );
    } );
} );
