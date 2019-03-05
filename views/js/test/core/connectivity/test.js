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
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define( [  'core/connectivity' ], function(  connectivity ) {
    'use strict';

    QUnit.module( 'API' );

    QUnit.test( 'module', function( assert ) {
        assert.expect( 1 );

        assert.equal( typeof connectivity, 'object', 'The module exposes an object' );
    } );

    QUnit.test( 'eventifier', function( assert ) {
        assert.expect( 3 );

        assert.equal( typeof connectivity.on, 'function', 'The module has the eventifier\'s on method' );
        assert.equal( typeof connectivity.off, 'function', 'The module has the eventifier\'s off method' );
        assert.equal( typeof connectivity.trigger, 'function', 'The module has the eventifier\'s trigger method' );
    } );

    QUnit.cases.init( [
        { title: 'isOnline' },
        { title: 'isOffline' },
        { title: 'setOnline' },
        { title: 'setOffline' }
    ] )
    .test( ' method ', function( data, assert ) {
        assert.expect( 1 );

        assert.equal( typeof connectivity[ data.title ], 'function', 'The tokenHandler instanceexposes a "' + data.name + '" function' );
    } );

    QUnit.module( 'Behavior', {
        beforeEach: function setup( assert ) {
            connectivity.setOnline();
        },
        afterEach: function teardown( assert ) {
            connectivity.off( 'online offline change' );
        }
    } );

    QUnit.test( 'set / get status', function( assert ) {
        assert.expect( 6 );

        assert.ok( connectivity.isOnline(), 'We start online' );
        assert.ok( !connectivity.isOffline(), 'If we are online, we are not offline' );

        connectivity.setOffline();

        assert.ok( connectivity.isOffline(), 'We are offline' );
        assert.ok( !connectivity.isOnline(), 'If we are offline, we are not online' );

        connectivity.setOnline();

        assert.ok( connectivity.isOnline(), 'We are online' );
        assert.ok( !connectivity.isOffline(), 'If we are online, we are not offline' );
    } );

    QUnit.test( 'offline event', function( assert ) {
        var ready = assert.async();
        assert.expect( 4 );

        assert.ok( connectivity.isOnline(), 'We start online' );
        assert.ok( !connectivity.isOffline(), 'If we are online, we are not offline' );

        connectivity.on( 'offline', function() {
            assert.ok( connectivity.isOffline(), 'We are offline' );
            assert.ok( !connectivity.isOnline(), 'If we are offline, we are not online' );
            ready();
        } );

        connectivity.setOffline();
    } );

    QUnit.test( 'online event', function( assert ) {
        var ready = assert.async();
        assert.expect( 6 );

        assert.ok( connectivity.isOnline(), 'We start online' );
        assert.ok( !connectivity.isOffline(), 'If we are online, we are not offline' );

        connectivity.setOffline();

        assert.ok( connectivity.isOffline(), 'We are offline' );
        assert.ok( !connectivity.isOnline(), 'If we are offline, we are not online' );

        connectivity.on( 'online', function() {
            assert.ok( connectivity.isOnline(), 'We are online' );
            assert.ok( !connectivity.isOffline(), 'If we are online, we are not offline' );
            ready();
        } );

        connectivity.setOnline();
    } );

    QUnit.test( 'change event', function( assert ) {
        var ready = assert.async();
        assert.expect( 5 );

        assert.ok( connectivity.isOnline(), 'We start online' );
        assert.ok( !connectivity.isOffline(), 'If we are online, we are not offline' );

        connectivity.on( 'change', function( online ) {
            assert.equal( online, false,  'We are offline' );
            assert.ok( connectivity.isOffline(), 'We are offline' );
            assert.ok( !connectivity.isOnline(), 'If we are offline, we are not online' );
            ready();
        } );

        connectivity.setOffline();
    } );

    QUnit.test( 'native offline event', function( assert ) {
        var ready = assert.async();
        assert.expect( 4 );

        assert.ok( connectivity.isOnline(), 'We start online' );
        assert.ok( !connectivity.isOffline(), 'If we are online, we are not offline' );

        connectivity.on( 'offline', function() {
            assert.ok( connectivity.isOffline(), 'We are offline' );
            assert.ok( !connectivity.isOnline(), 'If we are offline, we are not online' );
            ready();
        } );

        window.dispatchEvent( new Event( 'offline' ) );
    } );

    QUnit.test( 'native online event', function( assert ) {
        var ready = assert.async();
        assert.expect( 6 );

        assert.ok( connectivity.isOnline(), 'We start online' );
        assert.ok( !connectivity.isOffline(), 'If we are online, we are not offline' );

        window.dispatchEvent( new Event( 'offline' ) );

        assert.ok( connectivity.isOffline(), 'We are offline' );
        assert.ok( !connectivity.isOnline(), 'If we are offline, we are not online' );

        connectivity.on( 'online', function() {
            assert.ok( connectivity.isOnline(), 'We are online' );
            assert.ok( !connectivity.isOffline(), 'If we are online, we are not offline' );
            ready();
        } );

        window.dispatchEvent( new Event( 'online' ) );
    } );

    QUnit.module( 'Manual' );

    QUnit.test( 'manual test', function( assert ) {
        var container = document.querySelector( '.visual' );
        var update = function( online ) {
            if ( online ) {
                container.classList.remove( 'offline' );
                container.classList.add( 'online' );
            } else {
                container.classList.remove( 'online' );
                container.classList.add( 'offline' );
            }
        };
        assert.expect( 1 );

        assert.ok( container instanceof HTMLElement );

        update( connectivity.isOnline );
        connectivity.on( 'change', update );
    } );
} );
