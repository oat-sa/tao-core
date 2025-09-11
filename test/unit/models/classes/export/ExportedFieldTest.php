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
use oat\tao\model\export\implementation\sql\ExportedField;

class ExportedFieldTest extends TestCase
{
    private $exportedColumn;

    protected function setUp(): void
    {
        $this->exportedColumn = new ExportedColumn('testColumnName', ExportedColumn::TYPE_VARCHAR);
    }

    public function testGetColumn()
    {
        $field = new ExportedField($this->exportedColumn, 'test value');
        $column = $field->getColumn();
        $this->assertEquals($column, $this->exportedColumn);
    }

    public function testGetFormattedValueNullOrEmptyValue()
    {
        $integerColumn = new ExportedColumn('testColumnName', ExportedColumn::TYPE_INTEGER);
        $integerTypeField = new ExportedField($integerColumn, '');
        $integerValue = $integerTypeField->getFormattedValue();
        $this->assertEquals($integerValue, 'null');

        $varcharColumn = new ExportedColumn('testColumnName', ExportedColumn::TYPE_VARCHAR);
        $varcharTypeField = new ExportedField($varcharColumn, '');
        $varcharValue = $varcharTypeField->getFormattedValue();
        $this->assertEquals($varcharValue, "''");

        $varcharColumn = new ExportedColumn('testColumnName', ExportedColumn::TYPE_VARCHAR);
        $varcharTypeField = new ExportedField($varcharColumn, null);
        $varcharValue = $varcharTypeField->getFormattedValue();
        $this->assertEquals($varcharValue, 'null');
    }

    public function testGetFormattedValue()
    {
        $varcharColumn = new ExportedColumn('testColumnName', ExportedColumn::TYPE_VARCHAR);
        $varcharTypeField = new ExportedField($varcharColumn, 'test value');
        $varcharValue = $varcharTypeField->getFormattedValue();
        $this->assertEquals($varcharValue, "'test value'");

        $decimalColumn = new ExportedColumn('testColumnName', ExportedColumn::TYPE_DECIMAL);
        $decimalTypeField = new ExportedField($decimalColumn, '1.1234');
        $decimalValue = $decimalTypeField->getFormattedValue();
        $this->assertEquals($decimalValue, "'1.1234'");

        $timestampColumn = new ExportedColumn('testColumnName', ExportedColumn::TYPE_TIMESTAMP);
        $timestampTypeField = new ExportedField($timestampColumn, '02/08/2020 11:12:11');
        $timestampValue = $timestampTypeField->getFormattedValue();
        $this->assertEquals($timestampValue, "'2020-08-02 11:12:11'");

        $integerColumn = new ExportedColumn('testColumnName', ExportedColumn::TYPE_INTEGER);
        $integerTypeField = new ExportedField($integerColumn, '1234');
        $integerValue = $integerTypeField->getFormattedValue();
        $this->assertEquals($integerValue, '1234');

        $booleanColumn = new ExportedColumn('testColumnName', ExportedColumn::TYPE_BOOLEAN);
        $booleanTypeField = new ExportedField($booleanColumn, true);
        $booleanValue = $booleanTypeField->getFormattedValue();
        $this->assertEquals($booleanValue, '1');
    }
}
