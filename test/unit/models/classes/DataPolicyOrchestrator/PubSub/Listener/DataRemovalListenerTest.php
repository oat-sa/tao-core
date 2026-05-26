<?php

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\DataPolicyOrchestrator\PubSub\Listener;

use oat\tao\model\DataPolicyOrchestrator\Handler\DataPolicyHandlerInterface;
use oat\tao\model\DataPolicyOrchestrator\Model\DataRemovalMessage;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Listener\DataRemovalListener;
use oat\tao\model\Observer\GCP\PubSubClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionMethod;

class DataRemovalListenerTest extends TestCase
{
    public function testCreateDataPolicyMessageBuildsDataRemovalMessage(): void
    {
        $listener = new DataRemovalListener(
            $this->createMock(PubSubClientFactory::class),
            $this->createMock(DataPolicyHandlerInterface::class),
            $this->createMock(LoggerInterface::class),
            'subscription'
        );

        $method = new ReflectionMethod($listener, 'createDataPolicyMessage');
        $method->setAccessible(true);
        $message = $method->invoke($listener, [
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

        $this->assertInstanceOf(DataRemovalMessage::class, $message);
    }
}
