define(['jquery', 'ui/lock'], function($, lock){
        
    module('lock');
   
    test('module', function(){
        expect(1);

        ok(typeof lock === 'function', 'The module expose a function');
    });

    test('api', function(){
        expect(10);

        var lk = lock();
        ok(typeof lk === 'object'                       , 'The lock function creates an object');
        ok(typeof lk._container === 'object'            , 'The lock instance has a _container member');
        ok(typeof lk._container.selector === 'string'   , 'The _container is a jquery object');
        ok(typeof lk.message === 'function'             , 'The lock instance has a message method');
        ok(typeof lk.hasLock === 'function'             , 'The lock instance has a hasLock method');
        ok(typeof lk.locked === 'function'              , 'The lock instance has a locked method');
        ok(typeof lk.open === 'function'                , 'The lock instance has a open method');
        ok(typeof lk.display === 'function'             , 'The lock instance has a display method');
        ok(typeof lk.close === 'function'               , 'The lock instance has a close method');
        ok(typeof lk.release === 'function'             , 'The lock instance has a release method');
    });

    test('factory', function(){
        expect(3);

        var lock1 = lock();
        var lock2 = lock();
        ok(typeof lock1 === 'object'                       , 'The lock function creates an object');
        ok(typeof lock2 === 'object'                       , 'The lock function creates an object');
        notStrictEqual(lock1, lock2                        , 'The lock function creates object instances');
    });

    test('wrong container', function(){
        expect(1);

        throws(function(){

            lock( $('#foofoo'));

        }, Error, 'An exception should be thrown if the container is not an existing element');
    });
    
    test('state', function(){
        expect(11);
        
        var lk = lock().message();
        var lock2 = lock().message();

        ok(typeof lk === 'object'                       , 'The lock function creates an object');
        ok(typeof lk._state === 'string'                , 'The lock contains a _state member');
        ok(typeof lk.setState === 'function'            , 'The lock instance has a setState method');
        ok(typeof lk.isInState === 'function'           , 'The lock instance has an isInState method');
        equal(lk._state, 'created'                      , 'The lock instance starts with the created state');
        ok(lk.isInState('created')                      , 'The isInState method verify the current state');
        ok(lk.isInState(['created'])                      , 'The isInState method verify the current state');
        equal(lock2._state, 'created'                   , 'The 2nd lock instance starts with the created state');

        lock2.setState('closed');

        equal(lock2._state, 'closed'                      , 'Once changed the current state is changed');
        equal(lk._state, 'created'                        , 'Once changed it does not interfere with other instances');
        throws(function(){
            lock().setState('notAState');
        },Error, 'State doesn\'t exist so it throws an error');

    });

    test('default message', function(){
        expect(5);
        
        var lk = lock();
        var r2 = lk.message();

        ok(typeof lk === 'object'                       , 'The lock function creates an object');
        strictEqual(lk, r2                              , 'The message function is fluent');
        ok(typeof lk.content === 'string'               , 'The content property has been created');
        equal(lk.category, 'hasLock'                    , 'The category of info is hasLock');
        ok(/feedback-info/m.test(lk.content)            , 'The content property contains the right css class');
    });

    test('parameterized message', function(){
        expect(5);
        var lk = lock().locked('AWESOME_MESSAGE');

        ok(typeof lk === 'object'                       , 'The lock function creates an object');
        ok(typeof lk.content === 'string'               , 'The content property has been created');
        equal(lk.level, 'error'                         , 'The level is set to error');
        ok(/feedback-error/m.test(lk.content)           , 'The content property contains the right css class');
        ok(/AWESOME_MESSAGE/m.test(lk.content)          , 'The content property contains the message');
    });

    test('display message', function(){
        expect(3);
        var $container = $('#lock-box');

        var lk = lock($container).hasLock('LOCKED_RESOURCE');

        equal(lk.level, 'info'                               , 'The level is set to info');
        ok(/LOCKED_RESOURCE/m.test(lk.content)               , 'The content property contains the message');
        equal($('.feedback-info', $container).length, 1      , 'The lock content has been appended to the container');
    });
    
    test('close message', function(){
        expect(2);

        var $container = $('#lock-box');
        var lk = lock($container).message('locked', 'LOCKED_RESOURCE').display();

        equal($('.feedback-error', $container).length, 1 , 'The lock content has been appended to the container');

        lk.close();
        equal($('.feedback-error', $container).length, 0, 'The lock content has been removed from the container');
    });
    
    asyncTest('close event', function(){
    
        expect(2);

        var $container = $('#lock-box');
        var lk = lock($container).message('hasLock', 'LOCKED_RESOURCE').display();

        equal($('.feedback-info', $container).length, 1 , 'The lock content has been appended to the container');

        $container.on('close.lock', function(e){
            equal($('.feedback-info', $container).length, 0, 'The lock content has been removed from the container');
            start();
        });

        lk.close();
    });

    asyncTest('release fail', function(){

        expect(3);

        var $container = $('#lock-box');
        lock($container)
            .hasLock('LOCKED_RESOURCE',
            {
                failed : function(){
                    ok(true, 'The release failed and callback is called');
                },
                uri: 123,
                url : 'js/test/ui/lock/error.json'
            }).release().close();

        lock($container)
            .hasLock('LOCKED_RESOURCE',
            {
                failed : function(){
                    ok(true, 'The release failed and callback is called');
                },
                uri: 123,
                url : 'js/test/ui/lock/wrong.json'
            }).release().close();

        lock($container)
            .hasLock('LOCKED_RESOURCE',
            {
                released : function(){
                    ok(true, 'The release works and callback is called');
                    start();
                },
                uri: 123,
                url : 'js/test/ui/lock/success.json'
            }).release();
    });

    asyncTest('callbacks', function(){
    
        expect(3);

        var $container = $('#lock-box');
        lock($container)
            .message('locked', 'LOCKED_RESOURCE', {
                create : function(){
                    ok(true, 'The create callback is called');
                },
                display : function(){
                    ok(true, 'The close callback is called');
                },
                close : function(){
                    ok(true, 'The close callback is called');
                    start();
                } 
            })
            .display()
            .close();
    });

});


