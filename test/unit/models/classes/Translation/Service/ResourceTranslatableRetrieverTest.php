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

namespace oat\tao\test\unit\model\Translation\Service;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Query\ResourceTranslatableQuery;
use oat\tao\model\Translation\Repository\ResourceTranslatableRepository;
use oat\tao\model\Translation\Service\ResourceTranslatableRetriever;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ResourceTranslatableRetrieverTest extends TestCase
{
    private ResourceTranslatableRetriever $sut;

    /** @var ResourceTranslatableRepository|MockObject */
    private $resourceTranslatableRepository;

    /** @var Ontology|MockObject */
    private $ontology;

    /** @var MockObject|ServerRequestInterface */
    private $request;

    public function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->resourceTranslatableRepository = $this->createMock(ResourceTranslatableRepository::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->sut = new ResourceTranslatableRetriever($this->ontology, $this->resourceTranslatableRepository);
    }

    public function testGetByRequest(): void
    {
        $this->request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(
                [
                    'id' => ['id'],
                ]
            );

        $this->mockResource([TaoOntology::CLASS_URI_ITEM], 'uniqueId');

        $result = new ResourceCollection();

        $this->resourceTranslatableRepository
            ->expects($this->once())
            ->method('find')
            ->with(
                new ResourceTranslatableQuery(
                    TaoOntology::CLASS_URI_ITEM,
                    ['uniqueId']
                )
            )
            ->willReturn($result);

        $this->assertSame($result, $this->sut->getByRequest($this->request));
    }

    public function testGetByRequestRequiresResourceId(): void
    {
        $this->request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn([]);

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage('Resource id is required');

        $this->sut->getByRequest($this->request);
    }

    public function testGetByRequestRequiresResourceType(): void
    {
        $this->request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(['id' => 'id']);

        $this->mockResource([], 'uniqueId');

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage('Resource id must have a resource type');

        $this->sut->getByRequest($this->request);
    }

    public function testGetByRequestRequiresUniqueId(): void
    {
        $this->request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(
                [
                    'id' => 'id',
                ]
            );

        $this->mockResource([TaoOntology::CLASS_URI_ITEM], '');

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage('Resource id must have a unique identifier');

        $this->sut->getByRequest($this->request);
    }

    private function mockResource(array $classIds, string $uniqueId): core_kernel_classes_Resource
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $resource
            ->method('getParentClassesIds')
            ->willReturn($classIds);

        $resource
            ->method('getOnePropertyValue')
            ->willReturn($uniqueId);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->willReturn($resource);

        $this->ontology
            ->method('getProperty')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));

        return $resource;
    }
}
