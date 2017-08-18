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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\api;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Class ApiClientConnector
 *
 * Class to handle a http connection.
 *
 */
class ApiClientConnector implements ClientInterface
{
    /**
     * Send an HTTP request.
     *
     * @param RequestInterface $request Request to send
     * @param array            $options Request options to apply to the given
     *                                  request and to the transfer.
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function send(RequestInterface $request, array $options = [])
    {
        return $this->getClient()->send($request, $options);
    }

    /**
     * Create and send an HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string              $method  HTTP method.
     * @param string|UriInterface $uri     URI object or string.
     * @param array               $options Request options to apply.
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function request($method, $uri, array $options = [])
    {
        $request = new Request($method, $uri);
        if (!empty($data)) {
            $body = stream_for(json_encode($data));
            $request = $request->withBody($body)->withAddedHeader('Content-Type', 'application/json');
        }

        $response = $this->send($request);
        return $response;
    }

    /**
     * Return the Http Client e.q. Guzzle client
     *
     * @return Client
     */
    protected function getClient()
    {
        return new Client();
    }

    public function sendAsync(RequestInterface $request, array $options = [])
    {
        throw new \common_exception_NotImplemented(__METHOD__ . ' is not implemented.');
    }

    public function requestAsync($method, $uri, array $options = [])
    {
        throw new \common_exception_NotImplemented(__METHOD__ . ' is not implemented.');
    }

    public function getConfig($option = null)
    {
        throw new \common_exception_NotImplemented(__METHOD__ . ' is not implemented.');
    }

}