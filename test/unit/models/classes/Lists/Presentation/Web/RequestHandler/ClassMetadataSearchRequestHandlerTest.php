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
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\Presentation\Web\RequestHandler;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Lists\Business\Domain\ClassMetadataSearchRequest;
use oat\tao\model\Lists\Business\Input\ClassMetadataSearchInput;
use oat\tao\model\Lists\Presentation\Web\RequestHandler\ClassMetadataSearchRequestHandler;
use oat\tao\model\Lists\Presentation\Web\RequestValidator\ClassMetadataSearchRequestValidator;
use Psr\Http\Message\ServerRequestInterface;
use tao_helpers_form_elements_Calendar;

class ClassMetadataSearchRequestHandlerTest extends TestCase
{
    /** @var ClassMetadataSearchRequestValidator|MockObject */
    private $requestValidatorMock;

    /** @var ClassMetadataSearchRequestHandler|MockObject */
    private $sut;

    /**
     * @before
     */
    public function init(): void
    {
        $this->requestValidatorMock = $this->createMock(ClassMetadataSearchRequestValidator::class);

        $this->sut = new ClassMetadataSearchRequestHandler($this->requestValidatorMock);
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param ClassMetadataSearchRequest $expectedRequest
     * @param array $queryParameters
     *
     * @dataProvider dataProvider
     */
    public function testHandle(ClassMetadataSearchRequest $expectedRequest, array $queryParameters): void
    {
        $request = $this->createRequest($queryParameters);

        $this->requestValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            new ClassMetadataSearchInput($expectedRequest),
            $this->sut->handle($request)
        );
    }

    public function testValidationException(): void
    {
        $request = $this->createRequest();

        $exception = new Exception();

        $this->expectExceptionObject($exception);

        $this->requestValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willThrowException($exception);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->sut->handle($request);
    }

    public function dataProvider(): array
    {
        return [
            'Bare request' => [
                $this->createBareSearchRequest()
                    ->setClassUri('https://example.com/path#fragment'),
                [
                    'classUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'maxListSize' => 5
                ],
            ],
            'Request with maxListSize' => [
                $this->createBareSearchRequest()
                    ->setClassUri('https://example.com/path#fragment')
                    ->setMaxListSize(20),
                [
                    'classUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'maxListSize' => 20
                ],
            ],
        ];
    }

    private function createRequest(array $queryParameters = []): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $request
            ->expects($queryParameters ? $this->once() : $this->never())
            ->method('getQueryParams')
            ->willReturn($queryParameters);

        return $request;
    }

    private function createBareSearchRequest(): ClassMetadataSearchRequest
    {
        return (new ClassMetadataSearchRequest())
            ->setMaxListSize(5)
            ->ignoreWidgets(
                [
                    tao_helpers_form_elements_Calendar::WIDGET_ID,
                ]
            );
    }
}
