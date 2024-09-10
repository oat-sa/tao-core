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
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Translation\Service;

use InvalidArgumentException;
use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\tao\model\Translation\Query\ResourceTranslationQuery;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;
use Psr\Http\Message\ServerRequestInterface;

class ResourceTranslationRetriever
{
    private ResourceTranslationRepository $resourceTranslationRepository;

    public function __construct(ResourceTranslationRepository $resourceTranslationRepository)
    {
        $this->resourceTranslationRepository = $resourceTranslationRepository;
    }

    public function getByRequest(ServerRequestInterface $request): ResourceCollection
    {
        $queryParams = $request->getQueryParams();
        $resourceType = $queryParams['resourceType'] ?? null;
        $resourceUri = $queryParams['resourceUri'] ?? null;
        $languageUri = $queryParams['languageUri'] ?? null;

        if (empty($resourceType)) {
            throw new InvalidArgumentException('Param resourceType is required');
        }

        if (empty($resourceUri)) {
            throw new InvalidArgumentException('Param resourceUri is required');
        }

        return $this->resourceTranslationRepository->find(
            new ResourceTranslationQuery(
                $resourceType,
                $resourceUri,
                $languageUri
            )
        );
    }
}
