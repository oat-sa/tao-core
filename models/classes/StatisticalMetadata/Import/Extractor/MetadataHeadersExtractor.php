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

class MetadataHeadersExtractor
{
    /** @var array */
    private $cache = [];

    public function extract(array $header): array
    {
        $hash = $this->getHash($header);

        if (!isset($this->cache[$hash])) {
            $this->cache[$hash] = preg_grep(
                sprintf('/^%s/', Header::METADATA_PREFIX),
                $header
            );
        }

        return $this->cache[$hash];
    }

    private function getHash(array $header): string
    {
        sort($header);

        return hash('sha256', json_encode($header));
    }
}
