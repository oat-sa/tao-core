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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\model\http\builder;

use GuzzleHttp\Psr7\Response;
use JsonSerializable;
use oat\oatbox\service\ConfigurableService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ResponseBuilder extends ConfigurableService
{
    /** @var int */
    private $statusCode = 200;

    /** @var string[] */
    private $headers = [
        'Content-Type' => 'application/json; charset=UTF-8'
    ];

    /** @var string[] */
    private $body;

    public function withStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * @param string|array|JsonSerializable|null|resource|StreamInterface $body
     *
     * @return $this
     */
    public function withBody($body): self
    {
        $this->body = is_array($body) || $body instanceof JsonSerializable ? json_encode($body) : $body;

        return $this;
    }

    public function build(): ResponseInterface
    {
        return new Response(
            $this->statusCode,
            $this->headers,
            $this->body
        );
    }
}
