<?php

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\DataPolicyOrchestrator\PubSub\Publisher;

use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Topic;
use oat\tao\model\DataPolicyOrchestrator\Exception\DataPolicyException;
use oat\tao\model\DataPolicyOrchestrator\Model\DataRemovalMessage;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Publisher\DataRemovalConfirmationPublisher;
use oat\tao\model\Observer\GCP\PubSubClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class DataRemovalConfirmationPublisherTest extends TestCase
{
    private PubSubClientFactory|MockObject $pubSubClientFactory;
    private LoggerInterface|MockObject $logger;
    private DataRemovalConfirmationPublisher $subject;

    protected function setUp(): void
    {
        if (!class_exists(PubSubClient::class) || !class_exists(Topic::class)) {
            $this->markTestSkipped('google/cloud-pubsub is not installed');
        }

        $this->pubSubClientFactory = $this->createMock(PubSubClientFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->subject = new DataRemovalConfirmationPublisher($this->pubSubClientFactory, $this->logger);
    }

    public function testPublishPayloadSkipsWhenTopicNameIsEmpty(): void
    {
        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('Data policy topic is empty, skipping publish.');

        $this->pubSubClientFactory
            ->expects($this->never())
            ->method('create');

        $this->subject->publishPayload('', $this->createMessage());
    }

    public function testPublishPayloadPublishesEncodedMessage(): void
    {
        $pubSubClient = $this->createMock(PubSubClient::class);
        $topic = $this->createMock(Topic::class);

        $this->pubSubClientFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pubSubClient);

        $pubSubClient
            ->expects($this->once())
            ->method('topic')
            ->with('confirmation-topic')
            ->willReturn($topic);

        $topic
            ->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (array $payload): bool {
                $decoded = json_decode($payload['data'], true);
                $body = json_decode($decoded['body'], true);

                return $decoded['header']['type'] === 'confirmation-topic'
                    && $body['policyId'] === 'policy-1'
                    && $body['dataSubjectRawId'] === 'john.doe';
            }));

        $this->subject->publishPayload('confirmation-topic', $this->createMessage());
    }

    public function testPublishPayloadThrowsDataPolicyExceptionOnClientFailure(): void
    {
        $this->pubSubClientFactory
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new RuntimeException('gcp unavailable'));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Failed to publish confirmation to topic "confirmation-topic"'));

        $this->expectException(DataPolicyException::class);

        $this->subject->publishPayload('confirmation-topic', $this->createMessage());
    }

    private function createMessage(): DataRemovalMessage
    {
        return new DataRemovalMessage([
            'dataSubjectRawId' => 'john.doe',
            'ownerApp' => 'authoring',
            'policyId' => 'policy-1',
            'policyVersion' => '1',
            'tenantId' => 'tenant-1',
            'uniqueId' => 'uid-1',
            'name' => 'user',
            'storageType' => 'db',
            'metadata' => [],
        ]);
    }
}
