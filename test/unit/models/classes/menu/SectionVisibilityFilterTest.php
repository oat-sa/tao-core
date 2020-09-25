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
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\menu;

use oat\generis\test\TestCase;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\menu\SectionVisibilityFilter;
use PHPUnit\Framework\MockObject\MockObject;

class SectionVisibilityFilterTest extends TestCase
{
    /** @var SectionVisibilityFilter */
    private $subject;

    /** @var FeatureFlagChecker|MockObject  */
    private $featureFlagChecker;

    public function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagChecker::class);

        $this->subject = new SectionVisibilityFilter(
            [
                SectionVisibilityFilter::OPTION_FEATURE_FLAG_SECTIONS => [
                    'settings_manage_lti_keys' => [
                        'FEATURE_FLAG_LTI1P3',
                    ],
                ],
            ]
        );

        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock([
                FeatureFlagChecker::class => $this->featureFlagChecker,
            ])
        );
    }

    public function testIsHidden(): void
    {
        $this->featureFlagChecker
            ->method('isEnabled')
            ->willReturn(true);

        self::assertTrue($this->subject->isVisible('settings_manage_lti_keys'));
    }

    public function testIsHiddenLtiDisabled(): void
    {
        $this->featureFlagChecker
            ->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        self::assertFalse($this->subject->isVisible('settings_manage_lti_keys'));
    }

    public function testIsVisibleWithNoSections(): void
    {
        self::assertTrue($this->subject->isVisible('another_section'));
    }
}
