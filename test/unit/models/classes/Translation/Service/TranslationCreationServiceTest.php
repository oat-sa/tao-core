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

namespace oat\tao\test\unit\models\classes\Translation\Service;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\tao\model\Language\Business\Contract\LanguageRepositoryInterface;
use oat\tao\model\Language\Language;
use oat\tao\model\Language\LanguageCollection;
use oat\tao\model\OntologyClassService;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\ResourceTranslation;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Service\TranslationCreationService;
use oat\tao\model\Translation\Command\CreateTranslationCommand;
use oat\tao\model\Translation\Repository\ResourceTranslatableRepository;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;
use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\tao\model\Translation\Entity\ResourceTranslatable;
use oat\generis\model\data\Ontology;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TranslationCreationServiceTest extends TestCase
{
    private TranslationCreationService $service;

    /** @var Ontology|MockObject */
    private $ontology;

    /** @var ResourceTranslatableRepository|MockObject */
    private $resourceTranslatableRepository;

    /** @var ResourceTranslationRepository|MockObject */
    private $resourceTranslationRepository;

    /** @var LanguageRepositoryInterface|MockObject */
    private $languageRepository;

    /** @var OntologyClassService|MockObject */
    private $ontologyClassService;

    /** @var MockObject|LoggerInterface */
    private $logger;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->resourceTranslatableRepository = $this->createMock(ResourceTranslatableRepository::class);
        $this->resourceTranslationRepository = $this->createMock(ResourceTranslationRepository::class);
        $this->languageRepository = $this->createMock(LanguageRepositoryInterface::class);
        $this->ontologyClassService = $this->createMock(OntologyClassService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new TranslationCreationService(
            $this->ontology,
            $this->resourceTranslatableRepository,
            $this->resourceTranslationRepository,
            $this->languageRepository,
            $this->logger,
        );

        $this->service->setOntologyClassService(
            TaoOntology::CLASS_URI_ITEM,
            $this->ontologyClassService
        );
    }

    public function testCreate(): void
    {
        $resourceType = TaoOntology::CLASS_URI_ITEM;
        $uniqueId = 'id1';
        $languageUri = 'http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR';

        $languageProperty = $this->createMock(core_kernel_classes_Property::class);
        $translationTypeProperty = $this->createMock(core_kernel_classes_Property::class);
        $translationProgressProperty = $this->createMock(core_kernel_classes_Property::class);

        $resourceCollection = new ResourceCollection();
        $translatableCollection = new ResourceCollection($this->createMock(ResourceTranslatable::class));

        $language = $this->createMock(Language::class);
        $language
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn($languageUri);
        $language
            ->expects($this->once())
            ->method('getCode')
            ->willReturn('pt-BR');

        $instance = $this->createMock(core_kernel_classes_Resource::class);
        $instance
            ->expects($this->once())
            ->method('getTypes')
            ->willReturn([]);
        $instance
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('MyInstance');

        $clonedInstance = $this->createMock(core_kernel_classes_Resource::class);
        $clonedInstance
            ->expects($this->once())
            ->method('setLabel')
            ->with('MyInstance (pt-BR)');
        $clonedInstance
            ->expects($this->exactly(3))
            ->method('editPropertyValues')
            ->withConsecutive(
                [
                    $languageProperty,
                    $languageUri
                ],
                [
                    $translationTypeProperty,
                    TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION
                ],
                [
                    $translationProgressProperty,
                    TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_PENDING
                ]
            );

        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn($resourceCollection);

        $this->resourceTranslatableRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn($translatableCollection);

        $this->languageRepository
            ->expects($this->once())
            ->method('findAvailableLanguagesByUsage')
            ->willReturn(new LanguageCollection($language));

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->willReturn($instance);

        $this->ontology
            ->expects($this->exactly(3))
            ->method('getProperty')
            ->willReturnMap(
                [
                    [
                        TaoOntology::PROPERTY_LANGUAGE,
                        $languageProperty
                    ],
                    [
                        TaoOntology::PROPERTY_TRANSLATION_TYPE,
                        $translationTypeProperty
                    ],
                    [
                        TaoOntology::PROPERTY_TRANSLATION_PROGRESS,
                        $translationProgressProperty
                    ]
                ]
            );

        $this->ontologyClassService
            ->expects($this->once())
            ->method('cloneInstance')
            ->willReturn($clonedInstance);

        $this->assertInstanceOf(
            core_kernel_classes_Resource::class,
            $this->service->create(new CreateTranslationCommand($resourceType, $uniqueId, $languageUri))
        );
    }

    public function testCreateWillFailIfTranslationAlreadyExists(): void
    {
        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn(new ResourceCollection($this->createMock(ResourceTranslation::class)));

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage(
            'Translation already exists for [uniqueId=id1, locale=http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR]'
        );

        $this->service->create(
            new CreateTranslationCommand(
                TaoOntology::CLASS_URI_ITEM,
                'id1',
                'http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR'
            )
        );
    }

    public function testCreateWillFailIfResourceDoesNotExist(): void
    {
        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn(new ResourceCollection());

        $this->resourceTranslatableRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn(new ResourceCollection());

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage('There is not translatable resource for [uniqueId=id1]');

        $this->service->create(
            new CreateTranslationCommand(
                TaoOntology::CLASS_URI_ITEM,
                'id1',
                'http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR'
            )
        );
    }

    public function testCreateWillFailIfLanguageDoesNotExist(): void
    {
        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn(new ResourceCollection());

        $this->resourceTranslatableRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn(new ResourceCollection($this->createMock(ResourceTranslatable::class)));

        $this->languageRepository
            ->expects($this->once())
            ->method('findAvailableLanguagesByUsage')
            ->willReturn(new LanguageCollection());

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage('Language http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR does not exist');

        $this->service->create(
            new CreateTranslationCommand(
                TaoOntology::CLASS_URI_ITEM,
                'id1',
                'http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR'
            )
        );
    }
}
