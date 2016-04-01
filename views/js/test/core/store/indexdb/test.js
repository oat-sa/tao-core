define(['core/store/indexdb', 'core/promise' ], function(indexDbBackend, Promise){
    'use strict';

    QUnit.module('API');

    QUnit.test("module", function(assert){
        QUnit.expect(2);

        assert.ok(typeof indexDbBackend !== 'undefined', "The module exports something");
        assert.ok(typeof indexDbBackend === 'function', "The module exposes a function");
    });

    QUnit.test("factory", function(assert){
        QUnit.expect(4);

        assert.throws(function(){
            indexDbBackend();
        }, TypeError, 'The backend should be created with a store id');

        assert.throws(function(){
            indexDbBackend(false);
        }, TypeError, 'The backend should be created with a valid store id');

        var store = indexDbBackend('foo');

        assert.equal(typeof store, 'object', 'The factory return an object');
        assert.notDeepEqual(indexDbBackend('foo'), store, 'The factory creates a new object');
    });

    QUnit.test("store", function(assert){
        QUnit.expect(5);
        var store = indexDbBackend('foo');

        assert.equal(typeof store, 'object', 'The store is an object');
        assert.equal(typeof store.getItem, 'function', 'The store exposes the getItem method');
        assert.equal(typeof store.setItem, 'function', 'The store exposes the setItem method');
        assert.equal(typeof store.removeItem, 'function', 'The store exposes the removetItem method');
        assert.equal(typeof store.clear, 'function', 'The store exposes the clear method');

    });

    QUnit.module('basic access', {
    });

    QUnit.asyncTest("setItem", function(assert){
        QUnit.expect(3);

        var store = indexDbBackend('foo');
        assert.equal(typeof store, 'object', 'The store is an object');

        var p = store.setItem('bar', 'boz');
        assert.ok(p instanceof Promise, 'setItem returns a Promise');


        p.then(function(result){
            console.log(result);
            assert.equal(typeof result, 'object', 'The result is an object');
            QUnit.start();
        }).catch(function(err){
            assert.ok(false, err);
        });
    });

});



