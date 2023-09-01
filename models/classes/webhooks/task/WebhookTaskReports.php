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
use GuzzleHttp\Exception\RequestException;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\webhooks\log\WebhookEventLogInterface;
use Psr\Http\Message\ResponseInterface;

class WebhookTaskReports extends ConfigurableService
{
    /**
     * @param WebhookTaskContext $taskContext
     * @param \Exception $exception
     * @return \common_report_Report
     */
    public function reportInternalException(WebhookTaskContext $taskContext, \Exception $exception)
    {
        $message = $this->getExceptionMessage($exception, true);
        $this->getWebhookEventLog()->storeInternalErrorLog($taskContext, $message);
        return $this->reportError($taskContext, 'Internal error: ' . $message);
    }

    /**
     * @param WebhookTaskContext $taskContext
     * @param ConnectException $exception
     * @return \common_report_Report
     */
    public function reportConnectException(WebhookTaskContext $taskContext, ConnectException $exception)
    {
        $this->getWebhookEventLog()->storeNetworkErrorLog($taskContext, $exception->getMessage());
        return $this->reportError($taskContext, 'Connection exception: ' . $exception->getMessage());
    }

    /**
     * @param WebhookTaskContext $taskContext
     * @param RequestException $exception
     * @return \common_report_Report
     */
    public function reportRequestException(WebhookTaskContext $taskContext, RequestException $exception)
    {
        $message = $exception->getMessage();
        if ($response = $exception->getResponse()) {
            $this->getWebhookEventLog()->storeInvalidHttpStatusLog(
                $taskContext,
                $response->getStatusCode(),
                $response->getBody()
            );
            return $this->reportError($taskContext, 'Request exception: ' . $exception->getMessage(), $response);
        }

        $this->getWebhookEventLog()->storeNetworkErrorLog($taskContext, $message);
        return $this->reportError($taskContext, 'Request exception: ' . $exception->getMessage());
    }

    /**
     * @param WebhookTaskContext $taskContext
     * @param BadResponseException $badResponseException
     * @return \common_report_Report
     */
    public function reportBadResponseException(
        WebhookTaskContext $taskContext,
        BadResponseException $badResponseException
    ) {
        $statusCode = $badResponseException->getResponse()
            ? $badResponseException->getResponse()->getStatusCode()
            : 0;
        $this->getWebhookEventLog()->storeInvalidHttpStatusLog($taskContext, $statusCode);

        return $this->reportError(
            $taskContext,
            'Bad response: ' . $badResponseException->getMessage(),
            $badResponseException->getResponse()
        );
    }

    /**
     * @param WebhookTaskContext $taskContext
     * @param ResponseInterface $response
     * @return \common_report_Report
     */
    public function reportInvalidStatusCode(WebhookTaskContext $taskContext, ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        $this->getWebhookEventLog()->storeInvalidHttpStatusLog($taskContext, $statusCode);
        return $this->reportError($taskContext, "Response status code is $statusCode", $response);
    }

    /**
     * @param WebhookTaskContext $taskContext
     * @param ResponseInterface $response
     * @return \common_report_Report
     */
    public function reportInvalidBodyFormat(WebhookTaskContext $taskContext, ResponseInterface $response)
    {
        $this->getWebhookEventLog()->storeInvalidBodyFormat($taskContext, $response->getBody());
        return $this->reportError(
            $taskContext,
            "Event '" . $this->getEventId($taskContext) . "' wasn't delivered.",
            $response
        );
    }

    /**
     * @param WebhookTaskContext $taskContext
     * @param ResponseInterface $response
     * @param WebhookResponse $parsedResponse
     * @return \common_report_Report
     */
    public function reportInvalidAcknowledgement(WebhookTaskContext $taskContext, $response, $parsedResponse)
    {
        $eventId = $this->getEventId($taskContext);

        $this->getWebhookEventLog()->storeInvalidAcknowledgementLog(
            $taskContext,
            $parsedResponse->getStatus($eventId)
        );

        return $this->reportError(
            $taskContext,
            "Event '" . $eventId . "' wasn't delivered.",
            $response,
            $parsedResponse
        );
    }

    /**
     * @param WebhookTaskContext $taskContext
     * @param ResponseInterface $response
     * @param string $eventStatus
     * @return \common_report_Report
     */
    public function reportSuccess(WebhookTaskContext $taskContext, $response, $eventStatus)
    {
        $this->getWebhookEventLog()->storeSuccessfulLog($taskContext, $response->getBody(), $eventStatus);
        return \common_report_Report::createSuccess("Event delivered with '$eventStatus' status");
    }

    /**
     * @param WebhookTaskContext $taskContext
     * @param string $message
     * @param array $context
     */
    private function logErrorWithContext(WebhookTaskContext $taskContext, $message, $context = [])
    {
        $context['taskId'] = $taskContext->getTaskId();
        $context['eventId'] = $this->getEventId($taskContext);
        $this->logError($message, $context);
    }

    /**
     * Adds error to log and returns report
     * @param WebhookTaskContext $taskContext
     * @param string $message
     * @param ResponseInterface|null $response
     * @param WebhookResponse|null $parsedResponse
     * @return \common_report_Report
     */
    private function reportError(
        WebhookTaskContext $taskContext,
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
            $status = $parsedResponse->getStatus($this->getEventId($taskContext));
            $errors['status'] = $status !== null
                ? "Event status: '$status'"
                : 'eventId not found in response';
        }

        if ($response) {
            $context['httpStatus'] = $response->getStatusCode();
            $context['responseBody'] = (string)$response->getBody();
        }

        $this->logErrorWithContext($taskContext, implode('; ', $errors), $context);

        unset($errors['parse']);
        return \common_report_Report::createFailure(implode(PHP_EOL, $errors));
    }

    /**
     * Get exception message, append UserMessage if exception is common_exception_ClientException
     * @param \Exception|\common_exception_ClientException $exception
     * @param bool $includeSourceInfo Add information about file, line and exception type
     * @return string|null
     */
    private function getExceptionMessage(\Exception $exception, $includeSourceInfo = false)
    {
        $messages = [];
        if ($includeSourceInfo) {
            $messages[] = sprintf(
                '%s in %s:%d',
                get_class($exception),
                $exception->getFile(),
                $exception->getLine()
            );
        }
        if ($exception->getMessage() !== null) {
            $messages[] = $exception->getMessage();
        }
        if ($exception instanceof \common_exception_ClientException) {
            $messages[] = 'User message: ' . $exception->getUserMessage();
        }
        if (count($messages) > 0) {
            return implode('. ', $messages);
        }
        return null;
    }

    /**
     * @param WebhookTaskContext $taskContext
     * @return string
     */
    private function getEventId(WebhookTaskContext $taskContext)
    {
        $taskParams = $taskContext->getWebhookTaskParams();
        if (!$taskParams) {
            throw new \InvalidArgumentException("Task context doesn't contain task params");
        }
        return $taskParams->getEventId();
    }

    /**
     * @return WebhookEventLogInterface
     */
    private function getWebhookEventLog()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookEventLogInterface::SERVICE_ID);
    }
}
