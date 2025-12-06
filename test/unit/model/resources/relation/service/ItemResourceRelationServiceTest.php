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
 * Copyright (c) 2020-2025 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\model\resources\relation\service;

use core_kernel_classes_Class;
use oat\generis\model\data\Ontology;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\resources\relation\exception\NestedClassLimitExceededException;
use oat\tao\model\resources\relation\FindAllQuery;
use oat\tao\model\resources\relation\service\ItemResourceRelationService;
use PHPUnit\Framework\MockObject\MockObject;

class ItemResourceRelationServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    private ItemResourceRelationService $subject;
    private Ontology|MockObject $ontology;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->subject = new ItemResourceRelationService(
            [
                ItemResourceRelationService::OPTION_NESTED_CLASS_LIMIT => 0
            ]
        );
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    Ontology::SERVICE_ID => $this->ontology,
                ]
            )
        );
    }

    public function testGetRelations(): void
    {
        $this->assertCount(
            0,
            $this->subject->findRelations(new FindAllQuery('itemId'))->getIterator()->getArrayCopy()
        );
    }

    public function testGetRelationsWithNestedClassLimitExceededWillThrowException(): void
    {
        $this->expectException(NestedClassLimitExceededException::class);

        $class = $this->createMock(core_kernel_classes_Class::class);

        $class->method('getSubClasses')
            ->willReturn([1]);

        $this->ontology
            ->method('getClass')
            ->willReturn($class);

        $this->subject->findRelations(new FindAllQuery(null, 'classId'));
    }
}
