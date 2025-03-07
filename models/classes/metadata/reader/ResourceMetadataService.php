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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\metadata\reader;

use oat\generis\model\data\Ontology;
use oat\tao\model\metadata\exception\MetadataNotExist;

class ResourceMetadataService
{
    private Ontology $ontology;

    public function __construct(Ontology $ontology)
    {
        $this->ontology = $ontology;
    }

    public function getResourceMetadataValue(string $resourceUri, string $metadataUri): string
    {
        $resource = $this->ontology->getResource($resourceUri);
        $property = $this->ontology->getProperty($metadataUri);

        if (!($resource->exists() && $property->exists())) {
            throw new MetadataNotExist('Resource or property does not exist');
        }

        return (string) $resource->getOnePropertyValue($property) ?? '';
    }
}
