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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */
/**
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'jquery',
    'ui/tableModel'
], function (_, $, tableModelFactory) {
    'use strict';

    var fixtureContainer = '#qunit-fixture';

    QUnit.module('Module');

    QUnit.test('Module export', function (assert) {
        QUnit.expect(1);

        assert.ok(typeof tableModelFactory === 'function', 'The module expose a function');
    });

    QUnit
        .cases([
            { title: 'getRowCells' },
            { title: 'getRowCount' },
            { title: 'getColCells' },
            { title: 'getColCount' },
            { title: 'update' }
        ])
        .test('Instance API', function (data, assert) {
            var instance = tableModelFactory($('<table>'));
            QUnit.expect(1);
            assert.ok(typeof instance[data.title] === 'function', 'instance implements ' + data.title);
        });

    QUnit
        .cases([
            { title: 'no table' },
            { title: 'not a table', table: $('<div>') }
        ])
        .test('Factory constructor with incorrect table reference', function (data, assert) {
            QUnit.expect(1);
            assert.throws(function() { tableModelFactory(data.table); }, TypeError, 'factory throws an exception if given an incorrect parameter');
        });



    QUnit.module('Rows');

    QUnit.test('.getRowCount()', function(assert) {
        var $table = $(fixtureContainer).find('table'),
            tableModel = tableModelFactory($table);

        assert.equal(tableModel.getRowCount(), 6, 'returns the correct number of rows');
    });

    QUnit
        .cases([
            { title: 'index 0 (header)', index: 0, length: 4, cell1: 'header r1.c1', cell2: 'header r1.c2', cell3: 'header r1.c3', cell4: 'header r1.c4' },
            { title: 'index 1 (body 1)', index: 1, length: 4, cell1: 'body r1.c1', cell2: 'body r1.c2', cell3: 'body r1.c3', cell4: 'body r1.c4' },
            { title: 'index 2 (body 2)', index: 2, length: 4, cell1: 'body r2.c1', cell2: 'body r2.c2', cell3: 'body r2.c3', cell4: 'body r2.c4' },
            { title: 'index 3 (body 3)', index: 3, length: 4, cell1: 'body r3.c1', cell2: 'body r3.c2', cell3: 'body r3.c3', cell4: 'body r3.c4' },
            { title: 'index 4 (body 4)', index: 4, length: 4, cell1: 'body r4.c1', cell2: 'body r4.c2', cell3: 'body r4.c3', cell4: 'body r4.c4' },
            { title: 'index 5 (footer)', index: 5, length: 4, cell1: 'footer r1.c1', cell2: 'footer r1.c2', cell3: 'footer r1.c3', cell4: 'footer r1.c4' },
            { title: 'index -1 (footer)', index: -1, length: 4, cell1: 'footer r1.c1', cell2: 'footer r1.c2', cell3: 'footer r1.c3', cell4: 'footer r1.c4' },
            { title: 'index -2 (body 4)', index: -2, length: 4, cell1: 'body r4.c1', cell2: 'body r4.c2', cell3: 'body r4.c3', cell4: 'body r4.c4' },
            { title: 'index -3 (body 3)', index: -3, length: 4, cell1: 'body r3.c1', cell2: 'body r3.c2', cell3: 'body r3.c3', cell4: 'body r3.c4' },
            { title: 'index -4 (body 2)', index: -4, length: 4, cell1: 'body r2.c1', cell2: 'body r2.c2', cell3: 'body r2.c3', cell4: 'body r2.c4' },
            { title: 'index -5 (body 1)', index: -5, length: 4, cell1: 'body r1.c1', cell2: 'body r1.c2', cell3: 'body r1.c3', cell4: 'body r1.c4' },
            { title: 'index -6 (header)', index: -6, length: 4, cell1: 'header r1.c1', cell2: 'header r1.c2', cell3: 'header r1.c3', cell4: 'header r1.c4' }
        ])
        .test('getRowCells() return cells of the given row index', function(data, assert) {
            var $table = $(fixtureContainer).find('table'),
                tableModel = tableModelFactory($table),
                row = tableModel.getRowCells(data.index);

            QUnit.expect(10);

            assert.ok(row instanceof $, 'getRowCells() returns an jQuery collection');

            assert.equal(row.length, data.length, 'result has the correct size');

            assert.ok(row.eq(0) instanceof $, 'cell 1 is a jQuery Element');
            assert.ok(row.eq(1) instanceof $, 'cell 2 is a jQuery Element');
            assert.ok(row.eq(2) instanceof $, 'cell 3 is a jQuery Element');
            assert.ok(row.eq(3) instanceof $, 'cell 4 is a jQuery Element');

            assert.equal(row.eq(0).html(), data.cell1, 'cell 1 has the correct content');
            assert.equal(row.eq(1).html(), data.cell2, 'cell 2 has the correct content');
            assert.equal(row.eq(2).html(), data.cell3, 'cell 3 has the correct content');
            assert.equal(row.eq(3).html(), data.cell4, 'cell 4 has the correct content');
        });

    QUnit
        .cases([
            { title: 'no index' },
            { title: 'outOfRange', index: -7 },
            { title: 'outOfRange', index: 6 },
            { title: 'NaN', index: NaN },
            { title: 'string', index: 'invalid' }
        ])
        .test('.getRowCells() returns empty jQuery Collection with invalid index', function(data, assert) {
            var $table = $(fixtureContainer).find('table'),
                tableModel = tableModelFactory($table),
                row = tableModel.getRowCells(data.index);

            QUnit.expect(2);

            assert.ok(row instanceof $, 'getRowCells() returns a jQuery collection');
            assert.equal(row.length, 0, 'collection has the correct size');
        });


    QUnit.module('Columns');

    QUnit.test('.getColCount()', function(assert) {
        var $table = $(fixtureContainer).find('table'),
            tableModel = tableModelFactory($table);

        QUnit.expect(1);

        assert.equal(tableModel.getColCount(), 4, 'returns the correct number of columns');
    });

    QUnit
        .cases([
            { title: 'index 0 (column 1)', index: 0, length: 6, cell1: 'header r1.c1', cell2: 'body r1.c1', cell3: 'body r2.c1', cell4: 'body r3.c1', cell5: 'body r4.c1', cell6: 'footer r1.c1' },
            { title: 'index 1 (column 2)', index: 1, length: 6, cell1: 'header r1.c2', cell2: 'body r1.c2', cell3: 'body r2.c2', cell4: 'body r3.c2', cell5: 'body r4.c2', cell6: 'footer r1.c2' },
            { title: 'index 2 (column 3)', index: 2, length: 6, cell1: 'header r1.c3', cell2: 'body r1.c3', cell3: 'body r2.c3', cell4: 'body r3.c3', cell5: 'body r4.c3', cell6: 'footer r1.c3' },
            { title: 'index 3 (column 4)', index: 3, length: 6, cell1: 'header r1.c4', cell2: 'body r1.c4', cell3: 'body r2.c4', cell4: 'body r3.c4', cell5: 'body r4.c4', cell6: 'footer r1.c4' },
            { title: 'index -1 (column 4)', index: -1, length: 6, cell1: 'header r1.c4', cell2: 'body r1.c4', cell3: 'body r2.c4', cell4: 'body r3.c4', cell5: 'body r4.c4', cell6: 'footer r1.c4' },
            { title: 'index -2 (column 3)', index: -2, length: 6, cell1: 'header r1.c3', cell2: 'body r1.c3', cell3: 'body r2.c3', cell4: 'body r3.c3', cell5: 'body r4.c3', cell6: 'footer r1.c3' },
            { title: 'index -3 (column 2)', index: -3, length: 6, cell1: 'header r1.c2', cell2: 'body r1.c2', cell3: 'body r2.c2', cell4: 'body r3.c2', cell5: 'body r4.c2', cell6: 'footer r1.c2' },
            { title: 'index -4 (column 1)', index: -4, length: 6, cell1: 'header r1.c1', cell2: 'body r1.c1', cell3: 'body r2.c1', cell4: 'body r3.c1', cell5: 'body r4.c1', cell6: 'footer r1.c1' }
        ])
        .test('getColCells() return cells of the given column index', function(data, assert) {
            var $table = $(fixtureContainer).find('table'),
                tableModel = tableModelFactory($table),
                $column = tableModel.getColCells(data.index);

            QUnit.expect(14);

            assert.ok($column instanceof $, 'getColCells() returns an jQuery collection');

            assert.equal($column.length, data.length, 'collection has the correct size');

            assert.ok($column.eq(0) instanceof $, 'cell 1 is a jQuery Element');
            assert.ok($column.eq(1) instanceof $, 'cell 2 is a jQuery Element');
            assert.ok($column.eq(2) instanceof $, 'cell 3 is a jQuery Element');
            assert.ok($column.eq(3) instanceof $, 'cell 4 is a jQuery Element');
            assert.ok($column.eq(4) instanceof $, 'cell 4 is a jQuery Element');
            assert.ok($column.eq(5) instanceof $, 'cell 4 is a jQuery Element');

            assert.equal($column.eq(0).html(), data.cell1, 'cell 1 has the correct content');
            assert.equal($column.eq(1).html(), data.cell2, 'cell 2 has the correct content');
            assert.equal($column.eq(2).html(), data.cell3, 'cell 3 has the correct content');
            assert.equal($column.eq(3).html(), data.cell4, 'cell 4 has the correct content');
            assert.equal($column.eq(4).html(), data.cell5, 'cell 5 has the correct content');
            assert.equal($column.eq(5).html(), data.cell6, 'cell 6 has the correct content');
        });

    QUnit
        .cases([
            { title: 'no index' },
            { title: 'outOfRange (-5)', index: -5 },
            { title: 'outOfRange (4)', index: 4 },
            { title: 'NaN', index: NaN },
            { title: 'string', index: 'invalid' }
        ])
        .test('.getColCells() returns empty jQuery Collection with invalid index', function(data, assert) {
            var $table = $(fixtureContainer).find('table'),
                tableModel = tableModelFactory($table),
                column = tableModel.getColCells(data.index);

            QUnit.expect(2);

            assert.ok(column instanceof $, 'getColCells() returns a jQuery collection');
            assert.equal(column.length, 0, 'collection has the correct size');
        });
});