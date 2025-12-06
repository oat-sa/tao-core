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

namespace oat\tao\test\unit\AdvancedSearch;

use oat\generis\test\ServiceManagerMockTrait;
use oat\tao\model\search\SearchInterface;
use oat\tao\model\search\SearchProxy;
use oat\taoAdvancedSearch\model\SearchEngine\Driver\Elasticsearch\ElasticSearch;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use PHPUnit\Framework\TestCase;

class AdvancedSearchCheckerTest extends TestCase
{
    use ServiceManagerMockTrait;

    private AdvancedSearchChecker $sut;
    private FeatureFlagChecker|MockObject $featureFlagChecker;
    private SearchProxy|MockObject $search;

    protected function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagChecker::class);
        $this->search = $this->createMock(SearchInterface::class);

        $this->sut = new AdvancedSearchChecker();
        $this->sut->setServiceLocator(
            $this->getServiceManagerMock(
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
    public function testIsEnabled(bool $advancedSearchDisabled, bool $supportsCustomIndex, bool $expected): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn($advancedSearchDisabled);

        $this->search
            ->method('supportCustomIndex')
            ->willReturn($supportsCustomIndex);

        $this->assertEquals($expected, $this->sut->isEnabled());
    }

    /**
     * @dataProvider pingProvider
     *
     * @var SearchInterface|MockObject|null $advancedSearch
     */
    public function testPing(?SearchInterface $advancedSearch, ?bool $advancedSearchPing, bool $expected): void
    {
        $search = $this->createMock(SearchProxy::class);
        $sut = new AdvancedSearchChecker();
        $sut->setServiceManager(
            $this->getServiceManagerMock([
                SearchProxy::SERVICE_ID => $search,
            ])
        );

        if ($advancedSearch) {
            $advancedSearch
                ->expects($advancedSearchPing === null ? $this->never() : $this->once())
                ->method('ping')
                ->willReturn($advancedSearchPing);
        }

        $search
            ->expects($this->once())
            ->method('getAdvancedSearch')
            ->willReturn($advancedSearch);

        $this->assertEquals($expected, $sut->ping());
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

    public function pingProvider(): array
    {
        $data = [
            [
                'advancedSearch' => null,
                'advancedSearchPing' => null,
                'expected' => false,
            ]
        ];

        if (class_exists('oat\\taoAdvancedSearch\\model\\SearchEngine\\Driver\\Elasticsearch\\ElasticSearch')) {
            $data[] = [
                'advancedSearch' => $this->createMock(ElasticSearch::class),
                'advancedSearchPing' => true,
                'expected' => true,
            ];

            $data[] = [
                'advancedSearch' => $this->createMock(ElasticSearch::class),
                'advancedSearchPing' => false,
                'expected' => false,
            ];
        }

        return $data;
    }
}
