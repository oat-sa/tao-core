<?php

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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\model\export;

use PHPUnit\Framework\TestCase;
use oat\tao\model\export\implementation\sql\ExportedColumn;
use oat\tao\model\export\implementation\sql\ExportedTable;

class ExportedTableTest extends TestCase
{
    private $typeMappingFixture = [
        'Test Taker ID' => ExportedColumn::TYPE_VARCHAR,
        'Compilation Time' => ExportedColumn::TYPE_INTEGER
    ];

    private $rowsFixture = [
        [
            'Test Taker ID' => 'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6e202ad4b5d184\'f67e22',
            'Compilation Time' => 1594828375,
            'Field Without Type' => 12345
        ],
        [
            'Test Taker ID' => 'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6e202ad4b5d43f67e24',
            'Compilation Time' => 1594828388,
            'Field Without Type' => 33333
        ]
    ];

    private $tableNameFixture = 'fixture_table_name';

    /**
     * @var ExportedTable
     */
    private $exportedTable;

    protected function setUp(): void
    {
        $this->exportedTable = new ExportedTable(
            $this->rowsFixture,
            $this->typeMappingFixture,
            $this->tableNameFixture
        );
    }

    public function testGetTableName()
    {
        $tableName = $this->exportedTable->getTableName();
        $this->assertEquals($tableName, $this->tableNameFixture);
    }

    public function testGetColumns()
    {
        $columns = $this->exportedTable->getColumns();

        $this->assertEquals($columns[0]->getName(), 'Test Taker ID');
        $this->assertEquals($columns[0]->getFormattedName(), ExportedColumn::PREFIX . 'test_taker_id');
        $this->assertEquals($columns[0]->getType(), ExportedColumn::TYPE_VARCHAR);
        $this->assertEquals(
            $columns[0]->getColumnCreatingString(),
            ExportedColumn::PREFIX . 'test_taker_id' . ' ' . ExportedColumn::TYPE_VARCHAR
        );

        $this->assertEquals($columns[1]->getName(), 'Compilation Time');
        $this->assertEquals($columns[1]->getFormattedName(), ExportedColumn::PREFIX . 'compilation_time');
        $this->assertEquals($columns[1]->getType(), ExportedColumn::TYPE_INTEGER);
        $this->assertEquals(
            $columns[1]->getColumnCreatingString(),
            ExportedColumn::PREFIX . 'compilation_time' . ' ' . ExportedColumn::TYPE_INTEGER
        );

        $this->assertEquals($columns[2]->getName(), 'Field Without Type');
        $this->assertEquals($columns[2]->getFormattedName(), ExportedColumn::PREFIX . 'field_without_type');
        $this->assertEquals($columns[2]->getType(), ExportedColumn::TYPE_VARCHAR);
        $this->assertEquals(
            $columns[2]->getColumnCreatingString(),
            ExportedColumn::PREFIX . 'field_without_type' . ' ' . ExportedColumn::TYPE_VARCHAR
        );
    }

    public function testRows()
    {
        $rows = $this->exportedTable->getRows();

        $row = $rows[0];
        $rowField = $row[0];

        $this->assertEquals($rowField->getColumn()->getName(), 'Test Taker ID');
        $this->assertEquals($rowField->getColumn()->getFormattedName(), ExportedColumn::PREFIX . 'test_taker_id');
        $this->assertEquals($rowField->getColumn()->getType(), ExportedColumn::TYPE_VARCHAR);
        $this->assertEquals(
            $rowField->getColumn()->getColumnCreatingString(),
            ExportedColumn::PREFIX . 'test_taker_id' . ' ' . ExportedColumn::TYPE_VARCHAR
        );

        $this->assertEquals(
            $rowField->getFormattedValue(),
            '\'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6e202ad4b5d184"f67e22\''
        );

        $row = $rows[0];
        $rowField = $row[1];

        $this->assertEquals($rowField->getColumn()->getName(), 'Compilation Time');
        $this->assertEquals($rowField->getColumn()->getFormattedName(), ExportedColumn::PREFIX . 'compilation_time');
        $this->assertEquals($rowField->getColumn()->getType(), ExportedColumn::TYPE_INTEGER);
        $this->assertEquals(
            $rowField->getColumn()->getColumnCreatingString(),
            ExportedColumn::PREFIX . 'compilation_time' . ' ' . ExportedColumn::TYPE_INTEGER
        );

        $this->assertEquals($rowField->getFormattedValue(), '1594828375');

        $row = $rows[0];
        $rowField = $row[2];

        $this->assertEquals($rowField->getColumn()->getName(), 'Field Without Type');
        $this->assertEquals($rowField->getColumn()->getFormattedName(), ExportedColumn::PREFIX . 'field_without_type');
        $this->assertEquals($rowField->getColumn()->getType(), ExportedColumn::TYPE_VARCHAR);
        $this->assertEquals(
            $rowField->getColumn()->getColumnCreatingString(),
            ExportedColumn::PREFIX . 'field_without_type' . ' ' . ExportedColumn::TYPE_VARCHAR
        );

        $this->assertEquals($rowField->getFormattedValue(), "'12345'");


        $row = $rows[1];
        $rowField = $row[0];

        $this->assertEquals($rowField->getColumn()->getName(), 'Test Taker ID');
        $this->assertEquals($rowField->getColumn()->getFormattedName(), ExportedColumn::PREFIX . 'test_taker_id');
        $this->assertEquals($rowField->getColumn()->getType(), ExportedColumn::TYPE_VARCHAR);
        $this->assertEquals(
            $rowField->getColumn()->getColumnCreatingString(),
            ExportedColumn::PREFIX . 'test_taker_id' . ' ' . ExportedColumn::TYPE_VARCHAR
        );

        $this->assertEquals(
            $rowField->getFormattedValue(),
            "'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6e202ad4b5d43f67e24'"
        );

        $row = $rows[1];
        $rowField = $row[1];

        $this->assertEquals($rowField->getColumn()->getName(), 'Compilation Time');
        $this->assertEquals($rowField->getColumn()->getFormattedName(), ExportedColumn::PREFIX . 'compilation_time');
        $this->assertEquals($rowField->getColumn()->getType(), ExportedColumn::TYPE_INTEGER);
        $this->assertEquals(
            $rowField->getColumn()->getColumnCreatingString(),
            ExportedColumn::PREFIX . 'compilation_time' . ' ' . ExportedColumn::TYPE_INTEGER
        );

        $this->assertEquals($rowField->getFormattedValue(), '1594828388');

        $row = $rows[1];
        $rowField = $row[2];

        $this->assertEquals($rowField->getColumn()->getName(), 'Field Without Type');
        $this->assertEquals($rowField->getColumn()->getFormattedName(), ExportedColumn::PREFIX . 'field_without_type');
        $this->assertEquals($rowField->getColumn()->getType(), ExportedColumn::TYPE_VARCHAR);
        $this->assertEquals(
            $rowField->getColumn()->getColumnCreatingString(),
            ExportedColumn::PREFIX . 'field_without_type' . ' ' . ExportedColumn::TYPE_VARCHAR
        );

        $this->assertEquals($rowField->getFormattedValue(), "'33333'");
    }
}
