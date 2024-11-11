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
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Factory\ResourceTranslationFactory;
use oat\tao\model\Translation\Query\ResourceTranslationQuery;
use Psr\Log\LoggerInterface;
use Throwable;

class ResourceTranslationRepository
{
    private Ontology $ontology;
    private ComplexSearchService $complexSearch;
    private LoggerInterface $logger;
    private ResourceTranslationFactory $factory;

    public function __construct(
        Ontology $ontology,
        ComplexSearchService $complexSearch,
        ResourceTranslationFactory $resourceTranslationFactory,
        LoggerInterface $logger
    ) {
        $this->ontology = $ontology;
        $this->complexSearch = $complexSearch;
        $this->factory = $resourceTranslationFactory;
        $this->logger = $logger;
    }

    public function find(ResourceTranslationQuery $query): ResourceCollection
    {
        $output = [];
        $resourceUris = $query->getResourceUris();
        $languageUri = $query->getLanguageUri();

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
            TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI,
            SupportedOperatorHelper::IN,
            $resourceUris
        );

        if ($languageUri) {
            $searchQuery->addCriterion(
                TaoOntology::PROPERTY_LANGUAGE,
                SupportedOperatorHelper::EQUAL,
                $languageUri
            );
        }

        $queryBuilder->setCriteria($searchQuery);

        $result = $this->complexSearch->getGateway()->search($queryBuilder);
        $originResources = [];
        $originResourceProperty = $this->ontology->getProperty(TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI);

        /** @var core_kernel_classes_Resource $translationResource */
        foreach ($result as $translationResource) {
            try {
                $originResourceUri = $translationResource->getOnePropertyValue($originResourceProperty)->getUri();
                $originResources[$originResourceUri] ??= $this->ontology->getResource($originResourceUri);

                $output[] = $this->factory->create($originResources[$originResourceUri], $translationResource);
            } catch (Throwable $exception) {
                $this->logger->warning(
                    sprintf(
                        'Cannot read translation status for [ids=%s, translation=%s]: %s - %s',
                        implode(',', $resourceUris),
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
