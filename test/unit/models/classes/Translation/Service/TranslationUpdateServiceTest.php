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
use oat\generis\model\data\Ontology;
use oat\oatbox\event\EventManager;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Command\UpdateTranslationCommand;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Service\TranslatedIntoLanguagesSynchronizer;
use oat\tao\model\Translation\Service\TranslationUpdateService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TranslationUpdateServiceTest extends TestCase
{
    private TranslationUpdateService $service;

    /** @var Ontology|MockObject */
    private $ontology;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var TranslatedIntoLanguagesSynchronizer|MockObject  */
    private $translatedIntoLanguagesSynchronizer;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->translatedIntoLanguagesSynchronizer = $this->createMock(TranslatedIntoLanguagesSynchronizer::class);

        $this->service = new TranslationUpdateService(
            $this->ontology,
            $this->logger,
            $this->translatedIntoLanguagesSynchronizer,
            $this->createMock(EventManager::class)
        );
    }

    public function testUpdateSuccess(): void
    {
        $typeProperty = $this->createMock(core_kernel_classes_Property::class);
        $progressProperty = $this->createMock(core_kernel_classes_Property::class);
        $originalResourceUriProperty = $this->createMock(core_kernel_classes_Property::class);
        $languageProperty = $this->createMock(core_kernel_classes_Property::class);

        $translationType = $this->createMock(core_kernel_classes_Resource::class);
        $translationType
            ->method('getUri')
            ->willReturn(TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION);

        $translationProgress = $this->createMock(core_kernel_classes_Resource::class);
        $translationProgress
            ->method('getUri')
            ->willReturn(TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATING);

        $originalResourceUri = $this->createMock(core_kernel_classes_Resource::class);
        $originalResourceUri
            ->method('getUri')
            ->willReturn('originalResourceUri');

        $translationLanguage = $this->createMock(core_kernel_classes_Resource::class);
        $translationLanguage
            ->method('getUri')
            ->willReturn(TaoOntology::LANGUAGE_PREFIX . 'en-US');

        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $resource
            ->method('getOnePropertyValue')
            ->withConsecutive(
                [$typeProperty],
                [$progressProperty],
                [$originalResourceUriProperty],
                [$languageProperty]
            )
            ->willReturnOnConsecutiveCalls(
                $translationType,
                $translationProgress,
                $originalResourceUri,
                $translationLanguage
            );

        $resource
            ->method('exists')
            ->willReturn(true);

        $resource
            ->expects($this->once())
            ->method('editPropertyValues')
            ->with(
                $progressProperty,
                TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATED
            );

        $this->ontology
            ->method('getResource')
            ->willReturn($resource);

        $this->ontology
            ->method('getProperty')
            ->willReturnMap(
                [
                    [
                        TaoOntology::PROPERTY_TRANSLATION_TYPE,
                        $typeProperty
                    ],
                    [
                        TaoOntology::PROPERTY_TRANSLATION_PROGRESS,
                        $progressProperty
                    ],
                    [
                        TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI,
                        $originalResourceUriProperty
                    ],
                    [
                        TaoOntology::PROPERTY_LANGUAGE,
                        $languageProperty
                    ],
                ]
            );

        $this->translatedIntoLanguagesSynchronizer
            ->expects($this->once())
            ->method('sync')
            ->with($resource);

        $this->assertSame(
            $resource,
            $this->service->update(
                new UpdateTranslationCommand(
                    'http://example.com/resource/1',
                    TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATED
                )
            )
        );
    }

    public function testOriginalCannotBeUpdated(): void
    {
        $typeProperty = $this->createMock(core_kernel_classes_Property::class);

        $translationType = $this->createMock(core_kernel_classes_Resource::class);
        $translationType
            ->method('getUri')
            ->willReturn(TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL);

        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $resource
            ->method('getOnePropertyValue')
            ->willReturn($translationType);

        $resource
            ->method('exists')
            ->willReturn(true);

        $this->ontology
            ->method('getResource')
            ->willReturn($resource);

        $this->ontology
            ->method('getProperty')
            ->willReturnMap(
                [
                    [
                        TaoOntology::PROPERTY_TRANSLATION_TYPE,
                        $typeProperty
                    ]
                ]
            );

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage('Resource http://example.com/resource/1 is not a translation');

        $this->service->update(
            new UpdateTranslationCommand(
                'http://example.com/resource/1',
                TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATED
            )
        );
    }
}
