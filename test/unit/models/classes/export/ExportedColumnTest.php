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

class ExportedColumnTest extends TestCase
{
    private $fixtureColumnName = 'Test-&!@#$%^&*()coLumn NAME';

    private $expectedFormattedName = ExportedColumn::PREFIX . 'test_column_name';

    private $exportedColumn;

    protected function setUp(): void
    {
        $this->exportedColumn = new ExportedColumn($this->fixtureColumnName, ExportedColumn::TYPE_VARCHAR);
    }

    public function testGetFormattedName()
    {
        $formattedColumnName = $this->exportedColumn->getFormattedName();
        $this->assertEquals($formattedColumnName, $this->expectedFormattedName);
    }

    public function testGetColumnCreatingString()
    {
        $columnCreatingString = $this->exportedColumn->getColumnCreatingString();
        $this->assertEquals($columnCreatingString, $this->expectedFormattedName . ' ' . ExportedColumn::TYPE_VARCHAR);
    }

    public function testSetTypeAndGetType()
    {
        $type = $this->exportedColumn->getType();
        $this->assertEquals($type, ExportedColumn::TYPE_VARCHAR);
    }

    public function testGetName()
    {
        $name = $this->exportedColumn->getName();
        $this->assertEquals($name, $this->fixtureColumnName);
    }
}
