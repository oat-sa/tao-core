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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\notifiers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JTL\OpsGenie\Client\Alert\Alert;
use JTL\OpsGenie\Client\Alert\CreateAlertRequest;
use JTL\OpsGenie\Client\AlertApiClient;
use JTL\OpsGenie\Client\HttpClient;
use JTL\OpsGenie\Client\Priority;
use JTL\OpsGenie\Client\Responder;

/**
 * Sends a notification to OpsGenie
 *
 * Class OpsGenieNotifier
 * @author Andrey Niahrou <Andrei.Niahrou@1pt.com>
 * @package oat\tao\model\notifiers
 */
class OpsGenieNotifier implements NotifierInterface
{
    private const OPTION_BASE_URI = 'https://api.eu.opsgenie.com/v2/';

    /**
     * @var string
     */
    private $token;

    /**
     * @var array
     */
    private $parameters;

    public function __construct(string $token, array $parameters = [])
    {
        $this->token = $token;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritDoc}
     *
     * @throws GuzzleException
     */
    public function notify(string $title, string $description): array
    {
        $alert = $this->buildAlertModel($title, $description);
        $request = new CreateAlertRequest($alert);
        $client = $this->getAlertApiClient($this->token);
        $response = $client->createAlert($request);

        return [
            'status' => $response->isSuccessful(),
            'code' => $response->getStatusCode(),
            'message' => $response->getMessage()
        ];
    }

    /**
     * @param string $title
     * @param string $description
     * @return Alert
     */
    private function buildAlertModel(string $title, string $description): Alert
    {
        $alert = new Alert(
            isset($this->parameters['entity']) ? $this->parameters['entity'] : '',
            isset($this->parameters['alias']) ? $this->parameters['alias'] : '',
            $title,
            isset($this->parameters['source']) ? $this->parameters['source'] : '',
            isset($this->parameters['priority']) ? new Priority($this->parameters['priority']) : Priority::moderate()
        );

        $alert->setDescription($description);

        if (isset($this->parameters['tags'])) {
            foreach ($this->parameters['tags'] as $tag) {
                $alert->appendTag($tag);
            }
        }

        if (isset($this->parameters['responders'])) {
            foreach ($this->parameters['responders'] as $responder) {
                $responderObj = new Responder($responder['id'], $responder['type']);
                $alert->appendResponder($responderObj);
            }
        }

        return $alert;
    }

    /**
     * @param string $authToken
     * @return AlertApiClient
     */
    private function getAlertApiClient(string $authToken): AlertApiClient
    {
        $guzzleClient = new Client(
            [
                'base_uri' => self::OPTION_BASE_URI
            ]
        );

        return new AlertApiClient(new HttpClient($authToken, $guzzleClient));
    }
}
