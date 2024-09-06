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
use oat\tao\model\Translation\Entity\ResourceTranslation;
use oat\tao\model\Translation\Entity\ResourceTranslationCollection;
use oat\tao\model\Translation\Entity\ResourceTranslationException;
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

    public function find(ResourceTranslationQuery $query): ResourceTranslationCollection
    {
        $originResourceId = $query->getOriginResourceId();
        $originResource = $this->ontology->getResource($originResourceId);
        
        if (!$originResource->exists()) {
            throw new Exception(sprintf('Translation Origin Resource %s does not exist', $originResourceId));
        }

        /** @var core_kernel_classes_Resource $translationType */
        $translationType = $originResource->getUniquePropertyValue($this->ontology->getProperty(ResourceTranslation::PROPERTY_TRANSLATION_TYPE));

        if ($translationType->getUri() !== ResourceTranslation::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL) {
            throw new ResourceTranslationException(
                sprintf('Translation Origin Resource %s it not the original', $originResourceId)
            );
        }

        $output = new ResourceTranslationCollection($originResourceId);
        $uniqueId = $originResource->getUniquePropertyValue(
            $this->ontology->getProperty(ResourceTranslation::PROPERTY_UNIQUE_IDENTIFIER)
        );

        $queryBuilder = $this->complexSearch->query();
        $searchQuery = $this->complexSearch->searchType(
            $queryBuilder,
            'http://www.tao.lu/Ontologies/TAO.rdf#AssessmentContentObject',
            true
        );
        $searchQuery->addCriterion(
            ResourceTranslation::PROPERTY_TRANSLATION_TYPE,
            SupportedOperatorHelper::EQUAL,
            ResourceTranslation::PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION
        );
        $searchQuery->addCriterion(
            ResourceTranslation::PROPERTY_UNIQUE_IDENTIFIER,
            SupportedOperatorHelper::EQUAL,
            $uniqueId
        );
        
        if ($query->getLanguageUri()) {
            $searchQuery->addCriterion(
                ResourceTranslation::PROPERTY_LANGUAGE,
                SupportedOperatorHelper::EQUAL,
                $query->getLanguageUri()
            );    
        }
        
        $queryBuilder->setCriteria($searchQuery);

        $result = $this->complexSearch->getGateway()->search($queryBuilder);

        /** @var core_kernel_classes_Resource $resource */
        foreach ($result as $translationResource) {
            try {
                $output->addTranslation($this->factory->create($originResource, $translationResource));
            } catch (Throwable $exception) {
                $this->logger->warning(
                    sprintf(
                        'Cannot read translation status for [originResourceId=%s, translationResourceId=%s]: %s - %s',
                        $originResourceId,
                        $resource->getUri(),
                        $exception->getMessage(),
                        $exception->getTraceAsString()
                    )
                );
            }
        }

        return $output;
    }
}
