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
define(['util/namespace'], function (namespaceHelper) {
    'use strict';


    var namespaceApi = [
        {name: 'split', title: 'split'},
        {name: 'getName', title: 'getName'},
        {name: 'getNamespace', title: 'getNamespace'},
        {name: 'namespaceAll', title: 'namespaceAll'}
    ];


    QUnit.module('namespace');


    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.equal(typeof namespaceHelper, 'object', "The namespaceHelper module exposes an object");
    });


    QUnit
        .cases(namespaceApi)
        .test('has API ', function (data, assert) {
            QUnit.expect(1);
            assert.equal(typeof namespaceHelper[data.name], 'function', 'The namespaceHelper exposes a "' + data.name + '" function');
        });


    QUnit.test('split', function (assert) {
        QUnit.expect(7);

        assert.deepEqual(namespaceHelper.split(), [], 'An empty array is returned from an undefined list');
        assert.deepEqual(namespaceHelper.split({}), [], 'An empty array is returned from an unsupported type');
        assert.deepEqual(namespaceHelper.split(''), [], 'An empty array is returned from an empty list');
        assert.deepEqual(namespaceHelper.split(' '), [], 'An empty array is returned from an empty list');
        assert.deepEqual(namespaceHelper.split('n1'), ['n1'], 'An array with 1 entry is returned from a single name');
        assert.deepEqual(namespaceHelper.split('n1 N1.ns1 n2.NS2 n3'), ['n1', 'N1.ns1', 'n2.NS2', 'n3'], 'The names are split in an array with no normalization');
        assert.deepEqual(namespaceHelper.split('n1 N1.ns1 n2.NS2 n3', true), ['n1', 'n1.ns1', 'n2.ns2', 'n3'], 'The names are split in an array with normalization');
    });


    QUnit.test('getName', function (assert) {
        QUnit.expect(6);

        assert.equal(namespaceHelper.getName(), '', 'An empty name is returned from an undefined string');
        assert.equal(namespaceHelper.getName(''), '', 'An empty name is returned from an empty string');
        assert.equal(namespaceHelper.getName({}), '', 'An empty name is returned from an unsupported type');
        assert.equal(namespaceHelper.getName('.ns'), '', 'An empty name is returned from a string that only contains the namespace part');
        assert.equal(namespaceHelper.getName('n.ns'), 'n', 'The name is returned from a string that contains a namespaced name');
        assert.equal(namespaceHelper.getName('n'), 'n', 'The name is returned from a string that does not contains the namespace part');
    });


    QUnit.test('getNamespace', function (assert) {
        QUnit.expect(7);

        assert.equal(namespaceHelper.getNamespace(), '', 'An empty namespace is returned from an undefined string');
        assert.equal(namespaceHelper.getNamespace(''), '', 'An empty namespace is returned from an empty string');
        assert.equal(namespaceHelper.getNamespace({}), '', 'An empty namespace is returned from an unsupported type');
        assert.equal(namespaceHelper.getNamespace('.ns'), 'ns', 'The namespace is returned from a string that only contains the namespace part');
        assert.equal(namespaceHelper.getNamespace('n.ns'), 'ns', 'The namespace is returned from a string that contains a namespaced name');
        assert.equal(namespaceHelper.getNamespace('n'), '@', 'The default namespace is returned from a string that does not contains the namespace part');
        assert.equal(namespaceHelper.getNamespace('n', '#'), '#', 'The provided default namespace is returned from a string that does not contains the namespace part');
    });


    QUnit.test('namespaceAll', function (assert) {
        QUnit.expect(20);

        assert.equal(namespaceHelper.namespaceAll(), '', 'An empty list is returned from an undefined string');
        assert.equal(namespaceHelper.namespaceAll(''), '', 'An empty list is returned from an empty string');
        assert.equal(namespaceHelper.namespaceAll({}), '', 'An empty list is returned from an unsupported type');
        assert.equal(namespaceHelper.namespaceAll('n'), 'n', 'No namespace is added if none was provided (single name)');
        assert.equal(namespaceHelper.namespaceAll('n n'), 'n', 'No namespace is added if none was provided (same name twice)');
        assert.equal(namespaceHelper.namespaceAll('n1 n2'), 'n1 n2', 'No namespace is added if none was provided (several names)');
        assert.equal(namespaceHelper.namespaceAll('n', 'ns'), 'n.ns', 'The namespace is added to each provided name (single name)');
        assert.equal(namespaceHelper.namespaceAll('n n', 'ns'), 'n.ns', 'The namespace is added to each provided name (same name twice)');
        assert.equal(namespaceHelper.namespaceAll('n1 n2', 'ns'), 'n1.ns n2.ns', 'The namespace is added to each provided name (several names)');
        assert.equal(namespaceHelper.namespaceAll('n.ns', 'ns'), 'n.ns', 'The namespace is not added as the name already contains it (single name)');
        assert.equal(namespaceHelper.namespaceAll('n.ns1', 'ns'), 'n.ns1', 'The namespace is not added as the name already contains another one (single name)');
        assert.equal(namespaceHelper.namespaceAll('n n.ns', 'ns'), 'n.ns', 'The namespace is added to each provided name (same name twice)');
        assert.equal(namespaceHelper.namespaceAll('n n.ns1', 'ns'), 'n.ns n.ns1', 'The namespace is added to each provided name (same name twice but with a different namespace)');
        assert.equal(namespaceHelper.namespaceAll('n1 n2 n.ns1', 'ns'), 'n1.ns n2.ns n.ns1', 'The namespace is added to each provided name that does not contain one (several names)');
        assert.equal(namespaceHelper.namespaceAll('n n.ns', 'NS'), 'n.NS n.ns', 'The namespace is added to each provided name, case sensitive (same name twice)');
        assert.equal(namespaceHelper.namespaceAll('n n.ns', 'NS', true), 'n.ns', 'The namespace is added to each provided name, not case sensitive (same name twice)');
        assert.equal(namespaceHelper.namespaceAll('n n.NS1', 'ns'), 'n.ns n.NS1', 'The namespace is added to each provided name, case sensitive (same name twice but with a different namespace)');
        assert.equal(namespaceHelper.namespaceAll('n n.NS1', 'ns', true), 'n.ns n.ns1', 'The namespace is added to each provided name, not case sensitive (same name twice but with a different namespace)');
        assert.equal(namespaceHelper.namespaceAll('n1 N2 n.Ns1', 'ns'), 'n1.ns N2.ns n.Ns1', 'The namespace is added to each provided name that does not contain one, case sensitive (several names)');
        assert.equal(namespaceHelper.namespaceAll('n1 N2 n.Ns1', 'ns', true), 'n1.ns n2.ns n.ns1', 'The namespace is added to each provided name that does not contain one, not case sensitive (several names)');
    });
});
