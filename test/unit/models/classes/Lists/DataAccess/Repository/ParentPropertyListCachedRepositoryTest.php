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

namespace oat\tao\test\unit\models\classes\Lists\DataAccess\Repository;

use core_kernel_classes_Property;
use InvalidArgumentException;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\cache\SimpleCache;
use oat\tao\model\Lists\DataAccess\Repository\DependencyRepository;
use oat\tao\model\Lists\DataAccess\Repository\ParentPropertyListCachedRepository;
use oat\tao\model\Lists\DataAccess\Repository\ParentPropertyListRepository;
use oat\tao\model\Lists\DataAccess\Repository\DependsOnPropertyRepository;
use PHPUnit\Framework\MockObject\MockObject;

class ParentPropertyListCachedRepositoryTest extends TestCase
{
    use ServiceManagerMockTrait;

    private ParentPropertyListCachedRepository $sut;
    private SimpleCache|MockObject $simpleCache;
    private ParentPropertyListRepository|MockObject $parentPropertyListRepository;
    private DependsOnPropertyRepository|MockObject $dependencyRepository;

    protected function setUp(): void
    {
        $this->simpleCache = $this->createMock(SimpleCache::class);
        $this->parentPropertyListRepository = $this->createMock(ParentPropertyListRepository::class);
        $this->dependencyRepository = $this->createMock(DependencyRepository::class);
        $this->sut = new ParentPropertyListCachedRepository();
        $this->sut->setServiceLocator(
            $this->getServiceManagerMock(
                [

                    SimpleCache::SERVICE_ID => $this->simpleCache,
                    ParentPropertyListRepository::class => $this->parentPropertyListRepository,
                    DependencyRepository::class => $this->dependencyRepository,
                ]
            )
        );
    }

    public function testDeleteCacheWithoutListUriWillThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('listUri is required to clear the cache');

        $this->sut->deleteCache([]);
    }

    public function testDeleteWithoutEmptyFilter(): void
    {
        $this->dependencyRepository
            ->expects($this->once())
            ->method('findChildListUris')
            ->willReturn(
                [
                    'childUri1'
                ]
            );

        $this->simpleCache
            ->expects($this->exactly(2))
            ->method('has')
            ->willReturn(true);

        $this->simpleCache
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn(['depends_on_property-prop1-childUri1']);

        $this->simpleCache
            ->expects($this->exactly(4))
            ->method('delete');

        $this->sut->deleteCache(
            [
                'listUri' => 'uri',
            ]
        );
    }

    public function testDelete(): void
    {
        $this->simpleCache
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $this->simpleCache
            ->expects($this->once())
            ->method('get')
            ->willReturn(['depends_on_property-prop1-childUri1']);

        $this->simpleCache
            ->expects($this->exactly(2))
            ->method('delete');

        $this->sut->deleteCache(
            [
                'listUri' => 'uri',
            ]
        );
    }

    public function testFindAllUris(): void
    {
        $uris = [
            'propertyUri1',
            'propertyUri2',
        ];

        $this->parentPropertyListRepository
            ->expects($this->once())
            ->method('findAllUris')
            ->willReturn($uris);

        $this->simpleCache
            ->expects($this->exactly(2))
            ->method('has')
            ->willReturn(false);

        $this->simpleCache
            ->expects($this->exactly(2))
            ->method('set');

        $this->assertSame(
            $uris,
            $this->sut->findAllUris(
                [
                    'listUri' => 'uri',
                    'property' => $this->createMock(core_kernel_classes_Property::class),
                ]
            )
        );
    }

    public function testFindAllUrisFromCache(): void
    {
        $uris = [
            'propertyUri1',
            'propertyUri2',
        ];

        $this->simpleCache
            ->expects($this->exactly(2))
            ->method('has')
            ->willReturn(true);

        $this->simpleCache
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn($uris);

        $this->assertSame(
            $uris,
            $this->sut->findAllUris(
                [
                    'listUri' => 'uri',
                    'property' => $this->createMock(core_kernel_classes_Property::class),
                ]
            )
        );
    }
}
