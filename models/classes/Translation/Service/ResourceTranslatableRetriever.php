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

use oat\generis\model\data\Ontology;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Query\ResourceTranslatableQuery;
use oat\tao\model\Translation\Repository\ResourceTranslatableRepository;
use Psr\Http\Message\ServerRequestInterface;

class ResourceTranslatableRetriever
{
    private Ontology $ontology;
    private ResourceTranslatableRepository $resourceTranslatableRepository;

    public function __construct(
        Ontology $ontology,
        ResourceTranslatableRepository $resourceTranslationRepository
    ) {
        $this->resourceTranslatableRepository = $resourceTranslationRepository;
        $this->ontology = $ontology;
    }

    public function getByRequest(ServerRequestInterface $request): ResourceCollection
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'] ?? null;

        if (empty($id)) {
            throw new ResourceTranslationException('Resource id is required');
        }

        $resource = $this->ontology->getResource($id);

        $parentClassIds = $resource->getParentClassesIds();
        $resourceType = array_pop($parentClassIds);

        if (empty($resourceType)) {
            throw new ResourceTranslationException(sprintf('Resource %s must have a resource type', $id));
        }

        $uniqueId = $resource->getUniquePropertyValue(
            $this->ontology->getProperty(TaoOntology::PROPERTY_UNIQUE_IDENTIFIER)
        );

        if (empty($uniqueId)) {
            throw new ResourceTranslationException(sprintf('Resource %s must have a unique identifier', $id));
        }

        return $this->resourceTranslatableRepository->find(
            new ResourceTranslatableQuery(
                $resourceType,
                [
                    (string)$uniqueId
                ]
            )
        );
    }
}
