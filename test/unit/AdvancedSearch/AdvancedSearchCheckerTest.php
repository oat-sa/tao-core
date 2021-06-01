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

namespace oat\tao\test\unit\AdvancedSearch;

use oat\generis\test\TestCase;
use oat\tao\model\search\Search;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;

class AdvancedSearchCheckerTest extends TestCase
{
    /** @var FeatureFlagChecker|MockObject*/
    private $featureFlagChecker;

    /** @var AdvancedSearchChecker */
    private $advancedSearchChecker;

    public function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagChecker::class);
        $search = $this->createMock(Search::class);

        $this->advancedSearchChecker = new AdvancedSearchChecker();
        $this->advancedSearchChecker->setServiceLocator(
            $this->getServiceLocatorMock([
                FeatureFlagChecker::class => $this->featureFlagChecker,
                Search::SERVICE_ID => $search,
            ])
        );
    }

    /**
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled(bool $advancedSearchDisabled, bool $expected): void
    {
        $this->featureFlagChecker
            ->expects(static::once())
            ->method('isEnabled')
            ->willReturn($advancedSearchDisabled);

        $this->assertEquals($expected, $this->advancedSearchChecker->isEnabled());
    }

    public function isEnabledDataProvider(): array
    {
        return [
            [
                'advancedSearchDisabled' => true,
                'expected' => false,
            ],
            [
                'advancedSearchDisabled' => false,
                'expected' => true,
            ],
        ];
    }
}
