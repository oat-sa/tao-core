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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Translation\Service;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\AbstractResource;
use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;
use oat\tao\model\Translation\Service\TranslatedIntoLanguagesSynchronizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TranslatedIntoLanguagesSynchronizerTest extends TestCase
{
    /** @var Ontology|MockObject */
    private Ontology $ontology;

    /** @var ResourceTranslationRepository|MockObject */
    private ResourceTranslationRepository $resourceTranslationRepository;

    private TranslatedIntoLanguagesSynchronizer $sut;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->resourceTranslationRepository = $this->createMock(ResourceTranslationRepository::class);

        $this->sut = new TranslatedIntoLanguagesSynchronizer($this->ontology, $this->resourceTranslationRepository);
    }

    public function testSyncOriginalResource(): void
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $originalResourceUriProperty = $this->createMock(core_kernel_classes_Property::class);
        $translatedIntoLanguagesProperty = $this->createMock(core_kernel_classes_Property::class);

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->withConsecutive(
                [TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI],
                [TaoOntology::PROPERTY_TRANSLATED_INTO_LANGUAGES]
            )
            ->willReturnOnConsecutiveCalls($originalResourceUriProperty, $translatedIntoLanguagesProperty);

        $resource
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->with($originalResourceUriProperty)
            ->willReturn(null);

        $this->ontology
            ->expects($this->never())
            ->method('getResource');

        $resource
            ->expects($this->once())
            ->method('removePropertyValues')
            ->with($translatedIntoLanguagesProperty);

        $resource
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('resourceUri');

        $abstractResource1 = $this->createMock(AbstractResource::class);
        $abstractResource2 = $this->createMock(AbstractResource::class);

        $resourceCollection = new ResourceCollection($abstractResource1, $abstractResource2);

        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn($resourceCollection);

        $abstractResource1
            ->expects($this->once())
            ->method('getLanguageCode')
            ->willReturn('fr-FR');

        $abstractResource2
            ->expects($this->once())
            ->method('getLanguageCode')
            ->willReturn('it-IT');

        $resource
            ->expects($this->exactly(2))
            ->method('setPropertyValue')
            ->withConsecutive(
                [$translatedIntoLanguagesProperty, TaoOntology::LANGUAGE_PREFIX . 'fr-FR'],
                [$translatedIntoLanguagesProperty, TaoOntology::LANGUAGE_PREFIX . 'it-IT'],
            );

        $this->sut->sync($resource);
    }

    public function testSyncTranslationResource(): void
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $originalResourceUriProperty = $this->createMock(core_kernel_classes_Property::class);
        $translatedIntoLanguagesProperty = $this->createMock(core_kernel_classes_Property::class);

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->withConsecutive(
                [TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI],
                [TaoOntology::PROPERTY_TRANSLATED_INTO_LANGUAGES]
            )
            ->willReturnOnConsecutiveCalls($originalResourceUriProperty, $translatedIntoLanguagesProperty);

        $resource
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->with($originalResourceUriProperty)
            ->willReturn('originalResourceUri');

        $originalResource = $this->createMock(core_kernel_classes_Resource::class);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('originalResourceUri')
            ->willReturn($originalResource);

        $originalResource
            ->expects($this->once())
            ->method('removePropertyValues')
            ->with($translatedIntoLanguagesProperty);

        $originalResource
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('originalResourceUri');

        $abstractResource1 = $this->createMock(AbstractResource::class);
        $abstractResource2 = $this->createMock(AbstractResource::class);

        $resourceCollection = new ResourceCollection($abstractResource1, $abstractResource2);

        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn($resourceCollection);

        $abstractResource1
            ->expects($this->once())
            ->method('getLanguageCode')
            ->willReturn('fr-FR');

        $abstractResource2
            ->expects($this->once())
            ->method('getLanguageCode')
            ->willReturn('it-IT');

        $originalResource
            ->expects($this->exactly(2))
            ->method('setPropertyValue')
            ->withConsecutive(
                [$translatedIntoLanguagesProperty, TaoOntology::LANGUAGE_PREFIX . 'fr-FR'],
                [$translatedIntoLanguagesProperty, TaoOntology::LANGUAGE_PREFIX . 'it-IT'],
            );

        $this->sut->sync($resource);
    }
}
