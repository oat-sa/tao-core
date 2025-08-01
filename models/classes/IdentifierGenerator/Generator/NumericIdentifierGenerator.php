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
 * Copyright (c) 2024-2025 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\IdentifierGenerator\Generator;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\model\OntologyRdf;
use oat\search\helper\SupportedOperatorHelper;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\IdentifierGenerator\Repository\UniqueIdRepository;
use oat\tao\model\TaoOntology;

class NumericIdentifierGenerator implements IdentifierGeneratorInterface
{
    private const MAX_RETRIES = 200;
    private const FEATURE_FLAG_CHECK_STATEMENTS = 'FEATURE_FLAG_CHECK_STATEMENTS';
    
    private UniqueIdRepository $uniqueIdRepository;
    private ComplexSearchService $complexSearch;
    private FeatureFlagCheckerInterface $featureFlagChecker;
    
    public function __construct(
        UniqueIdRepository $uniqueIdRepository,
        ComplexSearchService $complexSearch,
        FeatureFlagCheckerInterface $featureFlagChecker
    ) {
        $this->uniqueIdRepository = $uniqueIdRepository;
        $this->complexSearch = $complexSearch;
        $this->featureFlagChecker = $featureFlagChecker;
    }
    
    /**
     * Generate a unique 9-digit numeric identifier that's guaranteed to be collision-free
     */
    public function generate(array $options = []): string
    {
        if (!isset($options['resource']) || !method_exists($options['resource'], 'getRootId')) {
            throw new Exception('Missing required "resource" option with getRootId() method');
        }

        $resourceType = $options['resource']->getRootId();
        $resourceId = $options['resource']->getUri() ?? null;

        $lastId = $this->uniqueIdRepository->getLastIdForResourceType($resourceType);
        $candidateId = $lastId ? $lastId + 1 : $this->uniqueIdRepository->getStartId();
        
        $retries = 0;
        while ($retries < self::MAX_RETRIES) {
            $candidateIdStr = str_pad((string)$candidateId, 9, '0', STR_PAD_LEFT);
            
            $shouldCheckStatements = $this->featureFlagChecker->isEnabled(self::FEATURE_FLAG_CHECK_STATEMENTS);
            
            if (!$shouldCheckStatements || !$this->checkIdExistsInStatements($resourceType, $candidateIdStr)) {
                try {
                    $this->uniqueIdRepository->insertUniqueId($resourceType, $candidateIdStr, $resourceId);
                    return $candidateIdStr;
                } catch (UniqueConstraintViolationException $e) {
                    $candidateId++;
                    $retries++;
                    continue;
                }
            }
            
            $candidateId++;
            $retries++;
        }
        
        throw new Exception("Failed to generate unique ID for resource type '{$resourceType}' after " . self::MAX_RETRIES . " retries");
    }

    private function checkIdExistsInStatements(string $resourceType, string $uniqueId): bool
    {
        $queryBuilder = $this->complexSearch->query();
        
        $query = $this->complexSearch->searchType($queryBuilder, $resourceType, true);
        
        $query->addCriterion(
            TaoOntology::PROPERTY_UNIQUE_IDENTIFIER,
            SupportedOperatorHelper::EQUAL,
            $uniqueId
        );
        
        $queryBuilder->setCriteria($query);
        
        $results = $this->complexSearch->getGateway()->search($queryBuilder);
        
        $resultsArray = iterator_to_array($results);
        
        return count($resultsArray) > 0;
    }
}
