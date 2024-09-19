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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Translation\Repository;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\ResourceTranslatable;
use oat\tao\model\Translation\Factory\ResourceTranslatableFactory;
use oat\tao\model\Translation\Query\ResourceTranslatableQuery;
use oat\tao\model\Translation\Repository\ResourceTranslatableRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResourceTranslatableRepositoryTest extends TestCase
{
    private ResourceTranslatableRepository $sut;

    /** @var Ontology|MockObject */
    private Ontology $ontology;

    /** @var ResourceTranslatableFactory|MockObject */
    private ResourceTranslatableFactory $factory;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->factory = $this->createMock(ResourceTranslatableFactory::class);

        $this->sut = new ResourceTranslatableRepository($this->ontology, $this->factory);
    }

    public function testFindReturnsResourceCollection(): void
    {
        $query = $this->createMock(ResourceTranslatableQuery::class);
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $property = $this->createMock(core_kernel_classes_Property::class);
        $translationType = $this->createMock(core_kernel_classes_Resource::class);
        $translatable = $this->createMock(ResourceTranslatable::class);

        $query
            ->method('getResourceUri')
            ->willReturn('resourceUri');

        $this->ontology
            ->method('getResource')
            ->with('resourceUri')
            ->willReturn($resource);

        $this->ontology
            ->method('getProperty')
            ->with(TaoOntology::PROPERTY_TRANSLATION_TYPE)
            ->willReturn($property);

        $resource
            ->method('getOnePropertyValue')
            ->with($property)
            ->willReturn($translationType);

        $translationType
            ->method('getUri')
            ->willReturn(TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL);

        $this->factory
            ->method('create')
            ->with($resource)
            ->willReturn($translatable);

        $result = $this->sut->find($query);

        $this->assertCount(1, $result);
        $this->assertContains($translatable, $result);
    }
}
