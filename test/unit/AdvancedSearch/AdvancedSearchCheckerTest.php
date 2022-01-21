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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\AdvancedSearch;

use oat\generis\test\TestCase;
use oat\tao\model\search\SearchInterface;
use oat\tao\model\search\SearchProxy;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use common_ext_ExtensionsManager;

class AdvancedSearchCheckerTest extends TestCase
{
    /** @var common_ext_ExtensionsManager|MockObject */
    private $extensionsManager;

    /** @var FeatureFlagChecker|MockObject */
    private $featureFlagChecker;

    /** @var AdvancedSearchChecker */
    private $sut;

    /** @var SearchProxy|MockObject */
    private $search;

    public function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagChecker::class);
        $this->search = $this->createMock(SearchInterface::class);
        $this->extensionsManager = $this->createMock(common_ext_ExtensionsManager::class);

        $this->sut = new AdvancedSearchChecker();
        $this->sut->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    common_ext_ExtensionsManager::SERVICE_ID => $this->extensionsManager,
                    FeatureFlagChecker::class => $this->featureFlagChecker,
                    SearchProxy::SERVICE_ID => $this->search,
                ]
            )
        );
    }

    /**
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled(bool $advancedSearchDisabled, bool $supportsCustomIndex, bool $expected): void
    {
        $this->featureFlagChecker
            ->expects(static::once())
            ->method('isEnabled')
            ->willReturn($advancedSearchDisabled);

        $this->search
            ->method('supportCustomIndex')
            ->willReturn($supportsCustomIndex);

        $this->assertEquals($expected, $this->sut->isEnabled());
    }

    public function isEnabledDataProvider(): array
    {
        return [
            [
                'advancedSearchDisabled' => true,
                'supportsCustomIndex' => true,
                'expected' => false,
            ],
            [
                'advancedSearchDisabled' => false,
                'supportsCustomIndex' => false,
                'expected' => false,
            ],
            [
                'advancedSearchDisabled' => false,
                'supportsCustomIndex' => true,
                'expected' => true,
            ],
            [
                'advancedSearchDisabled' => true,
                'supportsCustomIndex' => false,
                'expected' => false,
            ],
        ];
    }

    /**
     * @backupGlobals Used to preserve former $_ENV value between tests
     * @dataProvider getDisabledSectionsDataProvider
     */
    public function testGetDisabledSections(
        array $expected,
        bool $taoBookletEnabled,
        array $envVars
    ): void {
        $_ENV = array_merge($_ENV ?? [], $envVars);

        $this->extensionsManager
            ->method('isEnabled')
            ->with('taoBooklet')
            ->willReturn($taoBookletEnabled);

        $disabledSections = $this->sut->getDisabledSections();
        sort($disabledSections);

        $this->assertEquals($expected, $disabledSections);
    }

    public function getDisabledSectionsDataProvider(): array
    {
        return [
            'Default sections without taoBooklet installed' => [
                'expected' => ['results'],
                'taoBookletEnabled' => false,
                'envVars' => [],
            ],
            'Default sections with taoBooklet enabled' => [
                'expected' => ['results', 'taoBooklet_main'],
                'taoBookletEnabled' => true,
                'envVars' => [],
            ],
            'Sections configured without taoBooklet enabled' => [
                'expected' => ['results', 'section3'],
                'taoBookletEnabled' => false,
                'envVars' => [
                    'TAO_ADVANCED_SEARCH_DISABLED_SECTIONS' => 'section3'
                ],
            ],
            'Sections configured with empty values and without taoBooklet enabled' => [
                'expected' => ['results', 'section3'],
                'taoBookletEnabled' => false,
                'envVars' => [
                    'TAO_ADVANCED_SEARCH_DISABLED_SECTIONS' => ',,section3,,,'
                ],
            ],
            'Sections configured with taoBooklet installed' => [
                'expected' => ['results', 'section3', 'section4', 'taoBooklet_main'],
                'taoBookletEnabled' => true,
                'envVars' => [
                    'TAO_ADVANCED_SEARCH_DISABLED_SECTIONS' => 'section3, section4'
                ],
            ],
            'Sections configured with empty values and taoBooklet installed' => [
                'expected' => ['results', 'section3', 'section4', 'taoBooklet_main'],
                'taoBookletEnabled' => true,
                'envVars' => [
                    'TAO_ADVANCED_SEARCH_DISABLED_SECTIONS' => ',,,section3, section4,,,'
                ],
            ],
            'Duplicated sections configured without taoBooklet enabled' => [
                'expected' => ['results', 'section3'],
                'taoBookletEnabled' => false,
                'envVars' => [
                    'TAO_ADVANCED_SEARCH_DISABLED_SECTIONS' => 'section3,section3'
                ],
            ],
            'Duplicated sections configured with taoBooklet installed' => [
                'expected' => ['results', 'section3', 'taoBooklet_main'],
                'taoBookletEnabled' => true,
                'envVars' => [
                    'TAO_ADVANCED_SEARCH_DISABLED_SECTIONS' => 'section3,section3'
                ],
            ],
        ];
    }
}
