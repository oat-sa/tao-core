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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Translation\Service;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\Translation\Entity\ResourceTranslation;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;
use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\generis\model\data\Ontology;
use oat\tao\model\Translation\Service\TranslationMoveService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TranslationMoveServiceTest extends TestCase
{
    private TranslationMoveService $sut;
    private Ontology $ontology;
    private ResourceTranslationRepository $resourceTranslationRepository;
    private LoggerInterface $logger;
    private ResourceTransferCommand $transferCommand;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->resourceTranslationRepository = $this->createMock(ResourceTranslationRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->transferCommand = new ResourceTransferCommand('fromInstanceUri', 'toClassUri', null, null);

        $this->sut = new TranslationMoveService(
            $this->ontology,
            $this->resourceTranslationRepository,
            $this->logger
        );
    }

    public function testMoveTranslations(): void
    {
        $destinationClass = $this->createMock(core_kernel_classes_Class::class);
        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->with('toClassUri')
            ->willReturn($destinationClass);

        $translatedResourceURI = 'translatedItemUri';
        $translation = new ResourceTranslation($translatedResourceURI, 'label');
        $resourceCollection = new ResourceCollection($translation);

        $translationResource = $this->createMock(core_kernel_classes_Resource::class);

        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn($resourceCollection);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with($translatedResourceURI)
            ->willReturn($translationResource);

        $fromClass = $this->createMock(core_kernel_classes_Class::class);
        $translationResource
            ->expects($this->once())
            ->method('getTypes')
            ->willReturn([$fromClass]);

        $translationResource
            ->expects($this->once())
            ->method('removeType')
            ->with($fromClass);

        $translationResource
            ->expects($this->once())
            ->method('setType')
            ->with($destinationClass);

        $this->sut->moveTranslations($this->transferCommand);
    }

    public function testMoveTranslationsNoTranslationsFound(): void
    {
        $destinationClass = $this->createMock(core_kernel_classes_Class::class);
        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->with('toClassUri')
            ->willReturn($destinationClass);

        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn(new ResourceCollection());

        $this->ontology
            ->expects($this->never())
            ->method('getResource');

        $this->sut->moveTranslations($this->transferCommand);
    }

    public function testMoveTranslationsLogsError(): void
    {
        $destinationClass = $this->createMock(core_kernel_classes_Class::class);
        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->with('toClassUri')
            ->willReturn($destinationClass);

        $this->ontology
            ->expects($this->never())
            ->method('getResource');

        $errorMessage = 'errorMessage';
        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willThrowException(new ResourceTranslationException($errorMessage));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->callback(function ($arg) use ($errorMessage): bool {
                $this->assertStringContainsString($errorMessage, $arg);
                return true;
            }));

        $this->sut->moveTranslations($this->transferCommand);
    }
}
