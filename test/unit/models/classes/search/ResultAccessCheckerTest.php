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
 * Copyright (c) 2021-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\search;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\model\data\permission\PermissionHelper;
use PHPUnit\Framework\TestCase;
use oat\tao\model\search\ResultAccessChecker;

class ResultAccessCheckerTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const SAMPLE_VALUES = [
        'id' => 'uri1',
        'label' => 'label1'
    ];

    private PermissionHelper|MockObject $permissionHelperMock;
    private ResultAccessChecker $subject;

    protected function setUp(): void
    {
        $this->permissionHelperMock = $this->createMock(PermissionHelper::class);
        $modelMock = $this->createMock(Ontology::class);
        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);
        $class = $this->createMock(core_kernel_classes_Class::class);

        $modelMock
            ->method('getResource')
            ->willReturn($resourceMock);

        $resourceMock
            ->method('getUri')
            ->willReturn('uri1');

        $resourceMock
            ->method('getLabel')
            ->willReturn('label');

        $modelMock
            ->method('getClass')
            ->willReturn($class);

        $resourceMock
            ->method('getTypes')
            ->willReturn([$class]);

        $class
            ->method('getParentClasses')
            ->willReturn(
                [
                    $class
                ]
            );

        $this->subject = new ResultAccessChecker();
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    PermissionHelper::class => $this->permissionHelperMock
                ]
            )
        );

        $this->subject->setModel($modelMock);
    }

    public function testHasReadAccess()
    {
        $this->permissionHelperMock
            ->method('filterByPermission')
            ->willReturn(
                [
                    'uri1',
                ]
            );

        $result = $this->subject->hasReadAccess(self::SAMPLE_VALUES);

        $this->assertTrue($result);
    }

    public function testHasNoReadAccess()
    {
        $this->permissionHelperMock
            ->method('filterByPermission')
            ->willReturn([]);

        $result = $this->subject->hasReadAccess(self::SAMPLE_VALUES);

        $this->assertFalse($result);
    }
}
