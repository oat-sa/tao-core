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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

namespace oat\tao\test\unit\http;

use oat\generis\test\TestCase;
use oat\tao\model\http\builder\ResponseBuilder;
use oat\tao\model\http\response\SuccessJsonResponse;

class ResponseBuilderTest extends TestCase
{
    /**
     * @var ResponseBuilder
     */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new ResponseBuilder();
    }

    public function testBuildDefault(): void
    {
        $response = $this->subject->build();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['Content-Type' => ['application/json; charset=UTF-8']], $response->getHeaders());
        $this->assertSame('', (string)$response->getBody());
    }

    public function testBuildCustomized(): void
    {
        $body = '<html></html>';
        $statusCode = 400;
        $contentType = 'text/html';

        $response = $this->subject
            ->withStatusCode($statusCode)
            ->addHeader('Content-Type', $contentType)
            ->withBody($body)
            ->build();

        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertSame(['Content-Type' => [$contentType]], $response->getHeaders());
        $this->assertSame($body, (string)$response->getBody());
    }

    public function testBuildWithJsonSerializable(): void
    {
        $payload = new SuccessJsonResponse(['data']);

        $response = $this->subject
            ->withBody($payload)
            ->build();

        $this->assertSame($payload->jsonSerialize(), json_decode((string)$response->getBody(), true));
    }
}
