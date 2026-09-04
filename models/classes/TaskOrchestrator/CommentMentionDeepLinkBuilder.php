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
use oat\tao\model\menu\MenuService;
use oat\tao\model\menu\Perspective;
use tao_helpers_Uri;

/**
 * Builds Backoffice deep links for comment-mention emails (FR8).
 *
 * Resolves structure/ext/section from MenuService by matching a tree rootNode
 * to the given ontology root class URI. Callers own the wire type → class URI map.
 *
 * Example (item class URI):
 * https://backoffice.ngs.test/tao/Main/index?structure=items&ext=taoItems&section=manage_items&uri=https%3A%2F%2F...
 */
final class CommentMentionDeepLinkBuilder
{
    private string $backofficeBaseUrl;

    /** @var Perspective[] */
    private array $perspectives;

    /**
     * @param Perspective[]|null $perspectives Defaults to MenuService::getAllPerspectives()
     */
    public function __construct(?string $backofficeBaseUrl = null, ?array $perspectives = null)
    {
        $this->backofficeBaseUrl = rtrim(
            $backofficeBaseUrl ?? tao_helpers_Uri::getRootUrl(),
            '/'
        );
        $this->perspectives = $perspectives ?? MenuService::getAllPerspectives();
    }

    /**
     * @param string $rootClassUri Ontology root class URI of the resource tree (e.g. Item class)
     */
    public function build(string $rootClassUri, string $resourceUri): string
    {
        $rootClassUri = trim($rootClassUri);
        if ($rootClassUri === '') {
            throw new InvalidArgumentException(
                'Root class URI is required to build comment-mention deep link'
            );
        }

        $resourceUri = trim($resourceUri);
        if ($resourceUri === '') {
            throw new InvalidArgumentException(
                'Resource URI is required to build comment-mention deep link'
            );
        }

        $query = http_build_query(
            array_merge($this->resolveRoute($rootClassUri), ['uri' => $resourceUri]),
            '',
            '&',
            PHP_QUERY_RFC3986
        );

        return sprintf('%s/tao/Main/index?%s', $this->backofficeBaseUrl, $query);
    }

    /**
     * @return array{structure: string, ext: string, section: string}
     */
    private function resolveRoute(string $rootClassUri): array
    {
        foreach ($this->perspectives as $perspective) {
            foreach ($perspective->getChildren() as $section) {
                foreach ($section->getTrees() as $tree) {
                    if ($tree->get('rootNode') === $rootClassUri) {
                        return [
                            'structure' => $perspective->getId(),
                            'ext' => $perspective->getExtension(),
                            'section' => $section->getId(),
                        ];
                    }
                }
            }
        }

        throw new InvalidArgumentException(sprintf(
            'No backoffice structure/section found for root class "%s".',
            $rootClassUri
        ));
    }
}
