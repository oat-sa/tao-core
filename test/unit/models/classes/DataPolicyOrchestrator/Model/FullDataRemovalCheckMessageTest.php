<?php

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\DataPolicyOrchestrator\Model;

use oat\tao\model\DataPolicyOrchestrator\Model\FullDataRemovalCheckMessage;
use PHPUnit\Framework\TestCase;

class FullDataRemovalCheckMessageTest extends TestCase
{
    public function testItMapsPayloadAndSerializes(): void
    {
        $payload = [
            'dataSubjectRawId' => 'john.doe',
            'ownerApp' => 'authoring',
            'tenantId' => 'tenant-1',
            'policyId' => 'policy-1',
            'policyVersion' => '1',
        ];

        $message = new FullDataRemovalCheckMessage($payload);

        $this->assertSame('policy-1', $message->policyId);
        $this->assertSame($payload, $message->jsonSerialize());
    }
}
