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

use oat\generis\model\data\Ontology;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\ResourceTranslatableStatus;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Service\ResourceTranslatableStatusRetriever;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use core_kernel_classes_Resource;
use core_kernel_classes_Property;

class ResourceTranslatableStatusRetrieverTest extends TestCase
{
    private ResourceTranslatableStatusRetriever $sut;

    /** @var Ontology|MockObject */
    private $ontology;

    /** @var MockObject|ServerRequestInterface */
    private $request;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->sut = new ResourceTranslatableStatusRetriever(
            $this->ontology,
            $this->createMock(LoggerInterface::class)
        );
        $this->sut->addCallable(
            TaoOntology::CLASS_URI_ITEM,
            static function (ResourceTranslatableStatus $status) {
                $status->setEmpty(false);
            }
        );
    }

    public function testRetrieveByRequestRequest(): void
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $languageProperty = $this->createMock(core_kernel_classes_Property::class);
        $languagePropertyValue = $this->createMock(core_kernel_classes_Resource::class);
        $translationStatusProperty = $this->createMock(core_kernel_classes_Property::class);
        $translationStatusPropertyValue = $this->createMock(core_kernel_classes_Resource::class);

        $translationStatusPropertyValue
            ->expects($this->any())
            ->method('getUri')
            ->willReturn(TaoOntology::PROPERTY_VALUE_TRANSLATION_STATUS_READY);

        $languagePropertyValue
            ->expects($this->any())
            ->method('getUri')
            ->willReturn('languageUri');

        $resource
            ->expects($this->any())
            ->method('exists')
            ->willReturn(true);

        $resource
            ->expects($this->any())
            ->method('getRootId')
            ->willReturn(TaoOntology::CLASS_URI_ITEM);

        $resource
            ->expects($this->any())
            ->method('getUri')
            ->willReturn('uri');

        $resource
            ->expects($this->any())
            ->method('getOnePropertyValue')
            ->with($translationStatusProperty)
            ->willReturnOnConsecutiveCalls(
                $languagePropertyValue,
                $translationStatusPropertyValue
            );

        $this->request
            ->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(['id' => 'id']);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('id')
            ->willReturn($resource);

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->willReturnOnConsecutiveCalls(
                $languageProperty,
                $translationStatusProperty
            );

        $this->assertEquals(
            new ResourceTranslatableStatus(
                'uri',
                TaoOntology::CLASS_URI_ITEM,
                'languageUri',
                true,
                false
            ),
            $this->sut->retrieveByRequest($this->request)
        );
    }

    public function testRetrieveByRequestRequiresExistingResource(): void
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $resource
            ->expects($this->any())
            ->method('exists')
            ->willReturn(false);

        $this->request
            ->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(['id' => 'id']);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('id')
            ->willReturn($resource);

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage('Translatable resource id does not exist');

        $this->sut->retrieveByRequest($this->request);
    }

    public function testRetrieveByRequestRequiresResourceId(): void
    {
        $this->request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn([]);

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage('Resource id is required');

        $this->sut->retrieveByRequest($this->request);
    }
}
