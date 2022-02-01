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
use tao_helpers_form_elements_Htmlarea;
use tao_helpers_form_elements_Textarea;
use tao_helpers_form_elements_Textbox;

class MetadataPropertiesValidator
{
    private const ALLOWED_WIDGETS = [
        tao_helpers_form_elements_Textbox::WIDGET_ID,
        tao_helpers_form_elements_Textarea::WIDGET_ID,
        tao_helpers_form_elements_Htmlarea::WIDGET_ID
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

        $exceptions = [];

        foreach ($aliases as $alias) {
            if (!in_array($alias, $existingAliases, true)) {
                $exceptions[] = $this->buildHeaderException($alias, 'Property referenced by "%s" not found');
            }
        }

        $this->throwErrorOrAggregatedException(
            $exceptions,
            $aliases,
            'None of properties referenced by "%s" columns were found'
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
        $exceptions = [];

        foreach ($metadataProperties as $metadataProperty) {
            $alias = $metadataProperty->getAlias();

            if (!in_array($alias, $existingAliases, true)) {
                $existingAliases[] = $alias;

                continue;
            }

            if (!array_key_exists($alias, $exceptions)) {
                $exceptions[$alias] = $this->buildHeaderException(
                    $alias ?? $metadataProperty->getLabel(),
                    'Property referenced by "%s" is not unique'
                );
            }
        }

        $this->throwErrorOrAggregatedException(
            $exceptions,
            $existingAliases,
            'None of properties referenced by "%s" columns are unique'
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
        $exceptions = [];

        foreach ($metadataProperties as $metadataProperty) {
            if (!in_array($metadataProperty->getWidget()->getUri(), self::ALLOWED_WIDGETS, true)) {
                $exceptions[] = $this->buildHeaderException(
                    $metadataProperty->getAlias() ?? $metadataProperty->getLabel(),
                    'Property referenced by "%s" has invalid input type - only TEXT is allowed'
                );
            }

            if (!$metadataProperty->isStatistical()) {
                $exceptions[] = $this->buildHeaderException(
                    $metadataProperty->getAlias() ?? $metadataProperty->getLabel(),
                    'Property referenced by "%s" must be "statistical"'
                );
            }
        }

        $this->throwErrorOrAggregatedException(
            $exceptions,
            $metadataProperties,
            'None of properties referenced by "%s" columns have a valid input type - only TEXT is allowed'
        );
    }

    private function buildHeaderException(string $alias, string $message): HeaderValidationException
    {
        $column = Header::METADATA_PREFIX . $alias;

        $exception = new HeaderValidationException($message, [$column]);
        $exception->setColumn($column);

        return $exception;
    }

    /**
     * @param HeaderValidationException[] $exceptions
     */
    private function throwErrorOrAggregatedException(array $exceptions, array $valuesToCheck, string $message): void
    {
        if (empty($exceptions)) {
            return;
        }

        // @TODO Check with Andrei why this is necessary.
        // It is not showing error details in case all properties are wrong.
        if (count($valuesToCheck) === count($exceptions)) {
            throw new HeaderValidationException(
                $message,
                [Header::METADATA_PREFIX . '{property_alias}']
            );
        }

        throw new AggregatedValidationException($exceptions, []);
    }
}
