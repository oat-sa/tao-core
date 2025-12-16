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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\search;

use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\test\ServiceManagerMockTrait;
use oat\tao\model\search\SearchQuery;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\model\data\permission\PermissionHelper;
use oat\tao\model\search\ResultAccessChecker;
use oat\tao\model\search\ResultSet;
use oat\tao\model\search\ResultSetResponseNormalizer;
use PHPUnit\Framework\TestCase;

class ResultSetResponseNormalizerTest extends TestCase
{
    use ServiceManagerMockTrait;

    /** @var ResultSetResponseNormalizer */
    private $subject;

    /** @var PermissionHelper|MockObject */
    private $permissionHelperMock;

    /** @var SearchQuery|MockObject */
    private $searchQueryMock;

    /** @var ResultSet|MockObject */
    private $resultSetMock;

    /** @var Ontology|MockObject */
    private $modelMock;

    /** @var core_kernel_classes_Resource|MockObject */
    private $resourceMock;

    /** @var ResultAccessChecker|MockObject */
    private $resultAccessChecker;

    protected function setUp(): void
    {
        $this->permissionHelperMock = $this->createMock(PermissionHelper::class);
        $this->searchQueryMock = $this->createMock(SearchQuery::class);
        $this->resultSetMock = $this->createMock(ResultSet::class);
        $this->modelMock = $this->createMock(Ontology::class);
        $this->resourceMock = $this->createMock(core_kernel_classes_Resource::class);
        $this->resultAccessChecker = $this->createMock(ResultAccessChecker::class);

        $this->resourceMock
            ->method('getUri')
            ->willReturn('uri1');

        $this->resourceMock
            ->method('getLabel')
            ->willReturn('label');

        $this->modelMock
            ->method('getResource')
            ->willReturn($this->resourceMock);

        $this->resultSetMock
            ->method('getArrayCopy')
            ->willReturn(
                [
                    ['id' => 'uri1'],
                    ['id' => 'uri2'],
                ]
            );

        $this->subject = new ResultSetResponseNormalizer();
        $this->subject->setServiceManager(
            $this->getServiceManagerMock(
                [
                    PermissionHelper::class => $this->permissionHelperMock,
                    ResultAccessChecker::class => $this->resultAccessChecker
                ]
            )
        );

        $this->subject->setModel($this->modelMock);
    }

    public function testNormalizeWithAccessRestriction()
    {
        $this->resultSetMock
            ->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(100);

        $this->searchQueryMock
            ->method('getRows')
            ->willReturn(0);

        $this->permissionHelperMock
            ->expects($this->once())
            ->method('filterByPermission')
            ->willReturn(
                [
                    'READ' => 'uri1',
                    'WRITE' => 'uri2',
                ]
            );

        $this->searchQueryMock
            ->method('getPage')
            ->willReturn(1);

        $this->resultAccessChecker
            ->method('hasReadAccess')
            ->willReturn(false);

        $result = $this->subject->normalize($this->searchQueryMock, $this->resultSetMock, 'result');

        $this->assertSame(
            array(
                'data' => [
                    [
                        'label' => 'Access Denied',
                        'id' => 'uri1',
                    ],
                    [
                        'label' => 'Access Denied',
                        'id' => 'uri2',
                    ],
                ],
                'readonly' => [
                    'uri1' => true,
                    'uri2' => true,
                ],
                'success' => true,
                'page' => 1,
                'total' => 1,
                'totalCount' => 100,
                'records' => 2,
            ),
            $result
        );
    }

    public function testNormalizeWithPermissions()
    {
        $this->resultSetMock
            ->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(100);

        $this->searchQueryMock
            ->method('getRows')
            ->willReturn(0);

        $this->permissionHelperMock
            ->expects($this->once())
            ->method('filterByPermission')
            ->willReturn(
                [
                    'READ' => 'uri1',
                    'WRITE' => 'uri2',
                ]
            );

        $this->searchQueryMock
            ->method('getPage')
            ->willReturn(1);

        $this->resultAccessChecker
            ->method('hasReadAccess')
            ->willReturn(true);

        $result = $this->subject->normalize($this->searchQueryMock, $this->resultSetMock, 'result');

        $this->assertSame(
            array(
                'data' => [
                    [
                        'id' => 'uri1',
                    ],
                    [
                        'id' => 'uri2',
                    ],
                ],
                'readonly' => [
                    'uri1' => false,
                    'uri2' => false,
                ],
                'success' => true,
                'page' => 1,
                'total' => 1,
                'totalCount' => 100,
                'records' => 2,
            ),
            $result
        );
    }

    public function testNormalizeSafeClass(): void
    {
        $this->resultSetMock
            ->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(100);

        $this->searchQueryMock
            ->method('getRows')
            ->willReturn(0);

        $this->permissionHelperMock
            ->expects($this->once())
            ->method('filterByPermission')
            ->willReturn(
                [
                    'READ' => 'uri1',
                    'WRITE' => 'uri2',
                ]
            );

        $this->searchQueryMock
            ->method('getPage')
            ->willReturn(1);

        $this->resultAccessChecker
            ->expects($this->never())
            ->method('hasReadAccess');

        $result = $this->subject->normalizeSafeClass($this->searchQueryMock, $this->resultSetMock, 'result');

        $this->assertSame(
            [
                'data' => [
                    [
                        'id' => 'uri1',
                    ],
                    [
                        'id' => 'uri2',
                    ],
                ],
                'readonly' => [
                    'uri1' => false,
                    'uri2' => false,
                ],
                'success' => true,
                'page' => 1,
                'total' => 1,
                'totalCount' => 100,
                'records' => 2,
            ],
            $result
        );
    }
}
