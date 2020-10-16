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
 */

declare(strict_types=1);

namespace oat\tao\model\uri;

use core_kernel_classes_Class as RdfClass;
use core_kernel_classes_Resource as RdfResource;
use Generator;
use oat\oatbox\service\ConfigurableService;

class UriTypeMapperRegistry extends ConfigurableService
{
    public const OPTION_MAPPERS = 'mappers';

    public function __construct()
    {
        // TODO: move to configs
        parent::__construct(
            [
                'mappers' => [
                    new StructureUriTypeMapper()
                ]
            ]
        );
    }

    public function map(RdfResource $resource): string
    {
        foreach ($this->iterateParents($resource) as $class) {
            $url = $this->findLandingPage($class, $resource);

            if ($url) {
                return $url;
            }
        }

        throw new NoMatchedEndpointsFoundException('No endpoints found');
    }

    /**
     * @param RdfResource $resource
     *
     * @return Generator
     */
    private function iterateParents(RdfResource $resource): Generator
    {
        $parentClasses = ($resource instanceof RdfClass || $resource->isClass())
            ? $resource->getParentClasses(false)
            : $resource->getTypes();

        foreach ($parentClasses as $parentClass) {
            yield $parentClass;
            yield from $this->iterateParents($parentClass);
        }
    }

    private function findLandingPage(RdfResource $resourceType, RdfResource $resource): ?string
    {
        foreach ($this->getOption(self::OPTION_MAPPERS) as $mapper) {
            $url = $mapper->map($resourceType, $resource);

            if ($url) {
                return $url;
            }
        }

        return null;
    }
}
