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

use core_kernel_classes_Resource;
use Exception;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\helper\SupportedOperatorHelper;
use oat\tao\model\Translation\Entity\ResourceTranslation;
use oat\tao\model\Translation\Entity\ResourceTranslationStatus;
use oat\tao\model\Translation\Query\ResourceTranslationStatusQuery;
use Psr\Http\Message\ServerRequestInterface;

class ResourceTranslationStatusService
{
    private Ontology $ontology;
    private ComplexSearchService $complexSearch;

    public function __construct(Ontology $ontology, ComplexSearchService $complexSearch)
    {
        $this->ontology = $ontology;
        $this->complexSearch = $complexSearch;
    }

    public function getStatus(ResourceTranslationStatusQuery $query): ResourceTranslationStatus
    {
        $originResourceId = $query->getOriginResourceId();
        $output = new ResourceTranslationStatus($originResourceId);
        $resource = $this->ontology->getResource($originResourceId);

        if (!$resource->exists()) {
            throw new Exception(sprintf('Resource %s does not exist', $originResourceId));
        }

        $uniqueId = $resource->getUniquePropertyValue(
            $this->ontology->getProperty(ResourceTranslation::PROPERTY_UNIQUE_IDENTIFIER)
        );

        $queryBuilder = $this->complexSearch->query();
        $query = $this->complexSearch->searchType(
            $queryBuilder,
            'http://www.tao.lu/Ontologies/TAO.rdf#AssessmentContentObject',
            true
        );
        $query->addCriterion(
            ResourceTranslation::PROPERTY_TRANSLATION_TYPE,
            SupportedOperatorHelper::EQUAL,
            ResourceTranslation::PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION
        );
        $query->addCriterion(
            ResourceTranslation::PROPERTY_UNIQUE_IDENTIFIER,
            SupportedOperatorHelper::EQUAL,
            $uniqueId

        );
        $queryBuilder->setCriteria($query);

        $result = $this->complexSearch->getGateway()->search($queryBuilder);

        $valueProperty = $this->ontology->getProperty('http://www.w3.org/1999/02/22-rdf-syntax-ns#value');
        $progressProperty = $this->ontology->getProperty(ResourceTranslation::PROPERTY_TRANSLATION_PROGRESS);
        $languageProperty = $this->ontology->getProperty(ResourceTranslation::PROPERTY_LANGUAGE);

        /** @var core_kernel_classes_Resource $resource */
        foreach ($result as $resource) {
            /** @var core_kernel_classes_Resource $language */
            $language = $resource->getUniquePropertyValue($languageProperty);
            $languageCode = $language->getUniquePropertyValue($valueProperty);

            /** @var core_kernel_classes_Resource $progress */
            $progress = $resource->getUniquePropertyValue($progressProperty);

            $output->addTranslation(
                (string)$languageCode,
                ResourceTranslation::PROGRESS_MAPPING[$progress->getUri()],
                $resource->getUri()
            );
        }

        return $output;
    }

    public function getStatusByRequest(ServerRequestInterface $request): ResourceTranslationStatus
    {
        $uri = $request->getQueryParams()['resourceUri'] ?? ($request->getServerParams()['resourceUri'] ?? null);

        if (empty($uri)) {
            throw new InvalidArgumentException('Param resourceUri is required');
        }

        return $this->getStatus(new ResourceTranslationStatusQuery($uri));
    }
}
