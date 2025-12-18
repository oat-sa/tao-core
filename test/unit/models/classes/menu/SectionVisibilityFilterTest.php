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
 * Copyright (c) 2020-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\menu;

use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\menu\SectionVisibilityFilter;
use oat\tao\model\user\implementation\UserSettingsService;
use PHPUnit\Framework\MockObject\MockObject;

class SectionVisibilityFilterTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const SECTION_VISIBLE_BY_DEFAULT = 'section_visible_by_default';
    private const FEATURE_FLAG_SECTION_VISIBLE_BY_DEFAULT_DISABLED = 'FEATURE_FLAG_SECTION_VISIBLE_BY_DEFAULT_DISABLED';

    private SectionVisibilityFilter $subject;
    private FeatureFlagChecker|MockObject $featureFlagChecker;

    protected function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagChecker::class);
        $userSettingsService = $this->createMock(UserSettingsService::class);

        $this->subject = new SectionVisibilityFilter(
            [
                SectionVisibilityFilter::OPTION_FEATURE_FLAG_SECTIONS => [
                    'settings_manage_lti_keys' => [
                        'FEATURE_FLAG_LTI1P3',
                    ],
                ],
                SectionVisibilityFilter::OPTION_FEATURE_FLAG_SECTIONS_TO_HIDE => [
                    self::SECTION_VISIBLE_BY_DEFAULT => [
                        self::FEATURE_FLAG_SECTION_VISIBLE_BY_DEFAULT_DISABLED,
                    ],
                ],
            ]
        );

        $this->subject->setServiceLocator(
            $this->getServiceManagerMock([
                FeatureFlagChecker::class => $this->featureFlagChecker,
                UserSettingsService::class => $userSettingsService,
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
            ->method('isEnabled')
            ->willReturn(false);

        self::assertFalse($this->subject->isVisible('settings_manage_lti_keys'));
    }

    public function testIsVisibleWithNoSections(): void
    {
        self::assertTrue($this->subject->isVisible('another_section'));
    }

    /**
     * @dataProvider sectionsToHideDataProvider
     */
    public function testWhiteList($isEnabled, $result): void
    {
        $this->featureFlagChecker
            ->method('isEnabled')
            ->willReturn($isEnabled);

        self::assertSame($result, $this->subject->isVisible(self::SECTION_VISIBLE_BY_DEFAULT));
    }

    public function sectionsToHideDataProvider(): array
    {
        return [
            'feature flag enabled' => [
                'isEnabled' => true,
                'result' => false
            ],
            'feature flag disabled' => [
                'isEnabled' => false,
                'result' => true
            ],
        ];
    }
}
