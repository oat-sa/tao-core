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
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\Presentation\Web\RequestValidator;

use common_exception_BadRequest as BadRequestException;
use Exception;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Lists\Presentation\Web\RequestValidator\ValueCollectionSearchRequestValidator;
use Psr\Http\Message\ServerRequestInterface;

class ValueCollectionSearchRequestValidatorTest extends TestCase
{
    /** @var ValueCollectionSearchRequestValidator */
    private $sut;

    /**
     * @before
     */
    public function init(): void
    {
        $this->sut = new ValueCollectionSearchRequestValidator();
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param array          $queryParameters
     * @param Exception|null $expectedException
     *
     * @dataProvider dataProvider
     */
    public function testValidate(array $queryParameters, Exception $expectedException = null): void
    {
        if (null !== $expectedException) {
            $this->expectExceptionObject($expectedException);
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->sut->validate(
            $this->createRequest($queryParameters)
        );
    }

    public function dataProvider(): array
    {
        return [
            'Valid request'                          => [
                [
                    'propertyUri' => 'https_2_example_0_com_1_path_3_fragment',
                ],
            ],
            'Valid request with subject'             => [
                [
                    'propertyUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'subject'     => 'test',
                ],
            ],
            'Valid request with exclude'             => [
                [
                    'propertyUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'exclude'     => [
                        'https_2_example_0_com_1_path_3_fragment1',
                        'https_2_example_0_com_1_path_3_fragment2',
                    ],
                ],
            ],
            'Valid request with exclude and subject' => [
                [
                    'propertyUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'subject'     => 'test',
                    'exclude'     => [
                        'https_2_example_0_com_1_path_3_fragment1',
                        'https_2_example_0_com_1_path_3_fragment2',
                    ],
                ],
            ],
            'Missing propertyUri'                    => [
                [
                    'subject' => 'test',
                ],
                new BadRequestException('The following query parameters must be provided: "propertyUri".'),
            ],
            'Invalid subject type'                   => [
                [
                    'propertyUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'subject'     => ['array_value'],
                ],
                new BadRequestException('"subject" query parameter is expected to be of string type, array given.'),
            ],
            'Invalid exclude type'                   => [
                [
                    'propertyUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'exclude'     => 'string_value',
                ],
                new BadRequestException('"exclude" query parameter is expected to be of array type, string given.'),
            ],
            'Invalid exclude item type'              => [
                [
                    'propertyUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'exclude'     => [
                        'https_2_example_0_com_1_path_3_fragment1',
                        ['array_value'],
                    ],
                ],
                new BadRequestException('"exclude[1]" query parameter is expected to be of string type, array given.'),
            ],
        ];
    }

    private function createRequest(array $queryParameters): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $request
            ->expects(static::atLeastOnce())
            ->method('getQueryParams')
            ->willReturn($queryParameters);

        return $request;
    }
}
