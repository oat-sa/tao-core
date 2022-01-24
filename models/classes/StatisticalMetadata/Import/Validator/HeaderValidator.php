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

use RuntimeException;
use InvalidArgumentException;
use oat\tao\model\StatisticalMetadata\Contract\Header;
use oat\tao\model\StatisticalMetadata\Import\Mapper\StatisticalMetadataMapper;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataHeadersExtractor;

class HeaderValidator
{
    private const ALLOWED_WIDGETS = [
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea',
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea',
    ];

    public function __construct(MetadataHeadersExtractor $metadataHeadersExtractor)
    {
        $this->metadataHeadersExtractor = $metadataHeadersExtractor;
    }

    /** @var MetadataHeadersExtractor */
    private $metadataHeadersExtractor;

    public function validateRequiredHeaders(array $header): void
    {
        $missedColumns = [];

        foreach ([Header::ITEM_ID, Header::TEST_ID] as $headerName) {
            if (!in_array($headerName, $header, true)) {
                $missedColumns[] = $headerName;
            }
        }

        if (empty($this->metadataHeadersExtractor->extract($header))) {
            $missedColumns[] = Header::METADATA_PREFIX . '{property_alias}';
        }

        if (!empty($missedColumns)) {
            throw new RuntimeException(
                sprintf(
                    'required %s missing ("%s")',
                    $this->getCounteredString($missedColumns, 'columns are', 'column is'),
                    implode('", "', $missedColumns)
                )
            );
        }
    }

    public function validateMetadataHeaders(array $header, array $metadataMap): void
    {
        $invalid = array_diff(
            $this->metadataHeadersExtractor->extract($header),
            array_keys($metadataMap)
        );

        if (!empty($invalid)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s not found ("%s")',
                    $this->getCounteredString($invalid, 'properties', 'property'),
                    implode('", "',  $invalid)
                )
            );
        }
    }

    public function validateMetadataUniqueness(array $metadataMap): void
    {
        $invalid = [];

        foreach ($metadataMap as $metadataName => $metadata) {
            if (count($metadata) > 1) {
                $invalid[] = $metadataName;
            }
        }

        if (!empty($invalid)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s used on more than one property ("%s")',
                    $this->getCounteredString($invalid, 'aliases are', 'alias is'),
                    implode('", "', $invalid)
                )
            );
        }
    }

    public function validateMetadataTypes(array $metadataMap): void
    {
        $invalid = [];

        foreach ($metadataMap as $metadataName => $metadata) {
            $isAllowed = in_array(
                $metadata[0][StatisticalMetadataMapper::KEY_WIDGET],
                self::ALLOWED_WIDGETS,
                true
            );

            if (!$isAllowed) {
                $invalid[] = $metadataName;
            }
        }

        if (!empty($invalid)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s of the wrong input type - only TEXT is allowed ("%s")',
                    $this->getCounteredString($invalid, 'properties are', 'property is'),
                    implode('", "', $invalid)
                )
            );
        }
    }

    private function getCounteredString(array $values, string $multiple, string $single): string
    {
        return count($values) > 1 ? $multiple : $single;
    }
}
