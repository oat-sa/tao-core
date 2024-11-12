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

namespace oat\tao\test\unit\models\classes\Translation\Listener;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\AbstractResource;
use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\tao\model\Translation\Event\TranslationActionEvent;
use oat\tao\model\Translation\Listener\TranslationActionEventListener;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TranslationActionEventListenerTest extends TestCase
{
    /** @var Ontology|MockObject */
    private Ontology $ontology;

    /** @var ResourceTranslationRepository|MockObject */
    private ResourceTranslationRepository $resourceTranslationRepository;

    private TranslationActionEventListener $sut;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->resourceTranslationRepository = $this->createMock(ResourceTranslationRepository::class);

        $this->sut = new TranslationActionEventListener($this->ontology, $this->resourceTranslationRepository);
    }

    public function testPopulateTranslatedIntoLanguagesProperty(): void
    {
        $event = $this->createMock(TranslationActionEvent::class);

        $event
            ->expects($this->exactly(2))
            ->method('getResourceUri')
            ->willReturn('resourceUri');

        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('resourceUri')
            ->willReturn($resource);

        $property = $this->createMock(core_kernel_classes_Property::class);

        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->with(TaoOntology::PROPERTY_TRANSLATED_INTO_LANGUAGES)
            ->willReturn($property);

        $abstractResource1 = $this->createMock(AbstractResource::class);
        $abstractResource2 = $this->createMock(AbstractResource::class);

        $resourceCollection = new ResourceCollection($abstractResource1, $abstractResource2);

        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn($resourceCollection);

        $resource
            ->expects($this->once())
            ->method('removePropertyValues')
            ->with($property);

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
                [$property, TaoOntology::LANGUAGE_PREFIX . 'fr-FR'],
                [$property, TaoOntology::LANGUAGE_PREFIX . 'it-IT'],
            );

        $this->sut->populateTranslatedIntoLanguagesProperty($event);
    }
}
