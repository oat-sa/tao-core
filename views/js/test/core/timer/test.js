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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define( [  "lodash", "core/timer" ], function(  _, timerFactory ) {
    "use strict";

    QUnit.module( "timer" );

    QUnit.test( "module", function( assert ) {
        assert.equal( typeof timerFactory, "function", "The timer module exposes a function" );
        assert.equal( typeof timerFactory(), "object", "The timer factory produces an object" );
        assert.notStrictEqual( timerFactory(), timerFactory(), "The timer factory provides a different object on each call" );
    } );

    var timerApi = [
        { name: "start", title: "start" },
        { name: "stop", title: "stop" },
        { name: "pause", title: "pause" },
        { name: "resume", title: "resume" },
        { name: "tick", title: "tick" },
        { name: "getDuration", title: "getDuration" },
        { name: "is", title: "is" },
        { name: "add", title: "add" },
        { name: "sub", title: "sub" }
    ];

    QUnit
        .cases.init( timerApi )
        .test( "instance API ", function( data, assert ) {
            var instance = timerFactory();
            assert.equal( typeof instance[ data.name ], "function", 'The timer instance exposes a "' + data.name + '" function' );
        } );

    var timerOptions = [
        {
            title: "config: default",
            config: undefined,
            started: true,
            running: true
        }, {
            title: "config: autoStart=true",
            config: {
                autoStart: true
            },
            started: true,
            running: true
        }, {
            title: "config: autoStart=false",
            config: {
                autoStart: false
            },
            started: false,
            running: false
        }, {
            title: "config: startDuration=100",
            config: {
                autoStart: false,
                startDuration: 100
            },
            started: false,
            running: false,
            duration: 100
        }
    ];

    QUnit
        .cases.init( timerOptions )
        .test( "timer.start ", function( data, assert ) {
            var expectedDuration = data.duration || 0;
            var timer = timerFactory( data.config );
            assert.equal( Math.floor( timer.getDuration() ), expectedDuration, "The duration must be initialized with the right value (" + expectedDuration + ")" );
            assert.equal( timer.is( "started" ), data.started, "The timer started state must be " + data.started );
            assert.equal( timer.is( "running" ), data.running, "The timer running state must be " + data.running );

            timer.start( data.duration );
            assert.equal( Math.floor( timer.getDuration() ), expectedDuration, "The duration must be initialized with the right value (" + expectedDuration + ")" );
            assert.equal( timer.is( "started" ), true, "The timer must be started" );
            assert.equal( timer.is( "running" ), true, "The timer must be running" );
        } );

    QUnit
        .cases.init( timerOptions )
        .test( "timer.stop ", function( data, assert ) {
            var timer = timerFactory( data.config );

            assert.equal( timer.is( "started" ), data.started, "The timer started state must be " + data.started );
            assert.equal( timer.is( "running" ), data.running, "The timer running state must be " + data.running );

            timer.stop();
            assert.equal( timer.is( "started" ), false, "The timer must be stopped" );
            assert.equal( timer.is( "running" ), false, "The timer must not be running" );

            timer.start();
            assert.equal( timer.is( "started" ), true, "The timer must be started" );
            assert.equal( timer.is( "running" ), true, "The timer must be running" );

            timer.stop();
            assert.equal( timer.is( "started" ), false, "The timer must be stopped" );
            assert.equal( timer.is( "running" ), false, "The timer must not be running" );
        } );

    QUnit
        .cases.init( timerOptions )
        .test( "timer.pause ", function( data, assert ) {
            var timer = timerFactory( data.config );

            assert.equal( timer.is( "started" ), data.started, "The timer started state must be " + data.started );
            assert.equal( timer.is( "running" ), data.running, "The timer running state must be " + data.running );

            timer.pause();
            assert.equal( timer.is( "started" ), data.started, "The timer started state must be " + data.started );
            assert.equal( timer.is( "running" ), false, "The timer must not be running" );
        } );

    QUnit
        .cases.init( timerOptions )
        .test( "timer.resume ",  function( data, assert ) {
            var timer = timerFactory( data.config );

            assert.equal( timer.is( "started" ), data.started, "The timer started state must be " + data.started );
            assert.equal( timer.is( "running" ), data.running, "The timer running state must be " + data.running );

            timer.pause();
            assert.equal( timer.is( "started" ), data.started, "The timer started state must be " + data.started );
            assert.equal( timer.is( "running" ), false, "The timer must not be running" );

            timer.resume();
            assert.equal( timer.is( "started" ), true, "The timer must be started" );
            assert.equal( timer.is( "running" ), true, "The timer must be running" );
        } );

    QUnit
        .cases.init( timerOptions )
        .test( "timer.tick ", function( data, assert ) {
            var ready = assert.async();
            assert.expect( 6 );

            var delay = 200;
            var timer = timerFactory( data.config );

            assert.equal( timer.is( "started" ), data.started, "The timer started state must be " + data.started );
            assert.equal( timer.is( "running" ), data.running, "The timer running state must be " + data.running );

            if ( !timer.is( "started" ) ) {
                timer.start( data.duration );
            }

            setTimeout( function() {
                assert.ok( timer.tick() >= ( delay - 1 ), "The timer must return the right tick interval (>=" + delay + ")" );

                delay = 100;
                setTimeout( function() {
                    assert.ok( timer.tick() >= ( delay - 1 ), "The timer must return the right tick interval (>=" + delay + ")" );

                    timer.pause();
                    delay = 150;
                    setTimeout( function() {
                        assert.ok( !timer.tick(), "In pause there is not tick" );

                        timer.resume();
                        delay = 200;
                        setTimeout( function() {
                            var tick = timer.tick();
                            assert.ok( tick >= ( delay - 1 ), "The timer must return the right tick interval ( " + tick + ">=" + delay + ") once resumed" );
                            ready();

                        }, delay );
                    }, delay );
                }, delay );
            }, delay );
        } );

    QUnit
        .cases.init( timerOptions )
        .test( "timer.getDuration ",  function( data, assert ) {
            var ready = assert.async();
            var delay = 200;
            var expectedDuration = data.duration || 0;
            var timer = timerFactory( data.config );

            assert.equal( Math.floor( timer.getDuration() ), expectedDuration, "The duration must be initialized with the right value (" + expectedDuration + ")" );
            assert.equal( timer.is( "started" ), data.started, "The timer started state must be " + data.started );
            assert.equal( timer.is( "running" ), data.running, "The timer running state must be " + data.running );

            if ( !timer.is( "started" ) ) {
                timer.start( data.duration );
            }

            setTimeout( function() {
                expectedDuration += delay;
                assert.ok( timer.getDuration() >= ( expectedDuration - 1 ), "The timer must return the right duration (>=" + expectedDuration + ")" );

                timer.pause();
                expectedDuration = timer.getDuration();

                setTimeout( function() {
                    assert.equal( timer.getDuration(), expectedDuration, "The timer must return the right duration (=" + expectedDuration + ")" );

                    timer.resume();

                    setTimeout( function() {
                        expectedDuration += delay;
                        assert.ok( timer.getDuration() >= ( expectedDuration - 1 ), "The timer must return the right duration (>=" + expectedDuration + ")" );

                        setTimeout( function() {
                            expectedDuration += delay;
                            assert.ok( timer.getDuration() >= ( expectedDuration - 1 ), "The timer must return the right duration (>=" + expectedDuration + ")" );

                            timer.stop();
                            expectedDuration = timer.getDuration();

                            setTimeout( function() {
                                assert.equal( timer.getDuration(), expectedDuration, "The timer must return the right duration (=" + expectedDuration + ")" );
                                ready();
                            }, delay );
                        }, delay );
                    }, delay );
                }, delay );
            }, delay );
        } );

    QUnit
        .cases.init( timerOptions )
        .test( "timer.add ", function( data, assert ) {
            var ready = assert.async();
            var delay = 200;
            var expectedDuration = data.duration || 0;
            var timer = timerFactory( data.config );
            var extra = 300;

            assert.equal( Math.floor( timer.getDuration() ), expectedDuration, "The duration must be initialized with the right value (" + expectedDuration + ")" );
            assert.equal( timer.is( "started" ), data.started, "The timer started state must be " + data.started );
            assert.equal( timer.is( "running" ), data.running, "The timer running state must be " + data.running );

            if ( !timer.is( "started" ) ) {
                timer.start( data.duration );
            }

            setTimeout( function() {
                expectedDuration += delay + extra;
                timer.add( extra );
                assert.ok( timer.getDuration() >= ( expectedDuration - 1 ), "The timer must return the right duration (>=" + expectedDuration + ")" );

                timer.pause();
                expectedDuration = timer.getDuration();

                setTimeout( function() {
                    assert.equal( timer.getDuration(), expectedDuration, "The timer must return the right duration (=" + expectedDuration + ")" );

                    timer.resume();

                    setTimeout( function() {
                        expectedDuration += delay + extra;
                        timer.add( extra );
                        assert.ok( timer.getDuration() >= ( expectedDuration - 1 ), "The timer must return the right duration (>=" + expectedDuration + ")" );

                        setTimeout( function() {
                            expectedDuration += delay + extra;
                            timer.add( extra );
                            assert.ok( timer.getDuration() >= ( expectedDuration - 1 ), "The timer must return the right duration (>=" + expectedDuration + ")" );

                            timer.stop();
                            expectedDuration = timer.getDuration();

                            setTimeout( function() {
                                assert.equal( timer.getDuration(), expectedDuration, "The timer must return the right duration (=" + expectedDuration + ")" );
                                ready();
                            }, delay );
                        }, delay );
                    }, delay );
                }, delay );
            }, delay );
        } );

    QUnit
        .cases.init( timerOptions )
        .test( "timer.sub ",  function( data, assert ) {
            var ready = assert.async();
            var delay = 200;
            var expectedDuration = data.duration || 0;
            var timer = timerFactory( data.config );
            var extra = 50;

            assert.equal( Math.floor( timer.getDuration() ), expectedDuration, "The duration must be initialized with the right value (" + expectedDuration + ")" );
            assert.equal( timer.is( "started" ), data.started, "The timer started state must be " + data.started );
            assert.equal( timer.is( "running" ), data.running, "The timer running state must be " + data.running );

            if ( !timer.is( "started" ) ) {
                timer.start( data.duration );
            }

            setTimeout( function() {
                expectedDuration += delay - extra;
                timer.sub( extra );
                assert.ok( timer.getDuration() >= ( expectedDuration - 1 ), "The timer must return the right duration (>=" + expectedDuration + ")" );

                timer.pause();
                expectedDuration = timer.getDuration();

                setTimeout( function() {
                    assert.equal( timer.getDuration(), expectedDuration, "The timer must return the right duration (=" + expectedDuration + ")" );

                    timer.resume();

                    setTimeout( function() {
                        expectedDuration += delay - extra;
                        timer.sub( extra );
                        assert.ok( timer.getDuration() >= ( expectedDuration - 1 ), "The timer must return the right duration (>=" + expectedDuration + ")" );

                        setTimeout( function() {
                            expectedDuration += delay - extra;
                            timer.sub( extra );
                            assert.ok( timer.getDuration() >= ( expectedDuration - 1 ), "The timer must return the right duration (>=" + expectedDuration + ")" );

                            timer.stop();
                            expectedDuration = timer.getDuration();

                            setTimeout( function() {
                                assert.equal( timer.getDuration(), expectedDuration, "The timer must return the right duration (=" + expectedDuration + ")" );
                                ready();
                            }, delay );
                        }, delay );
                    }, delay );
                }, delay );
            }, delay );
        } );

} );
