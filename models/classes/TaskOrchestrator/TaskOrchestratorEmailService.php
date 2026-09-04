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

namespace oat\tao\model\TaskOrchestrator;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class TaskOrchestratorEmailService
{
    private TaskOrchestratorClient $client;
    private string $tenantId;
    private string $actorLogin;

    public function __construct(
        TaskOrchestratorClient $client,
        string $tenantId,
        string $actorLogin
    ) {
        $this->client = $client;
        $this->tenantId = $tenantId;
        $this->actorLogin = $actorLogin;
    }

    /**
     * @param array<string, mixed> $templateData
     * @param string|null $emailAddress When set, TO delivers to this address and skips portal-user lookup
     */
    public function sendEmail(
        string $templateId,
        string $recipientUserLogin,
        array $templateData = [],
        ?string $emailAddress = null
    ): string {
        $jobId = Uuid::uuid4()->toString();

        $email = [
            'templateId' => $templateId,
            'recipientUserLogin' => $recipientUserLogin,
            'data' => $templateData,
        ];

        if ($emailAddress !== null) {
            $emailAddress = trim($emailAddress);
            if ($emailAddress === '' || !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('emailAddress must be a valid email when provided');
            }
            $email['emailAddress'] = $emailAddress;
        }

        $jobPayload = [
            'type' => 'portalEmailNotification',
            'tenantId' => $this->tenantId,
            'status' => 'initial',
            'progress' => 0,
            'user' => [
                'id' => sprintf('%s_%s', $this->tenantId, $this->actorLogin),
                'login' => $this->actorLogin,
            ],
            'email' => $email,
            'meta' => [
                'labelKey' => 'tao_backoffice_email',
            ],
        ];

        $this->client->sendJob($jobId, $jobPayload);

        return $jobId;
    }

    /**
     * @param string $recipientUserLogin RDF / Backoffice login (correlation; still required by TO schema)
     * @param string $emailAddress RDF PROPERTY_USER_MAIL — delivery address
     */
    public function sendCommentMention(
        string $recipientUserLogin,
        string $emailAddress,
        CommentMentionEmailTemplatePayload $payload
    ): string {
        return $this->sendEmail(
            CommentMentionEmailTemplatePayload::TEMPLATE_ID,
            $recipientUserLogin,
            $payload->toTemplateData(),
            $emailAddress
        );
    }
}
