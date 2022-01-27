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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\StatisticalMetadata\Import\Extractor;

use RuntimeException;
use common_exception_Error;
use core_kernel_classes_Property;
use oat\search\base\exception\SearchGateWayExeption;
use oat\tao\model\StatisticalMetadata\Contract\Header;
use oat\generis\model\resource\Context\PropertyRepositoryContext;
use oat\generis\model\resource\Contract\ResourceRepositoryInterface;
use oat\tao\model\StatisticalMetadata\Import\Exception\ErrorValidationException;
use oat\tao\model\StatisticalMetadata\Import\Validator\MetadataPropertiesValidator;
use oat\tao\model\StatisticalMetadata\Import\Exception\AggregatedValidationException;

class MetadataPropertiesExtractor
{
    /** @var MetadataAliasesExtractor */
    private $metadataAliasesExtractor;

    /** @var ResourceRepositoryInterface */
    private $propertyRepository;

    /** @var MetadataPropertiesValidator */
    private $metadataPropertiesValidator;

    public function __construct(
        MetadataAliasesExtractor $metadataAliasesExtractor,
        ResourceRepositoryInterface $propertyRepository,
        MetadataPropertiesValidator $metadataPropertiesValidator
    ) {
        $this->metadataAliasesExtractor = $metadataAliasesExtractor;
        $this->propertyRepository = $propertyRepository;
        $this->metadataPropertiesValidator = $metadataPropertiesValidator;
    }

    /**
     * @throws RuntimeException
     * @throws AggregatedValidationException
     * @throws ErrorValidationException
     *
     * @return core_kernel_classes_Property[]
     */
    public function extract(array $header): array
    {
        $aliases = $this->metadataAliasesExtractor->extract($header);
        [$metadataProperties, $uniqueMetadataProperties] = $this->findByAliases($aliases);

        $this->metadataPropertiesValidator->validateMetadataExistence($aliases, $metadataProperties);
        $this->metadataPropertiesValidator->validateMetadataUniqueness($metadataProperties);
        $this->metadataPropertiesValidator->validateMetadataTypes($metadataProperties);

        return $uniqueMetadataProperties;
    }

    /**
     * @return core_kernel_classes_Property[][]
     */
    private function findByAliases(array $aliases): array
    {
        try {
            $metadataResources = $this->propertyRepository->findBy(
                new PropertyRepositoryContext(
                    [
                        PropertyRepositoryContext::PARAM_ALIASES => $aliases,
                    ]
                )
            );
        } catch (common_exception_Error | SearchGateWayExeption $exception) {
            throw new RuntimeException(
                'Unable to find aliased properties in the database due to an unexpected problem.'
            );
        }

        $metadataProperties = [];
        $uniqueMetadataProperties = [];

        foreach ($metadataResources as $resource) {
            $property = $resource->getProperty($resource->getUri());

            $metadataProperties[] = $property;
            $uniqueMetadataProperties[Header::METADATA_PREFIX . $property->getAlias()] = $property;
        }

        return [$metadataProperties, $uniqueMetadataProperties];
    }
}
