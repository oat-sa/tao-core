<?php

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\DataPolicyOrchestrator\PubSub\Listener;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use oat\tao\model\DataPolicyOrchestrator\Exception\DataPolicyException;
use oat\tao\model\DataPolicyOrchestrator\Handler\DataPolicyHandlerInterface;
use oat\tao\model\DataPolicyOrchestrator\Model\DataPolicyMessageInterface;
use oat\tao\model\DataPolicyOrchestrator\Model\DataRemovalMessage;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Listener\AbstractDataPolicyListener;
use oat\tao\model\Observer\GCP\PubSubClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class AbstractDataPolicyListenerTest extends TestCase
{
    private const SUBSCRIPTION_NAME = 'data-policy-subscription';

    private PubSubClientFactory|MockObject $pubSubClientFactory;
    private DataPolicyHandlerInterface|MockObject $handlerProxy;
    private LoggerInterface|MockObject $logger;
    private TestDataPolicyListener $subject;

    protected function setUp(): void
    {
        if (
            !class_exists(PubSubClient::class)
            || !class_exists(Subscription::class)
            || !class_exists(Message::class)
        ) {
            $this->markTestSkipped('google/cloud-pubsub is not installed');
        }

        $this->pubSubClientFactory = $this->createMock(PubSubClientFactory::class);
        $this->handlerProxy = $this->createMock(DataPolicyHandlerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->subject = new TestDataPolicyListener(
            $this->pubSubClientFactory,
            $this->handlerProxy,
            $this->logger,
            self::SUBSCRIPTION_NAME
        );
    }

    public function testRunThrowsDataPolicyExceptionIfClientCannotBeCreated(): void
    {
        $this->pubSubClientFactory
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new RuntimeException('cannot connect'));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Pub/Sub listener failed to initialize client'));

        $this->expectException(DataPolicyException::class);

        $this->subject->run(1, 0, 1);
    }

    public function testRunProcessesAuthoringMessageAndAcknowledges(): void
    {
        [$pubSubClient, $subscription, $message] = $this->preparePubSubFlow(
            json_encode(['body' => $this->createMessagePayload('authoring')])
        );

        $this->pubSubClientFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pubSubClient);

        $this->handlerProxy
            ->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (DataPolicyMessageInterface $message): bool {
                return $message instanceof DataRemovalMessage && $message->policyId === 'policy-1';
            }));

        $subscription
            ->expects($this->once())
            ->method('acknowledge')
            ->with($message);

        $subscription
            ->expects($this->never())
            ->method('modifyAckDeadline');

        $this->subject->run(1, 0, 1);
    }

    public function testRunSkipsNonAuthoringMessagesButAcknowledgesThem(): void
    {
        [$pubSubClient, $subscription, $message] = $this->preparePubSubFlow(
            json_encode(['body' => $this->createMessagePayload('delivery')])
        );

        $this->pubSubClientFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pubSubClient);

        $this->handlerProxy
            ->expects($this->never())
            ->method('handle');

        $subscription
            ->expects($this->once())
            ->method('acknowledge')
            ->with($message);

        $this->subject->run(1, 0, 1);
    }

    public function testRunNacksMessageWhenPayloadIsInvalid(): void
    {
        [$pubSubClient, $subscription, $message] = $this->preparePubSubFlow('not-a-json');

        $this->pubSubClientFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pubSubClient);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Pub/Sub processing failed for subscription'));

        $this->handlerProxy
            ->expects($this->never())
            ->method('handle');

        $subscription
            ->expects($this->once())
            ->method('modifyAckDeadline')
            ->with($message, 0);

        $subscription
            ->expects($this->never())
            ->method('acknowledge');

        $this->subject->run(1, 0, 1);
    }

    private function preparePubSubFlow(string $messageData): array
    {
        $pubSubClient = $this->createMock(PubSubClient::class);
        $subscription = $this->createMock(Subscription::class);
        $message = $this->createMock(Message::class);

        $pubSubClient
            ->method('subscription')
            ->with(self::SUBSCRIPTION_NAME)
            ->willReturn($subscription);

        $subscription
            ->method('pull')
            ->willReturn([$message]);

        $message
            ->method('data')
            ->willReturn($messageData);

        return [$pubSubClient, $subscription, $message];
    }

    private function createMessagePayload(string $ownerApp): array
    {
        return [
            'dataSubjectRawId' => 'john.doe',
            'ownerApp' => $ownerApp,
            'policyId' => 'policy-1',
            'policyVersion' => '1',
            'tenantId' => 'tenant-1',
            'uniqueId' => 'uid-1',
            'name' => 'user',
            'storageType' => 'db',
            'metadata' => [],
        ];
    }
}

class TestDataPolicyListener extends AbstractDataPolicyListener
{
    protected function createDataPolicyMessage(array $body): ?DataPolicyMessageInterface
    {
        return new DataRemovalMessage($body);
    }
}
