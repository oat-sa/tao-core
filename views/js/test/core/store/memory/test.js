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
 * Test the memory store backend
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define( [  "core/store/memory", "core/promise" ], function(  memoryStorageBackend, Promise ) {
    "use strict";

    QUnit.module( "API" );

    QUnit.test( "module", function( assert ) {
        assert.expect( 2 );

        assert.ok( typeof memoryStorageBackend !== "undefined", "The module exports something" );
        assert.ok( typeof memoryStorageBackend === "function", "The module exposes a function" );
    } );

    QUnit.test( "factory", function( assert ) {
        var store;
        assert.expect( 4 );

        assert.throws( function() {
            memoryStorageBackend();
        }, TypeError, "The backend should be created with a store id" );

        assert.throws( function() {
            memoryStorageBackend( false );
        }, TypeError, "The backend should be created with a valid store id" );

        store = memoryStorageBackend( "foo" );

        assert.equal( typeof store, "object", "The factory return an object" );
        assert.notDeepEqual( memoryStorageBackend( "foo" ), store, "The factory creates a new object" );
    } );

    QUnit.test( "storage backend", function( assert ) {
        assert.expect( 3 );

        assert.equal( typeof memoryStorageBackend.removeAll, "function", "The backend exposes the removeAll method" );
        assert.equal( typeof memoryStorageBackend.getAll, "function", "The backend exposes the getAll method" );
        assert.equal( typeof memoryStorageBackend.getStoreIdentifier, "function", "The backend exposes the getStoreIdentifier method" );
    } );

    QUnit.test( "store", function( assert ) {
        var store;
        assert.expect( 7 );

        store = memoryStorageBackend( "foo" );

        assert.equal( typeof store, "object", "The store is an object" );
        assert.equal( typeof store.getItem, "function", "The store exposes the getItem method" );
        assert.equal( typeof store.setItem, "function", "The store exposes the setItem method" );
        assert.equal( typeof store.removeItem, "function", "The store exposes the removetItem method" );
        assert.equal( typeof store.getItems, "function", "The store exposes the getItems method" );
        assert.equal( typeof store.clear, "function", "The store exposes the clear method" );
        assert.equal( typeof store.removeStore, "function", "The store exposes the removeStore method" );
    } );

    QUnit.module( "CRUD" );

    QUnit.test( "setItem", function( assert ) {
        var ready = assert.async();
        var store;
        var p;
        assert.expect( 4 );

        store = memoryStorageBackend( "foo" );
        assert.equal( typeof store, "object", "The store is an object" );

        p = store.setItem( "bar", "value" );
        assert.ok( p instanceof Promise, "setItem returns a Promise" );

        p.then( function( result ) {

            assert.equal( typeof result, "boolean", "The result is a boolean" );
            assert.ok( result, "The item is added" );

            ready();
        } ).catch( function( err ) {
            assert.ok( false, err );
            ready();
        } );
    } );

    QUnit.test( "getItem", function( assert ) {
        var ready = assert.async();
        var store;
        var p;
        assert.expect( 5 );

        store = memoryStorageBackend( "foo" );
        assert.equal( typeof store, "object", "The store is an object" );

        p = store.setItem( "bar", "noz" );
        assert.ok( p instanceof Promise, "setItem returns a Promise" );

        p.then( function( result ) {
            assert.ok( result, "The item is added" );

            store.getItem( "bar" ).then( function( value ) {

                assert.equal( typeof value, "string", "The result is a string" );
                assert.equal( value, "noz", "The retrieved value is correct" );

                ready();
            } );
        } ).catch( function( err ) {
            assert.ok( false, err );
            ready();
        } );
    } );

    QUnit.test( "get/set objects", function( assert ) {
        var ready = assert.async();
        var store;
        var sample = {
            collection: [ {
                item1: true,
                item2: "false",
                item3: 12
            }, {
                item4: { value: null }
            } ]
        };
        assert.expect( 3 );

        store = memoryStorageBackend( "foo" );
        assert.equal( typeof store, "object", "The store is an object" );

        store.setItem( "sample", sample ).then( function( added ) {
            assert.ok( added, "The item is added" );
            store.getItem( "sample" ).then( function( result ) {
                assert.deepEqual( result, sample, "Retrieving the sample" );
                ready();
            } );
        } ).catch( function( err ) {
            assert.ok( false, err );
            ready();
        } );
    } );

    QUnit.test( "removeItem", function( assert ) {
        var ready = assert.async();
        var store;
        assert.expect( 5 );

        store = memoryStorageBackend( "foo" );
        assert.equal( typeof store, "object", "The store is an object" );

        store.setItem( "moo", "noob" )
        .then( function( result ) {
            assert.ok( result, "The item is added" );

            return store.getItem( "moo" ).then( function( value ) {
                assert.equal( value, "noob", "The retrieved value is correct" );
            } );
        } ).then( function() {
            return store.removeItem( "moo" ).then( function( rmResult ) {
                assert.ok( rmResult, "The item is removed" );
            } );
        } ).then( function() {
            return store.getItem( "moo" ).then( function( value ) {
                assert.equal( typeof value, "undefined", "The value does not exists anymore" );
                ready();
            } );
        } ).catch( function( err ) {
            assert.ok( false, err );
            ready();
        } );
    } );

    QUnit.test( "clear", function( assert ) {
        var ready = assert.async();
        var store;

        assert.expect( 5 );

        store = memoryStorageBackend( "foo" );
        assert.equal( typeof store, "object", "The store is an object" );

        Promise.all( [
            store.setItem( "zoo", "zoob" ),
            store.setItem( "too", "toob" )
        ] )
        .then( function() {
            return store.getItem( "too" ).then( function( value ) {
                assert.equal( value, "toob", "The retrieved value is correct" );
            } );
        } ).then( function() {
            return store.clear().then( function( rmResult ) {
                assert.ok( rmResult, "The item is removed" );
            } );
        } ).then( function() {
            return store.getItem( "too" ).then( function( value ) {
                assert.equal( typeof value, "undefined", "The value does not exists anymore" );
                return store.getItem( "zoo" ).then( function( newValue ) {
                    assert.equal( typeof newValue, "undefined", "The value does not exists anymore" );
                    ready();
                } );
            } );
        } ).catch( function( err ) {
            assert.ok( false, err );
            ready();
        } );
    } );

    QUnit.test( "getItems", function( assert ) {
        var ready = assert.async();
        var store;

        assert.expect( 5 );

        store = memoryStorageBackend( "bar" );
        assert.equal( typeof store, "object", "The store is an object" );

        Promise.all( [
            store.setItem( "zoo", "zoob" ),
            store.setItem( "too", "toob" ),
            store.setItem( "moo", "moob" ),
            store.setItem( "joo", "joob" )
        ] )
        .then( function() {
            return store.getItem( "joo" ).then( function( value ) {
                assert.equal( value, "joob", "The retrieved value is correct" );
            } );
        } ).then( function() {
            return store.getItems().then( function( entries ) {
                assert.equal( typeof entries, "object", "The entries is an object" );
                assert.deepEqual( entries, {
                    zoo: "zoob",
                    too: "toob",
                    moo: "moob",
                    joo: "joob"
                }, "The entries contains the store values" );
            } );
        } )
        .then( function() {
            return store.setItem( "yoo", "yoob" );
        } )
        .then( function() {
            return store.removeItem( "moo" );
        } )
        .then( function() {
            return store.getItems().then( function( entries ) {
                assert.deepEqual( entries, {
                    zoo: "zoob",
                    too: "toob",
                    yoo: "yoob",
                    joo: "joob"
                }, "The entries contains the updated values" );
            } );
        } ).then( function() {
            ready();
        } ).catch( function( err ) {
            assert.ok( false, err );
            ready();
        } );
    } );

    QUnit.module( "Erase" );

    QUnit.test( "removeStore", function( assert ) {
        var ready = assert.async();
        var store;
        assert.expect( 5 );

        store = memoryStorageBackend( "foo" );
        assert.equal( typeof store, "object", "The store is an object" );

        Promise.all( [
            store.setItem( "zoo", "zoob" ),
            store.setItem( "too", "toob" )
        ] )
        .then( function() {
            return store.getItem( "too" ).then( function( value ) {
                assert.equal( value, "toob", "The retrieved value is correct" );
            } );
        } )
        .then( function() {
            return store.removeStore().then( function( rmResult ) {
                assert.ok( rmResult, "The store is removed" );
            } );
        } )
        .then( function() {
            return store.getItem( "too" ).then( function( value ) {
                assert.equal( typeof value, "undefined", "The value does not exists anymore" );
                return store.getItem( "zoo" ).then( function( newValue ) {
                    assert.equal( typeof newValue, "undefined", "The value does not exists anymore" );
                    ready();
                } );
            } );
        } )
        .catch( function( err ) {
            assert.ok( false, err );
            ready();
        } );
    } );

    QUnit.test( "removeAll", function( assert ) {
        var ready = assert.async();
        var store1;
        var store2;

        assert.expect( 7 );

        store1 = memoryStorageBackend( "foo1" );
        store2 = memoryStorageBackend( "foo2" );

        assert.equal( typeof store1, "object", "The store1 is an object" );
        assert.equal( typeof store2, "object", "The store2 is an object" );

        Promise.all( [
            store1.setItem( "zoo", "zooa" ),
            store2.setItem( "too", "toob" )
        ] )
        .then( function() {
            return store1.getItem( "zoo" ).then( function( value ) {
                assert.equal( value, "zooa", "The value zoo exists in store1" );
            } );
        } )
        .then( function() {
            return store2.getItem( "too" ).then( function( value ) {
                assert.equal( value, "toob", "The value too exists in store2" );
            } );
        } )
        .then( function() {
            return memoryStorageBackend.removeAll();
        } )
        .then( function( rmResult ) {
            assert.ok( rmResult, "The stores are removed" );
        } )
        .then( function() {
            return store1.getItem( "zoo" ).then( function( value ) {
                assert.equal( typeof value, "undefined", "The value zoo does not exist anymore in store1" );
            } );
        } )
        .then( function() {
            return store2.getItem( "too" ).then( function( value ) {
                assert.equal( typeof value, "undefined", "The value too does not exist anymore in store2" );
            } );
        } )
        .then( function() {
            ready();
        } )
        .catch( function( err ) {
            assert.ok( false, err );
            ready();
        } );
    } );

    QUnit.module( "get stores" );

    QUnit.test( "get all stores", function( assert ) {
        var ready = assert.async();
        var store1;
        var store2;
        var store3;

        assert.expect( 8 );

        assert.equal( typeof memoryStorageBackend.getAll, "function", "memorystorage backend exposes the getAll method" );

        store1 = memoryStorageBackend( "test-store-1" );
        store2 = memoryStorageBackend( "test-store-2" );
        store3 = memoryStorageBackend( "bar3" );

        assert.equal( typeof store1, "object", "The store1 is an object" );
        assert.equal( typeof store2, "object", "The store2 is an object" );
        assert.equal( typeof store3, "object", "The store2 is an object" );

        Promise.all( [
            store1.setItem( "test", true ),
            store2.setItem( "test", true ),
            store3.setItem( "test", true )
        ] ).then( function() {

            var validate = function( name ) {
                return name === "test-store-1" || name === "test-store-2";
            };

            return memoryStorageBackend.getAll( validate ).then( function( storeNames ) {
                assert.equal( storeNames.length, 2, "Two store names have been found" );
                assert.ok( storeNames.indexOf( "test-store-1" ) > -1, "The 1st store is selected" );
                assert.ok( storeNames.indexOf( "test-store-2" ) > -1, "The 2nd store is selected" );
                assert.ok( storeNames.indexOf( "bar3" ) === -1, "The 3rd store is filtered" );
            } );
        } )
        .then( function() {
            return memoryStorageBackend.removeAll();
        } )
        .then( function() {
            ready();
        } )
        .catch( function( err ) {
            assert.ok( false, err );
            ready();
        } );
    } );

    QUnit.module( "store id" );

    QUnit.test( "get store identifier", function( assert ) {
        var ready = assert.async();
        assert.expect( 4 );

        assert.equal( typeof memoryStorageBackend.getStoreIdentifier, "function", "IndexedDB backend has the getStoreIdentifier method" );

        memoryStorageBackend.getStoreIdentifier().then( function( id ) {

            assert.equal( typeof id, "string", "we have a store identifier" );
            assert.ok( id.length > 0, "the identifier is not empty" );

            return memoryStorageBackend.getStoreIdentifier().then( function( idNextCall ) {

                assert.equal( id, idNextCall, "The identifier should remain the same accross the store" );
                ready();
            } );

        } ).catch( function( err ) {
            assert.ok( false, err );
            ready();
        } );
    } );

} );
