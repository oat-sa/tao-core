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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\notifiers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Ramsey\Uuid\Uuid;

class GrafanaNotifier implements NotifierInterface
{
    /** @var string */
    private $notificationEndpoint;
    /** @var array */
    private $parameters;

    /**
     * @param string $notificationEndpoint - endpoint for notification specified in Grafana integration
     * @param array $parameters - list of parameters can be configured specifically for application stack
     */
    public function __construct(string $notificationEndpoint, array $parameters = [])
    {
        $this->notificationEndpoint = $notificationEndpoint;
        $this->parameters = $parameters;
    }

    /**
     * @inheritDoc
     */
    public function notify(string $title, string $description): array
    {
        $client = new Client();
        try {
            $response = $client->request('POST', $this->notificationEndpoint, [
                RequestOptions::JSON => $this->buildPayload($title, $description)
            ]);
            return [
                'status' => true,
                'code' => $response->getStatusCode(),
                'message' => $response->getBody()->getContents()
            ];
        } catch (GuzzleException $e) {
            return [
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }

    private function buildPayload(string $title, string $description): array
    {
        $payload = [
            "alert_uuid" => Uuid::uuid4()->toString(),
            "title" => $title,
            "message" => $description,
            'source' => defined(ROOT_URL) ? ROOT_URL : null
        ];


        if (count($this->parameters) > 0) {
            $payload['extra'] = $this->parameters;
        }

        return $payload;
    }
}
