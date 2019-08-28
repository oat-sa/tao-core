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

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use oat\oatbox\extension\AbstractAction;
use oat\oatbox\log\LoggerAwareTrait;
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
        } catch (ClientException $clientException) {
            return $this->reportError(
                'Client exception: ' . $clientException->getMessage(),
                $clientException->getResponse()
            );
        } catch (ConnectException $exception) {
            $this->retryTask();
            return $this->reportError('Connection exception: ' . $exception->getMessage());
        }

        return $this->handleResponse($response);
    }

    private function handleResponse(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        if (!$this->isAcceptableResponseStatusCode($statusCode)) {
            $this->retryTask();
            return $this->reportError("Response status code is $statusCode", $response);
        }

        $parsedResponse = $this->getWebhookResponseFactory()->create($response);

        if (!$parsedResponse->isDelivered($this->params->getEventId())) {
            return $this->reportError(
                "Event '" . $this->params->getEventId() . "' wasn't delivered.",
                $response,
                $parsedResponse
            );
        }

        $eventStatus = $parsedResponse->getStatus($this->params->getEventId());
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
    ) {
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
            $context['responseBody'] = (string) $response->getBody();
        }

        $this->logErrorWithTaskContext(implode(PHP_EOL, $errors), $context);

        unset($errors['parse']);
        return \common_report_Report::createFailure(implode(PHP_EOL, $errors));
    }

    private function logException(\Exception $exception)
    {
        $this->logErrorWithTaskContext(sprintf(
            '%s exception in %s:%d: %s',
            get_class($exception),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getMessage()
        ));
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
        return $this->getServiceLocator()->get(WebhookTaskServiceInterface::SERVICE_ID);
    }

    private function retryTask()
    {
        if (!$this->params->isMaxRetryCountReached()) {
            $this->params->increaseRetryCount();
            $this->getWebhookTaskService()->createTask($this->params);
        }
    }
}
