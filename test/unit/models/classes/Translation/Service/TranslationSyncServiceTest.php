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
use oat\tao\model\Translation\Service\TranslationSyncService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use stdClass;

class TranslationSyncServiceTest extends TestCase
{
    /** @var Ontology|MockObject */
    private Ontology $ontology;

    /** @var ResourceTranslationRepository|MockObject */
    private ResourceTranslationRepository $resourceTranslationRepository;

    /** @var LoggerInterface|MockObject */
    private LoggerInterface $logger;

    /** @var TranslatedIntoLanguagesSynchronizer|MockObject  */
    private TranslatedIntoLanguagesSynchronizer $translatedIntoLanguagesSynchronizer;

    private TranslationSyncService $sut;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->resourceTranslationRepository = $this->createMock(ResourceTranslationRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->translatedIntoLanguagesSynchronizer = $this->createMock(TranslatedIntoLanguagesSynchronizer::class);

        $this->sut = new TranslationSyncService(
            $this->ontology,
            $this->resourceTranslationRepository,
            $this->logger,
            $this->translatedIntoLanguagesSynchronizer
        );
    }

    public function testSync(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $request
            ->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'id' => 'resourceId',
                'languageUri' => 'languageUri',
            ]);

        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $translationResource = $this->createMock(core_kernel_classes_Resource::class);

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getResource')
            ->withConsecutive(['resourceId'], ['translationResourceId'])
            ->willReturnOnConsecutiveCalls($resource, $translationResource);

        $resource
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $property = $this->createMock(core_kernel_classes_Property::class);

        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->with(TaoOntology::PROPERTY_TRANSLATION_TYPE)
            ->willReturn($property);

        $translationType = $this->createMock(core_kernel_classes_Resource::class);

        $resource
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->with($property)
            ->willReturn($translationType);

        $translationType
            ->expects($this->once())
            ->method('getUri')
            ->willReturn(TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL);

        $resource
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('resourceUri');

        $translation = $this->createMock(AbstractResource::class);

        $translation
            ->expects($this->once())
            ->method('getResourceUri')
            ->willReturn('translationResourceId');

        $translationCollection = new ResourceCollection($translation);

        $this->resourceTranslationRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn($translationCollection);

        $translationResource
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $resource
            ->expects($this->once())
            ->method('getRootId')
            ->willReturn('rootId');

        $callable = $this
            ->getMockBuilder(stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();

        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($translationResource);

        $this->translatedIntoLanguagesSynchronizer
            ->expects($this->once())
            ->method('sync')
            ->with($resource);

        $this->sut->addSynchronizer('rootId', $callable);
        $this->assertEquals($resource, $this->sut->syncByRequest($request));
    }
}
