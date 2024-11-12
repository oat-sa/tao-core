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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\IdentifierGenerator\Generator;

use core_kernel_classes_Resource;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;

class IdentifierGeneratorProxy implements IdentifierGeneratorInterface
{
    private Ontology $ontology;
    private array $idGenerators = [];

    public function __construct(Ontology $ontology)
    {
        $this->ontology = $ontology;
    }

    public function addIdentifierGenerator(IdentifierGeneratorInterface $idGenerator, string $resourceType): void
    {
        if (isset($this->idGenerators[$resourceType])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Id generator for type %s already defined',
                    $resourceType
                )
            );
        }

        $this->idGenerators[$resourceType] = $idGenerator;
    }

    public function generate(array $options = []): string
    {
        $this->assertRequiredOptionsProvided($options);
        $resourceType = $this->getResourceType($options);

        return $this->getIdGenerator($resourceType)->generate($options);
    }

    private function assertRequiredOptionsProvided(array $options): void
    {
        if (!isset($options[self::OPTION_RESOURCE]) && !isset($options[self::OPTION_RESOURCE_ID])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Option "%s" or "%s" is required to generate ID',
                    self::OPTION_RESOURCE,
                    self::OPTION_RESOURCE_ID
                )
            );
        }
    }

    private function getResourceType(array $options): string
    {
        if (
            isset($options[self::OPTION_RESOURCE])
            && !$options[self::OPTION_RESOURCE] instanceof core_kernel_classes_Resource
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Option "%s" must be an instance of %s',
                    self::OPTION_RESOURCE,
                    core_kernel_classes_Resource::class
                )
            );
        }

        $resource = $options[self::OPTION_RESOURCE] ?? $this->ontology->getResource($options[self::OPTION_RESOURCE_ID]);

        return $resource->getRootId();
    }

    private function getIdGenerator(string $resourceType): IdentifierGeneratorInterface
    {
        if (!isset($this->idGenerators[$resourceType])) {
            throw new InvalidArgumentException(
                sprintf(
                    'ID generator for resource type %s not defined',
                    $resourceType
                )
            );
        }

        return $this->idGenerators[$resourceType];
    }
}
