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
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define(['core/delegator'], function(delegator) {
    'use strict';

    QUnit.module('delegator');


    QUnit.test('module', function(assert) {
        QUnit.expect(3);
        
        assert.equal(typeof delegator, 'function', "The delegator module exposes a function");
        assert.equal(typeof delegator(), 'function', 'The delegator helper returns a function');
        assert.notEqual(delegator(), delegator(), 'The delegator helper returns a different function on each call');
    });


    QUnit.test('delegate', function(assert) {
        var delegate;
        var expectedResponse = 'ok';
        var expectedArg1 = 'test1';
        var expectedArg2 = 'test2';
        var api = {
            isApi: true,
            action: function(arg1, arg2) {
                assert.ok(true, 'Action called from the api');
                return delegate('action', arg1, arg2);
            }
        };
        var adapter = {
            isAdapter: true,
            action: function(arg1, arg2) {
                assert.ok(true, 'Action delegated to the adapter');
                assert.ok(this.isApi, 'The context is bound to the API');
                assert.ok(!this.isAdapter, 'The context is not bound to the Adapter');
                assert.equal(arg1, expectedArg1, 'The delegate function forwarded the first argument');
                assert.equal(arg2, expectedArg2, 'The delegate function forwarded the second argument');
                return expectedResponse;
            }
        };

        QUnit.expect(8);
        
        delegate = delegator(api, adapter);
        
        assert.equal(typeof delegate, 'function', 'The delegator helper has created a delegate function');

        assert.equal(api.action(expectedArg1, expectedArg2), expectedResponse, 'The action has returned the expected response');
    });
    

    QUnit.asyncTest('delegate event', function(assert) {
        var delegate;
        var expectedResponse = 'ok';
        var expectedArg1 = 'test1';
        var expectedArg2 = 'test2';
        var api = {
            isApi: true,
            action: function(arg1, arg2) {
                assert.ok(true, 'Action called from the api');
                return delegate('action', arg1, arg2);
            },

            trigger: function(event, response, arg1, arg2) {
                assert.equal(event, 'action', 'The delegate function has triggered the related event');
                assert.equal(response, expectedResponse, 'The delegate function has forwarded the response');
                assert.equal(arg1, expectedArg1, 'The delegate function has forwarded the first argument');
                assert.equal(arg2, expectedArg2, 'The delegate function has forwarded the second argument');
                QUnit.start();
            }
        };
        var adapter = {
            isAdapter: true,
            action: function(arg1, arg2) {
                assert.ok(true, 'Action delegated to the adapter');
                assert.ok(this.isApi, 'The context is bound to the API');
                assert.ok(!this.isAdapter, 'The context is not bound to the Adapter');
                assert.equal(arg1, expectedArg1, 'The delegate function forwarded the first argument');
                assert.equal(arg2, expectedArg2, 'The delegate function forwarded the second argument');
                return expectedResponse;
            }
        };

        QUnit.expect(12);

        delegate = delegator(api, adapter);

        assert.equal(typeof delegate, 'function', 'The delegator helper has created a delegate function');

        assert.equal(api.action(expectedArg1, expectedArg2), expectedResponse, 'The action has returned the expected response');
    });


    QUnit.test('delegate event disabled', function(assert) {
        var delegate;
        var api = {
            action: function() {
                assert.ok(true, 'Action called from the api');
                return delegate('action', arguments);
            },

            trigger: function() {
                assert.ok(false, 'The delegate function must not trigger the related event');
            }
        };
        var adapter = {
            action: function() {
                assert.ok(true, 'Action delegated to the adapter');
            }
        };

        QUnit.expect(3);

        delegate = delegator(api, adapter, {
            eventifier: false
        });

        assert.equal(typeof delegate, 'function', 'The delegator helper has created a delegate function');

        api.action();
    });


    QUnit.test('delegate errors', function(assert) {
        var delegate;

        QUnit.expect(6);

        delegate = delegator();
        assert.equal(typeof delegate, 'function', 'The delegator helper has created a delegate function');
        assert.throws(function() {
            delegate('action');
        }, 'An error must be thrown if the delegate function is called with no adapter');

        delegate = delegator({}, {}, {required: true});
        assert.equal(typeof delegate, 'function', 'The delegator helper has created a delegate function');
        assert.throws(function() {
            delegate('action');
        }, 'An error must be thrown if the delegate function is called with an unknown target function');

        delegate = delegator({}, {});
        assert.equal(typeof delegate, 'function', 'The delegator helper has created a delegate function');
        try {
            delegate('action');
            assert.ok(true, 'A default delegated function has been called');
        } catch(e) {
            assert.ok(false, 'A default delegated function must be called when an unknown target is invoked while the `required` option is disabled');
        }
    });


    QUnit.test('delegate default', function(assert) {
        var delegate;

        var api = {};
        var provider = {};
        var expectedResponse = 'response';

        QUnit.expect(3);

        delegate = delegator(api, provider, {
            defaultProvider: function() {
                assert.ok(true, 'Default delegated function invoked!');
                assert.equal(this, api, 'The default delegated is called using the api context');
                return expectedResponse;
            }
        });

        assert.equal(delegate('test'), expectedResponse, 'The default delegated has been invoked');
    });


    QUnit.test('forward', function(assert) {
        var delegate;
        var expectedResponse = 'ok';
        var expectedArg1 = 'test1';
        var expectedArg2 = 'test2';
        var api = {
            isApi: true,
            action: function(arg1, arg2) {
                assert.ok(true, 'Action called from the api');
                return delegate('action', arg1, arg2);
            }
        };
        var adapter = {
            isAdapter: true,
            action: function(arg1, arg2) {
                assert.ok(true, 'Action delegated to the adapter');
                assert.ok(!this.isApi, 'The context is not bound to the API');
                assert.ok(this.isAdapter, 'The context is bound to the Adapter');
                assert.equal(arg1, expectedArg1, 'The delegate function forwarded the first argument');
                assert.equal(arg2, expectedArg2, 'The delegate function forwarded the second argument');
                return expectedResponse;
            }
        };

        QUnit.expect(8);

        delegate = delegator(api, adapter, {
            forward: true
        });

        assert.equal(typeof delegate, 'function', 'The delegator helper has created a delegate function');

        assert.equal(api.action(expectedArg1, expectedArg2), expectedResponse, 'The action has returned the expected response');
    });


    QUnit.asyncTest('forward event', function(assert) {
        var delegate;
        var expectedResponse = 'ok';
        var expectedArg1 = 'test1';
        var expectedArg2 = 'test2';
        var api = {
            isApi: true,
            action: function(arg1, arg2) {
                assert.ok(true, 'Action called from the api');
                return delegate('action', arg1, arg2);
            },

            trigger: function(event, response, arg1, arg2) {
                assert.equal(event, 'action', 'The delegate function has triggered the related event');
                assert.equal(response, expectedResponse, 'The delegate function has forwarded the response');
                assert.equal(arg1, expectedArg1, 'The delegate function has forwarded the first argument');
                assert.equal(arg2, expectedArg2, 'The delegate function has forwarded the second argument');
                QUnit.start();
            }
        };
        var adapter = {
            isAdapter: true,
            action: function(arg1, arg2) {
                assert.ok(true, 'Action delegated to the adapter');
                assert.ok(!this.isApi, 'The context is not bound to the API');
                assert.ok(this.isAdapter, 'The context is bound to the Adapter');
                assert.equal(arg1, expectedArg1, 'The delegate function forwarded the first argument');
                assert.equal(arg2, expectedArg2, 'The delegate function forwarded the second argument');
                return expectedResponse;
            }
        };

        QUnit.expect(12);

        delegate = delegator(api, adapter, {
            forward: true
        });

        assert.equal(typeof delegate, 'function', 'The delegator helper has created a delegate function');

        assert.equal(api.action(expectedArg1, expectedArg2), expectedResponse, 'The action has returned the expected response');
    });


    QUnit.asyncTest('wrapper', function(assert) {
        var delegate;
        var expectedResponse = 'ok';
        var expectedWrappedResponse = expectedResponse + expectedResponse;
        var expectedArg1 = 'test1';
        var expectedArg2 = 'test2';
        var api = {
            isApi: true,
            action: function(arg1, arg2) {
                assert.ok(true, 'Action called from the api');
                return delegate('action', arg1, arg2);
            },

            trigger: function(event, response, arg1, arg2) {
                assert.equal(event, 'action', 'The delegate function has triggered the related event');
                assert.equal(response, expectedWrappedResponse, 'The delegate function has forwarded the response');
                assert.equal(arg1, expectedArg1, 'The delegate function has forwarded the first argument');
                assert.equal(arg2, expectedArg2, 'The delegate function has forwarded the second argument');
                QUnit.start();
            }
        };
        var adapter = {
            isAdapter: true,
            action: function(arg1, arg2) {
                assert.ok(true, 'Action delegated to the adapter');
                assert.ok(this.isApi, 'The context is bound to the API');
                assert.ok(!this.isAdapter, 'The context is not bound to the Adapter');
                assert.equal(arg1, expectedArg1, 'The delegate function forwarded the first argument');
                assert.equal(arg2, expectedArg2, 'The delegate function forwarded the second argument');
                return expectedResponse;
            }
        };

        QUnit.expect(13);

        delegate = delegator(api, adapter, {
            wrapper: function(value) {
                assert.equal(value, expectedResponse, 'The response is provided to the wrapper');

                return value + value;
            }
        });

        assert.equal(typeof delegate, 'function', 'The delegator helper has created a delegate function');

        assert.equal(api.action(expectedArg1, expectedArg2), expectedWrappedResponse, 'The action has returned the expected response');
    });
});
