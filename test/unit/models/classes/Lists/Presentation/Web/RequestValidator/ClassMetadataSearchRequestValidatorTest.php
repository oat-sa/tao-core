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
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\Presentation\Web\RequestValidator;

use common_exception_BadRequest as BadRequestException;
use Exception;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Lists\Presentation\Web\RequestValidator\ClassMetadataSearchRequestValidator;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ClassMetadataSearchRequestValidatorTest
 *
 * @package oat\tao\test\unit\model\Lists\Presentation\Web\RequestValidator
 */
class ClassMetadataSearchRequestValidatorTest extends TestCase
{
    /** @var ClassMetadataSearchRequestValidator */
    private $sut;

    /**
     * @before
     */
    public function init(): void
    {
        $this->sut = new ClassMetadataSearchRequestValidator();
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
                    'classUri' => 'https_2_example_0_com_1_path_3_fragment',
                ],
            ],
            'Valid request with maxListSize'             => [
                [
                    'classUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'maxListSize' => '20',
                ],
            ],
            'Missing classUri' => [
                [
                    'maxListSize' => '20',
                ],
                new BadRequestException('The following query parameters must be provided: "classUri".'),
            ],

            'Invalid maxListSize' => [
                [
                    'classUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'maxListSize' => 'invalid',
                ],
                new BadRequestException('The parameter maxListSize should be a positive integer, got: "invalid".'),
            ],
        ];
    }

    private function createRequest(array $queryParameters): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $request
            ->expects($this->atLeastOnce())
            ->method('getQueryParams')
            ->willReturn($queryParameters);

        return $request;
    }
}
