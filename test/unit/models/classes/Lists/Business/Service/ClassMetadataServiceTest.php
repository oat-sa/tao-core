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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\Business\Service;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use oat\generis\model\data\Ontology;
use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\ClassMetadataSearchRequest;
use oat\tao\model\Lists\Business\Input\ClassMetadataSearchInput;
use oat\tao\model\Lists\Business\Service\ClassMetadataService;
use oat\tao\model\Lists\Business\Service\GetClassMetadataValuesService;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use PHPUnit\Framework\MockObject\MockObject;

class ClassMetadataServiceTest extends TestCase
{
    /** @var ClassMetadataService */
    private $sut;

    /** @var ValueCollectionService|MockObject */
    private $valueCollectionServiceMock;

    /** @var ValueCollectionRepositoryInterface|MockObject */
    private $repositoryMock;

    /** @var ClassMetadataSearchRequest|MockObject */
    private $classMetadataSearchRequestMock;

    /** @var ClassMetadataSearchInput|MockObject */
    private $classMetadataSearchInputMock;

    /** @var core_kernel_classes_Property|MockObject */
    private $property;

    public function setUp(): void
    {
        $this->repositoryMock = $this->createMock(ValueCollectionRepositoryInterface::class);
        $this->valueCollectionServiceMock = $this->createMock(GetClassMetadataValuesService::class);
        $this->classMetadataSearchRequestMock = $this->createMock(ClassMetadataSearchRequest::class);
        $this->classMetadataSearchInputMock = $this->createMock(ClassMetadataSearchInput::class);

        $this->classMetadataSearchInputMock
            ->expects($this->once())
            ->method('getSearchRequest')
            ->willReturn($this->classMetadataSearchRequestMock);

        $this->sut = new ClassMetadataService(
            $this->valueCollectionServiceMock
        );

        $ontologyServiceMock = $this->createMock(Ontology::class);
        $ontologyServiceMock
            ->expects($this->any())
            ->method('getClass')
            ->willReturn($this->createClassMock());

        $getClassMetadataValuesServiceMock = $this->createMock(GetClassMetadataValuesService::class);

        $this->sut->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    Ontology::SERVICE_ID => $ontologyServiceMock,
                    GetClassMetadataValuesService::class => $getClassMetadataValuesServiceMock,
                ]
            )
        );
    }

    public function testFindAll(): void
    {
        $widgetResource = $this->createMock(\core_kernel_classes_Resource::class);
        $this->property
            ->method('getWidget')
            ->willReturn($widgetResource);

        $this->property
            ->method('getLabel')
            ->willReturn('propertyLabel');

        $widgetResource
            ->method('getUri')
            ->willReturn('http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox');

        $this->classMetadataSearchRequestMock
            ->method('getMaxListSize')
            ->willReturn(10);

        $result = $this->sut->findAll(
            $this->classMetadataSearchInputMock
        );

        $this->assertSame(
            '[{"class":"uri","parent-class":null,"label":"label","metadata":[]}]',
            json_encode($result)
        );
    }

    private function createClassMock(): core_kernel_classes_Class
    {
        $class = $this->createMock(core_kernel_classes_Class::class);
        $this->property = $this->createMock(core_kernel_classes_Property::class);

        $class
            ->expects($this->once())
            ->method('isClass')
            ->willReturn(true);
        $class
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([]);
        $class
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('uri');
        $class
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('label');

        return $class;
    }

    public function testFindAllPropertyDoesNotHaveWidget(): void
    {
        $this->classMetadataSearchRequestMock
            ->method('getMaxListSize')
            ->willReturn(10);

        $this->property
            ->method('getWidget')
            ->willReturn(null);

        $result = $this->sut->findAll(
            $this->classMetadataSearchInputMock
        );

        $this->assertSame(
            '[{"class":"uri","parent-class":null,"label":"label","metadata":[]}]',
            json_encode($result)
        );
    }
}
