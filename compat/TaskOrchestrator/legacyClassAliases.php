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

/**
 * Legacy `oat\tao\model\TaskOrchestrator\*` class names for extensions that must not
 * depend on taoDeliverConnect. Active when the implementation extension is installed.
 */
$legacyAliases = [
    [
        \oat\taoDeliverConnect\model\TaskOrchestrator\TaskOrchestratorClient::class,
        'oat\tao\model\TaskOrchestrator\TaskOrchestratorClient',
    ],
    [
        \oat\taoDeliverConnect\model\TaskOrchestrator\TaskOrchestratorEmailService::class,
        'oat\tao\model\TaskOrchestrator\TaskOrchestratorEmailService',
    ],
    [
        \oat\taoDeliverConnect\model\TaskOrchestrator\CommentMentionDeepLinkBuilder::class,
        'oat\tao\model\TaskOrchestrator\CommentMentionDeepLinkBuilder',
    ],
    [
        \oat\taoDeliverConnect\model\TaskOrchestrator\CommentMentionEmailTemplatePayload::class,
        'oat\tao\model\TaskOrchestrator\CommentMentionEmailTemplatePayload',
    ],
];

foreach ($legacyAliases as [$canonical, $legacy]) {
    if (class_exists($canonical) && !class_exists($legacy, false)) {
        class_alias($canonical, $legacy);
    }
}