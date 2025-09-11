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

namespace oat\tao\test\unit\model\Lists\Presentation\Web\RequestHandler;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use oat\tao\model\Lists\Presentation\Web\RequestHandler\ValueCollectionSearchRequestHandler;
use oat\tao\model\Lists\Presentation\Web\RequestValidator\ValueCollectionSearchRequestValidator;
use Psr\Http\Message\ServerRequestInterface;

class ValueCollectionSearchRequestHandlerTest extends TestCase
{
    /** @var ValueCollectionSearchRequestValidator|MockObject */
    private $requestValidatorMock;

    /** @var ValueCollectionSearchRequestHandler|MockObject */
    private $sut;

    /**
     * @before
     */
    public function init(): void
    {
        $this->requestValidatorMock = $this->createMock(ValueCollectionSearchRequestValidator::class);

        $this->sut = $this->getMockBuilder(ValueCollectionSearchRequestHandler::class)
            ->setConstructorArgs([$this->requestValidatorMock])
            ->onlyMethods(['getPropertyListUri'])
            ->getMock();
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param ValueCollectionSearchRequest $expectedRequest
     * @param array                        $queryParameters
     *
     * @dataProvider dataProvider
     */
    public function testHandle(ValueCollectionSearchRequest $expectedRequest, array $queryParameters): void
    {
        $request = $this->createRequest($queryParameters);

        $this->requestValidatorMock
            ->expects(static::once())
            ->method('validate')
            ->with($request);

        $this->sut
            ->expects(static::once())
            ->method('getPropertyListUri')
            ->willReturn($queryParameters['valueCollectionUri'] ?? null);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            new ValueCollectionSearchInput($expectedRequest),
            $this->sut->handle($request)
        );
    }

    public function testValidationException(): void
    {
        $request = $this->createRequest();

        $exception = new Exception();

        $this->expectExceptionObject($exception);

        $this->requestValidatorMock
            ->expects(static::once())
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
                    ->setPropertyUri('https://example.com/path#fragment'),
                [
                    'propertyUri' => 'https_2_example_0_com_1_path_3_fragment',
                ],
            ],
            'Request with parent list values' => [
                $this->createBareSearchRequest()
                    ->setParentListValues(...['https://example.com/path#fragment'])
                    ->setPropertyUri('https://example.com/path#fragment'),
                [
                    'propertyUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'parentListValues' => ['https_2_example_0_com_1_path_3_fragment'],
                ],
            ],
            'Request with value collection' => [
                $this->createBareSearchRequest()
                    ->setPropertyUri('https://example.com/path#fragment')
                    ->setValueCollectionUri('https://example.com/path#vc_fragment'),
                [
                    'propertyUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'valueCollectionUri' => 'https://example.com/path#vc_fragment',
                ],
            ],
            'Request with subject' => [
                $this->createBareSearchRequest()
                    ->setPropertyUri('https://example.com/path#fragment')
                    ->setSubject('test'),
                [
                    'propertyUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'subject' => 'test',
                ],
            ],
            'Request with excluded' => [
                $this->createBareSearchRequest()
                    ->setPropertyUri('https://example.com/path#fragment')
                    ->addExcluded('https://example.com/path#fragment1')
                    ->addExcluded('https://example.com/path#fragment2'),
                [
                    'propertyUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'exclude' => [
                        'https_2_example_0_com_1_path_3_fragment1',
                        'https_2_example_0_com_1_path_3_fragment2',
                    ],
                ],
            ],
            'Request with excluded and subject' => [
                $this->createBareSearchRequest()
                    ->setPropertyUri('https://example.com/path#fragment')
                    ->setSubject('test')
                    ->addExcluded('https://example.com/path#fragment1')
                    ->addExcluded('https://example.com/path#fragment2'),
                [
                    'propertyUri' => 'https_2_example_0_com_1_path_3_fragment',
                    'subject' => 'test',
                    'exclude' => [
                        'https_2_example_0_com_1_path_3_fragment1',
                        'https_2_example_0_com_1_path_3_fragment2',
                    ],
                ],
            ],
        ];
    }

    private function createRequest(array $queryParameters = []): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $request
            ->expects($queryParameters ? static::once() : static::never())
            ->method('getQueryParams')
            ->willReturn($queryParameters);

        return $request;
    }

    private function createBareSearchRequest(): ValueCollectionSearchRequest
    {
        return (new ValueCollectionSearchRequest())
            ->setLimit(20);
    }
}
