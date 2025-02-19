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

use core_kernel_classes_Resource;
use oat\generis\model\resource\Contract\ResourceDeleterInterface;
use oat\oatbox\event\EventManager;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\ResourceTranslation;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;
use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\generis\model\data\Ontology;
use oat\tao\model\Translation\Service\TranslatedIntoLanguagesSynchronizer;
use oat\tao\model\Translation\Service\TranslationDeletionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class TranslationDeletionServiceTest extends TestCase
{
    private TranslationDeletionService $service;

    /** @var Ontology|MockObject */
    private $ontology;

    /** @var ResourceDeleterInterface|MockObject */
    private $resourceDeleter;

    /** @var ResourceTranslationRepository|MockObject */
    private $resourceTranslationRepository;

    /** @var MockObject|LoggerInterface */
    private $logger;

    /** @var TranslatedIntoLanguagesSynchronizer|MockObject  */
    private $translatedIntoLanguagesSynchronizer;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->resourceDeleter = $this->createMock(ResourceDeleterInterface::class);
        $this->resourceTranslationRepository = $this->createMock(ResourceTranslationRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->translatedIntoLanguagesSynchronizer = $this->createMock(TranslatedIntoLanguagesSynchronizer::class);

        $this->service = new TranslationDeletionService(
            $this->ontology,
            $this->resourceDeleter,
            $this->resourceTranslationRepository,
            $this->logger,
            $this->translatedIntoLanguagesSynchronizer,
            $this->createMock(EventManager::class)
        );
    }

    public function testDelete(): void
    {
        $resourceUri = 'id1';
        $languageUri = 'http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR';
        $translationResourceId = 'translationId1';

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(
                [
                    'id' => $translationResourceId,
                    'languageUri' => $languageUri,
                ]
            );

        $translation = new ResourceTranslation($translationResourceId, 'something');
        $translation->addMetadata(TaoOntology::PROPERTY_LANGUAGE, $languageUri, 'pt-BR');
        $resourceCollection = new ResourceCollection($translation);

        $translationResource = $this->createMock(core_kernel_classes_Resource::class);
        $originalResource = $this->createMock(core_kernel_classes_Resource::class);

        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn($resourceCollection);

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getResource')
            ->withConsecutive([$translationResourceId], [$translationResourceId])
            ->willReturnOnConsecutiveCalls($translationResource, $originalResource);

        $this->translatedIntoLanguagesSynchronizer
            ->expects($this->once())
            ->method('sync')
            ->with($originalResource);

        $this->assertSame($translationResource, $this->service->deleteByRequest($request));
    }


    public function testDeleteWithMissingIdParamWillThrowException(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(
                [
                    'languageUri' => 'http://www.tao.lu/Ontologies/TAO.rdf#Langpt-BR',
                ]
            );

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage('Resource id is required');

        $this->service->deleteByRequest($request);
    }

    public function testDeleteWithMissingLanguageParamWillThrowException(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(
                [
                    'id' => 'id1',
                ]
            );

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage('Parameter languageUri is mandatory');

        $this->service->deleteByRequest($request);
    }
}
