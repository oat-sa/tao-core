define( [  'jquery', 'ui', 'ui/btngrouper' ], function(  $, ui, btngrouper ) {
    'use strict';

    QUnit.module( 'Button Grouper Stand Alone Test' );

    QUnit.test( 'plugin', function( assert ) {
       assert.expect( 1 );
       assert.ok( typeof $.fn.btngrouper === 'function', 'The Button Grouper plugin is registered' );
    } );

    QUnit.test( 'Initialization', function( assert ) {
        var ready = assert.async();
        assert.expect( 2 );

        var $fixture = $( '#qunit-fixture' );

        var $group = $( '[data-button-group="toggle"]', $fixture );
        assert.ok( $group.length === 1, 'The Group is available' );

        $group.on( 'create.btngrouper', function() {
            assert.ok( typeof $group.data( 'ui.btngrouper' ) === 'object', 'The element is runing the plugin' );
            ready();
        } );
        $group.btngrouper( {
            action: 'toggle'
        } );
    } );

    QUnit.test( 'Toggle', function( assert ) {
        var ready = assert.async();
        assert.expect( 6 );

        var $fixture = $( '#qunit-fixture' );

        var $group = $( '[data-button-group="toggle"]', $fixture );
        assert.ok( $group.length === 1, 'The Group is available' );

        $group.on( 'create.btngrouper', function() {
            assert.equal( $group.find( '.active' ).length, 1, 'Only one element is active' );
            assert.equal( $group.btngrouper( 'value' ), 'Y', 'The group value is Y' );

            $group.find( 'li:first' ).trigger( 'click' );
        } );
        $group.on( 'toggle.btngrouper', function() {
            assert.equal( $group.find( '.active' ).length, 1, 'Only one element is active' );
            assert.ok( $group.find( 'li:last' ).hasClass( 'active' ), 'The active element is toggled' );
            assert.equal( $group.btngrouper( 'value' ), 'N', 'The group value is N' );
            ready();
        } );
        $group.btngrouper( {
            action: 'toggle'
        } );
    } );

    QUnit.test( 'switch', function( assert ) {
        var ready = assert.async();
        assert.expect( 5 );

        var $fixture = $( '#qunit-fixture' );

        var $group = $( "[data-button-group='switch']", $fixture );
        assert.ok( $group.length === 1, "The Group is available" );
        assert.ok( $group.find( "li:first" ).hasClass( "active" ), "The first element is active" );

        $group.on( 'create.btngrouper', function() {
            assert.equal( $group.btngrouper( 'value' ), 'B', 'The group value is B' );
            $group.find( 'li:first' ).trigger( 'click' );
        } );
        $group.on( 'switch.btngrouper', function() {
            assert.equal( $group.find( '.active' ).length, 0, 'No more element are active' );
            assert.equal( $group.btngrouper( 'value' ), [], 'No values' );
            ready();
        } );
        $group.btngrouper( {
            action: 'switch'
        } );
    } );

    QUnit.module( 'Button Grouper Data Attr Test' );

     QUnit.test( 'initialization', function( assert ) {
         var ready = assert.async();
         assert.expect( 3 );

         var $fixture = $( '#qunit-fixture' );

         var $group = $( '[data-button-group="toggle"]', $fixture );
         assert.ok( $group.length === 1, 'The Group is available' );

         $group.on( 'toggle.btngrouper', function() {
             assert.equal( $group.find( '.active' ).length, 1, 'Only one element is active' );
             assert.ok( $group.find( 'li:last' ).hasClass( 'active' ), 'The active element is toggled' );
             ready();
         } );

         btngrouper( $fixture );
         $group.find( 'li:last' ).trigger( 'click' );
     } );

} );

