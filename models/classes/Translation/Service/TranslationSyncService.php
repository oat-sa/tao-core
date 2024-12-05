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
use oat\tao\model\Translation\Entity\ResourceTranslation;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Query\ResourceTranslationQuery;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class TranslationSyncService
{
    private Ontology $ontology;
    private ResourceTranslationRepository $resourceTranslationRepository;
    private LoggerInterface $logger;
    private TranslatedIntoLanguagesSynchronizer $translatedIntoLanguagesSynchronizer;
    private array $synchronizers;

    public function __construct(
        Ontology $ontology,
        ResourceTranslationRepository $resourceTranslationRepository,
        LoggerInterface $logger,
        TranslatedIntoLanguagesSynchronizer $translatedIntoLanguagesSynchronizer
    ) {
        $this->ontology = $ontology;
        $this->resourceTranslationRepository = $resourceTranslationRepository;
        $this->logger = $logger;
        $this->translatedIntoLanguagesSynchronizer = $translatedIntoLanguagesSynchronizer;
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

        return $this->syncById($id);
    }

    public function syncById(string $id): core_kernel_classes_Resource
    {
        if (empty($id)) {
            throw new ResourceTranslationException('Resource id is required');
        }

        $resource = $this->ontology->getResource($id);

        $this->assertResourceExists($resource);
        $this->assertIsOriginal($resource);

        $translations = $this->getTranslations($resource, $requestParams['languageUri'] ?? null);

        foreach ($this->synchronizers[$resource->getRootId()] as $callable) {
            foreach ($translations as $translation) {
                $callable($translation);
            }
        }

        $this->translatedIntoLanguagesSynchronizer->sync($resource);

        return $resource;
    }

    private function assertResourceExists(core_kernel_classes_Resource $resource): void
    {
        if (!$resource->exists()) {
            throw new ResourceTranslationException(sprintf('Resource %s does not exist', $resource->getUri()));
        }
    }

    private function assertIsOriginal(core_kernel_classes_Resource $resource): void
    {
        $translationType = $resource->getOnePropertyValue(
            $this->ontology->getProperty(TaoOntology::PROPERTY_TRANSLATION_TYPE)
        );

        if ($translationType->getUri() !== TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL) {
            throw new ResourceTranslationException(
                sprintf('Resource %s is not the original', $resource->getUri())
            );
        }
    }

    /**
     * @return core_kernel_classes_Resource[]
     */
    private function getTranslations(core_kernel_classes_Resource $resource, ?string $languageUri): array
    {
        $translations = $this->resourceTranslationRepository->find(new ResourceTranslationQuery(
            [$resource->getUri()],
            $languageUri
        ));

        $resources = [];

        /** @var ResourceTranslation $translation */
        foreach ($translations as $translation) {
            $translationResource = $this->ontology->getResource($translation->getResourceUri());

            if (!$translationResource->exists()) {
                $this->logger->error('Resource %s does not exist', $translation->getResourceUri());

                continue;
            }

            $resources[] = $translationResource;
        }

        if (empty($resources)) {
            throw new ResourceTranslationException(
                sprintf(
                    'Translations for resource does not exist [Resource: %s, Language URI: %s]',
                    $resource->getUri(),
                    $languageUri
                )
            );
        }

        return $resources;
    }
}
