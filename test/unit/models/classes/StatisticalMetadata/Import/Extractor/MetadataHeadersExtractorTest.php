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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\StatisticalMetadata\Import\Extractor;

use PHPUnit\Framework\TestCase;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataHeadersExtractor;

class MetadataHeadersExtractorTest extends TestCase
{
    /** @var MetadataHeadersExtractor */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new MetadataHeadersExtractor();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testExtract(array $header, array $expected): void
    {
        $this->assertEquals($expected, $this->sut->extract($header));
    }

    public function dataProvider(): array
    {
        return [
            [
                'header' => [
                    'itemId',
                    'testId',
                    'metadata_alias_1',
                    'metadata_alias_2',
                    'metadata_alias_3',
                ],
                'expected' => [
                    'metadata_alias_1',
                    'metadata_alias_2',
                    'metadata_alias_3',
                ],
            ],
            [
                'header' => [
                    'itemId',
                    'testId',
                    'metadata_alias_1',
                    'without_prefix_alias_2',
                    'metadata_alias_3',
                ],
                'expected' => [
                    'metadata_alias_1',
                    'metadata_alias_3',
                ],
            ],
            [
                'header' => [
                    'itemId',
                    'testId',
                ],
                'expected' => [],
            ],
        ];
    }
}
