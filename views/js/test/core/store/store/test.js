define(['core/store', 'core/promise'], function(store, Promise){
    'use strict';

    var data = {};
    var mockBackend = function(name){

        if(!name){
            throw new TypeError('no name');
        }
        return {
            getItem : function getItem(key){
                return Promise.resolve(data[key]);
            },
            setItem : function setItem(key, value){
                data[key] = value;
                return Promise.resolve(true);
            },
            removeItem : function removeItem(key){
                delete data[key];
                return Promise.resolve(true);
            },
            clear : function clear(){
                data = {};
                return Promise.resolve(true);
            },
            removeStore : function removeStore(){
                data = {};
                return Promise.resolve(true);
            }
        };
    };

    mockBackend.removeAll = function(){
        return Promise.resolve(true);
    };

    mockBackend.getStoreIdentifier = function(){
        return Promise.resolve('aaaa-bbbb-cccc-dddd');
    };


    QUnit.module('API');

    QUnit.test("module", function(assert){
        QUnit.expect(3);

        assert.ok(typeof store !== 'undefined', "The module exports something");
        assert.ok(typeof store === 'function', "The module exposes a function");
        assert.ok(typeof store.backends === 'object', "The module has a backends object");
    });

    var factoryErrorCases = [{
        title: 'without parameter',
        name: undefined,
        backend : undefined
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
    }];

    QUnit
        .cases(factoryErrorCases)
        .asyncTest("factory", function(data, assert){
            QUnit.expect(2);

            var p = store(data.name, data.backend);
            assert.ok(p instanceof Promise, "The factory returns a promise");

            p.catch(function(err){
                assert.ok(err instanceof TypeError, err.message);
                QUnit.start();
            });
        });

    QUnit.asyncTest("factory", function(assert){
        QUnit.expect(3);
        var p = store('foo', mockBackend);
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
            assert.equal(err.message, 'This backend does not look like a store backend, it miss removeAll or getStoreIdentifier', 'The error message is the one expected');

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
            assert.equal(err.message, 'This backend does not look like a store backend, it miss removeAll or getStoreIdentifier', 'The error message is the one expected');

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
        wrongBackend.getStoreIdentifier = function() {};

        store('foo', wrongBackend).then(function(){
            assert.ok(false, 'The backend should not be validated');
        }).catch(function(err){
            assert.ok(err instanceof TypeError, 'The error is the one expected');
            assert.equal(err.message, 'The backend does not comply with the Storage interface', 'The error message is the one expected');

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
        wrongBackend.getStoreIdentifier = function() {};

        store('foo', wrongBackend).then(function(){
            assert.ok(false, 'The backend should not be validated');
        }).catch(function(err){
            assert.ok(err instanceof TypeError, 'The error is the one expected');
            assert.equal(err.message, 'The backend does not comply with the Storage interface', 'The error message is the one expected');

            QUnit.start();
        });
    });

    QUnit.module('CRUD', {
        setup    : function(){
            data = {};
        }
    });

    QUnit.asyncTest("setItem", function(assert){
        QUnit.expect(4);

        store('foo', mockBackend).then(function(storage){
            assert.equal(typeof storage, 'object', 'The store is an object');

            var p = storage.setItem('bar', 'boz');
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
            assert.equal(typeof storage, 'object', 'The store is an object');

            var p = storage.setItem('bar', 'noz');
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
                    return storage.getItem('zoo').then(function(value){
                        assert.equal(typeof value, 'undefined', 'The value does not exists anymore');
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
                        return storage.getItem('zoo').then(function(value){
                            assert.equal(typeof value, 'undefined', 'The value does not exists anymore');
                            QUnit.start();
                        });
                    });
                });
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });

    QUnit.asyncTest("removeAll", function(assert){
        QUnit.expect(3);

        var expectedValidate = function() {return true};
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

    QUnit.asyncTest("object", function(assert){
        QUnit.expect(3);

        var sample = {
            collection : [{
                item1: true,
                item2: 'false',
                item3: 12
            },{
                item4: { value : null }
            }]
        };
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

    QUnit.asyncTest("getIdentifier", function(assert){
        QUnit.expect(2);

        store.getIdentifier(mockBackend).then(function(id){
            assert.equal(typeof id, 'string', 'we have a store identifier');
            assert.equal(id, 'aaaa-bbbb-cccc-dddd', 'the identifier matches');
            QUnit.start();
        }).catch(function(err){
            assert.ok(false, err);
            QUnit.start();
        });
    });
});
