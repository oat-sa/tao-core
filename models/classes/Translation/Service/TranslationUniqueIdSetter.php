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

namespace oat\tao\model\Translation\Service;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\TaoOntology;

class TranslationUniqueIdSetter
{
    private FeatureFlagCheckerInterface $featureFlagChecker;
    private Ontology $ontology;

    /** @var AbstractQtiIdentifierSetter[] */
    private array $qtiIdentifierSetters = [];

    /** @var core_kernel_classes_Property[] */
    private array $properties = [];

    public function __construct(FeatureFlagCheckerInterface $featureFlagChecker, Ontology $ontology)
    {
        $this->featureFlagChecker = $featureFlagChecker;
        $this->ontology = $ontology;
    }

    public function addQtiIdentifierSetter(AbstractQtiIdentifierSetter $qtiIdentifierSetter, string $resourceType): void
    {
        if (isset($this->qtiIdentifierSetters[$resourceType])) {
            throw new InvalidArgumentException(
                'QTI Identifier setter already exists for resource type ' . $resourceType
            );
        }

        $this->qtiIdentifierSetters[$resourceType] = $qtiIdentifierSetter;
    }

    public function __invoke(core_kernel_classes_Resource $resource): void
    {
        if (
            !$this->featureFlagChecker->isEnabled('FEATURE_FLAG_UNIQUE_NUMERIC_QTI_IDENTIFIER')
            || !$this->featureFlagChecker->isEnabled('FEATURE_FLAG_TRANSLATION_ENABLED')
        ) {
            return;
        }

        $originalResource = $this->getOriginalResource($resource);
        $uniqueIdentifier = $this->getUniqueId($originalResource);

        $resource->editPropertyValues(
            $this->getProperty(TaoOntology::PROPERTY_UNIQUE_IDENTIFIER),
            $uniqueIdentifier
        );
        $this->getQtiIdentifierSetter($resource)->set([
            AbstractQtiIdentifierSetter::OPTION_RESOURCE => $resource,
            AbstractQtiIdentifierSetter::OPTION_IDENTIFIER => $uniqueIdentifier,
        ]);
    }

    private function getOriginalResource(core_kernel_classes_Resource $resource): core_kernel_classes_Resource
    {
        $property = $this->getProperty(TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI);
        $originalResourceUri = $resource->getOnePropertyValue($property);

        if (empty($originalResourceUri)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Resource %s is not a translation - original resource URI is empty',
                    $resource->getUri()
                )
            );
        }

        return $this->ontology->getResource($originalResourceUri);
    }

    private function getUniqueId(core_kernel_classes_Resource $resource): string
    {
        $property = $this->getProperty(TaoOntology::PROPERTY_UNIQUE_IDENTIFIER);
        $uniqueId = $resource->getOnePropertyValue($property);

        if (empty($uniqueId)) {
            throw new InvalidArgumentException('Unique ID must exists for resource URI ' . $resource->getUri());
        }

        return $uniqueId->literal;
    }

    private function getQtiIdentifierSetter(core_kernel_classes_Resource $resource): AbstractQtiIdentifierSetter
    {
        $resourceType = $resource->getRootId();

        if (!isset($this->qtiIdentifierSetters[$resourceType])) {
            throw new InvalidArgumentException(
                'QTI Identifier setter does not exist for resource type ' . $resourceType
            );
        }

        return $this->qtiIdentifierSetters[$resourceType];
    }

    private function getProperty(string $uri): core_kernel_classes_Property
    {
        if (!isset($this->properties[$uri])) {
            $this->properties[$uri] = $this->ontology->getProperty($uri);
        }

        return $this->properties[$uri];
    }
}
