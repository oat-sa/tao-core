<?php

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\DataPolicyOrchestrator\PubSub\Listener;

use oat\tao\model\DataPolicyOrchestrator\Handler\DataPolicyHandlerInterface;
use oat\tao\model\DataPolicyOrchestrator\Model\FullDataRemovalCheckMessage;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Listener\FullDataRemovalCheckListener;
use oat\tao\model\Observer\GCP\PubSubClientFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionMethod;

class FullDataRemovalCheckListenerTest extends TestCase
{
    public function testCreateDataPolicyMessageBuildsFullDataRemovalCheckMessage(): void
    {
        $listener = new FullDataRemovalCheckListener(
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
            'tenantId' => 'tenant-1',
            'policyId' => 'policy-1',
            'policyVersion' => '1',
        ]);

        $this->assertInstanceOf(FullDataRemovalCheckMessage::class, $message);
    }
}
