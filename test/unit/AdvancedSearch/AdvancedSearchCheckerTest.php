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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\AdvancedSearch;

use oat\generis\test\TestCase;
use oat\tao\model\search\SearchInterface;
use oat\tao\model\search\SearchProxy;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;

class AdvancedSearchCheckerTest extends TestCase
{
    /** @var FeatureFlagChecker|MockObject */
    private $featureFlagChecker;

    /** @var AdvancedSearchChecker */
    private $advancedSearchChecker;

    /** @var SearchProxy|MockObject */
    private $search;

    public function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagChecker::class);
        $this->search = $this->createMock(SearchProxy::class);
        $this->advancedSearchChecker = new AdvancedSearchChecker();
        $this->advancedSearchChecker->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    FeatureFlagChecker::class => $this->featureFlagChecker,
                    SearchProxy::SERVICE_ID => $this->search,
                ]
            )
        );
    }

    /**
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled(bool $advancedSearchDisabled, SearchInterface $advancedSearch, bool $expected): void
    {
        $this->featureFlagChecker
            ->expects(static::once())
            ->method('isEnabled')
            ->willReturn($advancedSearchDisabled);

        $this->search
            ->method('getAdvancedSearch')
            ->willReturn($advancedSearch);

        $this->assertEquals($expected, $this->advancedSearchChecker->isEnabled());
    }

    public function isEnabledDataProvider(): array
    {
        return [
            [
                'advancedSearchDisabled' => true,
                'advancedSearch' => $this->createMock(SearchInterface::class),
                'expected' => false,
            ],
            [
                'advancedSearchDisabled' => false,
                'advancedSearch' => $this->createMock(SearchInterface::class),
                'expected' => true,
            ],
        ];
    }
}
