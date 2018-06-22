/*
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
 * Copyright (c) 2018 (original work) Open Assessment Technlogies SA
 *
 */

/**
 * Test the module {@link layout/actions}
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'layout/actions', 'layout/actions/binder'], function($, actionsManager, binder){
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert){
        assert.ok(typeof actionsManager === 'object', 'The module expose a plain object');
    });

    QUnit.cases([
        { title : 'on' },
        { title : 'off' },
        { title : 'trigger' },
        { title : 'before' },
        { title : 'after' },
    ]).test('Eventifier API ', function(data, assert) {
        assert.equal(typeof actionsManager[data.title], 'function', 'The module exposes the eventifier method "' + data.title);
    });

    QUnit.cases([
        { title : 'init' },
        { title : 'updateState' },
        { title : 'updateContext' },
        { title : 'exec' },
        { title : 'getBy' },
    ]).test('Instance API ', function(data, assert) {
        assert.equal(typeof actionsManager[data.title], 'function', 'The module exposes the method "' + data.title + '"');
    });

    QUnit.module('Behavior');

    QUnit.test('Load actions and update context', function(assert){

        QUnit.expect(10);

        assert.equal(actionsManager.getBy('foo-new'), null, 'The foo-new action is not bound');
        assert.equal(actionsManager.getBy('foo-delete'), null, 'The foo-delete action is not bound');
        assert.equal(actionsManager.getBy('foo-special'), null, 'The foo-special action is not bound');

        actionsManager.init($('#qunit-fixture'));

        assert.deepEqual(actionsManager.getBy('foo-new'), {
            id      : 'foo-new',
            name    : 'New Foo',
            binding : 'addFoo',
            url     : 'https://foo.org/taoFoo/Foo/new',
            context : 'resource',
            multiple : false,
            rights  : { id : 'WRITE'},
            state : {
                disabled    : false,
                hidden      : true,
                active      : false
            }
        }, 'The foo-new action is now bound');
        assert.deepEqual(actionsManager.getBy('foo-delete'), {
            id      : 'foo-delete',
            name    : 'Delete',
            binding : 'removeFoo',
            url     : 'https://foo.org/taoFoo/Foo/delete',
            context : 'instance',
            multiple : false,
            rights  : { id : 'WRITE'},
            state : {
                disabled    : false,
                hidden      : true,
                active      : false
            }
        }, 'The foo-delete action is now bound');
        assert.deepEqual(actionsManager.getBy('foo-special'), {
            id      : 'foo-special',
            name    : 'Special',
            binding : 'special',
            url     : 'https://foo.org/taoFoo/Foo/special',
            context : 'class',
            multiple : false,
            rights  : { },
            state : {
                disabled    : true,
                hidden      : true,
                active      : false
            }
        }, 'The foo-special action is now bound');

        actionsManager.updateContext({
            type : 'instance',
            uri  : 'https://foo.org/Foo#123'
        });

        assert.equal(actionsManager.getBy('foo-new').state.hidden, false, 'The new action is now visible');
        assert.equal(actionsManager.getBy('foo-delete').state.hidden, false, 'The delete action is now visible');
        assert.equal(actionsManager.getBy('foo-special').state.hidden, true, 'The special action is not visible');
        assert.equal(actionsManager.getBy('foo-special').state.disabled, true, 'The special action remains disabled');
    });

    QUnit.asyncTest('execute an action', function(assert){
        var context = {
            type : 'instance',
            uri  : 'https://foo.org/Foo#123'
        };

        QUnit.expect(3);

        binder.register('addFoo', function(receivedContext){

            assert.deepEqual(this, {
                id      : 'foo-new',
                name    : 'New Foo',
                binding : 'addFoo',
                url     : 'https://foo.org/taoFoo/Foo/new',
                context : 'resource',
                multiple : false,
                rights  : { id : 'WRITE'},
                state : {
                    disabled    : false,
                    hidden      : false,
                    active      : true
                }
            }, 'The executed action has the actionContext as lexical scope');
            assert.deepEqual(context, receivedContext, 'The received context matches the current');
            return new Promise( function(resolve){
                setTimeout(resolve, 10);
            });
        });

        actionsManager.init($('#qunit-fixture'));
        actionsManager.updateContext(context);

        actionsManager.exec('foo-new')
            .then(function(){
                assert.ok(true, 'exec resolves once the action is done');
                QUnit.start();
            })
            .catch(function(err){
                assert.ok(false, err.message);
                QUnit.start();
            });
    });
});
