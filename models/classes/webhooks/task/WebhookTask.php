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

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use oat\oatbox\extension\AbstractAction;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use oat\tao\model\webhooks\configEntity\WebhookAuthInterface;
use oat\tao\model\webhooks\configEntity\WebhookInterface;
use oat\tao\model\webhooks\WebhookRegistryInterface;
use oat\tao\model\webhooks\WebhookTaskServiceInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WebhookTask extends AbstractAction implements TaskAwareInterface
{
    use TaskAwareTrait;

    /**
     * @var WebhookTaskParams
     */
    private $params;

    /**
     * @param array $paramsArray
     *
     * @return \common_report_Report
     * @throws GuzzleException
     */
    public function __invoke($paramsArray)
    {
        try {
            $this->params = $this->getWebhookTaskParamsFactory()->createFromArray($paramsArray);
            $webhookConfig = $this->getWebhookConfig();
            $request = $this->prepareRequest($webhookConfig);
            return $this->performRequest($request, $webhookConfig->getAuth());
        } catch (\Exception $exception) {
            return $this->getWebhookTaskReports()->reportInternalException($this->getTaskContext(), $exception);
        }
    }

    /**
     * @param WebhookInterface $webhookConfig
     *
     * @return RequestInterface
     * @throws \common_Exception
     */
    private function prepareRequest(WebhookInterface $webhookConfig)
    {
        $payloadFactory = $this->getWebhookPayloadFactory();

        $body = $payloadFactory->createPayload(
            $this->params->getEventName(),
            $this->params->getEventId(),
            $this->params->getTriggeredTimestamp(),
            $this->params->getEventData()
        );

        return new Request(
            $webhookConfig->getHttpMethod(),
            $webhookConfig->getUrl(),
            [
                'Content-Type' => $payloadFactory->getContentType(),
                'Accept' => $this->getWebhookResponseFactory()->getAcceptedContentType(),
            ],
            $body
        );
    }

    /**
     * @param RequestInterface $request
     * @param WebhookAuthInterface|null $authConfig
     *
     * @return \common_report_Report
     * @throws GuzzleException
     * @throws \common_exception_InvalidArgumentType
     */
    private function performRequest(RequestInterface $request, WebhookAuthInterface $authConfig = null)
    {
        $errorReport = $response = null;
        try {
            $response = $this->getWebhookSender()->performRequest($request, $authConfig);
        } catch (ServerException $connectException) {
            $this->retryTask();
            $errorReport = $this->getWebhookTaskReports()->reportBadResponseException(
                $this->getTaskContext(),
                $connectException
            );
        } catch (BadResponseException $badResponseException) {
            $errorReport = $this->getWebhookTaskReports()->reportBadResponseException(
                $this->getTaskContext(),
                $badResponseException
            );
        } catch (ConnectException $connectException) {
            $this->retryTask();
            $errorReport = $this->getWebhookTaskReports()->reportConnectException(
                $this->getTaskContext(),
                $connectException
            );
        } catch (RequestException $requestException) {
            $errorReport = $this->getWebhookTaskReports()->reportRequestException(
                $this->getTaskContext(),
                $requestException
            );
        }

        return $response
            ? $this->handleResponse($response)
            : $errorReport;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return \common_report_Report
     */
    private function handleResponse(ResponseInterface $response)
    {
        if (!$this->isAcceptableResponseStatusCode($response->getStatusCode())) {
            $this->retryTask();
            return $this->getWebhookTaskReports()->reportInvalidStatusCode($this->getTaskContext(), $response);
        }

        $parsedResponse = $this->getWebhookResponseFactory()->create($response);
        $eventId = $this->params->getEventId();

        if ($this->params->responseValidation()) {
            if ($this->params->getRetryMax() && $parsedResponse->getParseError()) {
                return $this->getWebhookTaskReports()->reportInvalidBodyFormat($this->getTaskContext(), $response);
            }

            if (!$parsedResponse->isDelivered($eventId)) {
                return $this->getWebhookTaskReports()->reportInvalidAcknowledgement(
                    $this->getTaskContext(),
                    $response,
                    $parsedResponse
                );
            }
        }

        return $this->getWebhookTaskReports()->reportSuccess(
            $this->getTaskContext(),
            $response,
            $parsedResponse->getStatus($eventId)
        );
    }

    /**
     * @param int $httpStatusCode
     *
     * @return bool
     */
    private function isAcceptableResponseStatusCode($httpStatusCode)
    {
        return $httpStatusCode >= 200 && $httpStatusCode < 300;
    }

    /**
     * @return WebhookInterface
     * @throws \common_exception_NotFound
     */
    private function getWebhookConfig()
    {
        $webhookConfig = $this->getWebhookRegistry()->getWebhookConfig($this->params->getWebhookConfigId());
        if ($webhookConfig === null) {
            throw new \common_exception_NotFound("Webhook config '$webhookConfig' not found");
        }
        return $webhookConfig;
    }

    /**
     * @return WebhookRegistryInterface
     */
    private function getWebhookRegistry()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookRegistryInterface::SERVICE_ID);
    }

    /**
     * @return WebhookPayloadFactoryInterface
     */
    private function getWebhookPayloadFactory()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookPayloadFactoryInterface::SERVICE_ID);
    }

    /**
     * @return WebhookTaskParamsFactory
     */
    private function getWebhookTaskParamsFactory()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookTaskParamsFactory::class);
    }

    /**
     * @return WebhookResponseFactoryInterface
     */
    private function getWebhookResponseFactory()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookResponseFactoryInterface::SERVICE_ID);
    }

    /**
     * @return WebhookSender
     */
    private function getWebhookSender()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookSender::class);
    }

    /**
     * @return WebhookTaskServiceInterface
     */
    private function getWebhookTaskService()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookTaskServiceInterface::SERVICE_ID);
    }

    /**
     * @return WebhookTaskReports
     */
    private function getWebhookTaskReports()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookTaskReports::class);
    }

    /**
     * @return WebhookTaskContext
     */
    private function getTaskContext()
    {
        $context = new WebhookTaskContext();
        $context->setTaskId($this->getTask()->getId());
        try {
            $context->setWebhookConfig($this->getWebhookConfig());
        } catch (\Exception $e) {
        }
        try {
            $context->setWebhookTaskParams($this->params);
        } catch (\Exception $e) {
        }
        return $context;
    }

    private function retryTask()
    {
        if (!$this->params->isMaxRetryCountReached()) {
            $this->params->increaseRetryCount();
            $this->getWebhookTaskService()->createTask($this->params);
        }
    }
}
