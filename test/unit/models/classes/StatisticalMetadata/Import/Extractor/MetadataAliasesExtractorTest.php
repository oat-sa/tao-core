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
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataAliasesExtractor;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataHeadersExtractor;

class MetadataAliasesExtractorTest extends TestCase
{
    /** @var MetadataHeadersExtractor|MockObject */
    private $metadataHeadersExtractor;

    /** @var MetadataAliasesExtractor */
    private $sut;

    protected function setUp(): void
    {
        $this->metadataHeadersExtractor = $this->createMock(MetadataHeadersExtractor::class);
        $this->sut = new MetadataAliasesExtractor($this->metadataHeadersExtractor);
    }

    public function testExtract(): void
    {
        $header = [
            'itemId',
            'testId',
            'metadata_alias_1',
            'metadata_alias_2',
            'metadata_alias_3',
        ];

        $this->metadataHeadersExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($header)
            ->willReturn(
                [
                    'metadata_alias_1',
                    'metadata_alias_2',
                    'metadata_alias_3',
                ]
            );

        $this->assertEquals(
            [
                'alias_1',
                'alias_2',
                'alias_3',
            ],
            $this->sut->extract($header)
        );
    }

    public function testExtractCache(): void
    {
        $header1 = [
            'itemId',
            'testId',
            'metadata_alias_1',
            'metadata_alias_2',
            'metadata_alias_3',
        ];
        $header2 = [
            'itemId',
            'testId',
            'metadata_alias_4',
            'metadata_alias_5',
            'metadata_alias_6',
        ];

        $this->metadataHeadersExtractor
            ->expects($this->exactly(2))
            ->method('extract')
            ->willReturnCallback(
                static function (array $header) use ($header1, $header2): array {
                    if ($header === $header1) {
                        return [
                            'metadata_alias_1',
                            'metadata_alias_2',
                            'metadata_alias_3',
                        ];
                    }

                    if ($header === $header2) {
                        return [
                            'metadata_alias_4',
                            'metadata_alias_5',
                            'metadata_alias_6',
                        ];
                    }

                    return [];
                }
            );

        // Check that $this->metadataHeadersExtractor->expects() is called 2 times instead of 4
        $this->sut->extract($header1);
        $this->sut->extract($header2);
        $this->sut->extract($header1);
        $this->sut->extract($header2);
    }
}
