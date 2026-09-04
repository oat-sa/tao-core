<?php

declare(strict_types=1);

namespace oat\tao\scripts\install;

use common_report_Report as Report;
use InvalidArgumentException;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\TaskOrchestrator\CommentMentionDeepLinkBuilder;
use oat\tao\model\TaskOrchestrator\CommentMentionEmailPayload;
use oat\tao\model\TaskOrchestrator\TaskOrchestratorEmailService;
use Throwable;

/**
 * Temporary script to test Task Orchestrator email integration.
 *
 * MFA:
 *   php index.php 'oat\tao\scripts\install\TestTaskOrchestratorEmailAction' \
 *     templateId=mfa-code-email recipientUserLogin=ngs-email-smoke
 *
 * Comment mention (NYSED-38):
 *   php index.php 'oat\tao\scripts\install\TestTaskOrchestratorEmailAction' \
 *     templateId=comment-mention recipientUserLogin=rdf-login \
 *     emailAddress=you@example.com \
 *     mentionedBy=JohnDoe username=jdoe \
 *     objectType=item resourceUri=https://backoffice.ngs.test/ontologies/tao.rdf#i... \
 *     resourceLabel=ItemABC name=Jane
 */
class TestTaskOrchestratorEmailAction extends InstallAction
{
    private const DEFAULT_TEMPLATE_ID = 'mfa-code-email';
    private const DEFAULT_RECIPIENT_LOGIN = 'ngs-email-smoke';
    private const DEFAULT_ITEM_URI =
        'https://backoffice.ngs.test/ontologies/tao.rdf#i6a96e3923cff32026090116391476b0b622';

    public function __invoke($params)
    {
        $options = $this->parseParams(is_array($params) ? $params : []);
        $templateId = $options['templateId'];
        $recipientUserLogin = $options['recipientUserLogin'];

        $this->logInfo(sprintf(
            'Attempting TO email: templateId=%s recipientUserLogin=%s',
            $templateId,
            $recipientUserLogin
        ));

        try {
            /** @var TaskOrchestratorEmailService $emailService */
            $emailService = $this->getServiceManager()
                ->getContainer()
                ->get(TaskOrchestratorEmailService::class);

            if ($templateId === CommentMentionEmailPayload::TEMPLATE_ID) {
                $emailAddress = $options['emailAddress'];
                if ($emailAddress === '' || !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                    throw new InvalidArgumentException(
                        'emailAddress is required for comment-mention (RDF mail), e.g. emailAddress=you@example.com'
                    );
                }
                $jobId = $emailService->sendCommentMention(
                    $recipientUserLogin,
                    $emailAddress,
                    $this->buildCommentMentionPayload($options)
                );
            } else {
                $jobId = $emailService->sendEmail(
                    $templateId,
                    $recipientUserLogin,
                    $this->buildGenericTemplateData($templateId, $options)
                );
            }

            $message = sprintf(
                'Successfully sent test email job to Task Orchestrator. Job ID: %s. Check TO logs and the recipient inbox.',
                $jobId
            );
            $this->logInfo($message);

            return Report::createSuccess($message);
        } catch (Throwable $e) {
            $this->logError(sprintf('Failed to send test email via Task Orchestrator: %s', $e->getMessage()));
            $this->logError(
                'Ensure TASK_ORCHESTRATOR_* / TAO_TENANT_ID / TAO_TASK_ORCHESTRATOR_ACTOR_LOGIN are set and TO services are running.'
            );

            return Report::createFailure(
                sprintf('Failed to send test email via Task Orchestrator: %s', $e->getMessage())
            );
        }
    }

    /**
     * @param array<string, string> $options
     */
    private function buildCommentMentionPayload(array $options): CommentMentionEmailPayload
    {
        $resourceType = $options['resourceType'] !== ''
            ? $options['resourceType']
            : ($options['objectType'] !== ''
                ? $options['objectType']
                : CommentMentionDeepLinkBuilder::OBJECT_TYPE_ITEM);

        return new CommentMentionEmailPayload(
            $options['mentionedBy'] !== '' ? $options['mentionedBy'] : 'John Doe',
            $options['username'] !== '' ? $options['username'] : 'jdoe',
            $resourceType,
            $this->resolveResourceUrl($options),
            $options['resourceLabel'] !== '' ? $options['resourceLabel'] : 'Item ABC',
            $options['name'] !== '' ? $options['name'] : 'Jane'
        );
    }

    /**
     * @param array<string, string> $options
     */
    private function resolveResourceUrl(array $options): string
    {
        if ($options['resourceUrl'] !== '') {
            return $options['resourceUrl'];
        }

        $objectType = $options['objectType'] !== ''
            ? $options['objectType']
            : CommentMentionDeepLinkBuilder::OBJECT_TYPE_ITEM;
        $resourceUri = $options['resourceUri'] !== ''
            ? $options['resourceUri']
            : self::DEFAULT_ITEM_URI;

        /** @var CommentMentionDeepLinkBuilder $deepLinkBuilder */
        $deepLinkBuilder = $this->getServiceManager()
            ->getContainer()
            ->get(CommentMentionDeepLinkBuilder::class);

        return $deepLinkBuilder->build($objectType, $resourceUri);
    }

    /**
     * @param array<string, string> $options
     * @return array<string, mixed>
     */
    private function buildGenericTemplateData(string $templateId, array $options): array
    {
        if ($templateId === 'mfa-code-email' || $templateId === 'mfa-code-email-tenantless') {
            return [
                'name' => $options['name'] !== '' ? $options['name'] : 'Test User',
                'mfaCode' => $options['mfaCode'] !== '' ? $options['mfaCode'] : '123456',
                'validityTime' => 3600,
                'locale' => 'en-US',
            ];
        }

        throw new InvalidArgumentException(sprintf(
            'Unsupported templateId "%s". Use "%s" or mfa-code-email (or pass a dedicated builder).',
            $templateId,
            CommentMentionEmailPayload::TEMPLATE_ID
        ));
    }

    /**
     * @param array<int|string, mixed> $params
     * @return array<string, string>
     */
    private function parseParams(array $params): array
    {
        $options = [
            'templateId' => self::DEFAULT_TEMPLATE_ID,
            'recipientUserLogin' => self::DEFAULT_RECIPIENT_LOGIN,
            'emailAddress' => '',
            'mentionedBy' => '',
            'username' => '',
            'resourceUrl' => '',
            'resourceUri' => '',
            'resourceType' => '',
            'objectType' => '',
            'resourceLabel' => '',
            'name' => '',
            'mfaCode' => '',
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
