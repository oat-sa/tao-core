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
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use oat\oatbox\extension\AbstractAction;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use oat\tao\model\webhooks\configEntity\WebhookAuthInterface;
use oat\tao\model\webhooks\configEntity\WebhookInterface;
use oat\tao\model\webhooks\log\WebhookEventLogInterface;
use oat\tao\model\webhooks\WebhookRegistryInterface;
use oat\tao\model\webhooks\WebhookTaskServiceInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WebhookTask extends AbstractAction implements TaskAwareInterface
{
    use LoggerAwareTrait;
    use TaskAwareTrait;

    /**
     * @var WebhookTaskParams
     */
    private $params;

    /**
     * @param array $paramsArray
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
            $this->logException($exception);
            return \common_report_Report::createFailure($exception->getMessage());
        }
    }

    /**
     * @param WebhookInterface $webhookConfig
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
                'Accept' => $this->getWebhookResponseFactory()->getAcceptedContentType()
            ],
            $body
        );
    }

    /**
     * @param RequestInterface $request
     * @param WebhookAuthInterface|null $authConfig
     * @return \common_report_Report
     * @throws GuzzleException
     * @throws \common_exception_InvalidArgumentType
     */
    private function performRequest(RequestInterface $request, WebhookAuthInterface $authConfig = null)
    {
        try {
            $response = $this->getWebhookSender()->performRequest($request, $authConfig);
        } catch (BadResponseException $badResponseException) {
            return $this->handleBadResponseException($badResponseException);
        } catch (ConnectException $connectException) {
            return $this->handleConnectException($connectException);
        }

        return $this->handleResponse($response);
    }

    /**
     * @param ConnectException $exception
     * @return \common_report_Report
     */
    private function handleConnectException(ConnectException $exception)
    {
        $this->getWebhookEventLog()->storeNetworkErrorLog($this->getTaskContext(), $exception->getMessage());
        $this->retryTask();
        return $this->reportError('Connection exception: ' . $exception->getMessage());
    }

    /**
     * @param BadResponseException $badResponseException
     * @return \common_report_Report
     */
    private function handleBadResponseException(BadResponseException $badResponseException)
    {
        $statusCode = $badResponseException->getResponse()
            ? $badResponseException->getResponse()->getStatusCode()
            : 0;
        $this->getWebhookEventLog()->storeInvalidHttpStatusLog($this->getTaskContext(), $statusCode);

        if ($badResponseException instanceof ServerException) {
            $this->retryTask();
        }

        return $this->reportError(
            'Bad response: ' . $badResponseException->getMessage(),
            $badResponseException->getResponse()
        );
    }

    /**
     * @param ResponseInterface $response
     * @return \common_report_Report
     */
    private function handleResponse(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        if (!$this->isAcceptableResponseStatusCode($statusCode)) {
            $this->getWebhookEventLog()->storeInvalidHttpStatusLog($this->getTaskContext(), $statusCode);

            $this->retryTask();
            return $this->reportError("Response status code is $statusCode", $response);
        }

        $parsedResponse = $this->getWebhookResponseFactory()->create($response);
        if ($parsedResponse->getParseError()) {
            $this->getWebhookEventLog()->storeInvalidBodyFormat($this->getTaskContext(), $response->getBody());
        }

        $eventId = $this->params->getEventId();
        if (!$parsedResponse->isDelivered($eventId)) {
            if (!$parsedResponse->getParseError()) {
                $this->getWebhookEventLog()->storeInvalidAcknowledgementLog($this->getTaskContext(), $parsedResponse->getStatus($eventId));
            }

            return $this->reportError(
                "Event '" . $eventId . "' wasn't delivered.",
                $response,
                $parsedResponse
            );
        }

        $eventStatus = $parsedResponse->getStatus($eventId);

        $this->getWebhookEventLog()->storeSuccessfulLog($this->getTaskContext(), $response->getBody(), $eventStatus);

        return \common_report_Report::createSuccess("Event delivered with '$eventStatus' status");
    }

    /**
     * @param int $httpStatusCode
     * @return bool
     */
    private function isAcceptableResponseStatusCode($httpStatusCode)
    {
        return $httpStatusCode >= 200 && $httpStatusCode < 300;
    }

    /**
     * @param string $message
     * @param ResponseInterface|null $response
     * @param WebhookResponse|null $parsedResponse
     * @return \common_report_Report
     */
    private function reportError(
        $message,
        ResponseInterface $response = null,
        WebhookResponse $parsedResponse = null
    )
    {
        $errors = [
            'message' => $message
        ];
        $context = [];

        if ($parsedResponse) {
            if ($parsedResponse->getParseError()) {
                $errors['parse'] = 'Parse error: ' . $parsedResponse->getParseError();
            }
            $status = $parsedResponse->getStatus($this->params->getEventId());
            $errors['status'] = $status !== null
                ? "Event status: '$status'"
                : 'eventId not found in response';
        }

        if ($response) {
            $context['httpStatus'] = $response->getStatusCode();
            $context['responseBody'] = (string)$response->getBody();
        }

        $this->logErrorWithTaskContext(implode('; ', $errors), $context);

        unset($errors['parse']);
        return \common_report_Report::createFailure(implode(PHP_EOL, $errors));
    }

    /**
     * @param \Exception $exception
     */
    private function logException(\Exception $exception)
    {
        $exceptionString = sprintf(
            '%s exception in %s:%d: %s',
            get_class($exception),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getMessage()
        );

        $this->getWebhookEventLog()->storeInternalErrorLog($this->getTaskContext(), $exceptionString);

        $this->logErrorWithTaskContext($exceptionString);
    }

    /**
     * @param string $message
     * @param array $context
     */
    private function logErrorWithTaskContext($message, $context = [])
    {
        $context['taskId'] = $this->getTask()->getId();
        $context['eventId'] = $this->params->getEventId();
        $this->logError($message, $context);
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
     * @return WebhookEventLogInterface
     */
    private function getWebhookEventLog()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookEventLogInterface::SERVICE_ID);
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
