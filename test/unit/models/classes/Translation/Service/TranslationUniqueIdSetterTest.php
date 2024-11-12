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

use core_kernel_classes_Literal;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Service\AbstractQtiIdentifierSetter;
use oat\tao\model\Translation\Service\TranslationUniqueIdSetter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TranslationUniqueIdSetterTest extends TestCase
{
    /** @var core_kernel_classes_Resource|MockObject */
    private core_kernel_classes_Resource $resource;

    /** @var AbstractQtiIdentifierSetter|MockObject */
    private AbstractQtiIdentifierSetter $qtiIdentifierSetter;

    /** @var FeatureFlagCheckerInterface|MockObject */
    private FeatureFlagCheckerInterface $featureFlagChecker;

    /** @var Ontology|MockObject */
    private Ontology $ontology;

    private TranslationUniqueIdSetter $sut;

    protected function setUp(): void
    {
        $this->resource = $this->createResourceMock();

        $this->qtiIdentifierSetter = $this->createMock(AbstractQtiIdentifierSetter::class);
        $this->featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);
        $this->ontology = $this->createMock(Ontology::class);

        $this->sut = new TranslationUniqueIdSetter($this->featureFlagChecker, $this->ontology);
        $this->sut->addQtiIdentifierSetter($this->qtiIdentifierSetter, 'resourceType');
    }

    public function testUniqueIdFeatureDisabled(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->with('FEATURE_FLAG_UNIQUE_NUMERIC_QTI_IDENTIFIER')
            ->willReturn(false);

        $this->resource
            ->expects($this->never())
            ->method($this->anything());

        $this->qtiIdentifierSetter
            ->expects($this->never())
            ->method('set');

        $this->sut->__invoke($this->resource);
    }

    public function testTranslationFeatureDisabled(): void
    {
        $this->featureFlagChecker
            ->expects($this->exactly(2))
            ->method('isEnabled')
            ->withConsecutive(
                ['FEATURE_FLAG_UNIQUE_NUMERIC_QTI_IDENTIFIER'],
                ['FEATURE_FLAG_TRANSLATION_ENABLED']
            )
            ->willReturnOnConsecutiveCalls(true, false);

        $this->resource
            ->expects($this->never())
            ->method($this->anything());

        $this->qtiIdentifierSetter
            ->expects($this->never())
            ->method('set');

        $this->sut->__invoke($this->resource);
    }

    public function testNoOriginalResourceUri(): void
    {
        $this->featureFlagChecker
            ->expects($this->exactly(2))
            ->method('isEnabled')
            ->withConsecutive(
                ['FEATURE_FLAG_UNIQUE_NUMERIC_QTI_IDENTIFIER'],
                ['FEATURE_FLAG_TRANSLATION_ENABLED']
            )
            ->willReturnOnConsecutiveCalls(true, true);

        $originalResourceUriProperty = $this->createPropertyMock();

        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->with(TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI)
            ->willReturn($originalResourceUriProperty);

        $this->resource
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->with($originalResourceUriProperty)
            ->willReturn(null);

        $this->resource
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('resourceUri');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resource resourceUri is not a translation - original resource URI is empty');

        $this->sut->__invoke($this->resource);
    }

    public function testNoUniqueId(): void
    {
        $this->featureFlagChecker
            ->expects($this->exactly(2))
            ->method('isEnabled')
            ->withConsecutive(
                ['FEATURE_FLAG_UNIQUE_NUMERIC_QTI_IDENTIFIER'],
                ['FEATURE_FLAG_TRANSLATION_ENABLED']
            )
            ->willReturnOnConsecutiveCalls(true, true);

        $originalResourceUriProperty = $this->createPropertyMock();
        $uniqueIdProperty = $this->createPropertyMock();

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->withConsecutive(
                [TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI],
                [TaoOntology::PROPERTY_UNIQUE_IDENTIFIER]
            )
            ->willReturnOnConsecutiveCalls(
                $originalResourceUriProperty,
                $uniqueIdProperty
            );

        $this->resource
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->with($originalResourceUriProperty)
            ->willReturn('originalResourceUri');

        $originalResource = $this->createResourceMock();

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('originalResourceUri')
            ->willReturn($originalResource);

        $originalResource
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->with($uniqueIdProperty)
            ->willReturn(null);

        $originalResource
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('originalResourceUri');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unique ID must exists for resource URI originalResourceUri');

        $this->sut->__invoke($this->resource);
    }

    public function testNoQtiIdentifierSetterForSpecifiedType(): void
    {
        $this->featureFlagChecker
            ->expects($this->exactly(2))
            ->method('isEnabled')
            ->withConsecutive(
                ['FEATURE_FLAG_UNIQUE_NUMERIC_QTI_IDENTIFIER'],
                ['FEATURE_FLAG_TRANSLATION_ENABLED']
            )
            ->willReturnOnConsecutiveCalls(true, true);

        $originalResourceUriProperty = $this->createPropertyMock();
        $uniqueIdProperty = $this->createPropertyMock();

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->withConsecutive(
                [TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI],
                [TaoOntology::PROPERTY_UNIQUE_IDENTIFIER]
            )
            ->willReturnOnConsecutiveCalls(
                $originalResourceUriProperty,
                $uniqueIdProperty
            );

        $this->resource
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->with($originalResourceUriProperty)
            ->willReturn('originalResourceUri');

        $originalResource = $this->createResourceMock();

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('originalResourceUri')
            ->willReturn($originalResource);

        $identifier = $this->createMock(core_kernel_classes_Literal::class);
        $identifier->literal = 'identifier';

        $originalResource
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->with($uniqueIdProperty)
            ->willReturn($identifier);

        $this->resource
            ->expects($this->once())
            ->method('editPropertyValues')
            ->with($uniqueIdProperty, 'identifier');

        $this->resource
            ->expects($this->once())
            ->method('getRootId')
            ->willReturn('missedResourceType');

        $this->qtiIdentifierSetter
            ->expects($this->never())
            ->method('set');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('QTI Identifier setter does not exist for resource type missedResourceType');

        $this->sut->__invoke($this->resource);
    }

    public function testSuccess(): void
    {
        $this->featureFlagChecker
            ->expects($this->exactly(2))
            ->method('isEnabled')
            ->withConsecutive(
                ['FEATURE_FLAG_UNIQUE_NUMERIC_QTI_IDENTIFIER'],
                ['FEATURE_FLAG_TRANSLATION_ENABLED']
            )
            ->willReturnOnConsecutiveCalls(true, true);

        $originalResourceUriProperty = $this->createPropertyMock();
        $uniqueIdProperty = $this->createPropertyMock();

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->withConsecutive(
                [TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI],
                [TaoOntology::PROPERTY_UNIQUE_IDENTIFIER]
            )
            ->willReturnOnConsecutiveCalls(
                $originalResourceUriProperty,
                $uniqueIdProperty
            );

        $this->resource
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->with($originalResourceUriProperty)
            ->willReturn('originalResourceUri');

        $originalResource = $this->createResourceMock();

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('originalResourceUri')
            ->willReturn($originalResource);

        $identifier = $this->createMock(core_kernel_classes_Literal::class);
        $identifier->literal = 'identifier';

        $originalResource
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->with($uniqueIdProperty)
            ->willReturn($identifier);

        $this->resource
            ->expects($this->once())
            ->method('editPropertyValues')
            ->with($uniqueIdProperty, 'identifier');

        $this->resource
            ->expects($this->once())
            ->method('getRootId')
            ->willReturn('resourceType');

        $this->qtiIdentifierSetter
            ->expects($this->once())
            ->method('set')
            ->with([
                AbstractQtiIdentifierSetter::OPTION_RESOURCE => $this->resource,
                AbstractQtiIdentifierSetter::OPTION_IDENTIFIER => 'identifier',
            ]);

        $this->sut->__invoke($this->resource);
    }

    /**
     * @return core_kernel_classes_Resource|MockObject
     */
    private function createResourceMock(): core_kernel_classes_Resource
    {
        return $this->createMock(core_kernel_classes_Resource::class);
    }

    /**
     * @return core_kernel_classes_Property|MockObject
     */
    private function createPropertyMock(): core_kernel_classes_Property
    {
        return $this->createMock(core_kernel_classes_Property::class);
    }
}
