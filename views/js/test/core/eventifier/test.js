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

define( [
    
    "lodash",
    "core/eventifier",
    "core/promise",
    "tao/test/core/logger/testLogger"
], function(  _, eventifier, Promise, testLogger ) {
    "use strict";

    QUnit.module( "eventifier" );

    QUnit.test( "api", function( assert ) {
        assert.expect( 2 );

        assert.ok( typeof eventifier !== "undefined", "The module exports something" );
        assert.ok( typeof eventifier === "function", "The module has an eventifier method" );
    } );

    QUnit.module( "eventification", {
        beforeEach: function setup( assert ) {
            testLogger.reset();
        }
    } );

    QUnit.test( "delegates", function( assert ) {

        var emitter = eventifier();

        assert.expect( 8 );

        assert.ok( typeof emitter === "object", "the emitter definition is an object" );
        assert.ok( typeof emitter.on === "function", "the emitter defintion holds the method on" );
        assert.ok( typeof emitter.before === "function", "the emitter defintion holds the method before" );
        assert.ok( typeof emitter.after === "function", "the emitter defintion holds the method after" );
        assert.ok( typeof emitter.off === "function", "the emitter defintion holds the method off" );
        assert.ok( typeof emitter.removeAllListeners === "function", "the emitter defintion holds the method removeAllListeners" );
        assert.ok( typeof emitter.trigger === "function", "the emitter defintion holds the method trigger" );
        assert.ok( typeof emitter.spread === "function", "the emitter defintion holds the method spread" );
    } );

    QUnit.test( "warn when overwriting", function( assert ) {

        assert.expect( 4 );

        assert.equal( testLogger.getMessages().warn.length, 0, "No warning" );

        eventifier();
        assert.equal( testLogger.getMessages().warn.length, 0, "No overwrite no warning" );
        testLogger.reset();

        eventifier( {
            spread: function() {}
        } );

        assert.equal( testLogger.getMessages().warn.length, 1, "A warning is created because the spread method exists" );
        testLogger.reset();

        eventifier( {
            spread: function() {},
            trigger: function() {},
            on: function() {},
            foo: function() {}
        } );

        assert.equal( testLogger.getMessages().warn.length, 3, "Warnings are created because 3 methods exist" );
        testLogger.reset();

    } );

    QUnit.test( "listen and trigger with params", function( assert ) {
        var ready = assert.async();

        var emitter = eventifier();
        var params = [ "bar", "baz" ];

        assert.expect( 3 );

        emitter.on( "foo", function handleFoo( p0, p1 ) {
            assert.ok( true, "The foo event is triggered on emitter" );
            assert.equal( p0, params[ 0 ], "The received parameters are those from the trigger" );
            assert.equal( p1, params[ 1 ], "The received parameters are those from the trigger" );
            ready();
        } );

        emitter.trigger( "foo", params[ 0 ], params[ 1 ] );
    } );

    QUnit.test( "on context", function( assert ) {

        var emitter1 = eventifier();
        var emitter2 = eventifier();

        assert.expect( 1 );

        assert.notDeepEqual( emitter1, emitter2, "Emitters are different objects" );
    } );

    QUnit.test( "trigger context", function( assert ) {
        var ready = assert.async();
        var emitter1 = eventifier();
        var emitter2 = eventifier();

        assert.expect( 2 );

        emitter1.on( "foo", function( success ) {
            assert.ok( success, "The foo event is triggered on emitter1" );
        } );
        emitter2.on( "foo", function( success ) {
            assert.ok( success, "The foo event is triggered on emitter2" );
            ready();
        } );

        emitter1.trigger( "foo", true );
        setTimeout( function() {
            emitter2.trigger( "foo", true );
        }, 10 );
    } );

    QUnit.test( "off", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 1 );

        emitter.on( "foo", function() {
            assert.ok( false, "The foo event shouldn't be triggered" );
        } );
        emitter.on( "bar", function() {
            assert.ok( true, "The bar event should be triggered" );
            ready();
        } );

        emitter.off( "foo" );
        emitter.trigger( "foo" );
        setTimeout( function() {
            emitter.trigger( "bar" );
        }, 10 );
    } );

    QUnit.test( "off empty", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 2 );

        emitter.on( "foo", function() {
            assert.ok( true, "The foo event should be triggered" );
        } );
        emitter.on( "bar", function() {
            assert.ok( true, "The bar event should be triggered" );
            ready();
        } );

        emitter.off();
        emitter.trigger( "foo" );
        setTimeout( function() {
            emitter.trigger( "bar" );
        }, 10 );
    } );

    QUnit.test( "removeAllListeners", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 0 );

        emitter.on( "foo", function() {
            assert.ok( false, "The foo event shouldn't be triggered" );
        } );
        emitter.on( "bar", function() {
            assert.ok( true, "The bar event shouldn't be triggered" );
        } );

        emitter.removeAllListeners();
        emitter.trigger( "foo" );
        emitter.trigger( "bar" );
        setTimeout( function() {
            emitter.trigger( "foo" );
            emitter.trigger( "bar" );

            setTimeout( function() {
                ready();
            }, 10 );
        }, 10 );
    } );

    QUnit.test( "multiple listeners", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 2 );

        emitter.on( "foo", function() {
            assert.ok( true, "The 1st foo listener should be executed" );
        } );
        emitter.on( "foo", function() {
            assert.ok( true, "The 2nd foo listener should be executed" );
            ready();
        } );

        emitter.trigger( "foo" );
    } );

    QUnit.module( "namespaces", {
        beforeEach: function setup( assert ) {
            testLogger.reset();
        }
    } );

    QUnit.test( "listen namespace, trigger without namespace", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 4 );

        emitter.on( "foo", function() {
            assert.ok( true, "the foo handler is called" );
        } );
        emitter.on( "foo.*", function() {
            assert.ok( true, "the foo.* handler is called" );
        } );
        emitter.on( "foo.@", function() {
            assert.ok( true, "the foo.@ handler is called" );
        } );
        emitter.on( "foo.bar", function() {
            assert.ok( true, "the foo.bar handler is called" );
            ready();
        } );

        emitter.trigger( "foo" );
    } );

    QUnit.test( "listen namespace, trigger with default namespace", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 4 );

        emitter.on( "foo", function() {
            assert.ok( true, "the foo handler is called" );
        } );
        emitter.on( "foo.*", function() {
            assert.ok( true, "the foo.* handler is called" );
        } );
        emitter.on( "foo.@", function() {
            assert.ok( true, "the foo.@ handler is called" );
        } );
        emitter.on( "foo.bar", function() {
            assert.ok( true, "the foo.bar handler is called" );
            ready();
        } );

        emitter.trigger( "foo.@" );
    } );

    QUnit.test( "listen namespace, trigger with namespace", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 2 );

        emitter.on( "foo", function() {
            assert.ok( false, "the foo handler should not be called" );
        } );
        emitter.on( "foo.@", function() {
            assert.ok( false, "the foo.@ handler should not be called" );
        } );
        emitter.on( "foo.*", function() {
            assert.ok( true, "the foo.* handler is called" );
        } );
        emitter.on( "foo.bar", function() {
            assert.ok( true, "the foo.bar handler is called" );
            ready();
        } );
        emitter.on( "foo.baz", function() {
            assert.ok( false, "the foo.baz handler should not be called" );
        } );

        emitter.trigger( "foo.bar" );
    } );

    QUnit.test( "off namespaced event", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 0 );

        emitter.on( "foo", function() {
            assert.ok( false, "the foo handler should not be called" );
        } );
        emitter.on( "foo.bar", function() {
            assert.ok( false, "the foo.bar handler should not be called" );
        } );
        emitter.off( "foo" );

        emitter.trigger( "foo" );
        setTimeout( function() {
            ready();
        }, 1 );
    } );

    QUnit.test( "off namespaced", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 2 );

        emitter.on( "foo", function() {
            assert.ok( true, "the foo handler should be called" );
        } );
        emitter.on( "foo.baz", function() {
            assert.ok( true, "the foo.baz handler should be called" );
            ready();
        } );
        emitter.on( "foo.bar", function() {
            assert.ok( false, "the foo.bar handler should not be called" );

        } );
        emitter.on( "norz.bar", function() {
            assert.ok( false, "the norz.bar handler should not be called" );
        } );

        emitter.off( ".bar" );

        emitter.trigger( "foo" ).trigger( "norz" );
    } );

    QUnit.test( "off all namespaces", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 1 );

        emitter.on( "foo", function() {
            assert.ok( true, "the foo handler should be called" );
            ready();
        } );
        emitter.on( "foo.baz", function() {
            assert.ok( false, "the foo.baz handler should not be called" );
        } );
        emitter.on( "foo.bar", function() {
            assert.ok( false, "the foo.bar handler should not be called" );

        } );
        emitter.on( "norz.bar", function() {
            assert.ok( false, "the norz.bar handler should not be called" );
        } );

        emitter.off( ".*" );

        emitter.trigger( "foo" ).trigger( "norz" );
    } );

    QUnit.module( "before", {
        beforeEach: function setup( assert ) {
            testLogger.reset();
        }
    } );

    QUnit.test( "sync", function( assert ) {
        var ready = assert.async();

        var testDriver = eventifier();
        var arg1 = "X",
            arg2 = "Y";

        assert.expect( 15 );

        testDriver.on( "next", function() {
            assert.ok( true, "The 1st listener should be executed : e.g. save context recovery" );
        } );
        testDriver.on( "next", function() {
            assert.ok( true, "The 2nd listener should be executed : e.g. save response " );
        } );
        testDriver.on( "next", function() {
            assert.ok( true, "The third and last listener should be executed : e.g. move to next item" );
            ready();
        } );

        testDriver.before( "next", function( e, a1, a2 ) {
            assert.equal( typeof e, "object", "the event context object is provided" );
            assert.equal( e.name, "next", "the event name is provided" );
            assert.equal( e.namespace, "@", "the event namespace is provided" );
            assert.equal( a1, arg1, "the first event arg is correct" );
            assert.equal( a2, arg2, "the second event arg is correct" );
            assert.ok( true, "The 1st 'before' listener should be executed : e.g. validate item state" );
        } );
        testDriver.before( "next", function( e, a1, a2 ) {
            assert.equal( typeof e, "object", "the event context object is provided" );
            assert.equal( e.name, "next", "the event name is provided" );
            assert.equal( e.namespace, "@", "the event namespace is provided" );
            assert.equal( a1, arg1, "the first event arg is correct" );
            assert.equal( a2, arg2, "the second event arg is correct" );
            assert.ok( true, "The 2nd 'before' listener should be executed : e.g. validate a special interaction state" );
        } );

        testDriver.trigger( "next", arg1, arg2 );
    } );

    QUnit.test( "async - resolved promise", function( assert ) {
        var ready = assert.async();

        var testDriver = eventifier();
        var arg1 = "X",
            arg2 = "Y";

        assert.expect( 15 );

        testDriver.on( "next", function() {
            assert.ok( true, "The 1st listener should be executed : e.g. save context recovery" );
        } );
        testDriver.on( "next", function() {
            assert.ok( true, "The 2nd listener should be executed : e.g. save resposne " );
        } );
        testDriver.on( "next", function() {
            assert.ok( true, "The third and last listener should be executed : e.g. move to next item" );
            ready();
        } );

        testDriver.before( "next", function( e, a1, a2 ) {
            assert.equal( typeof e, "object", "the event context object is provided" );
            assert.equal( e.name, "next", "the event name is provided" );
            assert.equal( e.namespace, "@", "the event namespace is provided" );
            assert.equal( a1, arg1, "the first event arg is correct" );
            assert.equal( a2, arg2, "the second event arg is correct" );
            assert.ok( true, "The 1st 'before' listener should be executed : e.g. validate item state" );
            return new Promise( function( resolve ) {
                setTimeout( function() {
                    resolve();
                } );
            } );
        } );

        testDriver.before( "next", function( e, a1, a2 ) {
            assert.equal( typeof e, "object", "the event context object is provided" );
            assert.equal( e.name, "next", "the event name is provided" );
            assert.equal( e.namespace, "@", "the event namespace is provided" );
            assert.equal( a1, arg1, "the first event arg is correct" );
            assert.equal( a2, arg2, "the second event arg is correct" );
            assert.ok( true, "The 2nd 'before' listener should be executed : e.g. validate a special interaction state" );
        } );

        testDriver.trigger( "next", arg1, arg2 );
    } );

    QUnit.test( "sync - return false", function( assert ) {
        var ready = assert.async();

        var itemEditor = eventifier();

        assert.expect( 11 );

        itemEditor.on( "save", function() {
            assert.ok( false, "The listener should not be executed : e.g. do save item" );
        } );
        itemEditor.before( "save", function( e ) {
            assert.ok( true, "The 1st 'before' listener should be executed : e.g. validate current edition form" );
            assert.equal( typeof e, "object", "the event context object is provided" );
            assert.equal( e.name, "save", "the event name is provided" );
            assert.equal( e.namespace, "@", "the event namespace is provided" );

            //Form invalid
            return false;
        } );
        itemEditor.before( "save", function( e ) {
            assert.ok( true, "The 2nd 'before' listener should be executed : e.g. do save item stylesheet" );
            assert.equal( typeof e, "object", "the event context object is provided" );
            assert.equal( e.name, "save", "the event name is provided" );
            assert.equal( e.namespace, "@", "the event namespace is provided" );
        } );

        itemEditor.trigger( "save" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace,
                stopTraces = allTraces.filter( function( trace ) {
                    return trace.stoppedIn;
                } );
            ready();

            assert.equal( stopTraces.length, 1, "one stop trace has been logged" );
            assert.equal( stopTraces[ 0 ].stoppedIn, "before", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "save", "event has the correct name" );
        }, 10 );
    } );

    QUnit.test( "async - rejected promise", function( assert ) {
        var ready = assert.async();

        var itemEditor = eventifier();

        assert.expect( 11 );

        itemEditor.on( "save", function() {
            assert.ok( false, "The listener should not be executed : e.g. do save item" );
        } );
        itemEditor.before( "save", function( e ) {
            assert.ok( true, "The 1st 'before' listener should be executed : e.g. validate current edition form" );
            assert.equal( typeof e, "object", "the event context object is provided" );
            assert.equal( e.name, "save", "the event name is provided" );
            assert.equal( e.namespace, "@", "the event namespace is provided" );
            return new Promise( function( resolve, reject ) {
                setTimeout( function() {
                    reject();
                } );
            } );
        } );
        itemEditor.before( "save", function( e ) {
            assert.ok( true, "The 2nd 'before' listener should be executed : e.g. do save item stylesheet" );
            assert.equal( typeof e, "object", "the event context object is provided" );
            assert.equal( e.name, "save", "the event name is provided" );
            assert.equal( e.namespace, "@", "the event namespace is provided" );
        } );

        itemEditor.trigger( "save" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace,
                stopTraces = allTraces.filter( function( trace ) {
                    return trace.stoppedIn;
                } );
            ready();

            assert.equal( stopTraces.length, 1, "one stop trace has been logged" );
            assert.equal( stopTraces[ 0 ].stoppedIn, "before", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "save", "event has the correct name" );
        }, 10 );
    } );

    QUnit.test( "namespaced events before order", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        var state = {
            foo: false,
            foobar: false,
            beforefoo: false,
            beforefoobar: false
        };

        assert.expect( 18 );

        emitter.on( "foo", function() {
            assert.ok( true, "The foo handler is called" );
            assert.equal( state.beforefoo, true, "The before foo handler should hoave been called" );
            assert.equal( state.beforefoobar, true, "The before foo.bar handler should have been called" );
            state.foo = true;
        } );
        emitter.on( "foo.bar", function() {
            assert.ok( true, "The foo.bar handler is called" );
            assert.equal( state.beforefoo, true, "The before foo handler should have been called" );
            assert.equal( state.beforefoobar, true, "The before foo.bar handler should have been called" );
            state.foobar = true;
        } );
        emitter.before( "foo", function( e ) {
            assert.ok( true, "The before foo handler is called" );
            assert.equal( state.foo, false, "The foo handler should not have been called" );
            assert.equal( state.foobar, false, "The foo.bar handler should have been called" );

            assert.equal( typeof e, "object", "the event context object is provided" );
            assert.equal( e.name, "foo", "the event name is provided" );
            assert.equal( e.namespace, "@", "the event namespace is provided" );

            state.beforefoo = true;
        } );
        emitter.before( "foo.bar", function( e ) {
            assert.ok( true, "The before foo.bar handler is called" );
            assert.equal( state.foo, false, "The foo handler should not have been called" );
            assert.equal( state.foobar, false, "The foo.bar handler should have been called" );

            assert.equal( typeof e, "object", "the event context object is provided" );
            assert.equal( e.name, "foo", "the event name is provided" );
            assert.equal( e.namespace, "@", "the event namespace is provided" );

            state.beforefoobar = true;
        } );

        emitter.trigger( "foo" );

        setTimeout( function() {
            ready();
        }, 10 );
    } );

    QUnit.test( "events context (simple)", function( assert ) {
        var emitter = eventifier();
        var ready = assert.async();
        var ready1 = assert.async();

        assert.expect( 25 );

        emitter
            .on( "ev1", function() {
                assert.ok( true, "The ev1 handler is called" );
            } )
            .on( "ev1.*", function() {
                assert.ok( true, "The ev1.* handler is called" );
            } )
            .on( "ev1.ns", function() {
                assert.ok( true, "The ev1.ns handler is called" );
            } )
            .before( "ev1", function( e ) {
                assert.ok( true, "The before ev1 handler is called" );
                assert.equal( typeof e, "object", "the event context object is provided" );
                assert.equal( e.name, "ev1", "the event name is provided" );
                assert.equal( e.namespace, "@", "the event namespace is provided" );
            } )
            .before( "ev1.*", function( e ) {
                assert.ok( true, "The before ev1.* handler is called" );
                assert.equal( typeof e, "object", "the event context object is provided" );
                assert.equal( e.name, "ev1", "the event name is provided" );
                assert.equal( e.namespace, "@", "the event namespace is provided" );
            } )
            .before( "ev1.ns", function( e ) {
                assert.ok( true, "The before ev1.ns handler is called" );
                assert.equal( typeof e, "object", "the event context object is provided" );
                assert.equal( e.name, "ev1", "the event name is provided" );
                assert.equal( e.namespace, "@", "the event namespace is provided" );

                ready();
            } );

        emitter
            .on( "ev2", function() {
                assert.ok( false, "The ev2 handler should not be called" );
            } )
            .on( "ev2.*", function() {
                assert.ok( true, "The ev2.* handler is called" );
            } )
            .on( "ev2.ns", function() {
                assert.ok( true, "The ev2.ns handler is called" );
            } )
            .before( "ev2", function() {
                assert.ok( false, "The before ev2 handler should not be called" );
            } )
            .before( "ev2.*", function( e ) {
                assert.ok( true, "The before ev2.* handler is called" );
                assert.equal( typeof e, "object", "the event context object is provided" );
                assert.equal( e.name, "ev2", "the event name is provided" );
                assert.equal( e.namespace, "ns", "the event namespace is provided" );
            } )
            .before( "ev2.ns", function( e ) {
                assert.ok( true, "The before ev2.ns handler is called" );
                assert.equal( typeof e, "object", "the event context object is provided" );
                assert.equal( e.name, "ev2", "the event name is provided" );
                assert.equal( e.namespace, "ns", "the event namespace is provided" );

                ready1();
            } );

        emitter.trigger( "ev1" );
        emitter.trigger( "ev2.ns" );
    } );

    QUnit.test( "events context (multi)", function( assert ) {
        var emitter = eventifier();

        assert.expect( 80 );
        var ready = assert.async(8);

        emitter
            .on( "ev1", function() {
                assert.ok( true, "The ev1 handler is called" );
            } )
            .on( "ev1.ns", function() {
                assert.ok( true, "The ev1.ns handler is called" );
            } )
            .before( "ev1", function( e ) {
                assert.ok( true, "The before ev1 handler is called" );
                assert.equal( typeof e, "object", "the event context object is provided" );
                assert.equal( e.name, "ev1", "the event name is provided" );
                assert.equal( e.namespace, "@", "the event namespace is provided" );
            } )
            .before( "ev1.ns", function( e ) {
                assert.ok( true, "The before ev1.ns handler is called" );
                assert.equal( typeof e, "object", "the event context object is provided" );
                assert.equal( e.name, "ev1", "the event name is provided" );
                assert.equal( e.namespace, "@", "the event namespace is provided" );

                ready();
            } );

        emitter
            .on( "ev2", function() {
                assert.ok( true, "The ev2 handler is called" );
            } )
            .on( "ev2.ns", function() {
                assert.ok( true, "The ev2.ns handler is called" );
            } )
            .before( "ev2", function( e ) {
                assert.ok( true, "The before ev1 handler is called" );
                assert.equal( typeof e, "object", "the event context object is provided" );
                assert.equal( e.name, "ev2", "the event name is provided" );
                assert.equal( e.namespace, "@", "the event namespace is provided" );
            } )
            .before( "ev2.ns", function( e ) {
                assert.ok( true, "The before ev1.ns handler is called" );
                assert.equal( typeof e, "object", "the event context object is provided" );
                assert.equal( e.name, "ev2", "the event name is provided" );
                assert.equal( e.namespace, "@", "the event namespace is provided" );

                ready();
            } );

        emitter
            .on( "ev3", function() {
                assert.ok( false, "The ev3 handler should not be called" );
            } )
            .on( "ev3.*", function() {
                assert.ok( true, "The ev3.* handler is called" );
            } )
            .on( "ev3.ns3", function() {
                assert.ok( true, "The ev3.ns3 handler is called" );
            } )
            .before( "ev3", function() {
                assert.ok( false, "The before ev3 handler should not be called" );
            } )
            .before( "ev3.*", function( e ) {
                assert.ok( true, "The before ev3.* handler is called" );
                assert.equal( typeof e, "object", "the event context object is provided" );
                assert.equal( e.name, "ev3", "the event name is provided" );
                assert.equal( e.namespace, "ns3", "the event namespace is provided" );
            } )
            .before( "ev3.ns3", function( e ) {
                assert.ok( true, "The before ev3.ns3 handler is called" );
                assert.equal( typeof e, "object", "the event context object is provided" );
                assert.equal( e.name, "ev3", "the event name is provided" );
                assert.equal( e.namespace, "ns3", "the event namespace is provided" );

                ready();
            } );

        emitter
            .on( "ev4", function() {
                assert.ok( false, "The ev4 handler should not be called" );
            } )
            .on( "ev4.*", function() {
                assert.ok( true, "The ev4.* handler is called" );
            } )
            .on( "ev4.ns4", function() {
                assert.ok( true, "The ev4.ns4 handler is called" );
            } )
            .before( "ev4", function() {
                assert.ok( false, "The before ev4 handler should not be called" );
            } )
            .before( "ev4.*", function( e ) {
                assert.ok( true, "The before ev4.* handler is called" );
                assert.equal( typeof e, "object", "the event context object is provided" );
                assert.equal( e.name, "ev4", "the event name is provided" );
                assert.equal( e.namespace, "ns4", "the event namespace is provided" );
            } )
            .before( "ev4.ns4", function( e ) {
                assert.ok( true, "The before ev4.ns4 handler is called" );
                assert.equal( typeof e, "object", "the event context object is provided" );
                assert.equal( e.name, "ev4", "the event name is provided" );
                assert.equal( e.namespace, "ns4", "the event namespace is provided" );

                ready();
            } );

        emitter.trigger( "ev1 ev2" );
        emitter.trigger( "ev3.ns3 ev4.ns4" );
        emitter.trigger( "ev1 ev3.ns3" );
        emitter.trigger( "ev4.ns4 ev2" );
    } );

    QUnit.module( "on/between", {
        beforeEach: function setup( assert ) {
            testLogger.reset();
        }
    } );

    QUnit.test( "async promise, resolved", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier(),
            state = {
                foo: false
            };

        assert.expect( 3 );

        emitter
            .on( "foo", function() {
                return new Promise( function( resolve ) {
                    setTimeout( function() {
                        assert.ok( true, "The foo handler is called" );
                        state.foo = true;
                        resolve();
                    }, 10 );
                } );
            } )
            .after( "foo", function() {
                assert.ok( true, "The after foo handler is called" );
                assert.ok( state.foo, "The foo handler has been called before the after foo handler" );
                ready();
            } )
            .trigger( "foo" );
    } );

    QUnit.test( "async promise, rejected", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 4 );

        emitter
            .on( "foo", function() {
                return new Promise( function( resolve, reject ) {
                    setTimeout( function() {
                        assert.ok( true, "The foo handler is called" );
                        reject();
                    }, 10 );
                } );
            } )
            .after( "foo", function() {
                assert.ok( false, "The after foo handler should not be called" );
            } )
            .trigger( "foo" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace,
                stopTraces = allTraces.filter( function( trace ) {
                    return trace.stoppedIn;
                } );
            ready();

            assert.equal( stopTraces.length, 1, "one stop trace has been logged" );
            assert.equal( stopTraces[ 0 ].stoppedIn, "on", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "foo", "event has the correct name" );
        }, 20 );
    } );

    QUnit.test( "async promise, multiple resolve", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier(),
            state = {
                foo1: false,
                foo2: false
            };

        assert.expect( 5 );

        emitter
            .on( "foo", function() {
                return new Promise( function( resolve ) {
                    setTimeout( function() {
                        assert.ok( true, "The foo first handler is called" );
                        state.foo1 = true;
                        resolve();
                    }, 10 );
                } );
            } )
            .on( "foo", function() {
                return new Promise( function( resolve ) {
                    setTimeout( function() {
                        assert.ok( true, "The foo second handler is called" );
                        state.foo2 = true;
                        resolve();
                    }, 20 );
                } );
            } )
            .after( "foo", function() {
                assert.ok( true, "The after foo handler is called" );
                assert.ok( state.foo1, "The first foo handler has been called before the after foo handler" );
                assert.ok( state.foo2, "The second foo handler has been called before the after foo handler" );
                ready();
            } )
            .trigger( "foo" );
    } );

    QUnit.test( "async promise, multiple with mixed response", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 5 );

        emitter
            .on( "foo", function() {
                return new Promise( function( resolve ) {
                    setTimeout( function() {
                        assert.ok( true, "The foo first handler is called" );
                        resolve();
                    }, 10 );
                } );
            } )
            .on( "foo", function() {
                return new Promise( function( resolve, reject ) {
                    setTimeout( function() {
                        assert.ok( true, "The foo second handler is called" );
                        reject();
                    }, 20 );
                } );
            } )
            .after( "foo", function() {
                assert.ok( false, "The after foo handler should not be called" );
            } )
            .trigger( "foo" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace,
                stopTraces = allTraces.filter( function( trace ) {
                    return trace.stoppedIn;
                } );
            ready();

            assert.equal( stopTraces.length, 1, "one stop trace has been logged" );
            assert.equal( stopTraces[ 0 ].stoppedIn, "on", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "foo", "event has the correct name" );
        }, 30 );
    } );

    QUnit.module( "after", {
        beforeEach: function setup( assert ) {
            testLogger.reset();
        }
    } );

    QUnit.test( "trigger", function( assert ) {
        var ready = assert.async();

        var testDriver = eventifier();

        assert.expect( 5 );

        testDriver.on( "next", function() {
            assert.ok( true, "This listener should be executed : e.g. move to next item" );
        } );

        testDriver.after( "next", function( bool, str, num ) {
            assert.equal( bool, true, "The 1st parameter is correct" );
            assert.equal( str, "yo", "The 2nd parameter is correct" );
            assert.equal( num, 1.4, "The 3rd parameter is correct" );
            assert.ok( true, "This listener should be executed : e.g. push response to storage" );
            ready();
        } );
        testDriver.trigger( "next", true, "yo", 1.4 );
    } );

    QUnit.test( "namespaced after events order", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        var state = {
            foo: false,
            foobar: false,
            afterfoo: false,
            afterfoobar: false
        };

        assert.expect( 12 );

        emitter.on( "foo", function() {
            assert.ok( true, "The foo handler is called" );
            assert.equal( state.afterfoo, false, "The after foo handler should not be called yet" );
            assert.equal( state.afterfoobar, false, "The after foo.bar handler should not be called yet" );
            state.foo = true;
        } );
        emitter.on( "foo.bar", function() {
            assert.ok( true, "The foo.bar handler is called" );
            assert.equal( state.afterfoo, false, "The after foo handler should not be called yet" );
            assert.equal( state.afterfoobar, false, "The after foo.bar handler should not be called yet" );
            state.foobar = true;
        } );
        emitter.after( "foo", function() {
            assert.ok( true, "The after foo handler is called" );
            assert.equal( state.foo, true, "The foo handler should have been called" );
            assert.equal( state.foobar, true, "The foo.bar handler should have been called" );
            state.afterfoo = true;
        } );
        emitter.after( "foo.bar", function() {
            assert.ok( true, "The after foo.bar handler is called" );
            assert.equal( state.foo, true, "The foo handler should have been called" );
            assert.equal( state.foobar, true, "The foo.bar handler should have been called" );
            state.afterfoobar = true;
        } );

        emitter.trigger( "foo" );

        setTimeout( function() {
            ready();
        }, 10 );
    } );

    QUnit.module( "multiple events names" );

    QUnit.test( "listen multiples, trigger one by one", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        var counter = 0;

        assert.expect( 2 );

        emitter.on( "foo bar", function() {
            assert.ok( true, "the handler is called" );

            if ( ++counter === 2 ) {
                ready();
            }
        } );
        emitter.trigger( "foo" )
               .trigger( "bar" );
    } );

    QUnit.test( "listen multiple, trigger multiples with params", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        var counter = 0;

        assert.expect( 8 );

        emitter.on( "foo bar", function( bool, str, num ) {
            assert.ok( true, "the handler is called" );
            assert.equal( bool, true, "The 1st parameter is correct" );
            assert.equal( str, "yo", "The 2nd parameter is correct" );
            assert.equal( num, 1.4, "The 3rd parameter is correct" );

            if ( ++counter === 2 ) {
                ready();
            }
        } );
        emitter.trigger( "foo bar", true, "yo", 1.4 );
    } );

    QUnit.test( "listen multiple, off multiple", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 1 );

        emitter.on( "foo bar", function() {
            assert.ok( false, "the handler must not be called" );
        } );
        emitter.off( "foo bar" );

        emitter.trigger( "foo" )
               .trigger( "bar" );

        setTimeout( function() {
            assert.ok( true, "control" );
            ready();
        }, 10 );
    } );

    QUnit.test( "support namespace in multiple events", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        var counter = 0;

        assert.expect( 3 );

        emitter.on( "foo bar.moo", function() {
            assert.ok( true, "the handler is called" );

            if ( ++counter === 3 ) {
                ready();
            }
        } );

        emitter.trigger( "foo bar.moo bar" );
    } );

    QUnit.module( "stopEvent", {
        beforeEach: function setup( assert ) {
            testLogger.reset();
        }
    } );

    QUnit.test( "stop in sync .before() handlers", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 4 );

        emitter
            .before( "save", function() {
                assert.ok( true, "The 1st .before() handler has been called" );
                emitter.stopEvent( "save" );
            } )
            .before( "save", function() {
                assert.ok( false, "The 2nd .before() handler should not be called" );
            } )
            .on( "save", function() {
                assert.ok( false, "The .on() handler should not be called" );
            } )
            .after( "save", function() {
                assert.ok( false, "The .after() handler should not be called" );
            } )
            .trigger( "save" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace,
                stopTraces = allTraces.filter( function( trace ) {
                    return trace.stoppedIn;
                } );
            ready();

            assert.equal( stopTraces.length, 1, "one stop trace has been logged" );
            assert.equal( stopTraces[ 0 ].stoppedIn, "before", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "save", "event has the correct name" );
        }, 10 );
    } );

    QUnit.test( "stop in sync .on() handlers", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 5 );

        emitter
            .before( "save", function() {
                assert.ok( true, "The .before() handler has been called" );
            } )
            .on( "save", function() {
                assert.ok( true, "The 1st .on() handler has been called" );
                emitter.stopEvent( "save" );
            } )
            .on( "save", function() {
                assert.ok( false, "The 2nd .on() handler should not be called" );
            } )
            .after( "save", function() {
                assert.ok( false, "The .after() handler should not be called" );
            } )
            .trigger( "save" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace,
                stopTraces = allTraces.filter( function( trace ) {
                    return trace.stoppedIn;
                } );
            ready();

            assert.equal( stopTraces.length, 1, "one stop trace has been logged" );
            assert.equal( stopTraces[ 0 ].stoppedIn, "on", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "save", "event has the correct name" );
        }, 10 );
    } );

    QUnit.test( "stop in sync .after() handlers", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 6 );

        emitter
            .before( "save", function() {
                assert.ok( true, "The .before() handler has been called" );
            } )
            .on( "save", function() {
                assert.ok( true, "The .on() handler has been called" );
            } )
            .after( "save", function() {
                assert.ok( true, "The .after() handler has been called" );
                emitter.stopEvent( "save" );
            } )
            .after( "save", function() {
                assert.ok( false, "The 2nd .after() handler should not be called" );
            } )
            .trigger( "save" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace,
                stopTraces = allTraces.filter( function( trace ) {
                    return trace.stoppedIn;
                } );
            ready();

            assert.equal( stopTraces.length, 1, "one stop trace has been logged" );
            assert.equal( stopTraces[ 0 ].stoppedIn, "after", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "save", "event has the correct name" );
        }, 10 );
    } );

    QUnit.test( "stop in async .before() handlers", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 6 );

        emitter
            .before( "save", function() {
                assert.ok( true, "The 1st .before() handler has been called" );
            } )
            .before( "save", function() {
                assert.ok( true, "The 2nd .before() handler has been called" );
                return new Promise( function( resolve ) {
                    setTimeout( function() {
                        emitter.stopEvent( "save" );
                        resolve();
                    }, 10 );
                } );
            } )
            .before( "save", function() {
                assert.ok( true, "The 3rd .before() handler has been called" );
            } )
            .on( "save", function() {
                assert.ok( false, "The .on() handler should not be called" );
            } )
            .after( "save", function() {
                assert.ok( false, "The .after() handler should not be called" );
            } )
            .trigger( "save" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace,
                stopTraces = allTraces.filter( function( trace ) {
                    return trace.stoppedIn;
                } );
            ready();

            assert.equal( stopTraces.length, 1, "one stop trace has been logged" );
            assert.equal( stopTraces[ 0 ].stoppedIn, "before", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "save", "event has the correct name" );
        }, 20 );
    } );

    QUnit.test( "stop in async .on() handlers", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 7 );

        emitter
            .before( "save", function() {
                assert.ok( true, "The .before() handler has been called" );
            } )
            .on( "save", function() {
                assert.ok( true, "The 1st .on() handler has been called" );
            } )
            .on( "save", function() {
                assert.ok( true, "The 2nd .on() handler has been called" );
                return new Promise( function( resolve ) {
                    setTimeout( function() {
                        emitter.stopEvent( "save" );
                        resolve();
                    }, 10 );
                } );
            } )
            .on( "save", function() {
                assert.ok( true, "The 3rd .on() handler has been called" );
            } )
            .after( "save", function() {
                assert.ok( false, "The .after() handler should not be called" );
            } )
            .trigger( "save" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace,
                stopTraces = allTraces.filter( function( trace ) {
                    return trace.stoppedIn;
                } );
            ready();

            assert.equal( stopTraces.length, 1, "one stop trace has been logged" );
            assert.equal( stopTraces[ 0 ].stoppedIn, "on", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "save", "event has the correct name" );
        }, 20 );
    } );

    QUnit.test( "stop in async .after() handlers", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 8 );

        emitter
            .before( "save", function() {
                assert.ok( true, "The .before() handler has been called" );
            } )
            .on( "save", function() {
                assert.ok( true, "The .on() handler has been called" );
            } )
            .after( "save", function() {
                assert.ok( true, "The 1st .after() handler has been called" );
            } )
            .after( "save", function() {
                assert.ok( true, "The 2nd .after() handler has been called" );
                return new Promise( function( resolve ) {
                    setTimeout( function() {
                        emitter.stopEvent( "save" );
                        resolve();
                    }, 10 );
                } );
            } )
            .after( "save", function() {
                assert.ok( true, "The 3rd .after() handler has been called" );
            } )
            .trigger( "save" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace,
                stopTraces = allTraces.filter( function( trace ) {
                    return trace.stoppedIn;
                } );
            ready();

            assert.equal( stopTraces.length, 1, "one stop trace has been logged" );
            assert.equal( stopTraces[ 0 ].stoppedIn, "after", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "save", "event has the correct name" );
        }, 20 );
    } );

    QUnit.test( "sync stop with multiple events", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 6 );

        emitter
            .on( "save", function() {
                assert.ok( true, "The .on(save) handler has been called" );
                emitter.stopEvent( "save" );
            } )
            .after( "save", function() {
                assert.ok( false, "The .after(save) handler should not be called" );
            } )
            .on( "exit", function() {
                assert.ok( true, "The .on(exit) handler has been called" );
            } )
            .after( "exit", function() {
                assert.ok( true, "The .after(exit) handler has been called" );
            } )
            .trigger( "save exit" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace,
                stopTraces = allTraces.filter( function( trace ) {
                    return trace.stoppedIn;
                } );
            ready();

            assert.equal( stopTraces.length, 1, "one stop trace has been logged" );
            assert.equal( stopTraces[ 0 ].stoppedIn, "on", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "save", "event has the correct name" );
        }, 10 );
    } );

    QUnit.test( "async stop with multiple events", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 7 );

        emitter
            .on( "save", function() {
                return new Promise( function( resolve ) {
                    setTimeout( function() {
                        assert.ok( true, "The .on(save) handler has been called" );
                        emitter.stopEvent( "save" );
                        resolve();
                    }, 10 );
                } );
            } )
            .after( "save", function() {
                assert.ok( false, "The .after(save) handler should not be called" );
            } )
            .before( "exit", function() {
                return new Promise( function( resolve ) {
                    setTimeout( function() {
                        assert.ok( true, "The .before(exit) handler has been called" );
                        emitter.stopEvent( "exit" );
                        resolve();
                    }, 20 );
                } );
            } )
            .on( "exit", function() {
                assert.ok( false, "The .on(save) handler should not be called" );
            } )
            .trigger( "save exit" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace,
                stopTraces = allTraces.filter( function( trace ) {
                    return trace.stoppedIn;
                } );
            ready();

            assert.equal( stopTraces.length, 2, "two stop traces have been logged" );

            assert.equal( stopTraces[ 0 ].stoppedIn, "on", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "save", "event has the correct name" );

            assert.equal( stopTraces[ 1 ].stoppedIn, "before", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 1 ].event, "exit", "event has the correct name" );
        }, 30 );
    } );

    QUnit.test( "stop cancel all namespaces", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 4 );

        emitter
            .before( "save.ns1", function() {
                assert.ok( true, "The .before() handler has been called" );
                emitter.stopEvent( "save" );
            } )
            .on( "save", function() {
                assert.ok( false, "The .on(save) handler should not be called" );
            } )
            .on( "save.ns1", function() {
                assert.ok( false, "The .on(save.ns1) handler should not be called" );
            } )
            .on( "save.ns2", function() {
                assert.ok( false, "The .on(save.ns2) handler should not be called" );
            } )
            .trigger( "save.ns1" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace,
                stopTraces = allTraces.filter( function( trace ) {
                    return trace.stoppedIn;
                } );
            ready();

            assert.equal( stopTraces.length, 1, "one stop trace has been logged" );
            assert.equal( stopTraces[ 0 ].stoppedIn, "before", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "save", "event has the correct name" );
        }, 10 );
    } );

    QUnit.module( "spread", {
        beforeEach: function setup( assert ) {
            testLogger.reset();
        }
    } );

    QUnit.test( "simple event", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();
        var destination = eventifier();

        assert.expect( 3 );

        emitter.spread( destination, "foo" );

        emitter.on( "foo", function() {
            assert.ok( true, "The emitter handler is called" );
        } );
        emitter.on( "bar", function() {
            assert.ok( true, "The emitter handler is called" );
            ready();
        } );

        destination.on( "foo", function() {
            assert.ok( true, "The event is spread" );
        } );
        destination.on( "bar", function() {
            assert.ok( false, "The event must not be fowarded" );
        } );

        emitter.trigger( "foo" );
        emitter.trigger( "bar" );
    } );

    QUnit.test( "simple event with parameters", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();
        var destination = eventifier();
        var param1 = [ "a", "b" ];
        var param2 = { "a": "b" };

        assert.expect( 6 );

        emitter.spread( destination, "foo" );

        emitter.on( "foo", function( received1, received2 ) {
            assert.ok( true, "The emitter handler is called" );
            assert.deepEqual( received1, param1 );
            assert.deepEqual( received2, param2 );
        } );

        destination.on( "foo", function( received1, received2 ) {
            assert.ok( true, "The event is spread" );
            assert.deepEqual( received1, param1 );
            assert.deepEqual( received2, param2 );
            ready();
        } );

        emitter.trigger( "foo", param1, param2 );
    } );

    QUnit.test( "multiple events with parameters", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();
        var destination = eventifier();
        var param1 = [ "a", "b" ];
        var param2 = { "a": "b" };

        assert.expect( 14 );

        emitter.spread( destination, "foo bar noz" );

        emitter.on( "foo bar", function( received1, received2 ) {
            assert.ok( true, "The emitter handler is called" );
            assert.deepEqual( received1, param1 );
            assert.deepEqual( received2, param2 );
        } );
        emitter.on( "noz", function() {
            assert.ok( true, "The emitter handler is called" );
        } );

        destination.on( "foo", function( received1, received2 ) {
            assert.ok( true, "The event is spread" );
            assert.deepEqual( received1, param1 );
            assert.deepEqual( received2, param2 );
        } );
        destination.on( "bar", function( received1, received2 ) {
            assert.ok( true, "The event is spread" );
            assert.deepEqual( received1, param1 );
            assert.deepEqual( received2, param2 );
        } );
        emitter.on( "noz", function() {
            assert.ok( true, "The event is spread" );
            ready();
        } );

        emitter.trigger( "foo bar", param1, param2 );
        emitter.trigger( "noz" );
    } );

    QUnit.test( "namespace", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();
        var destination = eventifier();

        assert.expect( 3 );

        emitter.spread( destination, "foo.bar" );

        emitter.on( "foo.noz", function() {
            assert.ok( true, "The emitter handler is called" );
        } );
        emitter.on( "foo.bar", function() {
            assert.ok( true, "The emitter handler is called" );
        } );

        destination.on( "foo.noz", function() {
            assert.ok( false, "The event should not be spread" );
        } );
        destination.on( "foo.bar", function() {
            assert.ok( true, "The event is spread" );
            ready();
        } );

        emitter.trigger( "foo.noz" );
        emitter.trigger( "foo.bar" );
    } );

    QUnit.test( "canceled", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();
        var destination = eventifier();

        assert.expect( 4 );

        emitter.spread( destination, "foo bar" );

        emitter.before( "foo", function() {
            assert.ok( true, "The emitter before phase is called" );
            return Promise.reject();
        } );
        emitter.on( "foo", function() {
            assert.ok( false, "The emitter handler should not be called" );
        } );

        destination.on( "foo", function() {
            assert.ok( false, "The event should not be spread" );
        } );

        emitter.before( "bar", function() {
            assert.ok( true, "The emitter before phase is called" );
            return Promise.resolve();
        } );
        emitter.on( "bar", function() {
            assert.ok( true, "The emitter handler is called" );
        } );

        destination.on( "bar", function() {
            assert.ok( true, "The event is spread" );
            ready();
        } );

        emitter.trigger( "foo" );
        emitter.trigger( "bar" );
    } );

    QUnit.module( "error and rejection", {
        beforeEach: function setup( assert ) {
            testLogger.reset();
        }
    } );

    QUnit.test( "cancel by rejection produces no errors", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 5 );

        emitter
            .before( "foo", function() {
                return new Promise( function( resolve, reject ) {
                    setTimeout( function() {
                        assert.ok( true, "The foo handler is called" );
                        reject();
                    }, 10 );
                } );
            } )
            .after( "foo", function() {
                assert.ok( false, "The after foo handler should not be called" );
            } )
            .trigger( "foo" );

        setTimeout( function() {
            var allTraces = testLogger.getMessages().trace;
            var allErrors = testLogger.getMessages().error;
            var stopTraces = allTraces.filter( function( trace ) {
                return trace.stoppedIn;
            } );

            assert.equal( allErrors.length, 0, "no error logged" );
            assert.equal( stopTraces.length, 1, "one stop trace has been logged" );
            assert.equal( stopTraces[ 0 ].stoppedIn, "before", "trace has been logged in the right place" );
            assert.equal( stopTraces[ 0 ].event, "foo", "event has the correct name" );

            ready();
        }, 20 );
    } );

    QUnit.test( "a runtime error rejects and log the error", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        var dummy = {};
        var runtimeError = function runtimeError() {
            return new Promise( function( resolve ) {
                var result = dummy.foo.bar();
                return resolve( result );
            } );
        };

        assert.expect( 4 );

        emitter
            .before( "foo", function() {

                return new Promise( function( resolve, reject ) {
                    setTimeout( function() {
                        assert.ok( true, "The foo handler is called" );

                        runtimeError()
                            .then( resolve )
                            .catch( reject );
                    }, 10 );
                } );
            } )
            .after( "foo", function() {
                assert.ok( false, "The after foo handler should not be called" );
            } )
            .trigger( "foo" );

        setTimeout( function() {
            var allErrors = testLogger.getMessages().error;

            assert.equal( allErrors.length, 1, "the error is logged" );
            assert.equal( typeof allErrors[ 0 ].err, "object", "the log entry contains the error" );
            assert.ok( allErrors[ 0 ].err instanceof TypeError, "the log entry contains the thrown error" );
            ready();
        }, 20 );
    } );

    QUnit.test( "a thrown error rejects and log the error", function( assert ) {
        var ready = assert.async();
        var emitter = eventifier();

        assert.expect( 4 );

        emitter
            .before( "foo", function() {

                return new Promise( function( resolve, reject ) {
                    setTimeout( function() {
                        assert.ok( true, "The foo handler is called" );

                        reject( new RangeError( "The value is not in the range" ) );
                    }, 10 );
                } );
            } )
            .after( "foo", function() {
                assert.ok( false, "The after foo handler should not be called" );
            } )
            .trigger( "foo" );

        setTimeout( function() {
            var allErrors = testLogger.getMessages().error;

            assert.equal( allErrors.length, 1, "the error is logged" );
            assert.equal( typeof allErrors[ 0 ].err, "object", "the log entry contains the error" );
            assert.ok( allErrors[ 0 ].err instanceof RangeError, "the log entry contains the thrown error" );
            ready();
        }, 20 );
    } );
} );
