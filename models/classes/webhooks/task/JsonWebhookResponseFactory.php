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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\webhooks\task;

use oat\oatbox\service\ConfigurableService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class JsonWebhookResponseFactory extends ConfigurableService implements WebhookResponseFactoryInterface
{
    const ACCEPTED_CONTENT_TYPE = 'application/json';

    const SUPPORTED_STATUSES = [
        WebhookResponse::STATUS_ACCEPTED,
        WebhookResponse::STATUS_IGNORED,
        WebhookResponse::STATUS_ERROR
    ];

    /**
     * @param ResponseInterface $response
     * @return WebhookResponse
     */
    public function create(ResponseInterface $response)
    {
        try {
            $this->validateContentType($response);
            $body = $this->decodeBody($response->getBody());
            $this->getJsonValidator()->validate($body);
            return $this->prepareResponse($body);
        } catch (InvalidJsonException $exception) {
            $errors = implode(', ', $exception->getValidationErrors());
            return new WebhookResponse([], $exception->getMessage() . ': ' . $errors);
        } catch (\Exception $exception) {
            return new WebhookResponse([], $exception->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getAcceptedContentType()
    {
        return 'application/json';
    }

    /**
     * TODO: in case of eventId duplication respects last item
     * @param \stdClass $body
     * @return WebhookResponse
     */
    private function prepareResponse($body)
    {
        $eventStatuses = [];
        foreach ($body->events as $eventRec) {
            $eventStatuses[$eventRec->eventId] = $eventRec->status;
        }
        return new WebhookResponse($eventStatuses, null);
    }

    /**
     * @param StreamInterface $body
     * @return \stdClass|mixed
     */
    private function decodeBody(StreamInterface $body)
    {
        $stringBody = (string) $body;
        $decoded = json_decode($stringBody, false);
        if ($decoded === null) {
            throw new \InvalidArgumentException('Body is not a valid json: ' . json_last_error_msg());
        }
        return $decoded;
    }

    private function validateContentType(ResponseInterface $response)
    {
        $headerValues = $response->getHeader('Content-Type');
        if (count($headerValues) > 0) {
            $contentType = reset($headerValues);
            if (strcasecmp($contentType, $this->getAcceptedContentType()) !== 0) {
                throw new \InvalidArgumentException("Unsupported Content-Type: $contentType");
            }
        }
    }

    /**
     * @return JsonValidator
     */
    private function getJsonValidator()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(JsonValidator::class);
    }
}
