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

use oat\tao\model\StatisticalMetadata\Contract\Header;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataHeadersExtractor;
use oat\tao\model\StatisticalMetadata\Import\Exception\HeaderValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\AggregatedValidationException;

class HeaderValidator
{
    /** @var MetadataHeadersExtractor */
    private $metadataHeadersExtractor;

    public function __construct(MetadataHeadersExtractor $metadataHeadersExtractor)
    {
        $this->metadataHeadersExtractor = $metadataHeadersExtractor;
    }

    public function validateRequiredHeaders(array $header): void
    {
        $exceptions = [];

        foreach ([Header::ITEM_ID, Header::TEST_ID] as $headerName) {
            if (!in_array($headerName, $header, true)) {
                $exceptions[] = new HeaderValidationException(
                    'Required column "%s" is missing',
                    [$headerName]
                );
            }
        }

        if (empty($this->metadataHeadersExtractor->extract($header))) {
            $exceptions[] = new HeaderValidationException(
                'At least one "%s" column must be specified',
                [Header::METADATA_PREFIX . '{property_alias}']
            );
        }

        if (!empty($exceptions)) {
            throw new AggregatedValidationException($exceptions, []);
        }
    }
}
