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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\tao\model\accessControl\filter;

use common_Utils;
use tao_helpers_Uri;

class JsonParameterFilter implements ParameterFilterInterface
{
    public function filter(array $requestParameters, array $filterNames): array
    {
        if (empty($filterNames)) {
            return [];
        }

        $groupedUris = [];

        $json = (array)json_decode(
            (string)(array_keys($requestParameters)[0] ?? ''),
            true
        );

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        foreach ($json as $key => $value) {
            if (in_array($key, $filterNames, true)) {
                $encodedUri = $this->getDecodedUri((string)$value);

                if (common_Utils::isUri($encodedUri)) {
                    $groupedUris[$key][] = $encodedUri;
                }
            }
        }

        return $groupedUris;
    }

    private function getDecodedUri(string $uri): string
    {
        // This is necessary cause JSON request might have converted '.' to '_'
        $decodedUri = str_replace('_', '.', $uri);

        $decodedUri = tao_helpers_Uri::isUriEncoded($decodedUri)
            ? tao_helpers_Uri::decode($decodedUri)
            : $decodedUri;

        return $decodedUri;
    }
}
