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
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class TranslationSyncService
{
    private Ontology $ontology;
    private LoggerInterface $logger;
    private array $synchronizers;

    public function __construct(Ontology $ontology, LoggerInterface $logger)
    {
        $this->ontology = $ontology;
        $this->logger = $logger;
    }

    public function addSynchronizer(string $resourceType, callable $synchronizer): void
    {
        $this->synchronizers[$resourceType] ??= [];
        $this->synchronizers[$resourceType][] = $synchronizer;
    }

    public function syncByRequest(ServerRequestInterface $request): core_kernel_classes_Resource
    {
        $requestParams = $request->getParsedBody();
        $id = $requestParams['id'] ?? null;

        if (empty($id)) {
            throw new ResourceTranslationException('Resource id is required');
        }

        $resource = $this->ontology->getResource($id);

        $this->assertResourceExists($resource);
        $this->assertIsTranslation($resource);

        $resourceType = $this->getResourceType($resource);

        try {
            foreach ($this->synchronizers[$resourceType] as $callable) {
                $resource = $callable($resource);
            }
        } catch (Throwable $exception) {
            $this->logger->error(
                'An error occurred while trying to synchronize resource translation: ' . $exception->getMessage()
            );

            throw $exception;
        }

        return $resource;
    }

    private function assertResourceExists(core_kernel_classes_Resource $resource): void
    {
        if (!$resource->exists()) {
            throw new ResourceTranslationException(sprintf('Resource %s does not exist', $resource->getUri()));
        }
    }

    private function assertIsTranslation(core_kernel_classes_Resource $test): void
    {
        $translationType = $test->getOnePropertyValue(
            $this->ontology->getProperty(TaoOntology::PROPERTY_TRANSLATION_TYPE)
        );

        if ($translationType->getUri() !== TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION) {
            throw new ResourceTranslationException(sprintf('Test %s is not the translation', $test->getUri()));
        }
    }

    private function getResourceType(core_kernel_classes_Resource $resource): string
    {
        $parentClassIds = $resource->getParentClassesIds();
        $resourceType = array_pop($parentClassIds);

        if (empty($resourceType)) {
            throw new ResourceTranslationException(
                sprintf('Resource %s must have a resource type', $resource->getUri())
            );
        }

        return $resourceType;
    }
}
