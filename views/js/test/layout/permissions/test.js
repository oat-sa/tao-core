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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */
/**
 * Test the module {@link layout/permissions}
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'layout/permissions'
], function (permissionsManager) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module export', function (assert) {
        QUnit.expect(1);

        assert.ok(typeof permissionsManager === 'object', 'The module exports an object');
    });

    QUnit.cases([
        {title: 'setSupportedRights'},
        {title: 'getRights'},
        {title: 'isSupported'},
        {title: 'getPermissions'},
        {title: 'hasPermission'},
        {title: 'clear'},
        {title: 'isContextAllowed'},
        {title: 'getResourceAccessMode'},
    ])
    .test('Instance API', function (data, assert) {
        QUnit.expect(1);
        assert.ok(typeof permissionsManager[data.title] === 'function', 'The permissionsManager exposes the method ' + data.title);
    });


    QUnit.module('rights');

    QUnit.test('supported', function(assert){

        QUnit.expect(10);

        assert.deepEqual(permissionsManager.getRights(), [], 'No supported rights by default');
        assert.ok( ! permissionsManager.isSupported('r'));
        assert.ok( ! permissionsManager.isSupported('w'));
        assert.ok( ! permissionsManager.isSupported('x'));
        assert.ok( ! permissionsManager.isSupported('y'));

        permissionsManager.setSupportedRights(['r', 'w', 'x']);

        assert.deepEqual(permissionsManager.getRights(), ['r', 'w', 'x'], 'New supported rights');
        assert.ok(permissionsManager.isSupported('r'));
        assert.ok(permissionsManager.isSupported('w'));
        assert.ok(permissionsManager.isSupported('x'));
        assert.ok( ! permissionsManager.isSupported('y'));
    });


    QUnit.module('Permissions', {
        setup: function setup(){
            permissionsManager.setSupportedRights(['READ', 'WRITE', 'GRANT']);
        },
        teardown : function teardown(){
            permissionsManager.setSupportedRights([]);
        }
    });

    QUnit.test('add and get one resource permissions', function(assert){
        var uri = 'http://foo.bar/a';

        QUnit.expect(4);

        assert.equal(typeof permissionsManager.getPermissions(uri), 'undefined', 'No permissions set for the resource');

        permissionsManager.addPermissions(uri, ['READ', 'WRITE']);

        assert.deepEqual(permissionsManager.getPermissions(uri), ['READ', 'WRITE'], 'Permissions are set for the resource');

        permissionsManager.addPermissions(uri, []);

        assert.deepEqual(permissionsManager.getPermissions(uri), [], 'No permissions set for the resource anymore');

        permissionsManager.addPermissions(uri, ['GRANT', 'FOO']);

        assert.deepEqual(permissionsManager.getPermissions(uri), ['GRANT'], 'Valid permissions only are kept');
    });

    QUnit.test('add and get multiple permissions', function(assert){
        var permissions = {
            'http://foo.bar/b' : ['READ', 'WRITE', 'GRANT'],
            'http://foo.bar/c' : ['READ'],
            'http://foo.bar/d' : ['FOO', 'BAR', 'WRITE'],
            'http://foo.bar/e' : []
        };

        QUnit.expect(8);

        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/b'), 'undefined', 'No permissions set for the resource');
        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/c'), 'undefined', 'No permissions set for the resource');
        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/d'), 'undefined', 'No permissions set for the resource');
        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/e'), 'undefined', 'No permissions set for the resource');

        permissionsManager.addPermissions(permissions);

        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/b'), ['READ', 'WRITE', 'GRANT'], 'Permissions set for the resource');
        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/c'), ['READ'], 'Permissions set for the resource');
        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/d'), ['WRITE'], 'Permissions set for the resource');
        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/e'), [], 'Permissions set for the resource');
    });

    QUnit.test('clear permissions', function(assert){
        var permissions = {
            'http://foo.bar/f' : ['READ', 'WRITE', 'GRANT'],
            'http://foo.bar/g' : ['READ'],
        };

        QUnit.expect(6);

        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/f'), 'undefined', 'No permissions set for the resource');
        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/g'), 'undefined', 'No permissions set for the resource');

        permissionsManager.addPermissions(permissions);

        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/f'), ['READ', 'WRITE', 'GRANT'], 'Permissions set for the resource');
        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/g'), ['READ'], 'Permissions set for the resource');

        permissionsManager.clear();

        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/f'), 'undefined', 'No permissions set for the resource');
        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/g'), 'undefined', 'No permissions set for the resource');
    });

    QUnit.test('has permissions', function(assert){
        var permissions = {
            'http://foo.bar/i' : ['READ', 'WRITE', 'GRANT'],
            'http://foo.bar/j' : ['READ', 'WRITE'],
            'http://foo.bar/k' : ['READ'],
            'http://foo.bar/l' : [],
        };

        QUnit.expect(23);

        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/i'), 'undefined', 'No permissions set for the resource');
        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/j'), 'undefined', 'No permissions set for the resource');
        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/k'), 'undefined', 'No permissions set for the resource');
        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/l'), 'undefined', 'No permissions set for the resource');

        permissionsManager.addPermissions(permissions);

        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/i'), ['READ', 'WRITE', 'GRANT'], 'Permissions set for the resource');
        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/j'), ['READ', 'WRITE'], 'Permissions set for the resource');
        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/k'), ['READ'], 'Permissions set for the resource');
        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/l'), [], 'Permissions set for the resource');


        assert.ok(permissionsManager.hasPermission('http://foo.bar/i', 'READ'));
        assert.ok(permissionsManager.hasPermission('http://foo.bar/i', 'WRITE'));
        assert.ok(permissionsManager.hasPermission('http://foo.bar/i', 'GRANT'));

        assert.ok(permissionsManager.hasPermission('http://foo.bar/j', 'READ'));
        assert.ok(permissionsManager.hasPermission('http://foo.bar/j', 'WRITE'));
        assert.ok( ! permissionsManager.hasPermission('http://foo.bar/j', 'GRANT'));

        assert.ok(permissionsManager.hasPermission('http://foo.bar/k', 'READ'));
        assert.ok( ! permissionsManager.hasPermission('http://foo.bar/k', 'WRITE'));
        assert.ok( ! permissionsManager.hasPermission('http://foo.bar/k', 'GRANT'));

        assert.ok( ! permissionsManager.hasPermission('http://foo.bar/l', 'READ'));
        assert.ok( ! permissionsManager.hasPermission('http://foo.bar/l', 'WRITE'));
        assert.ok( ! permissionsManager.hasPermission('http://foo.bar/l', 'GRANT'));

        assert.ok( ! permissionsManager.hasPermission('http://foo.bar/z', 'READ'));
        assert.ok( ! permissionsManager.hasPermission('http://foo.bar/z', 'WRITE'));
        assert.ok( ! permissionsManager.hasPermission('http://foo.bar/z', 'GRANT'));
    });

    QUnit.module('Action and context', {
        setup: function setup(){
            permissionsManager.setSupportedRights(['READ', 'WRITE', 'GRANT']);
        },
        teardown : function teardown(){
            permissionsManager.setSupportedRights([]);
        }
    });

    QUnit.cases([{
        title : 'allowed for a read action',
        requiredRights : { id : 'READ'},
        context : {
            id : 'http://foo.bar/o'
        },
        allowed : true
    }, {
        title : 'denied for a read action',
        requiredRights : { id : 'READ'},
        context : {
            id : 'http://foo.bar/p'
        },
        allowed : false
    }, {
        title : 'denied for a missing parameter',
        requiredRights : { id : 'READ'},
        context : {
            uri : 'http://foo.bar/p'
        },
        allowed : false
    }, {
        title : 'denied for a wrong parameter',
        requiredRights : { id : 'READ'},
        context : {
            uri : 'http://foo.bar/z'
        },
        allowed : false
    }, {
        title : 'denied for an empty context',
        requiredRights : { id : 'READ'},
        context : null,
        allowed : false
    }, {
        title : 'allowed for a READ/WRITE action',
        requiredRights : { id : 'READ', classUri: 'WRITE'},
        context : {
            id : 'http://foo.bar/m',
            classUri: 'http://foo.bar/n',
        },
        allowed : true
    }, {
        title : 'denied for a READ/WRITE action',
        requiredRights : { id : 'READ', classUri: 'WRITE'},
        context : {
            id : 'http://foo.bar/m',
            classUri: 'http://foo.bar/o',
        },
        allowed : false
    }, {
        title : 'allowed for empty rights',
        requiredRights : { },
        context : {
            id : 'http://foo.bar/m',
            classUri: 'http://foo.bar/n',
        },
        allowed : true
    }]).test('is context ', function(data, assert){
        var permissions = {
            'http://foo.bar/m' : ['READ', 'WRITE', 'GRANT'],
            'http://foo.bar/n' : ['READ', 'WRITE'],
            'http://foo.bar/o' : ['READ'],
            'http://foo.bar/p' : [],
        };

        QUnit.expect(9);

        permissionsManager.clear();

        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/m'), 'undefined', 'No permissions set for the resource');
        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/n'), 'undefined', 'No permissions set for the resource');
        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/o'), 'undefined', 'No permissions set for the resource');
        assert.equal(typeof permissionsManager.getPermissions('http://foo.bar/p'), 'undefined', 'No permissions set for the resource');

        permissionsManager.addPermissions(permissions);

        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/m'), ['READ', 'WRITE', 'GRANT'], 'Permissions set for the resource');
        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/n'), ['READ', 'WRITE'], 'Permissions set for the resource');
        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/o'), ['READ'], 'Permissions set for the resource');
        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/p'), [], 'Permissions set for the resource');

        assert.equal( permissionsManager.isContextAllowed(data.requiredRights, data.context), data.allowed);
    });


    QUnit.module('Resource', {
        setup: function setup(){
            permissionsManager.setSupportedRights([]);
        },
        teardown : function teardown(){
            permissionsManager.setSupportedRights([]);
        }
    });
    QUnit.cases([{
        title : 'allowed with no rights',
        supportedRights : [],
        resourceRights  : [],
        expected : 'allowed'
    }, {
        title : 'allowed when all rights matches',
        supportedRights : ['READ', 'WRITE', 'GRANT'],
        resourceRights  : ['READ', 'WRITE', 'GRANT'],
        expected : 'allowed'
    }, {
        title : 'partial when read only',
        supportedRights : ['READ', 'WRITE', 'GRANT'],
        resourceRights  : ['READ'],
        expected : 'partial'
    }, {
        title : 'denied when none',
        supportedRights : ['READ', 'WRITE', 'GRANT'],
        resourceRights  : [],
        expected : 'denied'
    }]).test('has mode ', function(data, assert){
        var permissions = {
            'http://foo.bar/q' : data.resourceRights
        };

        QUnit.expect(3);

        permissionsManager.setSupportedRights(data.supportedRights);
        permissionsManager.addPermissions(permissions);

        assert.deepEqual(permissionsManager.getRights(), data.supportedRights, 'List of supported rights configured');
        assert.deepEqual(permissionsManager.getPermissions('http://foo.bar/q'), data.resourceRights, 'Permissions set for the resource');
        assert.equal(permissionsManager.getResourceAccessMode('http://foo.bar/q'), data.expected);
    });


});
