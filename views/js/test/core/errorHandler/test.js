define( [  'core/errorHandler' ], function(  errorHandler ) {

    QUnit.test( 'module API', function( assert ) {
        assert.ok( typeof errorHandler === 'object', 'The errorHandler module exposes an object' );
        assert.ok( typeof errorHandler.getContext === 'function', 'The errorHandler has a method getContext' );
        assert.ok( typeof errorHandler.listen === 'function', 'The errorHandler has a method listen' );
        assert.ok( typeof errorHandler.throw === 'function', 'The errorHandler has a method throw' );
        assert.ok( typeof errorHandler.reset === 'function', 'The errorHandler has a method reset' );
    } );

    QUnit.module( 'Error', {
        afterEach: function( assert ) {
            errorHandler._contexts = {};
        }
    } );

    QUnit.test( 'create a context', function( assert ) {

        assert.ok( typeof errorHandler._contexts.context1 === 'undefined', 'The context is not yet created' );

        errorHandler.listen( 'context1' );

        assert.ok( typeof errorHandler._contexts.context1 === 'object', 'The context has been created' );
        assert.ok( typeof errorHandler.getContext( 'context1' ) === 'object', 'The context is available' );

    } );

    QUnit.test( 'reset a context', function( assert ) {

        assert.ok( typeof errorHandler._contexts.context1 === 'undefined', 'The context is not yet created' );

        errorHandler.listen( 'context1' );

        assert.ok( typeof errorHandler._contexts.context1 === 'object', 'The context has been created' );

        errorHandler.reset( 'context1' );

        assert.ok( typeof errorHandler._contexts.context1 === 'undefined', 'The context is now removed' );
    } );

    QUnit.test( 'throw an error', function( assert ) {
        var ready = assert.async();

        errorHandler.listen( 'footext', function( err ) {

            assert.ok( err instanceof Error, 'we got an Error' );
            assert.equal( err.message, 'foo', 'the error is the one thrown' );

            ready();
        } );

        errorHandler.throw( 'footext', new Error( 'foo' ) );
    } );

     QUnit.test( 'throw a string error',  function( assert ) {
         var ready = assert.async();

         errorHandler.listen( 'footext', function( err ) {

            assert.ok( err instanceof Error, 'we got an Error' );
            assert.equal( err.message, 'foo', 'the error is the one thrown' );

            ready();
        } );

        errorHandler.throw( 'footext', 'foo' );
    } );

    QUnit.test( 'listen typed errors',  function( assert ) {
        var ready = assert.async();

        errorHandler.listen( 'footext', 'TypeError', function( err ) {

            assert.ok( err instanceof Error, 'we got an Error' );
            assert.ok( err instanceof TypeError, 'we got a TypeError' );
            assert.equal( err.name, 'TypeError', 'the error is the one thrown' );
            assert.equal( err.message, 'bar', 'the error is the one thrown' );

            ready();
        } );

        errorHandler.throw( 'footext', new Error( 'foo' ) );
        errorHandler.throw( 'footext', new TypeError( 'bar' ) );
    } );

    QUnit.test( 'listen global and typed errors',  function( assert ) {
        var ready = assert.async();


        errorHandler.listen( 'footext', 'TypeError', function( err ) {
            assert.ok( err instanceof Error, 'we got an Error' );
            assert.ok( err instanceof TypeError, 'we got a TypeError' );
            assert.equal( err.name, 'TypeError', 'the error is the one thrown' );
            assert.equal( err.message, 'bar', 'the error is the one thrown' );
        } );

        errorHandler.listen( 'footext', function( err ) {

            assert.ok( err instanceof Error, 'we got an Error' );
            assert.ok( err instanceof TypeError, 'we got a TypeError' );
            assert.equal( err.name, 'TypeError', 'the error is the one thrown' );
            assert.equal( err.message, 'bar', 'the error is the one thrown' );

            ready();
        } );
        errorHandler.throw( 'footext', new TypeError( 'bar' ) );
    } );

} );
