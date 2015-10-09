define(['core/eventifier'], function(eventifier){
    'use strict';

    QUnit.module('eventifier');

    QUnit.test("api", 2, function(assert){
        assert.ok(typeof eventifier !== 'undefined', "The module exports something");
        assert.ok(typeof eventifier === 'function', "The module has an eventifier method");
    });

    QUnit.module('eventification');

    QUnit.test("delegates", 4, function(assert){

        var emitter = eventifier();

        assert.ok(typeof emitter === 'object', "the emitter definition is an object");
        assert.ok(typeof emitter.on === 'function', "the emitter defintion holds the method on");
        assert.ok(typeof emitter.trigger === 'function', "the emitter defintion holds the method trigger");
        assert.ok(typeof emitter.off === 'function', "the emitter defintion holds the method off");
    });

    QUnit.asyncTest("listen and trigger with params", 3, function(assert){

        var emitter = eventifier();
        var params = ['bar', 'baz'];

        emitter.on('foo', function handleFoo(p0, p1){
            assert.ok(true, "The foo event is triggered on emitter");
            assert.equal(p0, params[0], 'The received parameters are those from the trigger');
            assert.equal(p1, params[1], 'The received parameters are those from the trigger');
            QUnit.start();
        });

        emitter.trigger('foo', params[0], params[1]);
    });

    QUnit.test("on context", 1, function(assert){

        var emitter1 = eventifier();
        var emitter2 = eventifier();

        assert.notDeepEqual(emitter1, emitter2, "Emitters are different objects");
    });


    QUnit.asyncTest("trigger context", 2, function(assert){
        var emitter1 = eventifier();
        var emitter2 = eventifier();

        emitter1.on('foo', function(success){
            assert.ok(success, "The foo event is triggered on emitter1");
        });
        emitter2.on('foo', function(success){
            assert.ok(success, "The foo event is triggered on emitter2");
            QUnit.start();
        });

        emitter1.trigger('foo', true);
        setTimeout(function(){
            emitter2.trigger('foo', true);
        }, 10);
    });

    QUnit.asyncTest("off", 1, function(assert){
        var emitter = eventifier();

        emitter.on('foo', function(){
            assert.ok(false, "The foo event shouldn't be triggered");
        });
        emitter.on('bar', function(){
            assert.ok(true, "The bar event should  be triggered");
            QUnit.start();
        });

        emitter.off('foo');
        emitter.trigger('foo');
        setTimeout(function(){
            emitter.trigger('bar');
        }, 10);
    });

    QUnit.asyncTest("multiple listeners", 2, function(assert){
        var emitter = eventifier();

        emitter.on('foo', function(){
            assert.ok(true, "The 1st foo listener should be executed");
        });
        emitter.on('foo', function(){
            assert.ok(true, "The 2nd foo listener should be executed");
            QUnit.start();
        });

        emitter.trigger('foo');
    });
    
    QUnit.module('before');
    
    QUnit.asyncTest("success", 5, function(assert){

        var testDriver = eventifier();

        testDriver.on('next', function(){
            assert.ok(true, "The 1st listener should be executed : e.g. save context recovery");
        });
        testDriver.on('next', function(){
            assert.ok(true, "The 2nd listener should be executed : e.g. save resposne ");
        });
        testDriver.on('next', function(){
            assert.ok(true, "The third and last listener should be executed : e.g. move to next item");
            QUnit.start();
        });

        testDriver.before('next', function(e, ok){
            assert.ok(true, "The 1st 'before' listener should be executed : e.g. validate item state");
            ok();
        });
        testDriver.before('next', function(e, ok){
            assert.ok(true, "The 2nd 'before' listener should be executed : e.g. validate a special interaction state");
            ok();
        });

        testDriver.trigger('next');
    });

    QUnit.asyncTest("interruption", 2, function(assert){

        var itemEditor = eventifier();

        itemEditor.on('save', function(){
            assert.ok(true, "The listener should not be executed : e.g. do save item");
        });
        itemEditor.before('save', function(e, ok, interrupt){
            assert.ok(true, "The 1st 'before' listener should be executed : e.g. validate current edition form");
            //form invalid
            interrupt();
        });
        itemEditor.before('save', function(e, ok){
            assert.ok(true, "The 2nd 'before' listener should be executed : e.g. do save item stylesheet");
            ok();
            QUnit.start();
        });

        itemEditor.trigger('save');
    });
    
    QUnit.asyncTest("immediate interruption", 1, function(assert){

        var itemEditor = eventifier();

        itemEditor.on('save', function(){
            assert.ok(true, "The listener should not be executed : e.g. do save item");
        });
        itemEditor.before('save', function(e, ok, interrupt){
            assert.ok(true, "The 1st 'before' listener should be executed : e.g. validate current edition form");
            //form invalid that interrupt all following call
            interrupt(true);
            QUnit.start();
        });
        itemEditor.before('save', function(e, ok){
            assert.ok(true, "The 2nd 'before' listener should not be executed : e.g. do save item stylesheet");
            ok();
        });

        itemEditor.trigger('save');
    });
});