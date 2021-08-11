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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Domain;

use InvalidArgumentException;
use oat\tao\model\Context\AbstractContext;

class RemoteSourceContext extends AbstractContext
{
    public const PARAM_SOURCE_URL = 'sourceUrl';
    public const PARAM_URI_PATH = 'uriPath';
    public const PARAM_LABEL_PATH = 'labelPath';
    public const PARAM_DEPENDENCY_URI_PATH = 'dependencyUriPath';
    public const PARAM_PARSER = 'parser';
    public const PARAM_JSON = 'json';

    protected function getSupportedParameters(): array
    {
        return [
            self::PARAM_SOURCE_URL,
            self::PARAM_URI_PATH,
            self::PARAM_LABEL_PATH,
            self::PARAM_DEPENDENCY_URI_PATH,
            self::PARAM_PARSER,
            self::PARAM_JSON,
        ];
    }

    protected function validateParameter(string $parameter, $parameterValue): void
    {
        if ($parameter === self::PARAM_JSON && is_array($parameterValue)) {
            return;
        }

        if ($parameter === self::PARAM_DEPENDENCY_URI_PATH && $parameterValue === null) {
            return;
        }

        if ($parameter !== self::PARAM_JSON && is_string($parameterValue)) {
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Context parameter %s is not valid.',
                $parameter
            )
        );
    }
}
