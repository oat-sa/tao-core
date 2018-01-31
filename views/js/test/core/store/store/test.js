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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */

/**
 * Test the module {@link core/store}
 */
define(['core/store', 'core/promise'], function(store, Promise){
    'use strict';

    var mockedData = {};
    var mockBackend = function(name){

        if(!name){
            throw new TypeError('no name');
        }
        return {
            getItem : function getItem(key){
                return Promise.resolve(mockedData[key]);
            },
            setItem : function setItem(key, value){
                mockedData[key] = value;
                return Promise.resolve(true);
            },
            getItems : function getItems(){
                return Promise.resolve(mockedData);
            },
            removeItem : function removeItem(key){
                delete mockedData[key];
                return Promise.resolve(true);
            },
            clear : function clear(){
                mockedData = {};
                return Promise.resolve(true);
            },
            removeStore : function removeStore(){
                mockedData = {};
                return Promise.resolve(true);
            }
        };
    };

    mockBackend.removeAll = function(){};

    mockBackend.getAll = function(){};

    mockBackend.getStoreIdentifier = function(){};


    QUnit.module('API');

    QUnit.test("module", function(assert){
        QUnit.expect(7);

        assert.ok(typeof store !== 'undefined', "The module exports something");
        assert.ok(typeof store === 'function', "The module exposes a function");
        assert.ok(typeof store.backends === 'object', "The module has a backends object");
        assert.ok(typeof store.getIdentifier === 'function', "The module expose the getIdentifier method");
        assert.ok(typeof store.getAll === 'function', "The module expose the getAll method");
        assert.ok(typeof store.removeAll === 'function', "The module expose the removeAll method");
        assert.ok(typeof store.cleanUpSpace === 'function', "The module expose the cleanUpSpace method");
    });

    QUnit.cases([{
        title: 'without parameter',
    }, {
        title: 'with a name and a backend name',
        name: 'foo',
        backend : 'bar'
    }, {
        title: 'with an incomplete backend',
        name: 'foo',
        backend : function(){
            return  {
                getItem : function(){}
            };
        }
    }]).asyncTest("factory", function(data, assert){
        var p;

        QUnit.expect(2);

        p = store(data.name, data.backend);
        assert.ok(p instanceof Promise, "The factory returns a promise");

        p.catch(function(err){
            assert.ok(err instanceof TypeError, err.message);
            QUnit.start();
        });
    });

    QUnit.asyncTest("factory", function(assert){
        var p;
        QUnit.expect(3);

        p = store('foo', mockBackend);
        assert.ok(p instanceof Promise, "The factory returns a promise");

        p.then(function(storage){
            assert.ok(typeof storage === 'object', "The factory creates an object");
            store('foo', mockBackend).then(function(otherStorage){
                assert.notEqual(storage, otherStorage, "The factory creates an new object");
                QUnit.start();
            });
        });
    });

    QUnit.asyncTest("wrong backend", function(assert){
        var wrongBackend;

        QUnit.expect(2);

        wrongBackend = function(){
            return {};
        };

        store('foo', wrongBackend).then(function(){
            assert.ok(false, 'The backend should not be validated');
        }).catch(function(err){
            assert.ok(err instanceof TypeError, 'The error is the one expected');
            assert.equal(err.message, 'This backend doesn\'t comply with the store backend API', 'The error message is the one expected');

            QUnit.start();
        });
    });

    QUnit.asyncTest("missing backend methods", function(assert){
        var wrongBackend;

        QUnit.expect(2);

        wrongBackend = function(){
            return {};
        };
        wrongBackend.removeAll = function() {};

        store('foo', wrongBackend).then(function(){
            assert.ok(false, 'The backend should not be validated');
        }).catch(function(err){
            assert.ok(err instanceof TypeError, 'The error is the one expected');
            assert.equal(err.message, 'This backend doesn\'t comply with the store backend API', 'The error message is the one expected');

            QUnit.start();
        });
    });

    QUnit.asyncTest("wrong storage", function(assert){
        var wrongBackend;

        QUnit.expect(2);

        wrongBackend = function(){
            return {};
        };
        wrongBackend.removeAll = function() {};
        wrongBackend.getAll = function() {};
        wrongBackend.getStoreIdentifier = function() {};

        store('foo', wrongBackend).then(function(){
            assert.ok(false, 'The backend should not be validated');
        }).catch(function(err){
            assert.ok(err instanceof TypeError, 'The error is the one expected');
            assert.equal(err.message, 'The store doesn\'t comply with the Storage interface', 'The error message is the one expected');

            QUnit.start();
        });
    });

    QUnit.asyncTest("missing storage methods", function(assert){
        var wrongBackend;

        QUnit.expect(2);

        wrongBackend = function(){
            return {
                setItem : function(){},
                getItem : function(){},
                clear : function(){},
            };
        };
        wrongBackend.removeAll = function() {};
        wrongBackend.getAll = function() {};
        wrongBackend.getStoreIdentifier = function() {};

        store('foo', wrongBackend).then(function(){
            assert.ok(false, 'The backend should not be validated');
        }).catch(function(err){
            assert.ok(err instanceof TypeError, 'The error is the one expected');
            assert.equal(err.message, 'The store doesn\'t comply with the Storage interface', 'The error message is the one expected');

            QUnit.start();
        });
    });

    QUnit.module('CRUD', {
        setup    : function(){
            mockedData = {};
        }
    });

    QUnit.asyncTest("setItem", function(assert){
        QUnit.expect(4);

        store('foo', mockBackend).then(function(storage){
            var p;

            assert.equal(typeof storage, 'object', 'The store is an object');

            p = storage.setItem('bar', 'boz');
            assert.ok(p instanceof Promise, 'setItem returns a Promise');

            return p.then(function(result){

                assert.equal(typeof result, 'boolean', 'The result is a boolean');
                assert.ok(result, 'The item is added');

                QUnit.start();
            });
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });

    });

    QUnit.asyncTest("getItem", function(assert){
        QUnit.expect(5);

        store('foo', mockBackend).then(function(storage){
            var p;

            assert.equal(typeof storage, 'object', 'The store is an object');

            p = storage.setItem('bar', 'noz');
            assert.ok(p instanceof Promise, 'setItem returns a Promise');

            return p.then(function(result){
                assert.ok(result, 'The item is added');

                storage.getItem('bar').then(function(value){

                    assert.equal(typeof value, 'string', 'The result is a string');
                    assert.equal(value, 'noz', 'The retrieved value is correct');

                    QUnit.start();
                });
            });
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.asyncTest("get/set objects", function(assert){
        var sample = {
            collection : [{
                item1: true,
                item2: 'false',
                item3: 12
            },{
                item4: { value : null }
            }]
        };

        QUnit.expect(3);

        store('foo', mockBackend).then(function(storage){
            assert.equal(typeof storage, 'object', 'The store is an object');

            return storage.setItem('sample', sample).then(function(added){
                assert.ok(added, 'The item is added');
                storage.getItem('sample').then(function(result){
                    assert.deepEqual(result, sample, 'Retrieving the sample');
                    QUnit.start();
                });
            });
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.asyncTest("removeItem", function(assert){
        QUnit.expect(5);

        store('foo', mockBackend).then(function(storage){
            assert.equal(typeof storage, 'object', 'The store is an object');

            return storage.setItem('moo', 'noob')
            .then(function(result){
                assert.ok(result, 'The item is added');

                return storage.getItem('moo').then(function(value){
                    assert.equal(value, 'noob', 'The retrieved value is correct');
                });
            }).then(function(){
                return storage.removeItem('moo').then(function(rmResult){
                    assert.ok(rmResult, 'The item is removed');
                });
            }).then(function(){
                return storage.getItem('moo').then(function(value){
                    assert.equal(typeof value, 'undefined', 'The value does not exists anymore');
                    QUnit.start();
                });
            });
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.asyncTest("clear", function(assert){
        QUnit.expect(5);

        store('foo', mockBackend).then(function(storage){
            assert.equal(typeof storage, 'object', 'The store is an object');

            return Promise.all([
                storage.setItem('zoo', 'zoob'),
                storage.setItem('too', 'toob')
            ])
            .then(function(){
                return storage.getItem('too').then(function(value){
                    assert.equal(value, 'toob', 'The retrieved value is correct');
                });
            }).then(function(){
                return storage.clear().then(function(rmResult){
                    assert.ok(rmResult, 'The item is removed');
                });
            }).then(function(){
                return storage.getItem('too').then(function(value){
                    assert.equal(typeof value, 'undefined', 'The value does not exists anymore');
                    return storage.getItem('zoo').then(function(newValue){
                        assert.equal(typeof newValue, 'undefined', 'The value does not exists anymore');
                        QUnit.start();
                    });
                });
            });
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.asyncTest("removeStore", function(assert){
        QUnit.expect(5);

        store('foo', mockBackend).then(function(storage){
            assert.equal(typeof storage, 'object', 'The store is an object');

            return Promise.all([
                storage.setItem('zoo', 'zoob'),
                storage.setItem('too', 'toob')
            ])
                .then(function(){
                    return storage.getItem('too').then(function(value){
                        assert.equal(value, 'toob', 'The retrieved value is correct');
                    });
                }).then(function(){
                    return storage.removeStore().then(function(rmResult){
                        assert.ok(rmResult, 'The store is removed');
                    });
                }).then(function(){
                    return storage.getItem('too').then(function(value){
                        assert.equal(typeof value, 'undefined', 'The value does not exists anymore');
                        return storage.getItem('zoo').then(function(newValue){
                            assert.equal(typeof newValue, 'undefined', 'The value does not exists anymore');
                            QUnit.start();
                        });
                    });
                });
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.module('backend');

    QUnit.asyncTest("removeAll", function(assert){
        var expectedValidate = function() {
            return true;
        };

        QUnit.expect(3);

        mockBackend.removeAll = function(validate) {
            assert.ok(true, 'The store has delegated the call to the backend');
            assert.equal(validate, expectedValidate, 'The expected validator has been provided');
        };

        store.removeAll(expectedValidate, mockBackend).then(function(){
            assert.ok(true, 'The store has resolved the clean up');
            QUnit.start();
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.asyncTest("getAll", function(assert){
        var storeNames = ['foo', 'bar'];

        var expectedValidate = function() {
            return true;
        };

        QUnit.expect(3);

        mockBackend.getAll = function(validate) {
            assert.ok(true, 'The store has delegated the call to the backend');
            assert.equal(validate, expectedValidate, 'The expected validator has been provided');

            return Promise.resolve(storeNames);
        };

        store.getAll(expectedValidate, mockBackend).then(function(resultNames){
            assert.deepEqual(resultNames, storeNames, 'The method has resolved with the expected names');
            QUnit.start();
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });


    QUnit.asyncTest("getIdentifier", function(assert){
        QUnit.expect(3);

        mockBackend.getStoreIdentifier = function(){

            assert.ok(true, 'The store has delegated the call to the backend');
            return Promise.resolve('aaaa-bbbb-cccc-dddd');
        };

        store.getIdentifier(mockBackend).then(function(id){
            assert.equal(typeof id, 'string', 'we have a store identifier');
            assert.equal(id, 'aaaa-bbbb-cccc-dddd', 'the identifier matches');
            QUnit.start();
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.cases([{
        title : 'older than 30 seconds',
        since : 'PT30S',
        removed : ['store-1-day', 'store-10-days',  'store-20-days', 'store-40-days']
    }, {
        title : 'older than 1 day',
        since : 'P1D',
        removed : ['store-1-day', 'store-10-days',  'store-20-days', 'store-40-days']
    }, {
        title : 'older than 2 weeks',
        since : 'P2W',
        removed : ['store-20-days', 'store-40-days']
    }, {
        title : 'older than 6 months (using a timestamp)',
        since : Date.now() - (1000 * 60 * 60 * 24 * 182.5),
        removed : []
    }, {
        title : 'older than 1 day and starts with ^store-1',
        since : 'P1D',
        pattern : /^store-1/,
        removed : ['store-1-day', 'store-10-days']
    }]).asyncTest("cleanUpSpace, clean up stores", function(data, assert){

        var now = Date.now();
        var aDay = 1000 * 60 * 60 * 24;
        var testStores = {
            'store-now' : {
                name : 'store-now',
                lastOpen : now
            },
            'store-1-day' : {
                name : 'store-1-day',
                lastOpen : now - aDay
            },
            'store-10-days' : {
                name : 'store-10-days',
                lastOpen : now - (aDay * 10)
            },
            'store-20-days' : {
                name : 'store-20-days',
                lastOpen : now - (aDay * 20)
            },
            'store-40-days' : {
                name : 'store-40-days',
                lastOpen : now - (aDay * 40)
            },
        };

        QUnit.expect(8);

        mockBackend.removeAll = function(validate) {
            assert.ok(true, 'The store has delegated the call to the backend');
            assert.equal(typeof validate, 'function', 'The validator has been provided');

            assert.equal( validate('store-now', testStores['store-now']), data.removed.indexOf('store-now') > -1);
            assert.equal( validate('store-1-day', testStores['store-1-day']), data.removed.indexOf('store-1-day') > -1);
            assert.equal( validate('store-10-days', testStores['store-10-days']), data.removed.indexOf('store-10-days') > -1);
            assert.equal( validate('store-20-days', testStores['store-20-days']), data.removed.indexOf('store-20-days') > -1);
            assert.equal( validate('store-40-days', testStores['store-40-days']), data.removed.indexOf('store-40-days') > -1);
        };

        store.cleanUpSpace(data.since, data.pattern, mockBackend).then(function(){
            assert.ok(true, 'The store has resolved the clean up');
            QUnit.start();
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });
});
