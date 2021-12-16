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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\resources\Context;

use InvalidArgumentException;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\tao\model\Context\AbstractContext;

class ResourceRepositoryContext extends AbstractContext
{
    public const PARAM_REPOSITORY = 'repository';
    public const PARAM_RESOURCE = 'resource';
    public const PARAM_CLASS = 'class';
    public const PARAM_DELETE_REFERENCE = 'deleteReference';
    public const PARAM_SELECTED_CLASS = 'selectedClass';
    public const PARAM_PARENT_CLASS = 'parentClass';

    public const REPO_RESOURCE = 'resource';
    public const REPO_CLASS = 'class';

    protected function getSupportedParameters(): array
    {
        return [
            self::PARAM_REPOSITORY,
            self::PARAM_RESOURCE,
            self::PARAM_CLASS,
            self::PARAM_DELETE_REFERENCE,
            self::PARAM_SELECTED_CLASS,
            self::PARAM_PARENT_CLASS,
        ];
    }

    protected function validateParameter(string $parameter, $parameterValue): void
    {
        if (
            $parameter === self::PARAM_REPOSITORY
            && in_array($parameterValue, [self::REPO_RESOURCE, self::REPO_CLASS], true)
        ) {
            return;
        }

        if ($parameter === self::PARAM_RESOURCE && $parameterValue instanceof core_kernel_classes_Resource) {
            return;
        }

        if (
            in_array($parameter, [self::PARAM_CLASS, self::PARAM_SELECTED_CLASS, self::PARAM_PARENT_CLASS], true)
            && $parameterValue instanceof core_kernel_classes_Class
        ) {
            return;
        }

        if ($parameter === self::PARAM_DELETE_REFERENCE && is_bool($parameterValue)) {
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
