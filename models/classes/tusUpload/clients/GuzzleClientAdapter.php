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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\tusUpload\Clients;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class GuzzleClientAdapter implements ClientAdapterInterface
{
    /** @var GuzzleClient $client */
    private $client;
    private $defaultHeaders;

    public function __construct()
    {
        $this->client = new GuzzleClient();
    }

    public function setDefaultHeaders($headers)
    {
        $this->defaultHeaders = $headers;
    }

    public function post($url, $options = [])
    {
        $options = $this->addDefaultHeaders($options);
        $response = $this->client->post($url, $options);
        return $this->formatResponse($response);
    }

    public function head($url, $options = [])
    {
        $options = $this->addDefaultHeaders($options);
        $response = $this->client->head($url, $options);
        return $this->formatResponse($response);
    }

    public function patch($url, $options = [])
    {
        $options = $this->addDefaultHeaders($options);
        $response = $this->client->patch($url, $options);
        return $this->formatResponse($response);
    }

    public function get($url, $options = [])
    {
        $options = $this->addDefaultHeaders($options);
        $response = $this->client->get($url, $options);
        return $this->formatResponse($response);
    }

    private function addDefaultHeaders($options)
    {
        foreach ($this->defaultHeaders as $key => $header) {
            if (empty($options['headers'][$key])) {
                $options['headers'][$key] = $header;
            }
        }
        return $options;
    }

    /**
     * @param ResponseInterface $response
     *
     */
    private function formatResponse($response)
    {
        return ['status' => $response->getStatusCode(), 'headers' => $response->getHeaders()];
    }
}
