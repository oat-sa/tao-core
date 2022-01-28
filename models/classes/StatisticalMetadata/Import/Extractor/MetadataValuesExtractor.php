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

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\tao\model\StatisticalMetadata\Import\Exception\AbstractValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\AggregatedValidationException;
use oat\tao\model\StatisticalMetadata\Import\Validator\ResourceMetadataRelationValidator;

class MetadataValuesExtractor
{
    /** @var MetadataHeadersExtractor */
    private $metadataHeadersExtractor;

    /** @var ResourceMetadataRelationValidator */
    private $resourceMetadataRelationValidator;

    public function __construct(
        MetadataHeadersExtractor $metadataHeadersExtractor,
        ResourceMetadataRelationValidator $resourceMetadataRelationValidator
    ) {
        $this->metadataHeadersExtractor = $metadataHeadersExtractor;
        $this->resourceMetadataRelationValidator = $resourceMetadataRelationValidator;
    }

    /**
     * @param core_kernel_classes_Property[] $metadataProperties
     */
    public function extract(array $record, core_kernel_classes_Resource $resource, array $metadataProperties): array
    {
        $values = [];
        $exceptions = [];
        $metadataHeaders = $this->metadataHeadersExtractor->extract(array_keys($record));

        foreach ($metadataHeaders as $metadataHeader) {
            $value = trim($record[$metadataHeader]);

            if ($value !== '') {
                try {
                    $metadataProperty = $metadataProperties[$metadataHeader];
                    $this->resourceMetadataRelationValidator->validate($resource, $metadataProperty);
                    $values[$metadataProperty->getUri()] = $value;
                } catch (AbstractValidationException $exception) {
                    $exceptions[] = $exception->setColumn($metadataHeader);
                }
            }
        }

        if (!empty($exceptions)) {
            throw new AggregatedValidationException([], $exceptions);
        }

        return $values;
    }
}
