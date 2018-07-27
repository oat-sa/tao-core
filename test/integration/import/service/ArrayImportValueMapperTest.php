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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */
namespace oat\tao\test\integration\import\service;

use oat\tao\model\import\service\ArrayImportValueMapper;
use oat\tao\model\import\service\ImportValueMapperInterface;

class ArrayImportValueMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testMap()
    {
        $arrayImporter = new ArrayImportValueMapper([
            ArrayImportValueMapper::OPTION_DELIMITER => '|',
        ]);

        $this->assertEquals(['val1', 'val2', 'val3'], $arrayImporter->map('val1|val2|val3'));
    }

    public function testMapWithValueMapper()
    {
        $mapper = $this->getMockForAbstractClass(ImportValueMapperInterface::class);
        $mapper
            ->method('map')
            ->willReturnOnConsecutiveCalls('valueMappedInDB1','valueMappedInDB2');

        $arrayImporter = new ArrayImportValueMapper([
            ArrayImportValueMapper::OPTION_DELIMITER => '|',
            ArrayImportValueMapper::OPTION_VALUE_MAPPER => $mapper
        ]);

        $this->assertEquals(['valueMappedInDB1', 'valueMappedInDB2'], $arrayImporter->map('val1|val2'));
    }
}
