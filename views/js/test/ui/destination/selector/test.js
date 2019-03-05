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
define( [
    
    'jquery',
    'ui/destination/selector',
    'json!test/ui/destination/selector/classes.json'
], function(  $, destinationSelectorFactory, classes ) {
    'use strict';

    QUnit.module( 'API' );

    QUnit.test( 'module', function( assert ) {
        assert.expect( 3 );

        assert.equal( typeof destinationSelectorFactory, 'function', 'The module exposes a function' );
        assert.equal( typeof destinationSelectorFactory(), 'object', 'The factory produces an object' );
        assert.notStrictEqual( destinationSelectorFactory(), destinationSelectorFactory(), 'The factory provides a different object on each call' );
    } );

    QUnit.cases.init( [
        { title: 'init' },
        { title: 'destroy' },
        { title: 'render' },
        { title: 'show' },
        { title: 'hide' },
        { title: 'enable' },
        { title: 'disable' },
        { title: 'is' },
        { title: 'setState' },
        { title: 'getContainer' },
        { title: 'getElement' },
        { title: 'getTemplate' },
        { title: 'setTemplate' }
    ] ).test( 'Component API ', function( data, assert ) {
        var instance = destinationSelectorFactory();
        assert.equal( typeof instance[ data.title ], 'function', 'The destinationSelector exposes the component method "' + data.title );
    } );

    QUnit.cases.init( [
        { title: 'on' },
        { title: 'off' },
        { title: 'trigger' },
        { title: 'before' },
        { title: 'after' }
    ] ).test( 'Eventifier API ', function( data, assert ) {
        var instance = destinationSelectorFactory();
        assert.equal( typeof instance[ data.title ], 'function', 'The destinationSelector exposes the eventifier method "' + data.title );
    } );

    QUnit.cases.init( [
        { title: 'update' }
    ] ).test( 'Instance API ', function( data, assert ) {
        var instance = destinationSelectorFactory();
        assert.equal( typeof instance[ data.title ], 'function', 'The destinationSelector exposes the method "' + data.title );
    } );

    QUnit.module( 'Behavior' );

    QUnit.test( 'Lifecycle', function( assert ) {
        var ready = assert.async();
        var $container = $( '#qunit-fixture' );

        assert.expect( 2 );

        destinationSelectorFactory( $container, {
            classUri: 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject'
        } )
        .on( 'init', function() {
            assert.ok( !this.is( 'rendered' ), 'The component is not yet rendered' );
        } )
        .on( 'render', function() {
            assert.ok( this.is( 'rendered' ), 'The component is now rendered' );

            this.destroy();
        } )
        .on( 'destroy', function() {

            ready();
        } );
    } );

    QUnit.test( 'Rendering', function( assert ) {
        var ready = assert.async();
        var $container = $( '#qunit-fixture' );

        assert.expect( 8 );

        assert.equal( $( '.class-selector', $container ).length, 0, 'No class selector in the container' );

        destinationSelectorFactory( $container, {
            classUri: 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            title: 'Select a foo class',
            description: 'in oder to move a bar in a foo class',
            actionName: 'foo it'
        } )
        .on( 'query', function( params ) {
            this.update( classes, params );
        } )
        .on( 'render', function() {

            var $element = this.getElement();

            assert.equal( $( '.destination-selector', $container ).length, 1, 'The class selector has been inserted' );
            assert.equal( $( '.destination-selector', $container )[ 0 ], $element[ 0 ], 'The component element is correct' );

            assert.equal( $element.children( 'h2' ).text().trim(), 'Select a foo class' );
            assert.equal( $element.children( 'div' ).children( 'p' ).text().trim(), 'in oder to move a bar in a foo class' );
            assert.equal( $( '.actions button', $element ).length, 1, 'The action button is rendered' );
            assert.equal( $( '.actions button .action-label', $element ).text().trim(), 'foo it' );

            this.on( 'update', function() {

                assert.equal( $( '.resource-selector', $element ).length, 1, 'The resource selector has been added' );

                ready();
            } );
        } );
    } );

    QUnit.test( 'selection', function( assert ) {
        var ready = assert.async();
        var $container = $( '#qunit-fixture' );

        assert.expect( 7 );

        assert.equal( $( '.class-selector', $container ).length, 0, 'No class selector in the container' );

        destinationSelectorFactory( $container, {
            classUri: 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item'
        } )
        .on( 'query', function( params ) {
            this.update( classes, params );
        } )
        .on( 'select', function( uri ) {

            assert.equal( uri, 'http://bertaodev/tao.rdf#i1516810104355796', 'The selected URI is correct' );
            ready();
        } )
        .on( 'render', function() {

            var $element = this.getElement();
            var $action  = $( '.actions button', $element );
            assert.equal( $action.prop( 'disabled' ), true, 'The action starts disabled' );

            this.on( 'update', function() {

                var $classNode = $( '[data-uri="http://bertaodev/tao.rdf#i1516810104355796"]', $element );
                assert.equal( $classNode.length, 1, 'The target node exists' );
                assert.ok( !$classNode.hasClass( 'selected' ), 'The target node is not yet selected' );

                $classNode.trigger( 'click' );

                setTimeout( function() {

                    assert.ok( $classNode.hasClass( 'selected' ), 'The target node is now selected' );
                    assert.equal( $action.prop( 'disabled' ), false, 'The action is now enabled' );

                    $action.trigger( 'click' );
                }, 100 );
            } );
        } )
        .on( 'error', function( err ) {
            assert.ok( false, err.message );
            ready();
        } );
    } );

    QUnit.test( 'unselect', function( assert ) {
        var ready = assert.async();
        var $container = $( '#qunit-fixture' );

        assert.expect( 9 );

        assert.equal( $( '.class-selector', $container ).length, 0, 'No class selector in the container' );

        destinationSelectorFactory( $container, {
            classUri: 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item'
        } )
        .on( 'query', function( params ) {
            this.update( classes, params );
        } )
        .on( 'render', function() {
            var self = this;
            var $element = this.getElement();
            var $action  = $( '.actions button', $element );
            assert.equal( $action.prop( 'disabled' ), true, 'The action starts disabled' );

            this.on( 'update', function() {

                var $classNode = $( '[data-uri="http://bertaodev/tao.rdf#i1516810104355796"]', $element );
                assert.equal( $classNode.length, 1, 'The target node exists' );
                assert.ok( !$classNode.hasClass( 'selected' ), 'The target node is not yet selected' );

                $classNode.trigger( 'click' );

                setTimeout( function() {

                    assert.ok( $classNode.hasClass( 'selected' ), 'The target node is now selected' );
                    assert.equal( $action.prop( 'disabled' ), false, 'The action is now enabled' );

                    self.on( 'select', function( uri ) {
                        assert.equal( uri, 'http://bertaodev/tao.rdf#i1516810104355796', 'The selected URI is correct' );

                        $classNode.trigger( 'click' );

                        setTimeout( function() {

                            assert.ok( !$classNode.hasClass( 'selected' ), 'The target node is not selected' );
                            assert.equal( $action.prop( 'disabled' ), true, 'The action is now disabled' );

                            //Disallowed
                            $action.trigger( 'click' );

                            ready();
                        }, 10 );

                    } );
                    $action.trigger( 'click' );
                }, 10 );
            } );
        } )
        .on( 'error', function( err ) {
            assert.ok( false, err.message );
            ready();
        } );
    } );

    QUnit.module( 'Visual' );

    QUnit.test( 'playground', function( assert ) {
        var ready = assert.async();
        var container = document.getElementById( 'visual' );

        assert.expect( 1 );

        destinationSelectorFactory( container, {
            classUri: 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item'
        } )
        .on( 'query', function( params ) {
            this.update( classes, params );
        } )
        .on( 'render', function() {
            assert.ok( true );
            ready();
        } );
    } );
} );
