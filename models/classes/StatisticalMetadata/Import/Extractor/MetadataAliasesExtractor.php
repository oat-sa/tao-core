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

use oat\tao\model\StatisticalMetadata\Contract\Header;

class MetadataAliasesExtractor
{
    /** @var MetadataHeadersExtractor */
    private $metadataHeadersExtractor;

    /** @var array */
    private $cache = [];

    public function __construct(MetadataHeadersExtractor $metadataHeadersExtractor)
    {
        $this->metadataHeadersExtractor = $metadataHeadersExtractor;
    }

    public function extract(array $header): array
    {
        $hash = $this->getHash($header);

        if (!isset($this->cache[$hash])) {
            $this->cache[$hash] = $this->extractAliases($this->metadataHeadersExtractor->extract($header));
        }

        return $this->cache[$hash];
    }

    private function getHash(array $header): string
    {
        sort($header);

        return hash('sha256', json_encode($header));
    }

    private function extractAliases(array $metadata): array
    {
        $aliases = [];

        foreach ($metadata as $metadatum) {
            $aliases[] = str_replace(Header::METADATA_PREFIX, '', $metadatum);
        }

        return $aliases;
    }
}
