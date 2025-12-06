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
 * Copyright (c) 2022-2025 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\featureFlag\Repository;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use core_kernel_classes_Triple;
use oat\generis\model\data\Ontology;
use oat\tao\model\featureFlag\Repository\FeatureFlagRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class FeatureFlagRepositoryTest extends TestCase
{
    /** @var FeatureFlagRepository */
    private $subject;

    /** @var CacheInterface|MockObject */
    private $cache;

    /** @var Ontology|MockObject */
    private $ontology;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->subject = new FeatureFlagRepository(
            $this->ontology,
            $this->cache,
            [
                'FEATURE_FLAG_FROM_ENV' => true,
            ]
        );
    }

    public function testSaveFailsIfWrongFeatureFlagName(): void
    {
        $this->expectExceptionMessage('FeatureFlag name needs to start with "FEATURE_FLAG_"');

        $this->subject->save('FEATURE_FLASH', true);
    }

    public function testSaveAndDeleteCache(): void
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $resource->expects($this->once())
            ->method('editPropertyValues')
            ->with($property, 'true');

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('http://www.tao.lu/Ontologies/TAO.rdf#featureFlags')
            ->willReturn($resource);

        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->with('http://www.tao.lu/Ontologies/TAO.rdf#featureFlags_FEATURE_FLAG_NAME')
            ->willReturn($property);

        $this->cache
            ->expects($this->exactly(2))
            ->method('has')
            ->willReturn(true);

        $this->cache
            ->expects($this->exactly(2))
            ->method('delete');

        $this->subject->save('FEATURE_FLAG_NAME', true);
    }

    public function testList(): void
    {
        $triple1 = $this->createMock(core_kernel_classes_Triple::class);
        $triple1->predicate = 'FEATURE_FLAG_NAME';

        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $resource
            ->expects($this->once())
            ->method('getRdfTriples')
            ->willReturn([$triple1]);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('http://www.tao.lu/Ontologies/TAO.rdf#featureFlags')
            ->willReturn($resource);

        $this->cache
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(fn ($key) => match ($key) {
                'http://www.tao.lu/Ontologies/TAO.rdf#featureFlags_FEATURE_FLAG_NAME' => true,
                default => null,
            });

        $this->cache
            ->expects($this->once())
            ->method('set')
            ->with(
                'FEATURE_FLAG_LIST',
                [
                    'FEATURE_FLAG_NAME' => true,
                ]
            )
            ->willReturn(false);

        $this->assertSame(
            [
                'FEATURE_FLAG_NAME' => true,
                'FEATURE_FLAG_FROM_ENV' => true,
                'FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED' => false,
            ],
            $this->subject->list()
        );
    }

    public function testListFromCache(): void
    {
        $this->ontology
            ->expects($this->never())
            ->method('getResource');

        $this->cache
            ->expects($this->once())
            ->method('get')
            ->with('FEATURE_FLAG_LIST')
            ->willReturn(
                [
                    'FEATURE_FLAG_NAME' => true,
                ]
            );

        $this->assertSame(
            [
                'FEATURE_FLAG_NAME' => true,
                'FEATURE_FLAG_FROM_ENV' => true,
                'FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED' => false,

            ],
            $this->subject->list()
        );
    }

    public function testListMalformedCacheData(): void
    {
        $triple1 = $this->createMock(core_kernel_classes_Triple::class);
        $triple1->predicate = 'FEATURE_FLAG_NAME';

        $resource = $this->createMock(core_kernel_classes_Resource::class);

        // has() doesn't guarantee the key will be present on get()
        // and may introduce race conditions, ensure it is not used
        // when retrieving data
        $this->cache
            ->expects($this->never())
            ->method('has');

        $resource
            ->expects($this->once())
            ->method('getRdfTriples')
            ->willReturn([$triple1]);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('http://www.tao.lu/Ontologies/TAO.rdf#featureFlags')
            ->willReturn($resource);

        $this->cache
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(fn ($key) => match ($key) {
                'FEATURE_FLAG_LIST' => 'malformedData notAnArray',
                'http://www.tao.lu/Ontologies/TAO.rdf#featureFlags_FEATURE_FLAG_NAME' => true,
                default => null,
            });

        $this->assertSame(
            [
                'FEATURE_FLAG_NAME' => true,
                'FEATURE_FLAG_FROM_ENV' => true,
                'FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED' => false,
            ],
            $this->subject->list()
        );
    }

    public function testGetFromDbAndSaveCache(): void
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $resource
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn('true');

        $property = $this->createMock(core_kernel_classes_Property::class);

        // has() doesn't guarantee the key will be present on get()
        // and may introduce race conditions, ensure it is not used
        // when retrieving data
        $this->cache
            ->expects($this->never())
            ->method('has');

        $this->cache
            ->expects($this->once())
            ->method('set')
            ->willReturn(null);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('http://www.tao.lu/Ontologies/TAO.rdf#featureFlags')
            ->willReturn($resource);

        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->with('http://www.tao.lu/Ontologies/TAO.rdf#featureFlags_FEATURE_FLAG_NAME')
            ->willReturn($property);

        $this->assertSame(true, $this->subject->get('FEATURE_FLAG_NAME'));
    }

    public function testGetFromCache(): void
    {
        // has() doesn't guarantee the key will be present on get()
        // and may introduce race conditions, ensure it is not used
        // when retrieving data
        $this->cache
            ->expects($this->never())
            ->method('has');

        $this->cache
            ->expects($this->once())
            ->method('get')
            ->willReturn(true);

        $this->assertSame(true, $this->subject->get('FEATURE_FLAG_NAME'));
    }

    public function testGetFromEnvironment(): void
    {
        $this->assertSame(true, $this->subject->get('FEATURE_FLAG_FROM_ENV'));
    }

    public function testClearCache(): void
    {
        $triple1 = $this->createMock(core_kernel_classes_Triple::class);
        $triple1->predicate = 'predicate1';

        $triple2 = $this->createMock(core_kernel_classes_Triple::class);
        $triple2->predicate = 'predicate2';

        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $resource
            ->expects($this->once())
            ->method('getRdfTriples')
            ->willReturn([$triple1, $triple2]);

        $this->cache
            ->expects($this->exactly(3))
            ->method('has')
            ->willReturnCallback(fn ($key) => match ($key) {
                'predicate1', 'FEATURE_FLAG_LIST' => true,
                default => false,
            });

        $this->cache
            ->expects($this->exactly(2))
            ->method('delete');

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('http://www.tao.lu/Ontologies/TAO.rdf#featureFlags')
            ->willReturn($resource);

        $this->assertSame(1, $this->subject->clearCache());
    }
}
