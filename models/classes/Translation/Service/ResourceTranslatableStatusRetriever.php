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

use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\ResourceTranslatableStatus;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ResourceTranslatableStatusRetriever
{
    private Ontology $ontology;
    private LoggerInterface $logger;
    private array $callables;

    public function __construct(Ontology $ontology, LoggerInterface $logger)
    {
        $this->ontology = $ontology;
        $this->logger = $logger;
    }

    public function addCallable(string $resourceType, callable $callable): void
    {
        $this->callables[$resourceType][] = $callable;
    }

    public function retrieveByRequest(ServerRequestInterface $request): ResourceTranslatableStatus
    {
        $resourceUri = $request->getQueryParams()['id'] ?? null;

        if (!$resourceUri) {
            throw new ResourceTranslationException('Resource id is required');
        }

        $resource = $this->ontology->getResource($resourceUri);

        if (!$resource->exists()) {
            throw new ResourceTranslationException(sprintf('Translatable resource %s does not exist', $resourceUri));
        }

        return $this->retrieveByResource($resource);
    }

    public function retrieveByResource(core_kernel_classes_Resource $resource): ResourceTranslatableStatus
    {
        $rootUri = $resource->getRootId();
        $status = new ResourceTranslatableStatus(
            $resource->getUri(),
            $rootUri,
            $this->getLanguageUri($resource),
            $this->isReadyForTranslation($resource),
            true
        );

        foreach (($this->callables[$resource->getRootId()] ?? []) as $callable) {
            $callable($status);
        }

        return $status;
    }

    private function isReadyForTranslation(core_kernel_classes_Resource $resource): bool
    {
        $translationStatusProperty = $this->ontology->getProperty(TaoOntology::PROPERTY_TRANSLATION_STATUS);
        $translationStatusPropertyValue = $resource->getOnePropertyValue($translationStatusProperty);

        if (!$translationStatusPropertyValue instanceof core_kernel_classes_Resource) {
            return false;
        }

        return $translationStatusPropertyValue->getUri() === TaoOntology::PROPERTY_VALUE_TRANSLATION_STATUS_READY;
    }

    private function getLanguageUri(core_kernel_classes_Resource $resource): ?string
    {
        $languageProperty = $this->ontology->getProperty(TaoOntology::PROPERTY_LANGUAGE);
        $languagePropertyValue = $resource->getOnePropertyValue($languageProperty);

        if (!$languagePropertyValue instanceof core_kernel_classes_Resource) {
            return null;
        }

        return $languagePropertyValue->getUri();
    }
}
