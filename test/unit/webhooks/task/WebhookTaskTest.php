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

namespace oat\tao\test\unit\webhooks\task;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use oat\generis\test\TestCase;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\webhooks\configEntity\Webhook;
use oat\tao\model\webhooks\configEntity\WebhookAuth;
use oat\tao\model\webhooks\configEntity\WebhookAuthInterface;
use oat\tao\model\webhooks\task\WebhookPayloadFactoryInterface;
use oat\tao\model\webhooks\task\WebhookResponse;
use oat\tao\model\webhooks\task\WebhookResponseFactoryInterface;
use oat\tao\model\webhooks\task\WebhookSender;
use oat\tao\model\webhooks\task\WebhookTask;
use oat\tao\model\webhooks\task\WebhookTaskParams;
use oat\tao\model\webhooks\task\WebhookTaskParamsFactory;
use oat\tao\model\webhooks\WebhookRegistryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class WebhookTaskTest extends TestCase
{
    /**
     * @throws GuzzleException
     */
    public function testInvokeValid()
    {
        $whConfig = new Webhook('wh1', 'http://myurl', 'HMETHOD', new WebhookAuth('authClass', []));
        $webhookRegistry = $this->createWebhookRegistryMock(
            ['Test\Event' => ['wh1']],
            [
                'wh1' =>  $whConfig
            ]
        );

        $payloadFactory = $this->createWebhookPayloadFactoryMock('payloadCT', 'pay-load');

        $taskParamsFactory = $this->createWebhookTaskParamsFactoryMock(new WebhookTaskParams([
            WebhookTaskParams::EVENT_NAME => 'eventName',
            WebhookTaskParams::EVENT_ID => 'eventId',
            WebhookTaskParams::TRIGGERED_TIMESTAMP => 1234565,
            WebhookTaskParams::EVENT_DATA => ['d' => 4],
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1'
        ]));

        $webhookResponse = new WebhookResponse(['eventId' => 'accepted']);
        $webhookResponseFactory = $this->createWebhookResponseFactory('accCT', $webhookResponse);

        $psrResponse = new Response(200, ['Content-Type' => 'accCT'], 'resp_body');
        $webhookSender = $this->createWebhookSenderMock($psrResponse);
        $queueTask = $this->createTaskMock('queueTaskId');

        $task = new WebhookTask();

        $task->setServiceLocator($this->getServiceLocatorMock([
            WebhookRegistryInterface::SERVICE_ID => $webhookRegistry,
            WebhookPayloadFactoryInterface::SERVICE_ID => $payloadFactory,
            WebhookTaskParamsFactory::class => $taskParamsFactory,
            WebhookResponseFactoryInterface::SERVICE_ID => $webhookResponseFactory,
            WebhookSender::class => $webhookSender
        ]));
        $task->setTask($queueTask);

        $taskParamsFactory->method('createFromArray')->with(['ppp']);
        $webhookResponseFactory->method('create')->with($psrResponse);
        $payloadFactory->method('createPayload')->with('eventName', 'eventId', 1234565, ['d' => 4]);
        $webhookSender->method('performRequest')->with(
            $this->callback(static function (RequestInterface $request)
                {
                    return (string) $request->getBody() === 'pay-load' &&
                        $request->getHeader('Content-Type')[0] === 'payloadCT' &&
                        $request->getHeader('Accept')[0] === 'accCT';
                }),
            $this->callback(static function (WebhookAuthInterface $auth = null) use ($whConfig)
                {
                    return $auth === $whConfig->getAuth();
                })
        );

        $report = $task(['ppp']);

        $this->assertEmpty($report->getErrors());
        $this->assertSame(\common_report_Report::TYPE_SUCCESS, $report->getType());
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

        $taskParamsFactory = $this->createWebhookTaskParamsFactoryMock(new WebhookTaskParams([
            WebhookTaskParams::EVENT_NAME => 'eventName',
            WebhookTaskParams::EVENT_ID => 'eventId',
            WebhookTaskParams::TRIGGERED_TIMESTAMP => 1234565,
            WebhookTaskParams::EVENT_DATA => ['d' => 4],
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1'
        ]));

        $queueTask = $this->createTaskMock('queueTaskId');
        $loggerMock = $this->createLoggerMock();

        $task = new WebhookTask();

        $task->setServiceLocator($this->getServiceLocatorMock([
            WebhookRegistryInterface::SERVICE_ID => $webhookRegistry,
            WebhookTaskParamsFactory::class => $taskParamsFactory
        ]));
        $task->setTask($queueTask);
        $task->setLogger($loggerMock);

        $taskParamsFactory->method('createFromArray')->with(['ppp']);
        $loggerMock->expects($this->once())->method('error');
        $report = $task(['ppp']);

        $this->assertSame(\common_report_Report::TYPE_ERROR, $report->getType());
    }

    /**
     * @throws GuzzleException
     */
    public function testEventNotDelivered()
    {
        $whConfig = new Webhook('wh1', 'http://myurl', 'HMETHOD', new WebhookAuth('authClass', []));
        $webhookRegistry = $this->createWebhookRegistryMock(
            ['Test\Event' => ['wh1']],
            [
                'wh1' =>  $whConfig
            ]
        );

        $payloadFactory = $this->createWebhookPayloadFactoryMock('payloadCT', 'pay-load');

        $taskParamsFactory = $this->createWebhookTaskParamsFactoryMock(new WebhookTaskParams([
            WebhookTaskParams::EVENT_NAME => 'eventName',
            WebhookTaskParams::EVENT_ID => 'eventId',
            WebhookTaskParams::TRIGGERED_TIMESTAMP => 1234565,
            WebhookTaskParams::EVENT_DATA => ['d' => 4],
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1'
        ]));

        $webhookResponse = new WebhookResponse(['eventId' => 'error']);
        $webhookResponseFactory = $this->createWebhookResponseFactory('accCT', $webhookResponse);

        $psrResponse = new Response(200, ['Content-Type' => 'accCT'], 'resp_body');
        $webhookSender = $this->createWebhookSenderMock($psrResponse);
        $queueTask = $this->createTaskMock('queueTaskId');
        $loggerMock = $this->createLoggerMock();

        $task = new WebhookTask();

        $task->setServiceLocator($this->getServiceLocatorMock([
            WebhookRegistryInterface::SERVICE_ID => $webhookRegistry,
            WebhookPayloadFactoryInterface::SERVICE_ID => $payloadFactory,
            WebhookTaskParamsFactory::class => $taskParamsFactory,
            WebhookResponseFactoryInterface::SERVICE_ID => $webhookResponseFactory,
            WebhookSender::class => $webhookSender
        ]));
        $task->setTask($queueTask);
        $task->setLogger($loggerMock);
        $loggerMock->expects($this->once())->method('error');

        $report = $task(['ppp']);

        $this->assertSame(\common_report_Report::TYPE_ERROR, $report->getType());
    }

    /**
     * @throws GuzzleException
     */
    public function testWrongResponse()
    {
        $whConfig = new Webhook('wh1', 'http://myurl', 'HMETHOD', new WebhookAuth('authClass', []));
        $webhookRegistry = $this->createWebhookRegistryMock(
            ['Test\Event' => ['wh1']],
            [
                'wh1' =>  $whConfig
            ]
        );

        $payloadFactory = $this->createWebhookPayloadFactoryMock('payloadCT', 'pay-load');

        $taskParamsFactory = $this->createWebhookTaskParamsFactoryMock(new WebhookTaskParams([
            WebhookTaskParams::EVENT_NAME => 'eventName',
            WebhookTaskParams::EVENT_ID => 'eventId',
            WebhookTaskParams::TRIGGERED_TIMESTAMP => 1234565,
            WebhookTaskParams::EVENT_DATA => ['d' => 4],
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1'
        ]));

        $webhookResponse = new WebhookResponse([], 'parseError');
        $webhookResponseFactory = $this->createWebhookResponseFactory('accCT', $webhookResponse);

        $psrResponse = new Response(200, ['Content-Type' => 'WRONG_CC'], 'resp_body');
        $webhookSender = $this->createWebhookSenderMock($psrResponse);
        $queueTask = $this->createTaskMock('queueTaskId');
        $loggerMock = $this->createLoggerMock();

        $task = new WebhookTask();

        $task->setServiceLocator($this->getServiceLocatorMock([
            WebhookRegistryInterface::SERVICE_ID => $webhookRegistry,
            WebhookPayloadFactoryInterface::SERVICE_ID => $payloadFactory,
            WebhookTaskParamsFactory::class => $taskParamsFactory,
            WebhookResponseFactoryInterface::SERVICE_ID => $webhookResponseFactory,
            WebhookSender::class => $webhookSender
        ]));
        $task->setTask($queueTask);
        $task->setLogger($loggerMock);
        $loggerMock->expects($this->once())->method('error');

        $report = $task(['ppp']);

        $this->assertSame(\common_report_Report::TYPE_ERROR, $report->getType());
    }

    /**
     * @param array $events
     * @param Webhook[] $whConfigs
     * @return \PHPUnit_Framework_MockObject_MockObject|WebhookRegistryInterface
     */
    private function createWebhookRegistryMock($events, $whConfigs)
    {
        $registry = $this->createMock(WebhookRegistryInterface::class);

        $registry->method('getWebhookConfigIds')->willReturnCallback(
            static function ($eventName) use ($events) {
                return isset($events[$eventName])
                    ? $events[$eventName]
                    : [];
            });

        $registry->method('getWebhookConfig')->willReturnCallback(
            static function ($id) use ($whConfigs) {
                return isset($whConfigs[$id])
                    ? $whConfigs[$id]
                    : null;
            });

        return $registry;
    }

    /**
     * @param string $contentType
     * @param string $payload
     * @return \PHPUnit_Framework_MockObject_MockObject|WebhookPayloadFactoryInterface
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
     * @return \PHPUnit_Framework_MockObject_MockObject|WebhookTaskParamsFactory
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
     * @return \PHPUnit_Framework_MockObject_MockObject|WebhookResponseFactoryInterface
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
     * @param ClientException|null $exception
     * @return \PHPUnit_Framework_MockObject_MockObject|WebhookSender
     */
    private function createWebhookSenderMock(
        ResponseInterface $response = null,
        ClientException $exception = null
    ) {
        $sender = $this->createMock(WebhookSender::class);
        if ($response) {
            $sender->method('performRequest')->willReturn($response);
        } else if ($exception) {
            $sender->method('performRequest')->willThrowException($exception);
        }

        return $sender;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private function createLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }

    /**
     * @param string $taskId
     * @return \PHPUnit_Framework_MockObject_MockObject|TaskInterface
     */
    private function createTaskMock($taskId)
    {
        $task = $this->createMock(TaskInterface::class);
        $task->method('getId')->willReturn($taskId);
        return $task;
    }
}
