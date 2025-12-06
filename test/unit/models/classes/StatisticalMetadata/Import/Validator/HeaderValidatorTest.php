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

namespace oat\tao\test\unit\models\classes\StatisticalMetadata\Import\Validator;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\StatisticalMetadata\Import\Validator\HeaderValidator;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataHeadersExtractor;
use oat\tao\model\StatisticalMetadata\Import\Exception\AggregatedValidationException;

class HeaderValidatorTest extends TestCase
{
    /** @var MetadataHeadersExtractor|MockObject */
    private $metadataHeadersExtractor;

    /** @var HeaderValidator */
    private $sut;

    protected function setUp(): void
    {
        $this->metadataHeadersExtractor = $this->createMock(MetadataHeadersExtractor::class);
        $this->sut = new HeaderValidator($this->metadataHeadersExtractor);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testValidateRequiredHeaders(array $header, array $metadataHeader, bool $isExceptionExpected): void
    {
        $this->metadataHeadersExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($header)
            ->willReturn($metadataHeader);

        if ($isExceptionExpected) {
            $this->expectException(AggregatedValidationException::class);
        }

        $this->sut->validateRequiredHeaders($header);
    }

    public function dataProvider(): array
    {
        return [
            'Valid' => [
                'header' => [
                    'itemId',
                    'testId',
                    'metadata_alias',
                ],
                'metadataHeader' => [
                    'metadata_alias',
                ],
                'isExceptionExpected' => false,
            ],
            'Invalid - missed itemId' => [
                'header' => [
                    'testId',
                    'metadata_alias',
                ],
                'metadataHeader' => [
                    'metadata_alias',
                ],
                'isExceptionExpected' => true,
            ],
            'Invalid - missed testId' => [
                'header' => [
                    'itemId',
                    'metadata_alias',
                ],
                'metadataHeader' => [
                    'metadata_alias',
                ],
                'isExceptionExpected' => true,
            ],
            'Invalid - missed metadata' => [
                'header' => [
                    'itemId',
                    'testId',
                ],
                'metadataHeader' => [],
                'isExceptionExpected' => true,
            ],
            'Invalid - missed all' => [
                'header' => [],
                'metadataHeader' => [],
                'isExceptionExpected' => true,
            ],
        ];
    }
}
