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
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\tao\model\StatisticalMetadata\Contract\Header;
use oat\tao\model\StatisticalMetadata\Import\Exception\ErrorValidationException;

class RecordResourceValidator
{
    public function validateResourceId(array $record): void
    {
        if (empty($record[Header::ITEM_ID]) && empty($record[Header::TEST_ID])) {
            throw new ErrorValidationException(
            'At least one of %s or %s is required.',
                [
                    Header::ITEM_ID,
                    Header::TEST_ID,
                ]
            );
        }
    }

    public function validateResourceAvailability(core_kernel_classes_Resource $resource): void
    {
        if (!$resource->exists()) {
            throw new ErrorValidationException(
                'Resource referenced by "%s" is not found.',
                [
                    $resource->getUri(),
                ]
            );
        }
    }

    public function validateResourceType(
        core_kernel_classes_Resource $resource,
        core_kernel_classes_Class $rootClass
    ): void {
        if (!$resource->isInstanceOf($rootClass)) {
            throw new ErrorValidationException(
                'Resource referenced by "%s" has the wrong instance type.',
                [
                    $resource->getUri(),
                ]
            );
        }
    }
}
