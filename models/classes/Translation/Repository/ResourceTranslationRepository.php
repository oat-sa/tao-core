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
use Exception;
use oat\generis\model\data\Ontology;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\helper\SupportedOperatorHelper;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\tao\model\Translation\Entity\ResourceTranslatable;
use oat\tao\model\Translation\Factory\ResourceTranslationFactory;
use oat\tao\model\Translation\Query\ResourceTranslatableQuery;
use oat\tao\model\Translation\Query\ResourceTranslationQuery;
use Psr\Log\LoggerInterface;
use Throwable;

class ResourceTranslationRepository
{
    private Ontology $ontology;
    private ComplexSearchService $complexSearch;
    private LoggerInterface $logger;
    private ResourceTranslationFactory $factory;
    private ResourceTranslatableRepository $resourceTranslatableRepository;

    public function __construct(
        Ontology $ontology,
        ComplexSearchService $complexSearch,
        ResourceTranslatableRepository $resourceTranslatableRepository,
        ResourceTranslationFactory $resourceTranslationFactory,
        LoggerInterface $logger
    ) {
        $this->ontology = $ontology;
        $this->complexSearch = $complexSearch;
        $this->resourceTranslatableRepository = $resourceTranslatableRepository;
        $this->factory = $resourceTranslationFactory;
        $this->logger = $logger;
    }

    public function find(ResourceTranslationQuery $query): ResourceCollection
    {
        $uniqueIds = $query->getUniqueIds();
        $resources = $this->resourceTranslatableRepository->find(
            new ResourceTranslatableQuery(
                $query->getResourceType(),
                $uniqueIds
            )
        );

        if ($resources->count() === 0) {
            throw new Exception(
                sprintf(
                    'Translation Origin Resource [%s] does not exist',
                    implode(',', $uniqueIds)
                )
            );
        }

        $originResources = [];

        /** @var ResourceTranslatable $resource */
        foreach ($resources as $resource) {
            $originResources[$resource->getUniqueId()] = $resource;
        }

        $output = [];

        $queryBuilder = $this->complexSearch->query();
        $searchQuery = $this->complexSearch->searchType(
            $queryBuilder,
            'http://www.tao.lu/Ontologies/TAO.rdf#AssessmentContentObject',
            true
        );
        $searchQuery->addCriterion(
            TaoOntology::PROPERTY_TRANSLATION_TYPE,
            SupportedOperatorHelper::EQUAL,
            TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION
        );
        $searchQuery->addCriterion(
            TaoOntology::PROPERTY_UNIQUE_IDENTIFIER,
            SupportedOperatorHelper::IN,
            $uniqueIds
        );

        if ($query->getLanguageUri()) {
            $searchQuery->addCriterion(
                TaoOntology::PROPERTY_LANGUAGE,
                SupportedOperatorHelper::EQUAL,
                $query->getLanguageUri()
            );
        }

        $queryBuilder->setCriteria($searchQuery);

        $result = $this->complexSearch->getGateway()->search($queryBuilder);
        $resourceTypeClass = $this->ontology->getClass($query->getResourceType());

        /** @var core_kernel_classes_Resource $translationResource */
        foreach ($result as $translationResource) {
            try {
                if (!$translationResource->isInstanceOf($resourceTypeClass)) {
                    continue;
                }

                $uniqueId = (string) $translationResource->getOnePropertyValue(
                    $this->ontology->getProperty(TaoOntology::PROPERTY_UNIQUE_IDENTIFIER)
                );

                $output[] = $this->factory->create($originResources[$uniqueId], $translationResource);
            } catch (Throwable $exception) {
                $this->logger->warning(
                    sprintf(
                        'Cannot read translation status for [uniqueIds=%s, translationResourceId=%s]: %s - %s',
                        implode(',', $uniqueIds),
                        $translationResource->getUri(),
                        $exception->getMessage(),
                        $exception->getTraceAsString()
                    )
                );
            }
        }

        return new ResourceCollection(...$output);
    }
}
