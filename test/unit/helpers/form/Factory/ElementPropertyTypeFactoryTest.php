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

namespace oat\tao\helpers\test\unit\helpers\form\Factory;

use oat\generis\test\TestCase;
use oat\tao\helpers\form\Factory\ElementPropertyTypeFactory;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Specification\SecondaryPropertySpecification;
use oat\tao\model\Specification\PropertySpecificationInterface;

class ElementPropertyTypeFactoryTest extends TestCase
{
    /** @var ElementPropertyTypeFactory */
    private $sut;

    /** @var PropertySpecificationInterface */
    private $primaryPropertySpecification;

    /** @var SecondaryPropertySpecification */
    private $secondaryPropertySpecification;

    /** @var FeatureFlagCheckerInterface */
    private $featureFlagChecker;

    public function setUp(): void
    {
        $this->primaryPropertySpecification = $this->createMock(PropertySpecificationInterface::class);
        $this->secondaryPropertySpecification = $this->createMock(SecondaryPropertySpecification::class);
        $this->featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);

        $this->sut = new ElementPropertyTypeFactory(
            $this->primaryPropertySpecification,
            $this->secondaryPropertySpecification,
            $this->featureFlagChecker
        );
    }

    public function testCreate(): void
    {
        $this->markTestIncomplete('TODO');
    }
}
