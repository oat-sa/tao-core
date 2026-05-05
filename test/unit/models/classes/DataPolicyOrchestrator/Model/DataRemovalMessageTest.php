<?php

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\DataPolicyOrchestrator\Model;

use oat\tao\model\DataPolicyOrchestrator\Model\DataRemovalMessage;
use PHPUnit\Framework\TestCase;

class DataRemovalMessageTest extends TestCase
{
    public function testItMapsPayloadAndSerializes(): void
    {
        $payload = [
            'dataSubjectRawId' => 'john.doe',
            'ownerApp' => 'authoring',
            'policyId' => 'policy-1',
            'policyVersion' => '1',
            'tenantId' => 'tenant-1',
            'uniqueId' => 'uid-1',
            'name' => 'user',
            'storageType' => 'db',
            'metadata' => ['source' => 'test'],
        ];

        $message = new DataRemovalMessage($payload);

        $this->assertSame('john.doe', $message->dataSubjectRawId);
        $this->assertSame($payload, $message->jsonSerialize());
    }
}
