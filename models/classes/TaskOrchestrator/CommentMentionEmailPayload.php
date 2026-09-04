<?php

declare(strict_types=1);

namespace oat\tao\model\TaskOrchestrator;

use InvalidArgumentException;

/**
 * Payload for tao-templates `comment-mention` (NYSED-38 / FR8).
 *
 * Templates worker requiredParams: resourceType, resourceLabel, resourceUrl.
 * Also pass mentionedBy / username for product copy; optional name for greeting.
 */
final class CommentMentionEmailPayload
{
    public const TEMPLATE_ID = 'comment-mention';

    private string $mentionedBy;
    private string $username;
    private string $resourceType;
    private string $resourceUrl;
    private string $resourceLabel;
    private ?string $name;

    public function __construct(
        string $mentionedBy,
        string $username,
        string $resourceType,
        string $resourceUrl,
        string $resourceLabel,
        ?string $name = null
    ) {
        $this->mentionedBy = $this->assertNonEmpty('mentionedBy', $mentionedBy);
        $this->username = $this->assertNonEmpty('username', $username);
        $this->resourceType = $this->assertNonEmpty('resourceType', $resourceType);
        $this->resourceUrl = $this->assertNonEmpty('resourceUrl', $resourceUrl);
        $this->resourceLabel = $this->assertNonEmpty('resourceLabel', $resourceLabel);
        $this->name = $name !== null && trim($name) !== '' ? trim($name) : null;
    }

    /**
     * @return array{
     *     mentionedBy: string,
     *     username: string,
     *     resourceType: string,
     *     resourceUrl: string,
     *     resourceLabel: string,
     *     name?: string
     * }
     */
    public function toTemplateData(): array
    {
        $data = [
            'mentionedBy' => $this->mentionedBy,
            'username' => $this->username,
            'resourceType' => $this->resourceType,
            'resourceUrl' => $this->resourceUrl,
            'resourceLabel' => $this->resourceLabel,
        ];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        return $data;
    }

    private function assertNonEmpty(string $field, string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new InvalidArgumentException(sprintf('Comment mention email field "%s" is required', $field));
        }

        return $trimmed;
    }
}
