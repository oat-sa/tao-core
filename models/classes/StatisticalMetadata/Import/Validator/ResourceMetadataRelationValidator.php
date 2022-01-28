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

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\tao\model\StatisticalMetadata\Contract\Header;
use oat\tao\model\StatisticalMetadata\Import\Exception\ErrorValidationException;

class ResourceMetadataRelationValidator
{
    public function validate(
        core_kernel_classes_Resource $resource,
        core_kernel_classes_Property $metadataProperty
    ): void {
        /** @var core_kernel_classes_Class $metadataPropertyDomain */
        $metadataPropertyDomain = $metadataProperty->getDomain()->get(0);

        if (!$resource->isInstanceOf($metadataPropertyDomain)) {
            throw new ErrorValidationException(
                'Property referenced by "%s" does not belong to the resource "%s"',
                [
                    Header::METADATA_PREFIX . $metadataProperty->getAlias(),
                    $resource->getUri(),
                ]
            );
        }
    }
}
