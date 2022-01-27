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

namespace oat\tao\model\StatisticalMetadata\Import\Validator;

use core_kernel_classes_Property;
use oat\tao\model\StatisticalMetadata\Contract\Header;
use oat\tao\model\StatisticalMetadata\Import\Exception\HeaderValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\AggregatedValidationException;

class MetadataPropertiesValidator
{
    private const ALLOWED_WIDGETS = [
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea',
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea',
    ];

    /**
     * @param core_kernel_classes_Property[] $metadataProperties
     *
     * @throws AggregatedValidationException
     * @throws HeaderValidationException
     */
    public function validateMetadataExistence(array $aliases, array $metadataProperties): void
    {
        $existingAliases = [];

        foreach ($metadataProperties as $metadataProperty) {
            $existingAliases[] = $metadataProperty->getAlias();
        }

        $warnings = [];

        foreach ($aliases as $alias) {
            if (!in_array($alias, $existingAliases, true)) {
                $warnings[] = $this->buildHeaderException(
                    Header::METADATA_PREFIX . $alias,
                    'Property referenced by "%s" not found',
                    [$alias]
                );
            }
        }

        $this->throwErrorOrAggregatedException(
            $warnings,
            $aliases,
            'None of properties referenced by "%s_{property_alias}" columns were found'
        );
    }

    /**
     * @param core_kernel_classes_Property[] $metadataProperties
     *
     * @throws AggregatedValidationException
     * @throws HeaderValidationException
     */
    public function validateMetadataUniqueness(array $metadataProperties): void
    {
        $existingAliases = [];
        $warnings = [];

        foreach ($metadataProperties as $metadataProperty) {
            $alias = $metadataProperty->getAlias();

            if (!in_array($alias, $existingAliases, true)) {
                $existingAliases[] = $alias;

                continue;
            }

            if (!array_key_exists($alias, $warnings)) {
                $warnings[$alias] = $this->buildHeaderException(
                    Header::METADATA_PREFIX . $alias,
                    'Property referenced by "%s" is not unique',
                    [$alias]
                );
            }
        }

        $this->throwErrorOrAggregatedException(
            $warnings,
            $existingAliases,
            'None of properties referenced by "%s_{property_alias}" columns are unique'
        );
    }

    /**
     * @param core_kernel_classes_Property[] $metadataProperties
     *
     * @throws AggregatedValidationException
     * @throws HeaderValidationException
     */
    public function validateMetadataTypes(array $metadataProperties): void
    {
        $warnings = [];

        foreach ($metadataProperties as $metadataProperty) {
            if (!in_array($metadataProperty->getWidget()->getUri(), self::ALLOWED_WIDGETS, true)) {
                $alias = $metadataProperty->getAlias();
                $warnings[] = $this->buildHeaderException(
                    Header::METADATA_PREFIX . $alias,
                    'Property referenced by "%s" has invalid input type - only TEXT is allowed',
                    [$alias]
                );
            }
        }

        $this->throwErrorOrAggregatedException(
            $warnings,
            $metadataProperties,
            'None of properties referenced by "%s_{property_alias}" columns have a valid input type - only TEXT is allowed'
        );
    }

    private function buildHeaderException(
        string $column,
        string $message,
        array $interpolationData
    ): HeaderValidationException {
        $exception = new HeaderValidationException($message, $interpolationData);
        $exception->setColumn($column);

        return $exception;
    }

    /**
     * @param HeaderValidationException[] $headerExceptions
     */
    private function throwErrorOrAggregatedException(
        array $headerExceptions,
        array $valuesToCheck,
        string $message
    ): void {
        if (empty($headerExceptions)) {
            return;
        }

        if (count($valuesToCheck) === count($headerExceptions)) {
            throw new HeaderValidationException($message, [Header::METADATA_PREFIX]);
        }

        throw new AggregatedValidationException($headerExceptions, []);
    }
}
