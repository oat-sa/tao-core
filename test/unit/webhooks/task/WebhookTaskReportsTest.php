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
 * Copyright (c) 2019-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\webhooks\task;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use oat\generis\test\ServiceManagerMockTrait;
use oat\oatbox\service\ServiceManager;
use PHPUnit\Framework\TestCase;
use oat\tao\model\webhooks\log\WebhookEventLogInterface;
use oat\tao\model\webhooks\task\WebhookResponse;
use oat\tao\model\webhooks\task\WebhookTaskContext;
use oat\tao\model\webhooks\task\WebhookTaskParams;
use oat\tao\model\webhooks\task\WebhookTaskReports;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class WebhookTaskReportsTest extends TestCase
{
    use ServiceManagerMockTrait;

    private WebhookEventLogInterface|MockObject $webhookEventLogMock;
    private LoggerInterface|MockObject $loggerMock;
    private ServiceManager|MockObject $serviceLocatorMock;
    private WebhookTaskContext|MockObject $taskContextMock;

    protected function setUp(): void
    {
        $this->webhookEventLogMock = $this->createMock(WebhookEventLogInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->serviceLocatorMock = $this->getServiceManagerMock([
            WebhookEventLogInterface::SERVICE_ID => $this->webhookEventLogMock,
        ]);

        $taskParamsMock = $this->createMock(WebhookTaskParams::class);
        $taskParamsMock->method('getEventId')->willReturn('eventId');

        $this->taskContextMock = $this->createMock(WebhookTaskContext::class);
        $this->taskContextMock->method('getTaskId')->willReturn('taskId');
        $this->taskContextMock->method('getWebhookTaskParams')->willReturn($taskParamsMock);
    }

    public function testReportInternalException()
    {
        $reports = new WebhookTaskReports();
        $reports->setServiceLocator($this->serviceLocatorMock);
        $reports->setLogger($this->loggerMock);

        $exception = new \Exception('e_msg');

        $this->webhookEventLogMock->expects($this->once())
            ->method('storeInternalErrorLog')
            ->with(
                $this->taskContextMock,
                $this->callback(function ($message) {
                    return strpos($message, 'e_msg') !== false &&
                        strpos($message, 'Exception') !== false;
                })
            );

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($message) {
                    return strpos($message, 'e_msg') !== false &&
                        strpos($message, 'Exception') !== false &&
                        strpos($message, __FILE__) !== false;
                }),
                $this->callback(function ($context) {
                    return $context['taskId'] === 'taskId' &&
                        $context['eventId'] === 'eventId';
                })
            );

        $report = $reports->reportInternalException($this->taskContextMock, $exception);

        $this->assertSame(\common_report_Report::TYPE_ERROR, $report->getType());
        $this->assertStringContainsString('e_msg', $report->getMessage());
        $this->assertStringContainsString('Exception', $report->getMessage());
        $this->assertStringContainsString(__FILE__, $report->getMessage());
    }

    public function testReportConnectException()
    {
        $reports = new WebhookTaskReports();
        $reports->setServiceLocator($this->serviceLocatorMock);
        $reports->setLogger($this->loggerMock);

        $exception = new ConnectException('e_msg', new Request('POST', 'uru'));

        $this->webhookEventLogMock->expects($this->once())
            ->method('storeNetworkErrorLog')
            ->with($this->taskContextMock, 'e_msg');

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($message) {
                    return strpos($message, 'e_msg') !== false;
                }),
                $this->callback(function ($context) {
                    return $context['taskId'] === 'taskId' &&
                        $context['eventId'] === 'eventId';
                })
            );

        $report = $reports->reportConnectException($this->taskContextMock, $exception);
        $this->assertSame(\common_report_Report::TYPE_ERROR, $report->getType());
        $this->assertStringContainsString('e_msg', $report->getMessage());
    }

    public function testRequestExceptionWithResponse()
    {
        $reports = new WebhookTaskReports();
        $reports->setServiceLocator($this->serviceLocatorMock);
        $reports->setLogger($this->loggerMock);

        $exception = new RequestException(
            'e_msg',
            new Request('POST', 'uru'),
            new Response(400, [], 'resp_body')
        );

        $this->webhookEventLogMock->expects($this->once())
            ->method('storeInvalidHttpStatusLog')
            ->with($this->taskContextMock, 400, 'resp_body');

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($message) {
                    return strpos($message, 'e_msg') !== false;
                }),
                $this->callback(function ($context) {
                    return $context['taskId'] === 'taskId' &&
                        $context['eventId'] === 'eventId' &&
                        $context['httpStatus'] === 400 &&
                        $context['responseBody'] === 'resp_body';
                })
            );

        $report = $reports->reportRequestException($this->taskContextMock, $exception);
        $this->assertSame(\common_report_Report::TYPE_ERROR, $report->getType());
        $this->assertStringContainsString('e_msg', $report->getMessage());
    }

    public function testRequestExceptionWithoutResponse()
    {
        $reports = new WebhookTaskReports();
        $reports->setServiceLocator($this->serviceLocatorMock);
        $reports->setLogger($this->loggerMock);

        $exception = new RequestException('e_msg', new Request('POST', 'uri'), null);

        $this->webhookEventLogMock->expects($this->once())
            ->method('storeNetworkErrorLog')
            ->with($this->taskContextMock, 'e_msg');

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($message) {
                    return strpos($message, 'e_msg') !== false;
                }),
                $this->callback(function ($context) {
                    return $context['taskId'] === 'taskId' &&
                        $context['eventId'] === 'eventId';
                })
            );

        $report = $reports->reportRequestException($this->taskContextMock, $exception);
        $this->assertSame(\common_report_Report::TYPE_ERROR, $report->getType());
        $this->assertStringContainsString('e_msg', $report->getMessage());
    }

    public function testReportBadResponseException()
    {
        $reports = new WebhookTaskReports();
        $reports->setServiceLocator($this->serviceLocatorMock);
        $reports->setLogger($this->loggerMock);

        $exception = new BadResponseException(
            'e_msg',
            new Request('POST', 'uru'),
            new Response(403, [], 'resp_body')
        );

        $this->webhookEventLogMock->expects($this->once())
            ->method('storeInvalidHttpStatusLog')
            ->with($this->taskContextMock, 403);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($message) {
                    return strpos($message, 'e_msg') !== false;
                }),
                $this->callback(function ($context) {
                    return $context['taskId'] === 'taskId' &&
                        $context['eventId'] === 'eventId' &&
                        $context['httpStatus'] === 403 &&
                        $context['responseBody'] === 'resp_body';
                })
            );

        $report = $reports->reportBadResponseException($this->taskContextMock, $exception);
        $this->assertSame(\common_report_Report::TYPE_ERROR, $report->getType());
        $this->assertStringContainsString('e_msg', $report->getMessage());
    }

    public function testReportInvalidStatusCode()
    {
        $reports = new WebhookTaskReports();
        $reports->setServiceLocator($this->serviceLocatorMock);
        $reports->setLogger($this->loggerMock);

        $response = new Response(301, [], 'resp_body');

        $this->webhookEventLogMock->expects($this->once())
            ->method('storeInvalidHttpStatusLog')
            ->with($this->taskContextMock, 301);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($message) {
                    return strpos($message, '301') !== false;
                }),
                $this->callback(function ($context) {
                    return $context['taskId'] === 'taskId' &&
                        $context['eventId'] === 'eventId' &&
                        $context['httpStatus'] === 301 &&
                        $context['responseBody'] === 'resp_body';
                })
            );

        $report = $reports->reportInvalidStatusCode($this->taskContextMock, $response);
        $this->assertSame(\common_report_Report::TYPE_ERROR, $report->getType());
        $this->assertStringContainsString('301', $report->getMessage());
    }

    public function testReportInvalidBodyFormat()
    {
        $reports = new WebhookTaskReports();
        $reports->setServiceLocator($this->serviceLocatorMock);
        $reports->setLogger($this->loggerMock);

        $response = new Response(200, [], 'resp_body');

        $this->webhookEventLogMock->expects($this->once())
            ->method('storeInvalidBodyFormat')
            ->with($this->taskContextMock, 'resp_body');

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($message) {
                    return strpos($message, 'eventId') !== false;
                }),
                $this->callback(function ($context) {
                    return $context['taskId'] === 'taskId' &&
                        $context['eventId'] === 'eventId' &&
                        $context['httpStatus'] === 200 &&
                        $context['responseBody'] === 'resp_body';
                })
            );

        $report = $reports->reportInvalidBodyFormat($this->taskContextMock, $response);
        $this->assertSame(\common_report_Report::TYPE_ERROR, $report->getType());
        $this->assertStringContainsString('eventId', $report->getMessage());
    }

    public function testReportInvalidAcknowledgement()
    {
        $reports = new WebhookTaskReports();
        $reports->setServiceLocator($this->serviceLocatorMock);
        $reports->setLogger($this->loggerMock);

        $response = new Response(200, [], 'resp_body');

        $parsedResponse = new WebhookResponse(['eventId' => WebhookResponse::STATUS_ERROR], 'parseErr');

        $this->webhookEventLogMock->expects($this->once())
            ->method('storeInvalidAcknowledgementLog')
            ->with($this->taskContextMock, WebhookResponse::STATUS_ERROR);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($message) {
                    return strpos($message, 'parseErr') !== false &&
                        strpos($message, 'eventId') !== false &&
                        strpos($message, 'error') !== false;
                }),
                $this->callback(function ($context) {
                    return $context['taskId'] === 'taskId' &&
                        $context['eventId'] === 'eventId';
                })
            );

        $report = $reports->reportInvalidAcknowledgement($this->taskContextMock, $response, $parsedResponse);
        $this->assertSame(\common_report_Report::TYPE_ERROR, $report->getType());
        $this->assertStringContainsString('eventId', $report->getMessage());
        $this->assertStringContainsString('error', $report->getMessage());
    }

    public function testReportSuccess()
    {
        $reports = new WebhookTaskReports();
        $reports->setServiceLocator($this->serviceLocatorMock);
        $reports->setLogger($this->loggerMock);

        $response = new Response(200, [], 'resp_body');

        $this->webhookEventLogMock->expects($this->once())
            ->method('storeSuccessfulLog')
            ->with($this->taskContextMock, 'resp_body', WebhookResponse::STATUS_ACCEPTED);

        $report = $reports->reportSuccess($this->taskContextMock, $response, WebhookResponse::STATUS_ACCEPTED);
        $this->assertSame(\common_report_Report::TYPE_SUCCESS, $report->getType());
    }
}
