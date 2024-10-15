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

namespace oat\tao\test\unit\model\Translation\Factory;

use core_kernel_classes_Resource;
use oat\tao\model\Translation\Entity\ResourceTranslatable;
use oat\tao\model\Translation\Factory\ResourceTranslatableFactory;
use oat\tao\model\Translation\Service\ResourceMetadataPopulateService;
use oat\tao\model\TaoOntology;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResourceTranslatableFactoryTest extends TestCase
{
    private ResourceTranslatableFactory $sut;

    /** @var ResourceMetadataPopulateService|MockObject */
    private $metadataPopulateService;

    /** @var core_kernel_classes_Resource|MockObject */
    private $originResource;

    protected function setUp(): void
    {
        $this->metadataPopulateService = $this->createMock(ResourceMetadataPopulateService::class);
        $this->originResource = $this->createMock(core_kernel_classes_Resource::class);
        $this->sut = new ResourceTranslatableFactory($this->metadataPopulateService);
    }

    public function testCreateReturnsResourceTranslatable(): void
    {
        $uri = 'http://example.com/resource';
        $label = 'Test Resource';

        $this->originResource->method('getUri')->willReturn($uri);
        $this->originResource->method('getLabel')->willReturn($label);

        $this->metadataPopulateService
            ->expects($this->once())
            ->method('populate');

        $resource = $this->sut->create($this->originResource);

        $this->assertInstanceOf(ResourceTranslatable::class, $resource);
        $this->assertEquals($uri, $resource->getResourceUri());
        $this->assertEquals($label, $resource->getResourceLabel());
        $this->assertContains(TaoOntology::PROPERTY_TRANSLATION_STATUS, $resource->getMetadataUris());
    }
}
