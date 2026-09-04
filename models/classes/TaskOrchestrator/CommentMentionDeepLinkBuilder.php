<?php

declare(strict_types=1);

namespace oat\tao\model\TaskOrchestrator;

use InvalidArgumentException;
use tao_helpers_Uri;

/**
 * Builds Backoffice deep links for comment-mention emails (FR8).
 *
 * Example (item):
 * https://backoffice.ngs.test/tao/Main/index?structure=items&ext=taoItems&section=manage_items&uri=https%3A%2F%2F...
 */
final class CommentMentionDeepLinkBuilder
{
    public const OBJECT_TYPE_ITEM = 'item';
    public const OBJECT_TYPE_TEST = 'test';
    public const OBJECT_TYPE_ASSET = 'asset';

    private const ROUTES = [
        self::OBJECT_TYPE_ITEM => [
            'structure' => 'items',
            'ext' => 'taoItems',
            'section' => 'manage_items',
        ],
        self::OBJECT_TYPE_TEST => [
            'structure' => 'tests',
            'ext' => 'taoTests',
            'section' => 'manage_tests',
        ],
        self::OBJECT_TYPE_ASSET => [
            'structure' => 'taoMediaManager',
            'ext' => 'taoMediaManager',
            'section' => 'media_manager',
        ],
    ];

    private string $backofficeBaseUrl;

    public function __construct(?string $backofficeBaseUrl = null)
    {
        $this->backofficeBaseUrl = rtrim(
            $backofficeBaseUrl ?? tao_helpers_Uri::getRootUrl(),
            '/'
        );
    }

    /**
     * @param self::OBJECT_TYPE_* $objectType
     */
    public function build(string $objectType, string $resourceUri): string
    {
        $objectType = strtolower(trim($objectType));
        if (!isset(self::ROUTES[$objectType])) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported comment-mention object type "%s". Expected: item, test, asset.',
                $objectType
            ));
        }

        $resourceUri = trim($resourceUri);
        if ($resourceUri === '') {
            throw new InvalidArgumentException('Resource URI is required to build comment-mention deep link');
        }

        $query = http_build_query(
            array_merge(self::ROUTES[$objectType], ['uri' => $resourceUri]),
            '',
            '&',
            PHP_QUERY_RFC3986
        );

        return sprintf('%s/tao/Main/index?%s', $this->backofficeBaseUrl, $query);
    }
}
