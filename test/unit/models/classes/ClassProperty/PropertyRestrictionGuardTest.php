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

namespace oat\tao\test\unit\models\classes\ClassProperty;

use oat\generis\model\data\Ontology;
use oat\tao\model\ClassProperty\PropertyRestrictionGuard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use core_kernel_classes_Resource as Resource;
use core_kernel_classes_Property as Property;

class PropertyRestrictionGuardTest extends TestCase
{
    private Ontology|MockObject $ontologyMock;
    private PropertyRestrictionGuard $subject;
    private Resource|MockObject $instance;
    private Property|MockObject $property;
    private array $restrictedProperties;

    protected function setUp(): void
    {
        $this->ontologyMock = $this->createMock(Ontology::class);
        $this->subject = new PropertyRestrictionGuard($this->ontologyMock);
        $this->instance = $this->createMock(Resource::class);
        $this->property = $this->createMock(Property::class);
        $this->restrictedProperties = [
            'propertyUri' => [
                'restrictionPropertyUri' => ['value']
            ]
        ];
    }

    /**
     * @dataProvider propertyNonRestrictedDataProvider
     */
    public function testIsPropertyRestrictedFalse(
        string $propertyUri,
        int $propertyGetUriCount,
        int $getPropertyCallCount = 0,
        int $getOnePropertyValueCallCount = 0
    ): void {
        $this->property->expects(self::exactly($propertyGetUriCount))
            ->method('getUri')
            ->willReturn($propertyUri);

        $this->ontologyMock->expects(self::exactly($getPropertyCallCount))
            ->method('getProperty')
            ->with('restrictionPropertyUri')
            ->willReturn($this->property);

        $this->instance->expects(self::exactly($getOnePropertyValueCallCount))
            ->method('getOnePropertyValue')
            ->with($this->property)
            ->willReturn('value');

        self::assertFalse($this->subject->isPropertyRestricted(
            $this->instance,
            $this->property,
            $this->restrictedProperties
        ));
    }

    public function testIsPropertyRestrictedTrue(): void
    {
        $this->property->expects(self::once())
            ->method('getUri')
            ->willReturn('propertyUri');

        $this->ontologyMock->expects(self::once())
            ->method('getProperty')
            ->with('restrictionPropertyUri')
            ->willReturn($this->property);

        $this->instance->expects(self::once())
            ->method('getOnePropertyValue')
            ->with($this->property)
            ->willReturn('diffValue');

        self::assertTrue($this->subject->isPropertyRestricted(
            $this->instance,
            $this->property,
            $this->restrictedProperties
        ));
    }

    public function propertyNonRestrictedDataProvider(): array
    {
        return [
            'Property not in restrictedProperties list' => [
                'propertyUriNotRestricted',
                1
            ],
            'Property in restrictedProperties list' => [
                'propertyUri',
                1,
                1,
                1
            ]
        ];
    }
}
