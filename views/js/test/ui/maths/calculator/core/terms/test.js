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
 * Copyright (c) 2019 Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'ui/maths/calculator/core/terms'
], function (_, registeredTerms) {
    'use strict';

    var termTypes = [
        'digit',
        'aggregator',
        'separator',
        'operator',
        'variable',
        'constant',
        'error',
        'function'
    ];

    QUnit.module('Module');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);
        assert.equal(typeof registeredTerms, 'object', "The module exposes an object");
    });

    QUnit.test('terms', function (assert) {
        QUnit.expect(1 + _.size(registeredTerms) * 9);

        assert.ok(_.size(registeredTerms) > 0, 'A list of terms is exposed');

        _.forEach(registeredTerms, function (term, id) {
            assert.equal(typeof term.label, 'string', 'The term ' + id + ' has a property label');
            assert.equal(typeof term.value, 'string', 'The term ' + id + ' has a property value');
            assert.equal(typeof term.type, 'string', 'The term ' + id + ' has a property type');
            assert.equal(typeof term.description, 'string', 'The term ' + id + ' has a property description');

            assert.notEqual(termTypes.indexOf(term.type), -1, 'The term ' + id + ' has the known type ' + term.type);
            assert.notEqual(term.label, '', 'The property label of the term ' + id + ' is not empty');
            assert.notEqual(term.value, '', 'The property value of the term ' + id + ' is not empty');
            assert.notEqual(term.description, '', 'The property description of the term ' + id + ' is not empty');

            assert.ok('string' === typeof term.exponent || null === term.exponent, 'The property exponent of the term ' + id + ' is null or string');
        });
    });

});
