define( [  'jquery', 'ui/mediasizer' ], function(  $ ) {

    var mode = 'nat'; // Nat (=natural size) | px (=pixels) | pc (=%)

    $( '#_' + mode ).show();

    QUnit.module( 'MediaSizer Stand Alone Test' );

    QUnit.test( 'plugin', function( assert ) {
        assert.expect( 1 );
        assert.ok( typeof $.fn.mediasizer === 'function', 'The MediaSizer plugin is registered' );
    } );

    QUnit.test( 'Initialization', function( assert ) {
        var ready = assert.async();
        assert.expect( 4 );

        var $container = $( '#qunit-fixture-tmp' );
        assert.ok( $container.length === 1, 'Fixture is available' );

        var $elt = $( '#media-sizer-container', $container );
        assert.ok( $elt.length === 1, 'MediaSizer link is available' );

        var $target = $( '#_' + mode + ' img', $container );
        assert.ok( $target.length === 1, 'Target is available' );

        $elt.on( 'create.mediasizer', function() {
            var data = $elt.data( 'ui.mediasizer' );
            assert.ok( typeof data === 'object', 'The element is running the plugin' );

            ready();
        } );
        $elt.mediasizer( {
            target: $target
        } );

        $elt.on( 'sizechange.mediasizer', function( e, paras ) {
            console.log( paras );

        } );
    } );

} );
