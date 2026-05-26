<?php

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\DataPolicyOrchestrator\Model;

use oat\tao\model\DataPolicyOrchestrator\Model\FullDataRemovalConfirmationMessage;
use PHPUnit\Framework\TestCase;

class FullDataRemovalConfirmationMessageTest extends TestCase
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

        $message = new FullDataRemovalConfirmationMessage($payload);

        $this->assertSame('john.doe', $message->dataSubjectRawId);
        $this->assertSame($payload, $message->jsonSerialize());
    }
}
