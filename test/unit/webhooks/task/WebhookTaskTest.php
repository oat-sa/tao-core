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

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use oat\generis\test\ServiceManagerMockTrait;
use oat\oatbox\log\LoggerService;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\webhooks\configEntity\Webhook;
use oat\tao\model\webhooks\configEntity\WebhookAuth;
use oat\tao\model\webhooks\configEntity\WebhookAuthInterface;
use oat\tao\model\webhooks\configEntity\WebhookInterface;
use oat\tao\model\webhooks\log\WebhookEventLogInterface;
use oat\tao\model\webhooks\task\WebhookPayloadFactoryInterface;
use oat\tao\model\webhooks\task\WebhookResponse;
use oat\tao\model\webhooks\task\WebhookResponseFactoryInterface;
use oat\tao\model\webhooks\task\WebhookSender;
use oat\tao\model\webhooks\task\WebhookTask;
use oat\tao\model\webhooks\task\WebhookTaskContext;
use oat\tao\model\webhooks\task\WebhookTaskParams;
use oat\tao\model\webhooks\task\WebhookTaskParamsFactory;
use oat\tao\model\webhooks\task\WebhookTaskReports;
use oat\tao\model\webhooks\WebhookRegistryInterface;
use oat\tao\model\webhooks\WebhookTaskServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WebhookTaskTest extends TestCase
{
    use ServiceManagerMockTrait;

    private ServiceManager|MockObject $serviceManagerMock;
    private WebhookRegistryInterface|MockObject $webhookRegistryMock;
    private WebhookPayloadFactoryInterface|MockObject $webhookPayloadFactoryInterfaceMock;
    private WebhookTaskParamsFactory|MockObject $webhookTaskParamsFactoryMock;
    private WebhookResponseFactoryInterface|MockObject $webhookResponseFactoryInterfaceMock;
    private WebhookSender|MockObject $webhookSenderMock;
    private WebhookTaskServiceInterface|MockObject $webhookTaskServiceMock;
    private WebhookInterface|MockObject $webhookConfigMock;
    private WebhookTaskParams|MockObject $webhookTaskParamsMock;
    private RequestInterface|MockObject $requestMock;
    private WebhookResponse|MockObject $webhookResponseMock;
    private WebhookEventLogInterface|MockObject $webhookLogServiceMock;
    private WebhookTaskReports|MockObject $webhookTaskReports;

    protected function setUp(): void
    {
        $this->webhookRegistryMock = $this->createMock(WebhookRegistryInterface::class);
        $this->webhookPayloadFactoryInterfaceMock = $this->createMock(WebhookPayloadFactoryInterface::class);
        $this->webhookTaskParamsFactoryMock = $this->createMock(WebhookTaskParamsFactory::class);
        $this->webhookResponseFactoryInterfaceMock = $this->createMock(WebhookResponseFactoryInterface::class);
        $this->webhookSenderMock = $this->createMock(WebhookSender::class);
        $this->webhookTaskServiceMock = $this->createMock(WebhookTaskServiceInterface::class);
        $loggerMock = $this->createMock(LoggerService::class);
        $this->webhookLogServiceMock = $this->createMock(WebhookEventLogInterface::class);
        $this->webhookTaskReports = $this->createMock(WebhookTaskReports::class);

        $this->serviceManagerMock = $this->getServiceManagerMock([
            WebhookTaskReports::class => $this->webhookTaskReports,
            WebhookRegistryInterface::class => $this->webhookRegistryMock,
            WebhookPayloadFactoryInterface::SERVICE_ID => $this->webhookPayloadFactoryInterfaceMock,
            WebhookTaskParamsFactory::class => $this->webhookTaskParamsFactoryMock,
            WebhookResponseFactoryInterface::SERVICE_ID => $this->webhookResponseFactoryInterfaceMock,
            WebhookSender::class => $this->webhookSenderMock,
            WebhookTaskServiceInterface::SERVICE_ID => $this->webhookTaskServiceMock,
            LoggerService::SERVICE_ID => $loggerMock,
            WebhookEventLogInterface::SERVICE_ID => $this->webhookLogServiceMock,
        ]);
    }


    public function testRequestWithoutResponsevalidation()
    {
        $whConfig = new Webhook('wh1', 'http://myurl', 'HMETHOD', 0, new WebhookAuth('authClass', []), true);
        $webhookRegistry = $this->createWebhookRegistryMock(
            ['Test\Event' => ['wh1']],
            [
                'wh1' => $whConfig
            ]
        );

        $payloadFactory = $this->createWebhookPayloadFactoryMock('payloadCT', 'pay-load');

        $taskParams = new WebhookTaskParams([
            WebhookTaskParams::EVENT_NAME => 'eventName',
            WebhookTaskParams::EVENT_ID => 'eventId',
            WebhookTaskParams::TRIGGERED_TIMESTAMP => 1234565,
            WebhookTaskParams::EVENT_DATA => ['d' => 4],
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1',
            WebhookTaskParams::RESPONSE_VALIDATION => false,
            WebhookTaskParams::RETRY_MAX => 5,
        ]);
        $taskParamsFactory = $this->createWebhookTaskParamsFactoryMock($taskParams);

        $webhookResponse = new WebhookResponse(['eventId' => 'accepted']);
        $webhookResponseFactory = $this->createWebhookResponseFactory('accCT', $webhookResponse);

        $psrResponse = new Response(200, ['Content-Type' => 'accCT'], 'resp_body');
        $webhookSender = $this->createWebhookSenderMock($psrResponse);
        $queueTask = $this->createTaskMock('queueTaskId');

        $task = new WebhookTask();

        $task->setServiceLocator($this->getServiceManagerMock([
            WebhookRegistryInterface::class => $webhookRegistry,
            WebhookPayloadFactoryInterface::SERVICE_ID => $payloadFactory,
            WebhookTaskParamsFactory::class => $taskParamsFactory,
            WebhookResponseFactoryInterface::SERVICE_ID => $webhookResponseFactory,
            WebhookSender::class => $webhookSender,
            WebhookTaskReports::class => $this->webhookTaskReports
        ]));
        $task->setTask($queueTask);

        $taskParamsFactory->method('createFromArray')->with(['ppp']);
        $webhookResponseFactory->expects($this->once())->method('create')->with($psrResponse);
        $payloadFactory->method('createPayload')->with('eventName', 'eventId', 1234565, ['d' => 4]);
        $webhookSender->method('performRequest')->with(
            $this->callback(static function (RequestInterface $request) {
                return (string)$request->getBody() === 'pay-load' &&
                    $request->getHeader('Content-Type')[0] === 'payloadCT' &&
                    $request->getHeader('Accept')[0] === 'accCT';
            }),
            $this->callback(static function (WebhookAuthInterface $auth = null) use ($whConfig) {
                return $auth === $whConfig->getAuth();
            })
        );
        $report = \common_report_Report::createSuccess('success_text');
        $this->webhookTaskReports->expects($this->once())
            ->method('reportSuccess')
            ->with(
                $this->callback(function (WebhookTaskContext $context) use ($taskParams, $whConfig) {
                    return 'queueTaskId' === $context->getTaskId() &&
                        $taskParams === $context->getWebhookTaskParams() &&
                        $whConfig === $context->getWebhookConfig();
                }),
                $psrResponse,
                'accepted'
            )
            ->willReturn($report);

        $result = $task(['ppp']);

        $this->webhookTaskReports->expects($this->never())->method('reportInvalidBodyFormat');
        $this->webhookTaskReports->expects($this->never())->method('reportInvalidAcknowledgement');
        $this->assertSame($result, $report);
    }

    public function testInvokeRetryMechanismIncorectResponseCode()
    {
        $this->webhookTaskParamsMock = $this->createMock(WebhookTaskParams::class);
        $this->webhookTaskParamsMock->method('getWebhookConfigId')->willReturn('WebhookConfigId');
        $this->webhookTaskParamsFactoryMock->method('createFromArray')->willReturn($this->webhookTaskParamsMock);
        $this->webhookConfigMock = $this->createMock(WebhookInterface::class);
        $this->webhookRegistryMock->method('getWebhookConfig')->willReturn($this->webhookConfigMock);
        $this->webhookPayloadFactoryInterfaceMock->method('createPayload')->willReturn('body string');
        $this->webhookConfigMock->method('getHttpMethod')->willReturn('POST');
        $this->webhookConfigMock->method('getUrl')->willReturn('http://some.example.url');
        $this->webhookPayloadFactoryInterfaceMock->method('getContentType')->willReturn('html/php');
        $this->webhookResponseFactoryInterfaceMock->method('getAcceptedContentType')->willReturn('*');
        $this->requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $this->webhookSenderMock
            ->method('performRequest')
            ->willReturn($responseMock);
        $responseMock->method('getStatusCode')->willReturn(302);
        $this->webhookTaskParamsMock->method('isMaxRetryCountReached')->willReturn(false);
        $this->webhookResponseMock = $this->createMock(WebhookResponse::class);
        $this->webhookResponseFactoryInterfaceMock->method('create')->willReturn($this->webhookResponseMock);

        $this->webhookTaskParamsMock->expects($this->once())->method('increaseRetryCount');
        $this->webhookTaskServiceMock->expects($this->once())->method('createTask');

        $report = \common_report_Report::createFailure('rep_msg');
        $this->webhookTaskReports->expects($this->once())
            ->method('reportInvalidStatusCode')
            ->with(
                $this->callback(function (WebhookTaskContext $context) {
                    return 'someId' === $context->getTaskId() &&
                        $this->webhookTaskParamsMock === $context->getWebhookTaskParams() &&
                        $this->webhookConfigMock === $context->getWebhookConfig();
                }),
                $responseMock
            )
            ->willReturn($report);

        $paramArray = [];
        $webhookTask = new WebhookTask();
        $webhookTask->setTask($this->createTaskMock('someId'));
        $webhookTask->setServiceLocator($this->serviceManagerMock);
        /* @noinspection PhpUnhandledExceptionInspection */
        $result = $webhookTask($paramArray);
        $this->assertSame($report, $result);
    }

    public function testInvokeRetryMechanismConnectionException()
    {
        $this->webhookTaskParamsMock = $this->createMock(WebhookTaskParams::class);
        $this->webhookTaskParamsMock->method('getWebhookConfigId')->willReturn('WebhookConfigId');
        $this->webhookTaskParamsFactoryMock->method('createFromArray')->willReturn($this->webhookTaskParamsMock);
        $this->webhookConfigMock = $this->createMock(WebhookInterface::class);
        $this->webhookRegistryMock->method('getWebhookConfig')->willReturn($this->webhookConfigMock);
        $this->webhookPayloadFactoryInterfaceMock->method('createPayload')->willReturn('body string');
        $this->webhookConfigMock->method('getHttpMethod')->willReturn('POST');
        $this->webhookConfigMock->method('getUrl')->willReturn('http://some.example.url');
        $this->webhookPayloadFactoryInterfaceMock->method('getContentType')->willReturn('html/php');
        $this->webhookResponseFactoryInterfaceMock->method('getAcceptedContentType')->willReturn('*');
        $this->requestMock = $this->createMock(RequestInterface::class);
        $connectException = new ConnectException('timeout', $this->requestMock);
        $this->webhookSenderMock
            ->method('performRequest')
            ->willThrowException($connectException);
        $this->webhookTaskParamsMock->method('isMaxRetryCountReached')->willReturn(false);
        $this->webhookResponseMock = $this->createMock(WebhookResponse::class);
        $this->webhookResponseFactoryInterfaceMock->method('create')->willReturn($this->webhookResponseMock);

        $this->webhookTaskParamsMock->expects($this->once())->method('increaseRetryCount');
        $this->webhookTaskServiceMock->expects($this->once())->method('createTask');

        $report = \common_report_Report::createFailure('rep_msg');
        $this->webhookTaskReports->expects($this->once())
            ->method('reportConnectException')
            ->with(
                $this->callback(function (WebhookTaskContext $context) {
                    return 'someId' === $context->getTaskId() &&
                        $this->webhookTaskParamsMock === $context->getWebhookTaskParams() &&
                        $this->webhookConfigMock === $context->getWebhookConfig();
                }),
                $connectException
            )
            ->willReturn($report);

        $paramArray = [];
        $webhookTask = new WebhookTask();
        $webhookTask->setTask($this->createTaskMock('someId'));
        $webhookTask->setServiceLocator($this->serviceManagerMock);
        /* @noinspection PhpUnhandledExceptionInspection */
        $result = $webhookTask($paramArray);
        $this->assertSame($report, $result);
    }

    /**
     * @throws GuzzleException
     */
    public function testInvokeValid()
    {
        $whConfig = new Webhook('wh1', 'http://myurl', 'HMETHOD', 0, new WebhookAuth('authClass', []), true);
        $webhookRegistry = $this->createWebhookRegistryMock(
            ['Test\Event' => ['wh1']],
            [
                'wh1' => $whConfig
            ]
        );

        $payloadFactory = $this->createWebhookPayloadFactoryMock('payloadCT', 'pay-load');

        $taskParams = new WebhookTaskParams([
            WebhookTaskParams::EVENT_NAME => 'eventName',
            WebhookTaskParams::EVENT_ID => 'eventId',
            WebhookTaskParams::TRIGGERED_TIMESTAMP => 1234565,
            WebhookTaskParams::EVENT_DATA => ['d' => 4],
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1',
            WebhookTaskParams::RESPONSE_VALIDATION => true,
            WebhookTaskParams::RETRY_MAX => 5,
        ]);
        $taskParamsFactory = $this->createWebhookTaskParamsFactoryMock($taskParams);

        $webhookResponse = new WebhookResponse(['eventId' => 'accepted']);
        $webhookResponseFactory = $this->createWebhookResponseFactory('accCT', $webhookResponse);

        $psrResponse = new Response(200, ['Content-Type' => 'accCT'], 'resp_body');
        $webhookSender = $this->createWebhookSenderMock($psrResponse);
        $queueTask = $this->createTaskMock('queueTaskId');

        $task = new WebhookTask();

        $task->setServiceLocator($this->getServiceManagerMock([
            WebhookRegistryInterface::class => $webhookRegistry,
            WebhookPayloadFactoryInterface::SERVICE_ID => $payloadFactory,
            WebhookTaskParamsFactory::class => $taskParamsFactory,
            WebhookResponseFactoryInterface::SERVICE_ID => $webhookResponseFactory,
            WebhookSender::class => $webhookSender,
            WebhookTaskReports::class => $this->webhookTaskReports
        ]));
        $task->setTask($queueTask);

        $taskParamsFactory->method('createFromArray')->with(['ppp']);
        $webhookResponseFactory->method('create')->with($psrResponse);
        $payloadFactory->method('createPayload')->with('eventName', 'eventId', 1234565, ['d' => 4]);
        $webhookSender->method('performRequest')->with(
            $this->callback(static function (RequestInterface $request) {
                return (string)$request->getBody() === 'pay-load' &&
                    $request->getHeader('Content-Type')[0] === 'payloadCT' &&
                    $request->getHeader('Accept')[0] === 'accCT';
            }),
            $this->callback(static function (WebhookAuthInterface $auth = null) use ($whConfig) {
                return $auth === $whConfig->getAuth();
            })
        );
        $report = \common_report_Report::createSuccess('success_text');
        $this->webhookTaskReports->expects($this->once())
            ->method('reportSuccess')
            ->with(
                $this->callback(function (WebhookTaskContext $context) use ($taskParams, $whConfig) {
                    return 'queueTaskId' === $context->getTaskId() &&
                        $taskParams === $context->getWebhookTaskParams() &&
                        $whConfig === $context->getWebhookConfig();
                }),
                $psrResponse,
                'accepted'
            )
            ->willReturn($report);

        $result = $task(['ppp']);

        $this->assertSame($result, $report);
    }

    /**
     * @throws GuzzleException
     */
    public function testInvokeInvalidWebhookConfigId()
    {
        $webhookRegistry = $this->createWebhookRegistryMock(
            ['Test\Event' => ['wh1']],
            []
        );

        $taskParams = new WebhookTaskParams([
            WebhookTaskParams::EVENT_NAME => 'eventName',
            WebhookTaskParams::EVENT_ID => 'eventId',
            WebhookTaskParams::TRIGGERED_TIMESTAMP => 1234565,
            WebhookTaskParams::EVENT_DATA => ['d' => 4],
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1',
            WebhookTaskParams::RESPONSE_VALIDATION => true,
            WebhookTaskParams::RETRY_MAX => 5,
        ]);
        $taskParamsFactory = $this->createWebhookTaskParamsFactoryMock($taskParams);

        $queueTask = $this->createTaskMock('queueTaskId');

        $task = new WebhookTask();

        $task->setServiceLocator($this->getServiceManagerMock([
            WebhookRegistryInterface::class => $webhookRegistry,
            WebhookTaskParamsFactory::class => $taskParamsFactory,
            WebhookEventLogInterface::SERVICE_ID => $this->webhookLogServiceMock,
            WebhookTaskReports::class => $this->webhookTaskReports
        ]));
        $task->setTask($queueTask);

        $taskParamsFactory->method('createFromArray')->with(['ppp']);
        $report = \common_report_Report::createFailure('failure_text');
        $this->webhookTaskReports->expects($this->once())
            ->method('reportInternalException')
            ->with(
                $this->callback(function (WebhookTaskContext $context) use ($taskParams) {
                    return 'queueTaskId' === $context->getTaskId() &&
                        $taskParams === $context->getWebhookTaskParams() &&
                        null === $context->getWebhookConfig();
                }),
                $this->callback(function (\Exception $exception) {
                    return $exception instanceof \common_exception_NotFound;
                })
            )
            ->willReturn($report);

        $result = $task(['ppp']);

        $this->assertSame($report, $result);
    }

    /**
     * @throws GuzzleException
     */
    public function testEventNotDelivered()
    {
        $whConfig = new Webhook('wh1', 'http://myurl', 'HMETHOD', 0, new WebhookAuth('authClass', []), true);
        $webhookRegistry = $this->createWebhookRegistryMock(
            ['Test\Event' => ['wh1']],
            [
                'wh1' => $whConfig
            ]
        );

        $payloadFactory = $this->createWebhookPayloadFactoryMock('payloadCT', 'pay-load');

        $taskParams = new WebhookTaskParams([
            WebhookTaskParams::EVENT_NAME => 'eventName',
            WebhookTaskParams::EVENT_ID => 'eventId',
            WebhookTaskParams::TRIGGERED_TIMESTAMP => 1234565,
            WebhookTaskParams::EVENT_DATA => ['d' => 4],
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1',
            WebhookTaskParams::RESPONSE_VALIDATION => true,
            WebhookTaskParams::RETRY_MAX => 5,
        ]);
        $taskParamsFactory = $this->createWebhookTaskParamsFactoryMock($taskParams);

        $webhookResponse = new WebhookResponse(['eventId' => 'error']);
        $webhookResponseFactory = $this->createWebhookResponseFactory('accCT', $webhookResponse);

        $psrResponse = new Response(200, ['Content-Type' => 'accCT'], 'resp_body');
        $webhookSender = $this->createWebhookSenderMock($psrResponse);
        $queueTask = $this->createTaskMock('queueTaskId');

        $task = new WebhookTask();

        $task->setServiceLocator($this->getServiceManagerMock([
            WebhookRegistryInterface::class => $webhookRegistry,
            WebhookPayloadFactoryInterface::SERVICE_ID => $payloadFactory,
            WebhookTaskParamsFactory::class => $taskParamsFactory,
            WebhookResponseFactoryInterface::SERVICE_ID => $webhookResponseFactory,
            WebhookSender::class => $webhookSender,
            WebhookTaskReports::class => $this->webhookTaskReports
        ]));
        $task->setTask($queueTask);
        $report = \common_report_Report::createFailure('failure_text');
        $this->webhookTaskReports->expects($this->once())
            ->method('reportInvalidAcknowledgement')
            ->with(
                $this->callback(function (WebhookTaskContext $context) use ($whConfig, $taskParams) {
                    return 'queueTaskId' === $context->getTaskId() &&
                        $taskParams === $context->getWebhookTaskParams() &&
                        $whConfig === $context->getWebhookConfig();
                }),
                $psrResponse,
                $webhookResponse
            )
            ->willReturn($report);

        $result = $task(['ppp']);

        $this->assertSame($report, $result);
    }

    /**
     * @throws GuzzleException
     */
    public function testWrongResponse()
    {
        $whConfig = new Webhook('wh1', 'http://myurl', 'HMETHOD', 0, new WebhookAuth('authClass', []), true);
        $webhookRegistry = $this->createWebhookRegistryMock(
            ['Test\Event' => ['wh1']],
            [
                'wh1' => $whConfig
            ]
        );

        $payloadFactory = $this->createWebhookPayloadFactoryMock('payloadCT', 'pay-load');

        $taskParams = new WebhookTaskParams([
            WebhookTaskParams::EVENT_NAME => 'eventName',
            WebhookTaskParams::EVENT_ID => 'eventId',
            WebhookTaskParams::TRIGGERED_TIMESTAMP => 1234565,
            WebhookTaskParams::EVENT_DATA => ['d' => 4],
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1',
            WebhookTaskParams::RESPONSE_VALIDATION => true,
            WebhookTaskParams::RETRY_MAX => 5,
        ]);
        $taskParamsFactory = $this->createWebhookTaskParamsFactoryMock($taskParams);

        $webhookResponse = new WebhookResponse([], 'parseError');
        $webhookResponseFactory = $this->createWebhookResponseFactory('accCT', $webhookResponse);

        $psrResponse = new Response(200, ['Content-Type' => 'WRONG_CC'], 'resp_body');
        $webhookSender = $this->createWebhookSenderMock($psrResponse);
        $queueTask = $this->createTaskMock('queueTaskId');

        $task = new WebhookTask();

        $task->setServiceLocator($this->getServiceManagerMock([
            WebhookRegistryInterface::class => $webhookRegistry,
            WebhookPayloadFactoryInterface::SERVICE_ID => $payloadFactory,
            WebhookTaskParamsFactory::class => $taskParamsFactory,
            WebhookResponseFactoryInterface::SERVICE_ID => $webhookResponseFactory,
            WebhookSender::class => $webhookSender,
            WebhookTaskReports::class => $this->webhookTaskReports
        ]));
        $task->setTask($queueTask);

        $report = \common_report_Report::createFailure('failure_text');
        $this->webhookTaskReports->expects($this->once())
            ->method('reportInvalidBodyFormat')
            ->with(
                $this->callback(function (WebhookTaskContext $context) use ($whConfig, $taskParams) {
                    return 'queueTaskId' === $context->getTaskId() &&
                        $taskParams === $context->getWebhookTaskParams() &&
                        $whConfig === $context->getWebhookConfig();
                }),
                $psrResponse
            )
            ->willReturn($report);

        $result = $task(['ppp']);

        $this->assertSame($report, $result);
    }

    /**
     * @throws GuzzleException
     */
    public function testHttpError()
    {
        $this->webhookTaskParamsMock = $this->createMock(WebhookTaskParams::class);
        $whConfig = new Webhook('wh1', 'http://myurl', 'HMETHOD', 1, new WebhookAuth('authClass', []), true);
        $webhookRegistry = $this->createWebhookRegistryMock(
            ['Test\Event' => ['wh1']],
            [
                'wh1' => $whConfig
            ]
        );

        $payloadFactory = $this->createWebhookPayloadFactoryMock('payloadCT', 'pay-load');

        $taskParams = new WebhookTaskParams([
            WebhookTaskParams::EVENT_NAME => 'eventName',
            WebhookTaskParams::EVENT_ID => 'eventId',
            WebhookTaskParams::TRIGGERED_TIMESTAMP => 1234565,
            WebhookTaskParams::EVENT_DATA => ['d' => 4],
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1',
            WebhookTaskParams::RETRY_COUNT => 0,
            WebhookTaskParams::RETRY_MAX => 1,
            WebhookTaskParams::RESPONSE_VALIDATION => true,
        ]);
        $taskParamsFactory = $this->createWebhookTaskParamsFactoryMock($taskParams);

        $webhookResponse = new WebhookResponse([], 'parseError');
        $webhookResponseFactory = $this->createWebhookResponseFactory('accCT', $webhookResponse);

        $serverException = new ServerException(
            's_exc_m',
            new Request('POST', 'http://myurl'),
            new Response(500)
        );
        $webhookSender = $this->createWebhookSenderMock(null, $serverException);
        $queueTask = $this->createTaskMock('queueTaskId');

        $task = new WebhookTask();

        $task->setServiceLocator($this->getServiceManagerMock([
            WebhookRegistryInterface::class => $webhookRegistry,
            WebhookPayloadFactoryInterface::SERVICE_ID => $payloadFactory,
            WebhookTaskParamsFactory::class => $taskParamsFactory,
            WebhookTaskServiceInterface::SERVICE_ID => $this->webhookTaskServiceMock,
            WebhookResponseFactoryInterface::SERVICE_ID => $webhookResponseFactory,
            WebhookSender::class => $webhookSender,
            WebhookTaskReports::class => $this->webhookTaskReports
        ]));
        $task->setTask($queueTask);
        $this->webhookTaskServiceMock->expects($this->once())->method('createTask');

        $report = \common_report_Report::createFailure('failure_text');
        $this->webhookTaskReports->expects($this->once())
            ->method('reportBadResponseException')
            ->with(
                $this->callback(function (WebhookTaskContext $context) use ($whConfig, $taskParams) {
                    return 'queueTaskId' === $context->getTaskId() &&
                        $taskParams === $context->getWebhookTaskParams() &&
                        $whConfig === $context->getWebhookConfig();
                }),
                $serverException
            )
            ->willReturn($report);

        $result = $task(['ppp']);

        $this->assertSame($report, $result);
    }

    /**
     * @throws GuzzleException
     */
    public function testRequestExceptionHasResponse()
    {
        $this->webhookTaskParamsMock = $this->createMock(WebhookTaskParams::class);
        $whConfig = new Webhook('wh1', 'http://myurl', 'HMETHOD', 1, new WebhookAuth('authClass', []));
        $webhookRegistry = $this->createWebhookRegistryMock(
            ['Test\Event' => ['wh1']],
            [
                'wh1' => $whConfig
            ]
        );

        $payloadFactory = $this->createWebhookPayloadFactoryMock('payloadCT', 'pay-load');

        $taskParams = new WebhookTaskParams([
            WebhookTaskParams::EVENT_NAME => 'eventName',
            WebhookTaskParams::EVENT_ID => 'eventId',
            WebhookTaskParams::TRIGGERED_TIMESTAMP => 1234565,
            WebhookTaskParams::EVENT_DATA => ['d' => 4],
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1',
            WebhookTaskParams::RETRY_COUNT => 0,
            WebhookTaskParams::RETRY_MAX => 1,
            WebhookTaskParams::RESPONSE_VALIDATION => true,
        ]);
        $taskParamsFactory = $this->createWebhookTaskParamsFactoryMock($taskParams);

        $webhookResponse = new WebhookResponse([], 'parseError');
        $webhookResponseFactory = $this->createWebhookResponseFactory('accCT', $webhookResponse);

        $requestException = new RequestException(
            's_exc_m',
            new Request('POST', 'http://myurl'),
            new Response(400)
        );
        $webhookSender = $this->createWebhookSenderMock(null, $requestException);
        $queueTask = $this->createTaskMock('queueTaskId');

        $task = new WebhookTask();

        $task->setServiceLocator($this->getServiceManagerMock([
            WebhookRegistryInterface::class => $webhookRegistry,
            WebhookPayloadFactoryInterface::SERVICE_ID => $payloadFactory,
            WebhookTaskParamsFactory::class => $taskParamsFactory,
            WebhookTaskServiceInterface::SERVICE_ID => $this->webhookTaskServiceMock,
            WebhookResponseFactoryInterface::SERVICE_ID => $webhookResponseFactory,
            WebhookSender::class => $webhookSender,
            WebhookTaskReports::class => $this->webhookTaskReports
        ]));
        $task->setTask($queueTask);

        $report = \common_report_Report::createFailure('failure_text');
        $this->webhookTaskReports->expects($this->once())
            ->method('reportRequestException')
            ->with(
                $this->callback(function (WebhookTaskContext $context) use ($whConfig, $taskParams) {
                    return 'queueTaskId' === $context->getTaskId() &&
                        $taskParams === $context->getWebhookTaskParams() &&
                        $whConfig === $context->getWebhookConfig();
                }),
                $requestException
            )
            ->willReturn($report);

        $result = $task(['ppp']);

        $this->assertSame($report, $result);
    }

    /**
     * @param array $events
     * @param Webhook[] $whConfigs
     * @return MockObject|WebhookRegistryInterface
     */
    private function createWebhookRegistryMock($events, $whConfigs)
    {
        $registry = $this->createMock(WebhookRegistryInterface::class);

        $registry->method('getWebhookConfigIds')->willReturnCallback(
            static function ($eventName) use ($events) {
                return isset($events[$eventName])
                    ? $events[$eventName]
                    : [];
            }
        );

        $registry->method('getWebhookConfig')->willReturnCallback(
            static function ($id) use ($whConfigs) {
                return isset($whConfigs[$id])
                    ? $whConfigs[$id]
                    : null;
            }
        );

        return $registry;
    }

    /**
     * @param string $contentType
     * @param string $payload
     * @return MockObject|WebhookPayloadFactoryInterface
     */
    private function createWebhookPayloadFactoryMock($contentType, $payload)
    {
        $factory = $this->createMock(WebhookPayloadFactoryInterface::class);
        $factory->method('getContentType')->willReturn($contentType);
        $factory->method('createPayload')->willReturn($payload);
        return $factory;
    }

    /**
     * @param WebhookTaskParams $resultParams
     * @return MockObject|WebhookTaskParamsFactory
     */
    private function createWebhookTaskParamsFactoryMock(WebhookTaskParams $resultParams)
    {
        $factory = $this->createMock(WebhookTaskParamsFactory::class);
        $factory->method('createFromArray')->willReturn($resultParams);
        return $factory;
    }

    /**
     * @param string $acceptedContentType
     * @param WebhookResponse $webhookResponse
     * @return MockObject|WebhookResponseFactoryInterface
     */
    private function createWebhookResponseFactory($acceptedContentType, WebhookResponse $webhookResponse)
    {
        $factory = $this->createMock(WebhookResponseFactoryInterface::class);
        $factory->method('getAcceptedContentType')->willReturn($acceptedContentType);
        $factory->method('create')->willReturn($webhookResponse);
        return $factory;
    }

    /**
     * @param ResponseInterface|null $response
     * @param \Exception|null $exception
     * @return MockObject|WebhookSender
     */
    private function createWebhookSenderMock(
        ResponseInterface $response = null,
        \Exception $exception = null
    ) {
        $sender = $this->createMock(WebhookSender::class);
        if ($response) {
            $sender->method('performRequest')->willReturn($response);
        } elseif ($exception) {
            $sender->method('performRequest')->willThrowException($exception);
        }

        return $sender;
    }

    /**
     * @param string $taskId
     * @return MockObject|TaskInterface
     */
    private function createTaskMock($taskId)
    {
        $task = $this->createMock(TaskInterface::class);
        $task->method('getId')->willReturn($taskId);
        return $task;
    }
}
