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

namespace oat\tao\test\unit\models\classes\form\Modifier;

use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\featureFlag\Service\FeatureFlagPropertiesMapping;
use oat\tao\model\form\Modifier\UniqueIdFormModifier;
use oat\tao\model\TaoOntology;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use tao_helpers_form_Form;
use tao_helpers_Uri;

class UniqueIdFormModifierTest extends TestCase
{
    /** @var FeatureFlagCheckerInterface|MockObject */
    private $featureFlagChecker;

    private UniqueIdFormModifier $sut;

    protected function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);
        $featureFlagPropertiesMapping = $this->createMock(FeatureFlagPropertiesMapping::class);

        $featureFlagPropertiesMapping
            ->method('getFeatureProperties')
            ->with('FEATURE_FLAG_UNIQUE_NUMERIC_QTI_IDENTIFIER')
            ->willReturn([
                TaoOntology::PROPERTY_UNIQUE_IDENTIFIER,
            ]);

        $this->sut = new UniqueIdFormModifier($this->featureFlagChecker, $featureFlagPropertiesMapping);
    }

    public function testFeatureEnabled(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->with('FEATURE_FLAG_UNIQUE_NUMERIC_QTI_IDENTIFIER')
            ->willReturn(true);

        $form = $this->createMock(tao_helpers_form_Form::class);

        $form
            ->expects($this->never())
            ->method('removeElement');

        $this->sut->modify($form);
    }

    public function testFeatureDisabled(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->with('FEATURE_FLAG_UNIQUE_NUMERIC_QTI_IDENTIFIER')
            ->willReturn(false);

        $form = $this->createMock(tao_helpers_form_Form::class);

        $form
            ->expects($this->once())
            ->method('removeElement')
            ->with(tao_helpers_Uri::encode(TaoOntology::PROPERTY_UNIQUE_IDENTIFIER));

        $this->sut->modify($form);
    }
}
