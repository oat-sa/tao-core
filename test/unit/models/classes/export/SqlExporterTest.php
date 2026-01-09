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
use oat\tao\model\export\implementation\sql\SqlCreator;
use oat\tao\model\export\implementation\SqlExporter;

class SqlExporterTest extends TestCase
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

    public function testExport()
    {
        $exporter = new SqlExporter($this->rowsFixture, $this->typeMappingFixture, $this->tableNameFixture);
        $sqlData = $exporter->export();

        $sqlExpected = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'sqlFixture.sql');
        $this->assertEquals($sqlData, $sqlExpected);
    }
}
