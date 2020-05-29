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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\model\resources;

use common_exception_Error;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\tao\model\service\InjectionAwareService;

class ResourceRepository extends InjectionAwareService implements ResourceRepositoryInterface
{
    /**
     * @param string $uri
     *
     * @return ResourceInterface
     * @throws common_exception_Error
     */
    public function find(string $uri): ResourceInterface
    {
        $resource = new core_kernel_classes_Resource($uri);

        if ($resource->isClass()) {
            $resource = new core_kernel_classes_Class($resource);
        }

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function findChildren(RdfClassInterface $class, bool $recursive = false): array
    {
        $rdfClass = $class instanceof core_kernel_classes_Class
            ? $class
            : $this->find($class->getUri());

        return $rdfClass->getSubClasses($recursive);
    }

    public function findInstances(RdfClassInterface $class, bool $recursive = false): array
    {
        $rdfClass = $class instanceof core_kernel_classes_Class
            ? $class
            : new core_kernel_classes_Class($class->getUri());

        return $rdfClass->getInstances($recursive);
    }
}
