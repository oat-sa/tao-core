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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'ui/form/validator/validator'
], function (
    _,
    validatorFactory
) {
    'use strict';

    QUnit.module('Factory');

    QUnit.test('module', function (assert) {
        assert.expect(3);

        assert.equal(typeof validatorFactory, 'function', 'The module exposes a function');
        assert.equal(typeof validatorFactory(), 'object', 'The factory produces an object');
        assert.notStrictEqual(validatorFactory(), validatorFactory(), 'The factory provides a different object on each call');
    });

    QUnit.cases.init([
        {title: 'validate'},
        {title: 'addValidation'},
        {title: 'getValidation'},
        {title: 'getValidations'},
        {title: 'removeValidation'},
        {title: 'removeValidations'}
    ]).test('module API ', function (data, assert) {
        var instance = validatorFactory();
        assert.expect(1);
        assert.equal(typeof instance[data.title], 'function', 'The instance exposes a "' + data.title + '" function');
    });

    QUnit.module('API');

    QUnit.test('manage rules', function (assert) {
        var fooRule = {
            id: 'foo',
            predicate: /^\w+$/
        };
        var barRule = {
            id: 'bar',
            predicate: function(value) {
                return value === 'foo';
            }
        };
        var instance = validatorFactory({
            validations: [fooRule, barRule]
        });

        assert.expect(21);

        assert.deepEqual(instance.getValidations(), [fooRule, barRule], 'The list of rules is filled');
        assert.equal(instance.getValidation('foo'), fooRule, 'There is a rule identified by foo');
        assert.equal(instance.getValidation('bar'), barRule, 'There is a rule identified by bar');

        instance.removeValidations();
        assert.deepEqual(instance.getValidations(), [], 'The list of rules is now empty');
        assert.equal(instance.getValidation('foo'), null, 'There is no rule identified by foo anymore');
        assert.equal(instance.getValidation('bar'), null, 'There is no rule identified by bar anymore');

        assert.throws(function() {
            instance.addValidation();
        }, 'Should raise an error as the rule is missing');

        assert.throws(function() {
            instance.addValidation({});
        }, 'Should raise an error as the rule is empty');

        assert.throws(function() {
            instance.addValidation({id: ''});
        }, 'Should raise an error as the id is empty');

        assert.throws(function() {
            instance.addValidation({id: 'required'});
        }, 'Should raise an error as the predicate is missing');

        assert.throws(function() {
            instance.addValidation({id: 'required', predicate: false});
        }, 'Should raise an error as the predicate is invalid');

        instance = validatorFactory();
        assert.deepEqual(instance.getValidations(), [], 'The list of rules is empty');
        assert.equal(instance.getValidation('foo'), null, 'There is no rule identified by foo');
        assert.equal(instance.getValidation('bar'), null, 'There is no rule identified by bar');

        instance.addValidation(fooRule);
        assert.equal(instance.getValidation('foo'), fooRule, 'There is a rule identified by foo');

        instance.addValidation(barRule);
        assert.equal(instance.getValidation('bar'), barRule, 'There is a rule identified by bar');

        assert.deepEqual(instance.getValidations(), [fooRule, barRule], 'The list of rules is set');

        instance.removeValidation('foo');
        assert.equal(instance.getValidation('foo'), null, 'There is no rule identified by foo anymore');

        assert.deepEqual(instance.getValidations(), [barRule], 'The list of rules only contains the rule identified by bar');

        instance.removeValidation('bar');
        assert.equal(instance.getValidation('bar'), null, 'There is no rule identified by bar anymore');

        assert.deepEqual(instance.getValidations(), [], 'The list of rules is empty');
    });

    QUnit.test('validate no rules', function (assert) {
        var ready = assert.async();
        var instance = validatorFactory();

        assert.expect(3);

        Promise
            .all([
                new Promise(function(resolve) {
                    instance.validate('')
                        .then(function() {
                            assert.ok(true, 'The value is valid');
                        })
                        .catch(function(err) {
                            assert.ok(false, 'The value should be valid');
                            assert.pushResult({
                                result: false,
                                message: err
                            });
                        })
                        .then(function() {
                            resolve();
                        });
                }),
                new Promise(function(resolve) {
                    instance.validate('foo')
                        .then(function() {
                            assert.ok(true, 'The value is valid');
                        })
                        .catch(function(err) {
                            assert.ok(false, 'The value should be valid');
                            assert.pushResult({
                                result: false,
                                message: err
                            });
                        })
                        .then(function() {
                            resolve();
                        });
                }),
                new Promise(function(resolve) {
                    instance.validate('10')
                        .then(function() {
                            assert.ok(true, 'The value is valid');
                        })
                        .catch(function(err) {
                            assert.ok(false, 'The value should be valid');
                            assert.pushResult({
                                result: false,
                                message: err
                            });
                        })
                        .then(function() {
                            resolve();
                        });
                })
            ])
            .catch(function(err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
            })
            .then(function() {
                ready();
            });
    });

    QUnit.test('validate rules', function (assert) {
        var ready = assert.async();
        var defaultMessage = 'Oops!';
        var instance = validatorFactory({
            defaultMessage: defaultMessage,
            validations: [{
                id: 'numeric',
                message: 'This field must be numerical',
                predicate: /^\d+$/,
                precedence: 2
            }, {
                id: 'required',
                message: 'This field is required',
                predicate: function (value) {
                    return value.length > 0;
                },
                precedence: 1
            }, {
                id: 'domain',
                predicate: function (value) {
                    value = parseInt(value, 10) || 0;
                    return Promise.resolve(value <= 10);
                },
                precedence: 3
            }]
        });

        assert.expect(7);

        Promise
            .all([
                new Promise(function(resolve) {
                    instance.validate('')
                        .then(function() {
                            assert.ok(false, 'The value should not be valid');
                        })
                        .catch(function(messages) {
                            assert.ok(true, 'The value is not valid');
                            assert.deepEqual(messages, ['This field is required', 'This field must be numerical'], 'The expected messages are returned');
                        })
                        .then(function() {
                            resolve();
                        });
                }),
                new Promise(function(resolve) {
                    instance.validate('foo')
                        .then(function() {
                            assert.ok(false, 'The value should not be valid');
                        })
                        .catch(function(messages) {
                            assert.ok(true, 'The value is not valid');
                            assert.deepEqual(messages, ['This field must be numerical'], 'The expected messages are returned');
                        })
                        .then(function() {
                            resolve();
                        });
                }),
                new Promise(function(resolve) {
                    instance.validate('100')
                        .then(function() {
                            assert.ok(false, 'The value should not be valid');
                        })
                        .catch(function(messages) {
                            assert.ok(true, 'The value is not valid');
                            assert.deepEqual(messages, [defaultMessage], 'The expected messages are returned');
                        })
                        .then(function() {
                            resolve();
                        });
                }),
                new Promise(function(resolve) {
                    instance.validate('10')
                        .then(function() {
                            assert.ok(true, 'The value is valid');
                        })
                        .catch(function(err) {
                            assert.ok(false, 'The value should be valid');
                            assert.pushResult({
                                result: false,
                                message: err
                            });
                        })
                        .then(function() {
                            resolve();
                        });
                })
            ])
            .catch(function(err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
            })
            .then(function() {
                ready();
            });
    });

    QUnit.test('validate precise rule', function (assert) {
        var ready = assert.async();
        var defaultMessage = 'Oops!';
        var instance = validatorFactory({
            defaultMessage: defaultMessage,
            validations: [{
                id: 'precise',
                message: 'This field must be yes',
                predicate: 'yes'
            }]
        });

        assert.expect(7);

        Promise
            .all([
                new Promise(function(resolve) {
                    instance.validate('')
                        .then(function() {
                            assert.ok(false, 'The value should not be valid');
                        })
                        .catch(function(messages) {
                            assert.ok(true, 'The value is not valid');
                            assert.deepEqual(messages, ['This field must be yes'], 'The expected messages are returned');
                        })
                        .then(function() {
                            resolve();
                        });
                }),
                new Promise(function(resolve) {
                    instance.validate('foo')
                        .then(function() {
                            assert.ok(false, 'The value should not be valid');
                        })
                        .catch(function(messages) {
                            assert.ok(true, 'The value is not valid');
                            assert.deepEqual(messages, ['This field must be yes'], 'The expected messages are returned');
                        })
                        .then(function() {
                            resolve();
                        });
                }),
                new Promise(function(resolve) {
                    instance.validate('100')
                        .then(function() {
                            assert.ok(false, 'The value should not be valid');
                        })
                        .catch(function(messages) {
                            assert.ok(true, 'The value is not valid');
                            assert.deepEqual(messages, ['This field must be yes'], 'The expected messages are returned');
                        })
                        .then(function() {
                            resolve();
                        });
                }),
                new Promise(function(resolve) {
                    instance.validate('yes')
                        .then(function() {
                            assert.ok(true, 'The value is valid');
                        })
                        .catch(function(err) {
                            assert.ok(false, 'The value should be valid');
                            assert.pushResult({
                                result: false,
                                message: err
                            });
                        })
                        .then(function() {
                            resolve();
                        });
                })
            ])
            .catch(function(err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
            })
            .then(function() {
                ready();
            });
    });

    QUnit.test('validate list', function (assert) {
        var ready = assert.async();
        var defaultMessage = 'Oops!';
        var instance = validatorFactory({
            defaultMessage: defaultMessage,
            validations: [{
                id: 'precise',
                predicate: ['yes', 'no']
            }]
        });

        assert.expect(8);

        Promise
            .all([
                new Promise(function(resolve) {
                    instance.validate('')
                        .then(function() {
                            assert.ok(false, 'The value should not be valid');
                        })
                        .catch(function(messages) {
                            assert.ok(true, 'The value is not valid');
                            assert.deepEqual(messages, [defaultMessage], 'The expected messages are returned');
                        })
                        .then(function() {
                            resolve();
                        });
                }),
                new Promise(function(resolve) {
                    instance.validate('foo')
                        .then(function() {
                            assert.ok(false, 'The value should not be valid');
                        })
                        .catch(function(messages) {
                            assert.ok(true, 'The value is not valid');
                            assert.deepEqual(messages, [defaultMessage], 'The expected messages are returned');
                        })
                        .then(function() {
                            resolve();
                        });
                }),
                new Promise(function(resolve) {
                    instance.validate('100')
                        .then(function() {
                            assert.ok(false, 'The value should not be valid');
                        })
                        .catch(function(messages) {
                            assert.ok(true, 'The value is not valid');
                            assert.deepEqual(messages, [defaultMessage], 'The expected messages are returned');
                        })
                        .then(function() {
                            resolve();
                        });
                }),
                new Promise(function(resolve) {
                    instance.validate('yes')
                        .then(function() {
                            assert.ok(true, 'The value is valid');
                        })
                        .catch(function(err) {
                            assert.ok(false, 'The value should be valid');
                            assert.pushResult({
                                result: false,
                                message: err
                            });
                        })
                        .then(function() {
                            resolve();
                        });
                }),
                new Promise(function(resolve) {
                    instance.validate('no')
                        .then(function() {
                            assert.ok(true, 'The value is valid');
                        })
                        .catch(function(err) {
                            assert.ok(false, 'The value should be valid');
                            assert.pushResult({
                                result: false,
                                message: err
                            });
                        })
                        .then(function() {
                            resolve();
                        });
                })
            ])
            .catch(function(err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
            })
            .then(function() {
                ready();
            });
    });
});
