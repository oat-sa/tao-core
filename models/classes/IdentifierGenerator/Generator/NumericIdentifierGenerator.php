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
use InvalidArgumentException;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\helper\SupportedOperatorHelper;
use oat\tao\model\IdentifierGenerator\Repository\UniqueIdRepository;
use oat\tao\model\TaoOntology;
use core_kernel_classes_Resource;

class NumericIdentifierGenerator implements IdentifierGeneratorInterface
{
    public function __construct(
        private readonly UniqueIdRepository $uniqueIdRepository,
        private readonly ComplexSearchService $complexSearch,
        private ?int $maxRetries,
        private ?bool $shouldCheckStatements,
        private ?int $startId,
    ) {
        $this->maxRetries = $this->maxRetries ?? 100;
        $this->shouldCheckStatements = $this->shouldCheckStatements ?? true;
        $this->startId = $this->startId ?? 1;
    }

    /**
     * Generate a unique 9-digit numeric identifier that's guaranteed to be collision-free
     */
    public function generate(array $options = []): string
    {
        if (!isset($options['resource']) || !($options['resource'] instanceof core_kernel_classes_Resource)) {
            throw new InvalidArgumentException(
                'Missing required "resource" option that must be an instance of core_kernel_classes_Resource'
            );
        }

        $resourceType = $options['resource']->getRootId();
        $resourceId = $options['resource']->getUri();

        $existingRecord = $this->uniqueIdRepository->findOneBy([
            UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
        ]);

        if ($existingRecord) {
            return (string)$existingRecord[UniqueIdRepository::FIELD_UNIQUE_ID];
        }

        $lastIdRecord = $this->uniqueIdRepository->findOneBy(
            [UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType],
            [UniqueIdRepository::FIELD_UNIQUE_ID => 'DESC']
        );
        $candidateId = $lastIdRecord && $lastIdRecord[UniqueIdRepository::FIELD_UNIQUE_ID]
            ? ((int)$lastIdRecord[UniqueIdRepository::FIELD_UNIQUE_ID] + 1)
            : $this->startId;

        $retries = 0;
        while ($retries < $this->maxRetries) {
            $candidateIdStr = str_pad((string)$candidateId, 9, '0', STR_PAD_LEFT);

            if ($this->shouldCheckStatements && $this->checkIdExistsInStatements($resourceType, $candidateIdStr)) {
                $candidateId++;
                continue;
            }

            try {
                $this->uniqueIdRepository->save([
                    UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                    UniqueIdRepository::FIELD_UNIQUE_ID => $candidateIdStr,
                    UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
                ]);
                return $candidateIdStr;
            } catch (UniqueConstraintViolationException $e) {
                $retries++;
                $candidateId++;

                if ($retries >= $this->maxRetries) {
                    throw new Exception(
                        sprintf(
                            'Max retries reached when trying to generate unique ID for resource type: %s',
                            $resourceType
                        )
                    );
                }
            }
        }

        throw new Exception(
            sprintf(
                "Failed to generate unique ID for resource type '%s' after %s retries",
                $resourceType,
                $this->maxRetries
            )
        );
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
