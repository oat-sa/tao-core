define(['core/eventifier', 'core/promise'], function(eventifier, Promise){
    'use strict';

    QUnit.module('eventifier');

    QUnit.test("api", function(assert){
        QUnit.expect(2);

        assert.ok(typeof eventifier !== 'undefined', "The module exports something");
        assert.ok(typeof eventifier === 'function', "The module has an eventifier method");
    });


    QUnit.module('eventification');

    QUnit.test("delegates", function(assert){

        var emitter = eventifier();

        QUnit.expect(7);

        assert.ok(typeof emitter === 'object', "the emitter definition is an object");
        assert.ok(typeof emitter.on === 'function', "the emitter defintion holds the method on");
        assert.ok(typeof emitter.before === 'function', "the emitter defintion holds the method before");
        assert.ok(typeof emitter.after === 'function', "the emitter defintion holds the method after");
        assert.ok(typeof emitter.off === 'function', "the emitter defintion holds the method off");
        assert.ok(typeof emitter.removeAllListeners === 'function', "the emitter defintion holds the method removeAllListeners");
        assert.ok(typeof emitter.trigger === 'function', "the emitter defintion holds the method trigger");
    });

    QUnit.asyncTest("listen and trigger with params", function(assert){

        var emitter = eventifier();
        var params = ['bar', 'baz'];

        QUnit.expect(3);

        emitter.on('foo', function handleFoo(p0, p1){
            assert.ok(true, "The foo event is triggered on emitter");
            assert.equal(p0, params[0], 'The received parameters are those from the trigger');
            assert.equal(p1, params[1], 'The received parameters are those from the trigger');
            QUnit.start();
        });

        emitter.trigger('foo', params[0], params[1]);
    });

    QUnit.test("on context", function(assert){

        var emitter1 = eventifier();
        var emitter2 = eventifier();

        QUnit.expect(1);

        assert.notDeepEqual(emitter1, emitter2, "Emitters are different objects");
    });


    QUnit.asyncTest("trigger context", function(assert){
        var emitter1 = eventifier();
        var emitter2 = eventifier();

        QUnit.expect(2);

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

    QUnit.asyncTest("off", function(assert){
        var emitter = eventifier();

        QUnit.expect(1);

        emitter.on('foo', function(){
            assert.ok(false, "The foo event shouldn't be triggered");
        });
        emitter.on('bar', function(){
            assert.ok(true, "The bar event should be triggered");
            QUnit.start();
        });

        emitter.off('foo');
        emitter.trigger('foo');
        setTimeout(function(){
            emitter.trigger('bar');
        }, 10);
    });

    QUnit.asyncTest("off empty", function(assert){
        var emitter = eventifier();

        QUnit.expect(2);

        emitter.on('foo', function(){
            assert.ok(true, "The foo event should be triggered");
        });
        emitter.on('bar', function(){
            assert.ok(true, "The bar event should be triggered");
            QUnit.start();
        });

        emitter.off();
        emitter.trigger('foo');
        setTimeout(function(){
            emitter.trigger('bar');
        }, 10);
    });

    QUnit.asyncTest("removeAllListeners", function(assert){
        var emitter = eventifier();

        QUnit.expect(0);

        emitter.on('foo', function(){
            assert.ok(false, "The foo event shouldn't be triggered");
        });
        emitter.on('bar', function(){
            assert.ok(true, "The bar event shouldn't be triggered");
        });

        emitter.removeAllListeners();
        emitter.trigger('foo');
        emitter.trigger('bar');
        setTimeout(function(){
            emitter.trigger('foo');
            emitter.trigger('bar');

            setTimeout(function(){
                QUnit.start();
            }, 10);
        }, 10);
    });

    QUnit.asyncTest("multiple listeners", function(assert){
        var emitter = eventifier();

        QUnit.expect(2);

        emitter.on('foo', function(){
            assert.ok(true, "The 1st foo listener should be executed");
        });
        emitter.on('foo', function(){
            assert.ok(true, "The 2nd foo listener should be executed");
            QUnit.start();
        });

        emitter.trigger('foo');
    });

    QUnit.module('namespaces');

    QUnit.asyncTest("listen namespace, trigger without namespace", function(assert){
        var emitter = eventifier();

        QUnit.expect(4);

        emitter.on('foo', function(){
            assert.ok(true, 'the foo handler is called');
        });
        emitter.on('foo.*', function(){
            assert.ok(true, 'the foo.* handler is called');
        });
        emitter.on('foo.@', function(){
            assert.ok(true, 'the foo.@ handler is called');
        });
        emitter.on('foo.bar', function(){
            assert.ok(true, 'the foo.bar handler is called');
            QUnit.start();
        });

        emitter.trigger('foo');
    });

    QUnit.asyncTest("listen namespace, trigger with default namespace", function(assert){
        var emitter = eventifier();

        QUnit.expect(4);

        emitter.on('foo', function(){
            assert.ok(true, 'the foo handler is called');
        });
        emitter.on('foo.*', function(){
            assert.ok(true, 'the foo.* handler is called');
        });
        emitter.on('foo.@', function(){
            assert.ok(true, 'the foo.@ handler is called');
        });
        emitter.on('foo.bar', function(){
            assert.ok(true, 'the foo.bar handler is called');
            QUnit.start();
        });

        emitter.trigger('foo.@');
    });

    QUnit.asyncTest("listen namespace, trigger with namespace", function(assert){
        var emitter = eventifier();

        QUnit.expect(2);

        emitter.on('foo', function(){
            assert.ok(false, 'the foo handler should not be called');
        });
        emitter.on('foo.@', function(){
            assert.ok(false, 'the foo.@ handler should not be called');
        });
        emitter.on('foo.*', function(){
            assert.ok(true, 'the foo.* handler is called');
        });
        emitter.on('foo.bar', function(){
            assert.ok(true, 'the foo.bar handler is called');
            QUnit.start();
        });
        emitter.on('foo.baz', function(){
            assert.ok(false, 'the foo.baz handler should not be called');
        });

        emitter.trigger('foo.bar');
    });

    QUnit.asyncTest("off namespaced event", function(assert){
        var emitter = eventifier();

        QUnit.expect(0);

        emitter.on('foo', function(){
            assert.ok(false, 'the foo handler should not be called');
        });
        emitter.on('foo.bar', function(){
            assert.ok(false, 'the foo.bar handler should not be called');
        });
        emitter.off('foo');

        emitter.trigger('foo');
        setTimeout(function(){
            QUnit.start();
        }, 1);
    });

    QUnit.asyncTest("off namespaced", function(assert){
        QUnit.expect(2);

        var emitter = eventifier();

        emitter.on('foo', function(){
            assert.ok(true, 'the foo handler should be called');
        });
        emitter.on('foo.baz', function(){
            assert.ok(true, 'the foo.baz handler should be called');
            QUnit.start();
        });
        emitter.on('foo.bar', function(){
            assert.ok(false, 'the foo.bar handler should not be called');

        });
        emitter.on('norz.bar', function(){
            assert.ok(false, 'the norz.bar handler should not be called');
        });

        emitter.off('.bar');

        emitter.trigger('foo').trigger('norz');
    });

    QUnit.asyncTest("off all namespaces", function(assert){
        QUnit.expect(1);

        var emitter = eventifier();

        emitter.on('foo', function(){
            assert.ok(true, 'the foo handler should be called');
            QUnit.start();
        });
        emitter.on('foo.baz', function(){
            assert.ok(false, 'the foo.baz handler should not be called');
        });
        emitter.on('foo.bar', function(){
            assert.ok(false, 'the foo.bar handler should not be called');

        });
        emitter.on('norz.bar', function(){
            assert.ok(false, 'the norz.bar handler should not be called');
        });

        emitter.off('.*');

        emitter.trigger('foo').trigger('norz');
    });

    QUnit.module('before');

    QUnit.asyncTest("sync done - return nothing", function(assert){

        var testDriver = eventifier();
        var arg1 = 'X',
            arg2 = 'Y';

        QUnit.expect(21);

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

        testDriver.before('next', function(e, a1, a2){
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'next', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            assert.equal(a1, arg1, 'the first event arg is correct');
            assert.equal(a2, arg2, 'the second event arg is correct');
            assert.ok(true, "The 1st 'before' listener should be executed : e.g. validate item state");
        });
        testDriver.before('next', function(e, a1, a2){
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'next', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            assert.equal(a1, arg1, 'the first event arg is correct');
            assert.equal(a2, arg2, 'the second event arg is correct');
            assert.ok(true, "The 2nd 'before' listener should be executed : e.g. validate a special interaction state");
        });

        testDriver.trigger('next', arg1, arg2);
    });

    QUnit.asyncTest("async done", function(assert){

        var testDriver = eventifier();
        var arg1 = 'X',
            arg2 = 'Y';

        QUnit.expect(21);

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

        testDriver.before('next', function(e, a1, a2){
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'next', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            assert.equal(a1, arg1, 'the first event arg is correct');
            assert.equal(a2, arg2, 'the second event arg is correct');
            assert.ok(true, "The 1st 'before' listener should be executed : e.g. validate item state");
            var done = e.done();
            setTimeout(function(){
                done();
            }, 10);
        });

        testDriver.before('next', function(e, a1, a2){
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'next', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            assert.equal(a1, arg1, 'the first event arg is correct');
            assert.equal(a2, arg2, 'the second event arg is correct');
            assert.ok(true, "The 2nd 'before' listener should be executed : e.g. validate a special interaction state");
        });

        testDriver.trigger('next', arg1, arg2);
    });

    QUnit.asyncTest("async promise", function(assert){

        var testDriver = eventifier();
        var arg1 = 'X',
            arg2 = 'Y';

        QUnit.expect(21);

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

        testDriver.before('next', function(e, a1, a2){
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'next', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            assert.equal(a1, arg1, 'the first event arg is correct');
            assert.equal(a2, arg2, 'the second event arg is correct');
            assert.ok(true, "The 1st 'before' listener should be executed : e.g. validate item state");
            return new Promise(function(resolve) {
                setTimeout(function(){
                    resolve();
                });
            });
        });

        testDriver.before('next', function(e, a1, a2){
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'next', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            assert.equal(a1, arg1, 'the first event arg is correct');
            assert.equal(a2, arg2, 'the second event arg is correct');
            assert.ok(true, "The 2nd 'before' listener should be executed : e.g. validate a special interaction state");
        });

        testDriver.trigger('next', arg1, arg2);
    });

    QUnit.test("async done - fail to call done()", function(assert){

        var testDriver = eventifier();

        QUnit.expect(7);

        testDriver.on('next', function(){
            assert.ok(false, "The listener should not be executed : e.g. save context recovery");
        });

        testDriver.before('next', function(e){
            assert.ok(true, "The 1st 'before' listener should be executed : e.g. validate item state");
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'next', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            var done = e.done();
            //fail to call done here although we are in an async context
        });

        testDriver.before('next', function(e){
            assert.ok(false, "The 2nd 'before' listener should not be executed : e.g. validate a special interaction state");
        });

        testDriver.trigger('next');
    });

    QUnit.asyncTest("sync prevent - return false", function(assert){

        var itemEditor = eventifier();

        QUnit.expect(14);

        itemEditor.on('save', function(){
            assert.ok(false, "The listener should not be executed : e.g. do save item");
        });
        itemEditor.before('save', function(e){
            assert.ok(true, "The 1st 'before' listener should be executed : e.g. validate current edition form");
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'save', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            //form invalid
            return false;
        });
        itemEditor.before('save', function(e){
            assert.ok(true, "The 2nd 'before' listener should be executed : e.g. do save item stylesheet");
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'save', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            QUnit.start();
        });

        itemEditor.trigger('save');
    });

    QUnit.asyncTest("sync prevent - call prevent()", function(assert){

        var itemEditor = eventifier();

        QUnit.expect(14);

        itemEditor.on('save', function(){
            assert.ok(false, "The listener should not be executed : e.g. do save item");
        });
        itemEditor.before('save', function(e){
            assert.ok(true, "The 1st 'before' listener should be executed : e.g. validate current edition form");
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'save', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            //form invalid
            e.prevent();
        });
        itemEditor.before('save', function(e){
            assert.ok(true, "The 2nd 'before' listener should be executed : e.g. do save item stylesheet");
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'save', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            QUnit.start();
        });

        itemEditor.trigger('save');
    });

    QUnit.asyncTest("async prevent", function(assert){

        var itemEditor = eventifier();

        QUnit.expect(14);

        itemEditor.on('save', function(){
            assert.ok(false, "The listener should not be executed : e.g. do save item");
        });
        itemEditor.before('save', function(e){
            assert.ok(true, "The 1st 'before' listener should be executed : e.g. validate current edition form");
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'save', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            var done = e.done();
            setTimeout(function(){
                e.prevent();
            }, 10);
            //form invalid
            return false;
        });
        itemEditor.before('save', function(e){
            assert.ok(true, "The 2nd 'before' listener should be executed : e.g. do save item stylesheet");
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'save', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            QUnit.start();
        });

        itemEditor.trigger('save');
    });

    QUnit.asyncTest("async prevent with promise", function(assert){

        var itemEditor = eventifier();

        QUnit.expect(14);

        itemEditor.on('save', function(){
            assert.ok(false, "The listener should not be executed : e.g. do save item");
        });
        itemEditor.before('save', function(e){
            assert.ok(true, "The 1st 'before' listener should be executed : e.g. validate current edition form");
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'save', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            return new Promise(function(resolve, reject) {
                setTimeout(function(){
                    reject();
                });
            });
        });
        itemEditor.before('save', function(e){
            assert.ok(true, "The 2nd 'before' listener should be executed : e.g. do save item stylesheet");
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'save', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            QUnit.start();
        });

        itemEditor.trigger('save');
    });

    QUnit.asyncTest("sync prevent now", function(assert){

        var itemEditor = eventifier();

        QUnit.expect(7);

        itemEditor.on('save', function(){
            assert.ok(false, "The listener should not be executed : e.g. do save item");
        });
        itemEditor.before('save', function(e){
            assert.ok(true, "The 1st 'before' listener should be executed : e.g. validate current edition form");
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'save', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            //form invalid that interrupt all following call
            e.preventNow();
            QUnit.start();
        });
        itemEditor.before('save', function(e){
            assert.ok(false, "The 2nd 'before' listener should not be executed : e.g. do save item stylesheet");
        });

        itemEditor.trigger('save');
    });


    QUnit.asyncTest("async prevent now", function(assert){

        var itemEditor = eventifier();

        QUnit.expect(7);

        itemEditor.on('save', function(){
            assert.ok(false, "The listener should not be executed : e.g. do save item");
        });
        itemEditor.before('save', function(e){
            assert.ok(true, "The 1st 'before' listener should be executed : e.g. validate current edition form");
            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'save', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            e.done();

            //form invalid that interrupt all following call
            setTimeout(function(){
                e.preventNow();
            }, 10);

            QUnit.start();
        });
        itemEditor.before('save', function(e){
            assert.ok(false, "The 2nd 'before' listener should not be executed : e.g. do save item stylesheet");
        });

        itemEditor.trigger('save');
    });

    QUnit.asyncTest("namespaced events before order", function(assert){
        var emitter = eventifier();

        var state = {
            foo : false,
            foobar : false,
            beforefoo: false,
            beforefoobar : false
        };

        QUnit.expect(24);

        emitter.on('foo', function(){
            assert.ok(true, "The foo handler is called");
            assert.equal(state.beforefoo, true, 'The before foo handler should hoave been called');
            assert.equal(state.beforefoobar, true, 'The before foo.bar handler should have been called');
            state.foo = true;
        });
        emitter.on('foo.bar', function(){
            assert.ok(true, "The foo.bar handler is called");
            assert.equal(state.beforefoo, true, 'The before foo handler should have been called');
            assert.equal(state.beforefoobar, true, 'The before foo.bar handler should have been called');
            state.foobar = true;
        });
        emitter.before('foo', function(e){
            assert.ok(true, "The before foo handler is called");
            assert.equal(state.foo, false, 'The foo handler should not have been called');
            assert.equal(state.foobar, false, 'The foo.bar handler should have been called');

            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'foo', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');

            state.beforefoo = true;
        });
        emitter.before('foo.bar', function(e){
            assert.ok(true, "The before foo.bar handler is called");
            assert.equal(state.foo, false, 'The foo handler should not have been called');
            assert.equal(state.foobar, false, 'The foo.bar handler should have been called');

            assert.equal(typeof e, 'object', 'the event context object is provided');
            assert.equal(e.name, 'foo', 'the event name is provided');
            assert.equal(e.namespace, '@', 'the event namespace is provided');
            assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
            assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
            assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');

            state.beforefoobar = true;
        });

        emitter.trigger('foo');

        setTimeout(function(){
            QUnit.start();
        }, 10);
    });

    QUnit.asyncTest("events context (simple)", function(assert){
        var emitter = eventifier();

        QUnit.expect(40);
        QUnit.stop(1);

        emitter
            .on('ev1', function(){
                assert.ok(true, "The ev1 handler is called");
            })
            .on('ev1.*', function(){
                assert.ok(true, "The ev1.* handler is called");
            })
            .on('ev1.ns', function(){
                assert.ok(true, "The ev1.ns handler is called");
            })
            .before('ev1', function(e){
                assert.ok(true, "The before ev1 handler is called");
                assert.equal(typeof e, 'object', 'the event context object is provided');
                assert.equal(e.name, 'ev1', 'the event name is provided');
                assert.equal(e.namespace, '@', 'the event namespace is provided');
                assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
                assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
                assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            })
            .before('ev1.*', function(e){
                assert.ok(true, "The before ev1.* handler is called");
                assert.equal(typeof e, 'object', 'the event context object is provided');
                assert.equal(e.name, 'ev1', 'the event name is provided');
                assert.equal(e.namespace, '@', 'the event namespace is provided');
                assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
                assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
                assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            })
            .before('ev1.ns', function(e){
                assert.ok(true, "The before ev1.ns handler is called");
                assert.equal(typeof e, 'object', 'the event context object is provided');
                assert.equal(e.name, 'ev1', 'the event name is provided');
                assert.equal(e.namespace, '@', 'the event namespace is provided');
                assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
                assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
                assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');

                QUnit.start();
            });

        emitter
            .on('ev2', function(){
                assert.ok(false, "The ev2 handler should not be called");
            })
            .on('ev2.*', function(){
                assert.ok(true, "The ev2.* handler is called");
            })
            .on('ev2.ns', function(){
                assert.ok(true, "The ev2.ns handler is called");
            })
            .before('ev2', function(e){
                assert.ok(false, "The before ev2 handler should not be called");
            })
            .before('ev2.*', function(e){
                assert.ok(true, "The before ev2.* handler is called");
                assert.equal(typeof e, 'object', 'the event context object is provided');
                assert.equal(e.name, 'ev2', 'the event name is provided');
                assert.equal(e.namespace, 'ns', 'the event namespace is provided');
                assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
                assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
                assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            })
            .before('ev2.ns', function(e){
                assert.ok(true, "The before ev2.ns handler is called");
                assert.equal(typeof e, 'object', 'the event context object is provided');
                assert.equal(e.name, 'ev2', 'the event name is provided');
                assert.equal(e.namespace, 'ns', 'the event namespace is provided');
                assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
                assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
                assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');

                QUnit.start();
            });

        emitter.trigger('ev1');
        emitter.trigger('ev2.ns');
    });

    QUnit.asyncTest("events context (multi)", function(assert){
        var emitter = eventifier();

        QUnit.expect(128);
        QUnit.stop(7);

        emitter
            .on('ev1', function(){
                assert.ok(true, "The ev1 handler is called");
            })
            .on('ev1.ns', function(){
                assert.ok(true, "The ev1.ns handler is called");
            })
            .before('ev1', function(e){
                assert.ok(true, "The before ev1 handler is called");
                assert.equal(typeof e, 'object', 'the event context object is provided');
                assert.equal(e.name, 'ev1', 'the event name is provided');
                assert.equal(e.namespace, '@', 'the event namespace is provided');
                assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
                assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
                assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            })
            .before('ev1.ns', function(e){
                assert.ok(true, "The before ev1.ns handler is called");
                assert.equal(typeof e, 'object', 'the event context object is provided');
                assert.equal(e.name, 'ev1', 'the event name is provided');
                assert.equal(e.namespace, '@', 'the event namespace is provided');
                assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
                assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
                assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');

                QUnit.start();
            });

        emitter
            .on('ev2', function(){
                assert.ok(true, "The ev2 handler is called");
            })
            .on('ev2.ns', function(){
                assert.ok(true, "The ev2.ns handler is called");
            })
            .before('ev2', function(e){
                assert.ok(true, "The before ev1 handler is called");
                assert.equal(typeof e, 'object', 'the event context object is provided');
                assert.equal(e.name, 'ev2', 'the event name is provided');
                assert.equal(e.namespace, '@', 'the event namespace is provided');
                assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
                assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
                assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            })
            .before('ev2.ns', function(e){
                assert.ok(true, "The before ev1.ns handler is called");
                assert.equal(typeof e, 'object', 'the event context object is provided');
                assert.equal(e.name, 'ev2', 'the event name is provided');
                assert.equal(e.namespace, '@', 'the event namespace is provided');
                assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
                assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
                assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');

                QUnit.start();
            });

        emitter
            .on('ev3', function(){
                assert.ok(false, "The ev3 handler should not be called");
            })
            .on('ev3.*', function(){
                assert.ok(true, "The ev3.* handler is called");
            })
            .on('ev3.ns3', function(){
                assert.ok(true, "The ev3.ns3 handler is called");
            })
            .before('ev3', function(e){
                assert.ok(false, "The before ev3 handler should not be called");
            })
            .before('ev3.*', function(e){
                assert.ok(true, "The before ev3.* handler is called");
                assert.equal(typeof e, 'object', 'the event context object is provided');
                assert.equal(e.name, 'ev3', 'the event name is provided');
                assert.equal(e.namespace, 'ns3', 'the event namespace is provided');
                assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
                assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
                assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            })
            .before('ev3.ns3', function(e){
                assert.ok(true, "The before ev3.ns3 handler is called");
                assert.equal(typeof e, 'object', 'the event context object is provided');
                assert.equal(e.name, 'ev3', 'the event name is provided');
                assert.equal(e.namespace, 'ns3', 'the event namespace is provided');
                assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
                assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
                assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');

                QUnit.start();
            });

        emitter
            .on('ev4', function(){
                assert.ok(false, "The ev4 handler should not be called");
            })
            .on('ev4.*', function(){
                assert.ok(true, "The ev4.* handler is called");
            })
            .on('ev4.ns4', function(){
                assert.ok(true, "The ev4.ns4 handler is called");
            })
            .before('ev4', function(e){
                assert.ok(false, "The before ev4 handler should not be called");
            })
            .before('ev4.*', function(e){
                assert.ok(true, "The before ev4.* handler is called");
                assert.equal(typeof e, 'object', 'the event context object is provided');
                assert.equal(e.name, 'ev4', 'the event name is provided');
                assert.equal(e.namespace, 'ns4', 'the event namespace is provided');
                assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
                assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
                assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');
            })
            .before('ev4.ns4', function(e){
                assert.ok(true, "The before ev4.ns4 handler is called");
                assert.equal(typeof e, 'object', 'the event context object is provided');
                assert.equal(e.name, 'ev4', 'the event name is provided');
                assert.equal(e.namespace, 'ns4', 'the event namespace is provided');
                assert.equal(typeof e.done, 'function', 'the event async enabler API is provided');
                assert.equal(typeof e.prevent, 'function', 'the event preventer API is provided');
                assert.equal(typeof e.preventNow, 'function', 'the event immediate preventer API is provided');

                QUnit.start();
            });

        emitter.trigger('ev1 ev2');
        emitter.trigger('ev3.ns3 ev4.ns4');
        emitter.trigger('ev1 ev3.ns3');
        emitter.trigger('ev4.ns4 ev2');
    });


    QUnit.module('after');

    QUnit.asyncTest("trigger", function(assert){

        var testDriver = eventifier();

        QUnit.expect(2);

        testDriver.on('next', function(){
            assert.ok(true, "This listener should be executed : e.g. move to next item");
        });

        testDriver.after('next', function(){
            assert.ok(true, "This listener should be executed : e.g. push response to storage");
            QUnit.start();
        });

        testDriver.trigger('next');
    });

    QUnit.asyncTest("namespaced after events order", function(assert){
        var emitter = eventifier();

        var state = {
            foo : false,
            foobar : false,
            afterfoo: false,
            afterfoobar : false
        };

        QUnit.expect(12);

        emitter.on('foo', function(){
            assert.ok(true, "The foo handler is called");
            assert.equal(state.afterfoo, false, 'The after foo handler should not be called yet');
            assert.equal(state.afterfoobar, false, 'The after foo.bar handler should not be called yet');
            state.foo = true;
        });
        emitter.on('foo.bar', function(){
            assert.ok(true, "The foo.bar handler is called");
            assert.equal(state.afterfoo, false, 'The after foo handler should not be called yet');
            assert.equal(state.afterfoobar, false, 'The after foo.bar handler should not be called yet');
            state.foobar = true;
        });
        emitter.after('foo', function(){
            assert.ok(true, "The after foo handler is called");
            assert.equal(state.foo, true, 'The foo handler should have been called');
            assert.equal(state.foobar, true, 'The foo.bar handler should have been called');
            state.afterfoo = true;
        });
        emitter.after('foo.bar', function(){
            assert.ok(true, "The after foo.bar handler is called");
            assert.equal(state.foo, true, 'The foo handler should have been called');
            assert.equal(state.foobar, true, 'The foo.bar handler should have been called');
            state.afterfoobar = true;
        });

        emitter.trigger('foo');

        setTimeout(function(){
            QUnit.start();
        }, 10);
    });


    QUnit.module('multiple events names');

    QUnit.asyncTest("listen multiples, trigger one by one", function(assert){
        var emitter = eventifier();

        var counter = 0;

        QUnit.expect(2);

        emitter.on('foo bar', function(){
            assert.ok(true, 'the handler is called');

            if(++counter === 2){
                QUnit.start();
            }
        });
        emitter.trigger('foo')
               .trigger('bar');
    });

    QUnit.asyncTest("listen multiple, trigger multiples with params", function(assert){
        var emitter = eventifier();

        var counter = 0;

        QUnit.expect(8);

        emitter.on('foo bar', function(bool, str, num){
            assert.ok(true, 'the handler is called');
            assert.equal(bool, true, 'The 1st parameter is correct');
            assert.equal(str, 'yo', 'The 2nd parameter is correct');
            assert.equal(num, 1.4, 'The 3rd parameter is correct');

            if(++counter === 2){
                QUnit.start();
            }
        });
        emitter.trigger('foo bar', true, 'yo', 1.4);
    });

    QUnit.asyncTest("listen multiple, off multiple", function(assert){
        var emitter = eventifier();

        QUnit.expect(1);

        emitter.on('foo bar', function(){
            assert.ok(false, 'the handler must not be called');
        });
        emitter.off('foo bar');

        emitter.trigger('foo')
               .trigger('bar');

        setTimeout(function(){
            assert.ok(true, 'control');
            QUnit.start();
        }, 10);
    });

    QUnit.asyncTest("support namespace in multiple events", function(assert){
        var emitter = eventifier();

        var counter = 0;

        QUnit.expect(3);

        emitter.on('foo bar.moo', function(){
            assert.ok(true, 'the handler is called');

            if(++counter === 3){
                QUnit.start();
            }
        });

        emitter.trigger('foo bar.moo bar');
    });

});
