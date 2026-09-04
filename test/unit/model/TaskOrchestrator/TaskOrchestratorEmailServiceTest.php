<?php

declare(strict_types=1);

namespace oat\tao\test\unit\model\TaskOrchestrator;

use InvalidArgumentException;
use oat\tao\model\TaskOrchestrator\CommentMentionEmailPayload;
use oat\tao\model\TaskOrchestrator\TaskOrchestratorClient;
use oat\tao\model\TaskOrchestrator\TaskOrchestratorEmailService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaskOrchestratorEmailServiceTest extends TestCase
{
    /** @var TaskOrchestratorClient|MockObject */
    private $client;

    private TaskOrchestratorEmailService $sut;

    protected function setUp(): void
    {
        $this->client = $this->createMock(TaskOrchestratorClient::class);
        $this->sut = new TaskOrchestratorEmailService(
            $this->client,
            'local-dev-acc.nextgen-stack-local',
            'tao-backoffice-bot'
        );
    }

    public function testSendCommentMentionBuildsPortalEmailNotificationJobWithRdfEmail(): void
    {
        $payload = new CommentMentionEmailPayload(
            'John Doe',
            'jdoe',
            'item',
            'https://backoffice.example/items/123',
            'Item ABC',
            'Jane'
        );

        $this->client
            ->expects($this->once())
            ->method('sendJob')
            ->with(
                $this->callback(static function (string $jobId): bool {
                    return $jobId !== '';
                }),
                $this->callback(static function (array $job): bool {
                    return $job['type'] === 'portalEmailNotification'
                        && $job['tenantId'] === 'local-dev-acc.nextgen-stack-local'
                        && $job['user']['login'] === 'tao-backoffice-bot'
                        && $job['email']['templateId'] === CommentMentionEmailPayload::TEMPLATE_ID
                        && $job['email']['recipientUserLogin'] === 'jdoe'
                        && $job['email']['emailAddress'] === 'jdoe@example.com'
                        && $job['email']['data'] === [
                            'mentionedBy' => 'John Doe',
                            'username' => 'jdoe',
                            'resourceType' => 'item',
                            'resourceUrl' => 'https://backoffice.example/items/123',
                            'resourceLabel' => 'Item ABC',
                            'name' => 'Jane',
                        ];
                })
            )
            ->willReturn(['status' => 'ok']);

        $jobId = $this->sut->sendCommentMention('jdoe', 'jdoe@example.com', $payload);

        $this->assertNotSame('', $jobId);
    }

    public function testCommentMentionPayloadRejectsEmptyRequiredField(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('resourceUrl');

        new CommentMentionEmailPayload('John', 'jdoe', 'item', '  ', 'Item ABC');
    }

    public function testSendCommentMentionRejectsInvalidEmailAddress(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('emailAddress');

        $this->sut->sendCommentMention(
            'jdoe',
            'not-an-email',
            new CommentMentionEmailPayload(
                'John Doe',
                'jdoe',
                'item',
                'https://backoffice.example/items/123',
                'Item ABC'
            )
        );
    }
}
