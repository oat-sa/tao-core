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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Translation\Service;

use core_kernel_classes_Literal;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\model\OntologyRdf;
use oat\tao\model\Translation\Entity\AbstractResource;

class ResourceMetadataPopulateService
{
    private Ontology $ontology;
    private array $metadata;

    public function __construct(Ontology $ontology)
    {
        $this->ontology = $ontology;
    }

    public function addMetadata(string $resourceType, string $metadataUri): void
    {
        $this->metadata[$resourceType] ??= [];
        $this->metadata[$resourceType] = array_unique(array_merge($this->metadata[$resourceType], [$metadataUri]));
    }

    public function populate(AbstractResource $resource, core_kernel_classes_Resource $originResource): void
    {
        $valueProperty = $this->ontology->getProperty(OntologyRdf::RDF_VALUE);

        $parentClasses = $originResource->getParentClassesIds();
        $resourceType = array_pop($parentClasses);

        foreach ($this->metadata[$resourceType] ?? [] as $metadataUri) {
            $resource->addMetadataUri($metadataUri);
        }

        foreach ($resource->getMetadataUris() as $metadataUri) {
            $values = $originResource->getPropertyValues($this->ontology->getProperty($metadataUri));

            if (empty($values)) {
                continue;
            }

            $literal = null;
            $value = empty($values) ? null : current($values);

            if ($value) {
                $literalProperty = $this->ontology->getProperty($value);
                $oneValue = $literalProperty->getOnePropertyValue($valueProperty);

                if ($oneValue instanceof core_kernel_classes_Literal) {
                    $literal = $oneValue->literal;
                }
            }

            $resource->addMetadata($metadataUri, $value, $literal);
        }
    }
}
