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
use oat\tao\model\Translation\Entity\ResourceTranslation;
use oat\tao\model\Translation\Factory\ResourceTranslationFactory;
use oat\tao\model\Translation\Service\ResourceMetadataPopulateService;
use oat\tao\model\TaoOntology;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResourceTranslationFactoryTest extends TestCase
{
    private ResourceTranslationFactory $sut;

    /** @var ResourceMetadataPopulateService|MockObject */
    private $metadataPopulateService;

    /** @var core_kernel_classes_Resource|MockObject */
    private $resourceTranslatable;

    /** @var core_kernel_classes_Resource|MockObject */
    private $translationResource;

    protected function setUp(): void
    {
        $this->metadataPopulateService = $this->createMock(ResourceMetadataPopulateService::class);
        $this->resourceTranslatable = $this->createMock(core_kernel_classes_Resource::class);
        $this->translationResource = $this->createMock(core_kernel_classes_Resource::class);
        $this->sut = new ResourceTranslationFactory($this->metadataPopulateService);
    }

    public function testCreateReturnsResourceTranslation(): void
    {
        $originUri = 'http://example.com/origin';
        $translationUri = 'http://example.com/translation';
        $label = 'Test Resource';

        $this->resourceTranslatable->method('getUri')->willReturn($originUri);
        $this->translationResource->method('getUri')->willReturn($translationUri);
        $this->translationResource->method('getLabel')->willReturn($label);

        $this->metadataPopulateService
            ->expects($this->once())
            ->method('populate');

        $resource = $this->sut->create($this->resourceTranslatable, $this->translationResource);

        $this->assertInstanceOf(ResourceTranslation::class, $resource);
        $this->assertEquals($translationUri, $resource->getResourceUri());
        $this->assertEquals($label, $resource->getResourceLabel());
        $this->assertEquals($originUri, $resource->getOriginResourceUri());
        $this->assertContains(TaoOntology::PROPERTY_TRANSLATION_PROGRESS, $resource->getMetadataUris());
    }
}
