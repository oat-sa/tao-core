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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\Business\Validation;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use oat\generis\model\data\Ontology;
use oat\tao\helpers\form\Decorator\ElementDecorator;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Specification\PrimaryPropertySpecification;
use oat\tao\model\Lists\Business\Specification\RemoteListClassSpecification;
use oat\tao\model\Lists\Business\Specification\SecondaryPropertySpecification;
use oat\tao\model\Lists\Business\Validation\PropertyListValidator;
use PHPUnit\Framework\TestCase;

class PropertyTypeValidatorTest extends TestCase
{
    /** @var Ontology */
    private $ontology;

    /** @var PrimaryPropertySpecification */
    private $primaryPropertySpecification;

    /** @var SecondaryPropertySpecification */
    private $secondaryPropertySpecification;

    /** @var RemoteListClassSpecification */
    private $remoteListClassSpecification;

    /** @var FeatureFlagCheckerInterface */
    private $featureFlagChecker;

    /** @var PropertyListValidator */
    private $sut;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->primaryPropertySpecification = $this->createMock(PrimaryPropertySpecification::class);
        $this->secondaryPropertySpecification = $this->createMock(SecondaryPropertySpecification::class);
        $this->remoteListClassSpecification = $this->createMock(RemoteListClassSpecification::class);
        $this->featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);

        $this->sut = new PropertyListValidator(
            $this->ontology,
            $this->primaryPropertySpecification,
            $this->secondaryPropertySpecification,
            $this->remoteListClassSpecification,
            $this->featureFlagChecker
        );
    }

    public function testEvaluate(): void
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $class = $this->createMock(core_kernel_classes_Class::class);
        $elementDecorator = $this->createMock(ElementDecorator::class);

        $elementDecorator
            ->method('getProperty')
            ->willReturn($property);

        $elementDecorator
            ->method('getRangeClass')
            ->willReturn($class);

        $this->featureFlagChecker
            ->method('isEnabled')
            ->willReturn(true);

        $this->primaryPropertySpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->secondaryPropertySpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->remoteListClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->sut->withElementDecorator($elementDecorator);

        $this->assertTrue($this->sut->evaluate('doesNotMatter'));
    }
}
