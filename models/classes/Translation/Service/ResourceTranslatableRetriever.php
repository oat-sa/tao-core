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

use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Query\ResourceTranslatableQuery;
use oat\tao\model\Translation\Repository\ResourceTranslatableRepository;
use Psr\Http\Message\ServerRequestInterface;

class ResourceTranslatableRetriever
{
    private ResourceTranslatableRepository $resourceTranslatableRepository;

    public function __construct(ResourceTranslatableRepository $resourceTranslationRepository)
    {
        $this->resourceTranslatableRepository = $resourceTranslationRepository;
    }

    public function getByRequest(ServerRequestInterface $request): ResourceCollection
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'] ?? null;

        if (empty($id)) {
            throw new ResourceTranslationException('Resource id is required');
        }

        return $this->resourceTranslatableRepository->find(new ResourceTranslatableQuery($id));
    }
}
