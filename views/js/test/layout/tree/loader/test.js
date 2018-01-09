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
 * Copyright (c) 2017 (original work) Open Assessment Technlogies SA
 *
 */
define(['layout/tree/loader'], function(treeLoader){
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert){
        assert.ok(typeof treeLoader === 'function', 'The module expose a function');
    });


    QUnit.module('loader');

    QUnit.test('default', function(assert){
        var provider;

        QUnit.expect(4);

        provider = treeLoader();

        assert.equal(typeof provider, 'object', 'A provider is returned');
        assert.equal(provider.name, 'jstree', 'The default provider is jstree');

        provider = treeLoader('foo');

        assert.equal(typeof provider, 'object', 'A provider is returned');
        assert.equal(provider.name, 'jstree', 'The default provider is jstree');
    });

    QUnit.test('get by name', function(assert){
        var provider;

        QUnit.expect(2);

        provider = treeLoader('resource-selector');

        assert.equal(typeof provider, 'object', 'A provider is returned');
        assert.equal(provider.name, 'resource-selector', 'The resource selector provider is loaded');
    });

    QUnit.test('change the default', function(assert){
        var provider;

        QUnit.expect(2);

        window.requirejs.config ({
            config: {
                'layout/tree/loader' : {
                    treeProvider : 'resource-selector'
                }
            }
        });

        provider = treeLoader();

        assert.equal(typeof provider, 'object', 'A provider is returned');
        assert.equal(provider.name, 'resource-selector', 'The resource selector provider is loaded');
    });
});


