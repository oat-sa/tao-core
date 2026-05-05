<?php

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\DataPolicyOrchestrator\Model;

use oat\tao\model\DataPolicyOrchestrator\Model\DataRemovalConfirmationMessage;
use PHPUnit\Framework\TestCase;

class DataRemovalConfirmationMessageTest extends TestCase
{
    public function testItSetsFailedStatusWhenErrorsArePresent(): void
    {
        $message = new DataRemovalConfirmationMessage($this->createPayload(), ['failure']);

        $this->assertSame('failed', $message->status);
        $this->assertSame(['failure'], $message->errors);
    }

    public function testItSetsRemovedStatusWhenErrorsAreEmpty(): void
    {
        $message = new DataRemovalConfirmationMessage($this->createPayload(), []);

        $this->assertSame('removed', $message->status);
        $this->assertSame([], $message->errors);
    }

    private function createPayload(): array
    {
        return [
            'dataSubjectRawId' => 'john.doe',
            'ownerApp' => 'authoring',
            'policyId' => 'policy-1',
            'policyVersion' => '1',
            'tenantId' => 'tenant-1',
            'uniqueId' => 'uid-1',
            'name' => 'user',
            'storageType' => 'db',
        ];
    }
}
