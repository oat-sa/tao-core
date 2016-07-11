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
 * Copyright (c) 2016  (original work) Open Assessment Technologies SA;
 * 
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\tao\test\export;


use oat\tao\model\export\JsonLdExport;
use oat\tao\test\TaoPhpUnitTestRunner;

class JsonLdExportTest extends TaoPhpUnitTestRunner
{
    public function testMissingResource()
    {
        $id = '#undefinedInDbId';
        $resource = new \core_kernel_classes_Resource($id);
        $export = new JsonLdExport($resource);
        
        $this->assertEquals(['@context' => [], '@id' => $id, '@type' => '#missing'], $export->jsonSerialize());
    }
    
    public function testExistingResource()
    {
        $id = '#definedInDbId';

        $resource = $this->prophesize(\core_kernel_classes_Resource::class);
        $collection = $this->prophesize(\core_kernel_classes_ContainerCollection::class);
        $collection->toArray()->willReturn([]);
        $resource->getRdfTriples()->willReturn($collection->reveal());
        $resource->getUri()->willReturn($id);
        $resource->getTypes()->willReturn([]);
        $resource->exists()->willReturn(true);

        $export = new JsonLdExport($resource->reveal());
        $this->assertEquals(['@context' => [], '@id' => $id], $export->jsonSerialize());

    }
}
