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

namespace oat\tao\model\Translation\Repository;

use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\helper\SupportedOperatorHelper;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\tao\model\Translation\Factory\ResourceTranslatableFactory;
use oat\tao\model\Translation\Query\ResourceTranslatableQuery;

class ResourceTranslatableRepository
{
    private Ontology $ontology;
    private ComplexSearchService $complexSearch;
    private ResourceTranslatableFactory $factory;

    public function __construct(
        Ontology $ontology,
        ComplexSearchService $complexSearch,
        ResourceTranslatableFactory $factory
    ) {
        $this->complexSearch = $complexSearch;
        $this->factory = $factory;
        $this->ontology = $ontology;
    }

    public function find(ResourceTranslatableQuery $query): ResourceCollection
    {
        $queryBuilder = $this->complexSearch->query();
        $searchQuery = $this->complexSearch->searchType(
            $queryBuilder,
            'http://www.tao.lu/Ontologies/TAO.rdf#AssessmentContentObject',
            true
        );
        $searchQuery->addCriterion(
            TaoOntology::PROPERTY_TRANSLATION_TYPE,
            SupportedOperatorHelper::EQUAL,
            TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL
        );

        if (!empty($query->getUniqueIds())) {
            $searchQuery->addCriterion(
                TaoOntology::PROPERTY_UNIQUE_IDENTIFIER,
                SupportedOperatorHelper::IN,
                $query->getUniqueIds()
            );
        }

        $queryBuilder->setCriteria($searchQuery);

        $result = $this->complexSearch->getGateway()->search($queryBuilder);
        $output = [];

        $resourceTypeClass = $this->ontology->getClass($query->getResourceType());

        /** @var core_kernel_classes_Resource $resource */
        foreach ($result as $resource) {
            if ($resource->isInstanceOf($resourceTypeClass)) {
                $output[] = $this->factory->create($resource);
            }
        }

        return new ResourceCollection(...$output);
    }
}
