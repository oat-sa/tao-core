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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\TaskOrchestrator;

use InvalidArgumentException;
use oat\tao\model\TaskOrchestrator\CommentMentionEmailTemplatePayload;
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
        $this->client->method('isConfigured')->willReturn(true);
        $this->sut = new TaskOrchestratorEmailService(
            $this->client,
            'local-dev-acc.nextgen-stack-local'
        );
    }

    public function testIsConfiguredRequiresClientAndTenant(): void
    {
        $this->assertTrue($this->sut->isConfigured());

        $unconfiguredClient = $this->createMock(TaskOrchestratorClient::class);
        $unconfiguredClient->method('isConfigured')->willReturn(false);
        $withoutClient = new TaskOrchestratorEmailService($unconfiguredClient, 'tenant');
        $this->assertFalse($withoutClient->isConfigured());

        $configuredClient = $this->createMock(TaskOrchestratorClient::class);
        $configuredClient->method('isConfigured')->willReturn(true);
        $withoutTenant = new TaskOrchestratorEmailService($configuredClient, '  ');
        $this->assertFalse($withoutTenant->isConfigured());
    }

    public function testSendEmailRejectsWhenNotConfigured(): void
    {
        $client = $this->createMock(TaskOrchestratorClient::class);
        $client->method('isConfigured')->willReturn(false);
        $sut = new TaskOrchestratorEmailService($client, 'tenant');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not configured');

        $sut->sendEmail('generic.template', 'jdoe', [], null, 'alice.author');
    }

    public function testSendEmailBuildsPortalEmailNotificationJob(): void
    {
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
                        && $job['user']['login'] === 'alice.author'
                        && $job['user']['id'] === 'local-dev-acc.nextgen-stack-local_alice.author'
                        && $job['email']['templateId'] === 'generic.template'
                        && $job['email']['recipientUserLogin'] === 'jdoe'
                        && $job['email']['data'] === ['foo' => 'bar']
                        && !isset($job['email']['emailAddress']);
                })
            )
            ->willReturn(['status' => 'ok']);

        $jobId = $this->sut->sendEmail('generic.template', 'jdoe', ['foo' => 'bar'], null, 'alice.author');

        $this->assertNotSame('', $jobId);
    }

    public function testSendEmailIncludesOptionalEmailAddress(): void
    {
        $this->client
            ->expects($this->once())
            ->method('sendJob')
            ->with(
                $this->callback(static function (string $jobId): bool {
                    return $jobId !== '';
                }),
                $this->callback(static function (array $job): bool {
                    return $job['email']['templateId'] === 'generic.template'
                        && $job['email']['recipientUserLogin'] === 'jdoe'
                        && $job['email']['emailAddress'] === 'jdoe@example.com'
                        && $job['email']['data'] === []
                        && $job['user']['login'] === 'alice.author';
                })
            )
            ->willReturn(['status' => 'ok']);

        $jobId = $this->sut->sendEmail('generic.template', 'jdoe', [], 'jdoe@example.com', 'alice.author');

        $this->assertNotSame('', $jobId);
    }

    public function testSendEmailRejectsMissingActorLogin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('actorLogin');

        $this->sut->sendEmail('generic.template', 'jdoe', []);
    }

    public function testSendEmailRejectsInvalidEmailAddress(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('emailAddress');

        $this->sut->sendEmail('generic.template', 'jdoe', [], 'not-an-email', 'alice.author');
    }

    public function testSendCommentMentionBuildsPortalEmailNotificationJobWithRdfEmail(): void
    {
        $payload = new CommentMentionEmailTemplatePayload(
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
                        && $job['user']['login'] === 'john.author'
                        && $job['user']['id'] === 'local-dev-acc.nextgen-stack-local_john.author'
                        && $job['email']['templateId'] === CommentMentionEmailTemplatePayload::TEMPLATE_ID
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

        $jobId = $this->sut->sendCommentMention('jdoe', 'jdoe@example.com', $payload, 'john.author');

        $this->assertNotSame('', $jobId);
    }

    public function testCommentMentionPayloadRejectsEmptyRequiredField(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('resourceUrl');

        new CommentMentionEmailTemplatePayload('John', 'jdoe', 'item', '  ', 'Item ABC');
    }

    public function testSendCommentMentionRejectsInvalidEmailAddress(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('emailAddress');

        $this->sut->sendCommentMention(
            'jdoe',
            'not-an-email',
            new CommentMentionEmailTemplatePayload(
                'John Doe',
                'jdoe',
                'item',
                'https://backoffice.example/items/123',
                'Item ABC'
            ),
            'john.author'
        );
    }
}
