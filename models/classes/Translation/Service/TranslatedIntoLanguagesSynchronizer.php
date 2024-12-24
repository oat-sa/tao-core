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

use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\AbstractResource;
use oat\tao\model\Translation\Query\ResourceTranslationQuery;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;

class TranslatedIntoLanguagesSynchronizer
{
    private Ontology $ontology;
    private ResourceTranslationRepository $resourceTranslationRepository;
    private array $callbacks;

    public function __construct(Ontology $ontology, ResourceTranslationRepository $resourceTranslationRepository)
    {
        $this->ontology = $ontology;
        $this->resourceTranslationRepository = $resourceTranslationRepository;
    }

    public function addCallback(string $type, callable $callback): void
    {
        $this->callbacks[$type] = $this->callbacks[$type] ?? [];
        $this->callbacks[$type][] = $callback;
    }

    public function sync(core_kernel_classes_Resource $resource): void
    {
        $originalResource = $this->getOriginalResource($resource);

        $property = $this->ontology->getProperty(TaoOntology::PROPERTY_TRANSLATED_INTO_LANGUAGES);
        $originalResource->removePropertyValues($property);

        /** @var AbstractResource[] $translations */
        $translations = $this->resourceTranslationRepository->find(
            new ResourceTranslationQuery([$originalResource->getUri()])
        );

        foreach ($translations as $translation) {
            $originalResource->setPropertyValue(
                $property,
                TaoOntology::LANGUAGE_PREFIX . $translation->getLanguageCode()
            );
        }

        foreach (($this->callbacks[$originalResource->getRootId()] ?? []) as $callback) {
            $callback($originalResource);
        }
    }

    private function getOriginalResource(core_kernel_classes_Resource $resource): core_kernel_classes_Resource
    {
        $originalResourceUriProperty = $this->ontology->getProperty(
            TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI
        );
        $originalResourceUri = $resource->getOnePropertyValue($originalResourceUriProperty);

        return !empty($originalResourceUri)
            ? $this->ontology->getResource($originalResourceUri)
            : $resource;
    }
}
