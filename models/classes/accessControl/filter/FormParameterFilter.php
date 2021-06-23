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
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use tao_helpers_Uri;

class FormParameterFilter implements ParameterFilterInterface
{
    public function filter(array $requestParameters, array $filterNames): array
    {
        if (empty($filterNames)) {
            return [];
        }

        $groupedUris = [];

        foreach ($this->flattenArray($requestParameters) as $key => $value) {
            $encodedUri = $this->getEncodedUri($value);

            if (in_array($key, $filterNames, true) && common_Utils::isUri($encodedUri)) {
                $groupedUris[$key][] = $encodedUri;
            }
        }

        return $groupedUris;
    }

    private function flattenArray(array $multiDimensionalArray): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveArrayIterator($multiDimensionalArray)
        );
    }

    private function getEncodedUri(string $decodedUri): string
    {
        return tao_helpers_Uri::isUriEncoded($decodedUri)
            ? tao_helpers_Uri::decode($decodedUri)
            : $decodedUri;
    }
}
