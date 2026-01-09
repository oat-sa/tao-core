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
 *
 */

namespace oat\tao\model\http\formatter;

use JsonSerializable;
use oat\oatbox\service\ConfigurableService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7\Utils;

class ResponseFormatter extends ConfigurableService
{
    /** @var int */
    private $statusCode;

    /** @var string[] */
    private $headers = [];

    /** @var StreamInterface */
    private $body;

    public function withStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function withJsonHeader(): self
    {
        return $this->addHeader('Content-Type', 'application/json');
    }

    public function withExpiration(int $timestamp): self
    {
        return $this->addHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', $timestamp));
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
        $this->body = Utils::streamFor(is_array($body) || $body instanceof JsonSerializable ? json_encode($body) : $body);

        return $this;
    }

    public function format(ResponseInterface $response): ResponseInterface
    {
        if ($this->body) {
            $response = $response->withBody($this->body);
        }

        if ($this->statusCode) {
            $response = $response->withStatus($this->statusCode);
        }

        foreach ($this->headers as $headerName => $headerValue) {
            $response = $response->withHeader($headerName, $headerValue);
        }

        return $response;
    }
}
