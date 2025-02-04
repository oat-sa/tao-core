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

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\oatbox\event\EventManager;
use oat\tao\model\Language\Business\Contract\LanguageRepositoryInterface;
use oat\tao\model\Language\Language;
use oat\tao\model\Language\LanguageCollection;
use oat\tao\model\OntologyClassService;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\ResourceTransferInterface;
use oat\tao\model\resources\ResourceTransferResult;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Service\TranslatedIntoLanguagesSynchronizer;
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
    private $resourceTransfer;

    /** @var MockObject|LoggerInterface */
    private $logger;

    /** @var TranslatedIntoLanguagesSynchronizer|MockObject  */
    private $translatedIntoLanguagesSynchronizer;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->resourceTranslatableRepository = $this->createMock(ResourceTranslatableRepository::class);
        $this->resourceTranslationRepository = $this->createMock(ResourceTranslationRepository::class);
        $this->languageRepository = $this->createMock(LanguageRepositoryInterface::class);
        $this->resourceTransfer = $this->createMock(ResourceTransferInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->translatedIntoLanguagesSynchronizer = $this->createMock(TranslatedIntoLanguagesSynchronizer::class);

        $this->service = new TranslationCreationService(
            $this->ontology,
            $this->resourceTranslatableRepository,
            $this->resourceTranslationRepository,
            $this->languageRepository,
            $this->logger,
            $this->translatedIntoLanguagesSynchronizer,
            $this->createMock(EventManager::class)
        );

        $this->service->setResourceTransfer(TaoOntology::CLASS_URI_ITEM, $this->resourceTransfer);
    }

    public function testCreate(): void
    {
        $resourceUri = 'id1';
        $languageUri = 'http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR';

        $translatable = $this->createMock(ResourceTranslatable::class);

        $resourceType = $this->createMock(core_kernel_classes_Class::class);
        $languageProperty = $this->createMock(core_kernel_classes_Property::class);
        $translationTypeProperty = $this->createMock(core_kernel_classes_Property::class);
        $translationProgressProperty = $this->createMock(core_kernel_classes_Property::class);
        $originalResourceUriProperty = $this->createMock(core_kernel_classes_Property::class);
        $resourceTransferResult = new ResourceTransferResult('cloneUri');

        $resourceCollection = new ResourceCollection();
        $translatableCollection = new ResourceCollection($translatable);

        $translatable
            ->method('isReadyForTranslation')
            ->willReturn(true);

        $language = $this->createMock(Language::class);
        $language
            ->expects($this->exactly(3))
            ->method('getUri')
            ->willReturn($languageUri);
        $language
            ->expects($this->exactly(2))
            ->method('getCode')
            ->willReturn('pt-BR');

        $instance = $this->createMock(core_kernel_classes_Resource::class);
        $instance
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('MyInstance');
        $instance
            ->expects($this->once())
            ->method('getParentClassId')
            ->willReturn(TaoOntology::CLASS_URI_ITEM);
        $instance
            ->expects($this->once())
            ->method('getRootId')
            ->willReturn(TaoOntology::CLASS_URI_ITEM);

        $clonedInstance = $this->createMock(core_kernel_classes_Resource::class);
        $clonedInstance
            ->expects($this->once())
            ->method('setLabel')
            ->with('MyInstance (pt-BR)');
        $clonedInstance
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
                ],
                [
                    $originalResourceUriProperty,
                    $resourceUri
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
            ->expects($this->exactly(2))
            ->method('getResource')
            ->willReturnOnConsecutiveCalls(
                $instance,
                $clonedInstance
            );

        $this->ontology
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
                    ],
                    [
                        TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI,
                        $originalResourceUriProperty
                    ]
                ]
            );

        $this->resourceTransfer
            ->expects($this->once())
            ->method('transfer')
            ->with(
                new ResourceTransferCommand(
                    'id1',
                    TaoOntology::CLASS_URI_ITEM,
                    null,
                    null
                )
            )
            ->willReturn($resourceTransferResult);

        $this->translatedIntoLanguagesSynchronizer
            ->expects($this->once())
            ->method('sync')
            ->with($instance);

        $this->assertInstanceOf(
            core_kernel_classes_Resource::class,
            $this->service->create(new CreateTranslationCommand($resourceUri, $languageUri))
        );
    }

    public function testCreateWillFailIfTranslationAlreadyExists(): void
    {
        $translatable = $this->createMock(ResourceTranslatable::class);

        $translatable
            ->method('isReadyForTranslation')
            ->willReturn(true);

        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn(new ResourceCollection($translatable));

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage(
            'Translation already exists for [id=id1, locale=http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR]'
        );

        $this->service->create(
            new CreateTranslationCommand(
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
        $this->expectExceptionMessage('Resource [id=id1] is not translatable');

        $this->service->create(
            new CreateTranslationCommand(
                'id1',
                'http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR'
            )
        );
    }

    public function testCreateWillFailIfLanguageDoesNotExist(): void
    {
        $translatable = $this->createMock(ResourceTranslatable::class);

        $translatable
            ->method('isReadyForTranslation')
            ->willReturn(true);

        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn(new ResourceCollection());

        $this->resourceTranslatableRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn(new ResourceCollection($translatable));

        $this->languageRepository
            ->expects($this->once())
            ->method('findAvailableLanguagesByUsage')
            ->willReturn(new LanguageCollection());

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage('Language http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR does not exist');

        $this->service->create(
            new CreateTranslationCommand(
                'id1',
                'http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR'
            )
        );
    }

    public function testCreateWillFailIfResourceIsNotReadyForTranslation(): void
    {
        $translatable = $this->createMock(ResourceTranslatable::class);

        $translatable
            ->method('isReadyForTranslation')
            ->willReturn(false);

        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn(new ResourceCollection());

        $this->resourceTranslatableRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn(new ResourceCollection($translatable));

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage('Resource [id=id1] is not ready for translation');

        $this->service->create(
            new CreateTranslationCommand(
                'id1',
                'http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR'
            )
        );
    }
}
