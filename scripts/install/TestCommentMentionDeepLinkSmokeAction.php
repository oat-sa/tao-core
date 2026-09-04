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

namespace oat\tao\scripts\install;

use common_report_Report as Report;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\TaskOrchestrator\CommentMentionDeepLinkBuilder;
use oat\tao\model\TaskOrchestrator\CommentMentionEmailPayload;
use oat\tao\model\TaskOrchestrator\TaskOrchestratorEmailService;
use Throwable;

/**
 * Smoke: build FR8 item deep link and send comment-mention via Task Orchestrator.
 *
 * Usage:
 *   php index.php 'oat\tao\scripts\install\TestCommentMentionDeepLinkSmokeAction'
 *   php index.php 'oat\tao\scripts\install\TestCommentMentionDeepLinkSmokeAction' \
 *     recipientUserLogin=ngs-email-smoke \
 *     emailAddress=you@example.com \
 *     resourceUri=https://backoffice.ngs.test/ontologies/tao.rdf#i6a99334baa54620260903104355cbdf0402 \
 *     backofficeBaseUrl=https://backoffice.ngs.test
 *
 * Or pass the full Backoffice deep link:
 *   php index.php 'oat\tao\scripts\install\TestCommentMentionDeepLinkSmokeAction' \
 *     emailAddress=you@example.com \
 *     resourceUrl='https://backoffice.ngs.test/tao/Main/index?structure=items&ext=taoItems&section=manage_items&uri=https%3A%2F%2Fbackoffice.ngs.test%2Fontologies%2Ftao.rdf%23i6a99334baa54620260903104355cbdf0402'
 */
class TestCommentMentionDeepLinkSmokeAction extends InstallAction
{
    private const DEFAULT_RECIPIENT_LOGIN = 'ngs-email-smoke';
    private const DEFAULT_ITEM_URI =
        'https://backoffice.ngs.test/ontologies/tao.rdf#i6a99334baa54620260903104355cbdf0402';
    private const DEFAULT_BACKOFFICE_BASE = 'https://backoffice.ngs.test';
    private const DEFAULT_RESOURCE_URL =
        'https://backoffice.ngs.test/tao/Main/index?structure=items&ext=taoItems&section=manage_items&uri=https%3A%2F%2Fbackoffice.ngs.test%2Fontologies%2Ftao.rdf%23i6a99334baa54620260903104355cbdf0402';

    public function __invoke($params)
    {
        $options = $this->parseParams(is_array($params) ? $params : []);

        try {
            $baseUrl = $options['backofficeBaseUrl'] !== ''
                ? $options['backofficeBaseUrl']
                : self::DEFAULT_BACKOFFICE_BASE;
            $recipient = $options['recipientUserLogin'] !== ''
                ? $options['recipientUserLogin']
                : self::DEFAULT_RECIPIENT_LOGIN;
            $emailAddress = $options['emailAddress'] !== ''
                ? $options['emailAddress']
                : '';

            if ($emailAddress === '' || !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                return Report::createFailure(
                    'emailAddress is required (RDF delivery address), e.g. emailAddress=you@example.com'
                );
            }

            if ($options['resourceUrl'] !== '') {
                $resourceUrl = $options['resourceUrl'];
                $resourceUri = $this->extractResourceUri($resourceUrl) ?? self::DEFAULT_ITEM_URI;
            } elseif ($options['resourceUri'] !== '') {
                $resourceUri = $options['resourceUri'];
                $deepLinkBuilder = new CommentMentionDeepLinkBuilder($baseUrl);
                $resourceUrl = $deepLinkBuilder->build(
                    CommentMentionDeepLinkBuilder::OBJECT_TYPE_ITEM,
                    $resourceUri
                );
            } else {
                $resourceUri = self::DEFAULT_ITEM_URI;
                $resourceUrl = self::DEFAULT_RESOURCE_URL;
            }

            $validation = $this->assertDeepLinkShape($resourceUrl, $resourceUri, $baseUrl);
            if ($validation !== null) {
                $this->logError($validation);

                return Report::createFailure($validation);
            }

            $this->logInfo(sprintf('Deep link OK: %s', $resourceUrl));

            /** @var TaskOrchestratorEmailService $emailService */
            $emailService = $this->getServiceManager()
                ->getContainer()
                ->get(TaskOrchestratorEmailService::class);

            $resourceType = $options['resourceType'] !== ''
                ? $options['resourceType']
                : CommentMentionDeepLinkBuilder::OBJECT_TYPE_ITEM;

            $jobId = $emailService->sendCommentMention(
                $recipient,
                $emailAddress,
                new CommentMentionEmailPayload(
                    $options['mentionedBy'] !== '' ? $options['mentionedBy'] : 'John Doe',
                    $options['username'] !== '' ? $options['username'] : 'jdoe',
                    $resourceType,
                    $resourceUrl,
                    $options['resourceLabel'] !== '' ? $options['resourceLabel'] : 'Item ABC',
                    $options['name'] !== '' ? $options['name'] : 'Jane'
                )
            );

            $message = sprintf(
                'Comment-mention deep-link smoke OK. resourceUrl=%s emailAddress=%s jobId=%s',
                $resourceUrl,
                $emailAddress,
                $jobId
            );
            $this->logInfo($message);

            return Report::createSuccess($message);
        } catch (Throwable $e) {
            $this->logError(sprintf('Comment-mention deep-link smoke failed: %s', $e->getMessage()));

            return Report::createFailure(
                sprintf('Comment-mention deep-link smoke failed: %s', $e->getMessage())
            );
        }
    }

    private function assertDeepLinkShape(string $resourceUrl, string $resourceUri, string $baseUrl): ?string
    {
        $expectedPrefix = rtrim($baseUrl, '/') . '/tao/Main/index?';
        if (!str_starts_with($resourceUrl, $expectedPrefix)) {
            return sprintf('Deep link must start with "%s", got: %s', $expectedPrefix, $resourceUrl);
        }

        $parts = parse_url($resourceUrl);
        if (!isset($parts['query'])) {
            return 'Deep link is missing query string';
        }

        parse_str($parts['query'], $query);
        $expected = [
            'structure' => 'items',
            'ext' => 'taoItems',
            'section' => 'manage_items',
            'uri' => $resourceUri,
        ];

        foreach ($expected as $key => $value) {
            if (($query[$key] ?? null) !== $value) {
                return sprintf(
                    'Deep link query "%s" expected "%s", got "%s" (url=%s)',
                    $key,
                    $value,
                    (string) ($query[$key] ?? ''),
                    $resourceUrl
                );
            }
        }

        // Encoded form must appear in the raw URL (as in production mail links).
        $encodedUri = rawurlencode($resourceUri);
        if (!str_contains($resourceUrl, 'uri=' . $encodedUri)) {
            return sprintf(
                'Deep link must contain RFC3986-encoded uri=%s, got: %s',
                $encodedUri,
                $resourceUrl
            );
        }

        return null;
    }

    private function extractResourceUri(string $resourceUrl): ?string
    {
        $parts = parse_url($resourceUrl);
        if (!isset($parts['query'])) {
            return null;
        }

        parse_str($parts['query'], $query);
        $uri = $query['uri'] ?? null;

        return is_string($uri) && $uri !== '' ? $uri : null;
    }

    /**
     * @param array<int|string, mixed> $params
     * @return array<string, string>
     */
    private function parseParams(array $params): array
    {
        $options = [
            'recipientUserLogin' => '',
            'emailAddress' => '',
            'resourceUri' => '',
            'resourceUrl' => '',
            'resourceType' => '',
            'backofficeBaseUrl' => '',
            'mentionedBy' => '',
            'username' => '',
            'resourceLabel' => '',
            'name' => '',
        ];

        foreach ($params as $param) {
            if (!is_string($param) || !str_contains($param, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $param, 2);
            $key = trim($key);
            $value = trim($value);
            if ($key !== '' && array_key_exists($key, $options)) {
                $options[$key] = $value;
            }
        }

        return $options;
    }
}
