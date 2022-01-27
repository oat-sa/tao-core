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
use oat\tao\model\StatisticalMetadata\Import\Exception\ErrorValidationException;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataAliasesExtractor;

class HeaderValidator
{
    /** @var MetadataAliasesExtractor */
    private $metadataAliasesExtractor;

    public function __construct(MetadataAliasesExtractor $metadataAliasesExtractor)
    {
        $this->metadataAliasesExtractor = $metadataAliasesExtractor;
    }

    public function validateRequiredHeaders(array $header): void
    {
        $missedColumns = [];

        foreach ([Header::ITEM_ID, Header::TEST_ID] as $headerName) {
            if (!in_array($headerName, $header, true)) {
                $missedColumns[] = $headerName;
            }
        }

        if (empty($this->metadataAliasesExtractor->extract($header))) {
            $missedColumns[] = Header::METADATA_PREFIX . '{property_alias}';
        }

        if (!empty($missedColumns)) {
            throw new ErrorValidationException(
                'Required columns are missing: "%s"',
                [
                    implode('", "', $missedColumns),
                ]
            );
        }
    }
}
