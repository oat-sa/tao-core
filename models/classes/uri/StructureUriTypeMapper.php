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

use core_kernel_classes_Resource as RdfResource;

class StructureUriTypeMapper implements UriTypeMapperInterface
{
    /** @var array */
    private $map;

    public function __construct()
    {
        // TODO: move to configs

        $this->map = [
            'http://www.tao.lu/Ontologies/TAOItem.rdf#Item' => ['structure' => 'items', 'ext' => 'taoItems'],
            'http://www.tao.lu/Ontologies/TAOTest.rdf#Test' => ['structure' => 'tests', 'ext' => 'taoTests'],
        ];
    }

    public function map(RdfResource $resourceType, RdfResource $resource): ?string
    {
        if (isset($this->map[$resourceType->getUri()])) {
            $params = $this->map[$resourceType->getUri()];
            $params['uri'] = $resource->getUri();

            return _url('index', 'Main', 'tao', $params);
        }

        return null;
    }
}
