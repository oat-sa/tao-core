define( [  "jquery", "ui", "ui/inplacer" ], function(  $, ui ) {
    "use strict";

    QUnit.module( "Inplacer Stand Alone Test" );

    QUnit.test( "plugin", function( assert ) {
        assert.expect( 1 );
        assert.ok( typeof $.fn.inplacer === "function", "The Inplacer plugin is registered" );
    } );

    QUnit.test( "initialization", function( assert ) {
        var ready = assert.async();
        assert.expect( 5 );

        var $container = $( "#div-0" );
        assert.ok( $container.length === 1, "Test the fixture is available" );

        $container.on( "create.inplacer", function() {
            assert.ok( typeof $container.data( "ui.inplacer" ) === "object", "config object stored in data" );
            assert.ok( $container.hasClass( "inplace" ), "has inplace class" );
            assert.equal( $container.siblings( "#edit-me" ).length, 1, "target created" );
            ready();
        } );

        assert.equal( $container.siblings( "#edit-me" ).length, 0, "target not created yet" );
        $container.inplacer( {
            target: $( "#edit-me" )
        } );
    } );

    QUnit.test( "edit and leave a <div>", function( assert ) {
        var ready = assert.async();
        assert.expect( 5 );

        var $container = $( "#div-0" );
        assert.ok( $container.length === 1, "Test the fixture is available" );

        $container.on( "create.inplacer", function() {

            //Click the editable to start editing mode
            $container.click();
        } ).on( "edit.inplacer", function() {
            assert.equal( $container.find( "textarea" ).length, 1, "input in focus" );

            //Make some text change
            $container.find( "textarea" ).val( "AAA" );

            //Leave
            $container.find( "textarea" ).blur();
        } ).on( "leave.inplacer", function( e, val ) {

            //Check that the the container has been correctly updated
            assert.equal( $container.find( "textarea" ).length, 0, "input is blurred" );
            assert.equal( val, "AAA", "returned value is correct" );
            assert.equal( $container.html(), "AAA", "editable container has been updater" );

            ready();
        } ).inplacer( {
            target: $( "#edit-me" )
        } );
    } );

    QUnit.test( "edit and leave a <span>", function( assert ) {
        var ready = assert.async();
        assert.expect( 5 );

        var $container = $( "#span-0" );
        assert.ok( $container.length === 1, "Test the fixture is available" );

        $container.on( "create.inplacer", function() {

            //Click the editable to start editing mode
            $container.click();
        } ).on( "edit.inplacer", function() {
            assert.equal( $container.find( ":text" ).length, 1, "input in focus" );

            //Make some text change
            $container.find( ":text" ).val( "AAA" );

            //Leave
            $container.find( ":text" ).blur();
        } ).on( "leave.inplacer", function( e, val ) {

            //Check that the the container has been correctly updated
            assert.equal( $container.find( ":text" ).length, 0, "input is blurred" );
            assert.equal( val, "AAA", "returned value is correct" );
            assert.equal( $container.html(), "AAA", "editable container has been updated" );

            ready();
        } ).inplacer( {
            target: $( "#edit-me" )
        } );
    } );

    QUnit.test( "destroy", function( assert ) {
        var ready = assert.async();
        assert.expect( 6 );

        var $container = $( "#span-0" );
        assert.ok( $container.length === 1, "Test the fixture is available" );

        $container.on( "create.inplacer", function() {
            assert.ok( typeof $container.data( "ui.inplacer" ) === "object", "config object stored in data" );
            assert.ok( $container.hasClass( "inplace" ), "has inplace class" );
            assert.equal( $container.siblings( "#edit-me" ).length, 1, "target created" );

            //Test destroy method
            $container.inplacer( "destroy" );
        } ).on( "destroy.inplacer", function() {

            //Check clean up
            assert.equal( $container.data( "ui.inplacer" ), undefined, "data object removed" );
            assert.ok( !$container.hasClass( "inplace" ), "inplace class removed" );

            ready();
        } ).inplacer( {
            target: $( "#edit-me" )
        } );
    } );
} );

