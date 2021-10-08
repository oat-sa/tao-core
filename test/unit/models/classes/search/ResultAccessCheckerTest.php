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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\search;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\model\data\permission\PermissionHelper;
use oat\generis\test\TestCase;
use oat\tao\model\search\ResultAccessChecker;

class ResultAccessCheckerTest extends TestCase
{
    /** @var PermissionHelper|MockObject */
    private $permissionHelperMock;

    /** @var Ontology|MockObject */
    private $modelMock;

    /** @var core_kernel_classes_Resource|MockObject */
    private $resourceMock;

    /** @var core_kernel_classes_Class|MockObject */
    private $class;

    public function setUp(): void
    {
        $this->permissionHelperMock = $this->createMock(PermissionHelper::class);
        $this->modelMock = $this->createMock(Ontology::class);
        $this->resourceMock = $this->createMock(core_kernel_classes_Resource::class);
        $this->class = $this->createMock(core_kernel_classes_Class::class);

        $this->modelMock
            ->method('getResource')
            ->willReturn($this->resourceMock);
        
        $this->resourceMock
            ->method('getUri')
            ->willReturn('uri1');

        $this->resourceMock
            ->method('getLabel')
            ->willReturn('label');

        $this->modelMock
            ->method('getClass')
            ->willReturn($this->class);

        $this->resourceMock
            ->method('getTypes')
            ->willReturn([$this->class]);

        $this->class
            ->method('getParentClasses')
            ->willReturn(
                [
                    $this->class
                ]
            );

        $this->subject = new ResultAccessChecker();

        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    PermissionHelper::class => $this->permissionHelperMock
                ]
            )
        );

        $this->subject->setModel($this->modelMock);
    }

    public function testHasReadAccess()
    {
        $this->getSampleValues();

        $this->permissionHelperMock
            ->method('filterByPermission')
            ->willReturn(
                [
                    'uri1',
                ]
            );

        $result = $this->subject->hasReadAccess($this->result, $this->permissionHelperMock);

        $this->assertTrue($result);
    }

    public function testHasNoReadAccess()
    {
        $this->getSampleValues();

        $this->permissionHelperMock
            ->method('filterByPermission')
            ->willReturn([]);

        $result = $this->subject->hasReadAccess($this->result, $this->permissionHelperMock);

        $this->assertFalse($result);
    }

    private function getSampleValues(): array
    {
        return $this->result = [
            [
                'id' => 'uri1',
                'label' => 'label1'
            ]
        ];
    }
}
