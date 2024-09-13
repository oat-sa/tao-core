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
use oat\tao\model\Language\Business\Contract\LanguageRepositoryInterface;
use oat\tao\model\Language\Language;
use oat\tao\model\OntologyClassService;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Command\CreateTranslationCommand;
use oat\tao\model\Translation\Entity\ResourceTranslatable;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Query\ResourceTranslatableQuery;
use oat\tao\model\Translation\Query\ResourceTranslationQuery;
use oat\tao\model\Translation\Repository\ResourceTranslatableRepository;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class TranslationCreationService
{
    private Ontology $ontology;
    private ResourceTranslatableRepository $resourceTranslatableRepository;
    private ResourceTranslationRepository $resourceTranslationRepository;
    private LanguageRepositoryInterface $languageRepository;
    private LoggerInterface $logger;
    private array $ontologyClassServices;
    private array $callables;

    public function __construct(
        Ontology $ontology,
        ResourceTranslatableRepository $resourceTranslatableRepository,
        ResourceTranslationRepository $resourceTranslationRepository,
        LanguageRepositoryInterface $languageRepository,
        LoggerInterface $logger
    ) {
        $this->ontology = $ontology;
        $this->resourceTranslatableRepository = $resourceTranslatableRepository;
        $this->resourceTranslationRepository = $resourceTranslationRepository;
        $this->languageRepository = $languageRepository;
        $this->logger = $logger;
    }

    public function setOntologyClassService(string $resourceType, OntologyClassService $ontologyClassService): void
    {
        $this->ontologyClassServices[$resourceType] = $ontologyClassService;
    }

    public function addPostCreation(string $resourceType, callable $callable): void
    {
        $this->callables[$resourceType] = $this->callables[$resourceType] ?? [];
        $this->callables[$resourceType][] = $callable;
    }

    public function createByRequest(ServerRequestInterface $request): core_kernel_classes_Resource
    {
        $requestParams = $request->getParsedBody();
        $id = $requestParams['id'] ?? null;

        if (empty($id)) {
            throw new ResourceTranslationException('Resource id is required');
        }

        $resource = $this->ontology->getResource($id);

        if (!$resource->exists()) {
            throw new ResourceTranslationException(
                sprintf(
                    'Resource %s does not exist',
                    $id
                )
            );
        }

        /** @var core_kernel_classes_Resource $translationType */
        $translationType = $resource->getUniquePropertyValue(
            $this->ontology->getProperty(TaoOntology::PROPERTY_TRANSLATION_TYPE)
        );

        if ($translationType->getUri() !== TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL) {
            throw new ResourceTranslationException(
                sprintf(
                    'Resource %s is not the original',
                    $id
                )
            );
        }

        $parentClassIds = $resource->getParentClassesIds();
        $resourceType = array_pop($parentClassIds);

        if (empty($resourceType)) {
            throw new ResourceTranslationException(sprintf('Resource %s must have a resource type', $id));
        }

        $uniqueId = $resource->getUniquePropertyValue(
            $this->ontology->getProperty(TaoOntology::PROPERTY_UNIQUE_IDENTIFIER)
        );

        if (empty($uniqueId)) {
            throw new ResourceTranslationException(sprintf('Resource %s must have a unique identifier', $id));
        }

        if (empty($requestParams['languageUri'])) {
            throw new ResourceTranslationException('Parameter languageUri is mandatory');
        }

        return $this->create(
            new CreateTranslationCommand(
                $resourceType,
                (string)$uniqueId,
                $requestParams['languageUri']
            )
        );
    }

    public function create(CreateTranslationCommand $command): core_kernel_classes_Resource
    {
        try {
            return $this->doCreate($command);
        } catch (Throwable $exception) {
            $this->logger->error(
                sprintf(
                    'Could not translate [uniqueId=%s, resourceType=%s, language=%s] (%s): %s',
                    $command->getUniqueId(),
                    $command->getResourceType(),
                    $command->getLanguageUri(),
                    get_class($exception),
                    $exception->getMessage()
                )
            );

            throw $exception;
        }
    }

    private function doCreate(CreateTranslationCommand $command): core_kernel_classes_Resource
    {
        $translations = $this->resourceTranslationRepository->find(
            new ResourceTranslationQuery(
                $command->getResourceType(),
                [$command->getUniqueId()],
                $command->getLanguageUri()
            )
        );

        if ($translations->count() > 0) {
            throw new ResourceTranslationException(
                sprintf(
                    'Translation already exists for [uniqueId=%s, locale=%s]',
                    $command->getUniqueId(),
                    $command->getLanguageUri()
                )
            );
        }

        $resources = $this->resourceTranslatableRepository->find(
            new ResourceTranslatableQuery(
                $command->getResourceType(),
                [$command->getUniqueId()]
            )
        );

        if ($resources->count() === 0) {
            throw new ResourceTranslationException(
                sprintf(
                    'There is not translatable resource for [uniqueId=%s]',
                    $command->getUniqueId()
                )
            );
        }

        $existingLanguages = $this->languageRepository->findAvailableLanguagesByUsage();
        $language = null;

        /** @var Language $language */
        foreach ($existingLanguages as $existingLanguage) {
            if ($existingLanguage->getUri() === $command->getLanguageUri()) {
                $language = $existingLanguage;
            }
        }

        if (!$language) {
            throw new ResourceTranslationException(
                sprintf(
                    'Language %s does not exist',
                    $command->getLanguageUri()
                )
            );
        }

        /** @var ResourceTranslatable $resource */
        $resource = $resources->current();

        if ($resource->getLanguageUri() === $language->getUri()) {
            throw new ResourceTranslationException(
                sprintf(
                    'Cannot translate to original language %s',
                    $command->getLanguageUri()
                )
            );
        }

        $instance = $this->ontology->getResource($resource->getResourceUri());
        $types = $instance->getTypes();
        $type = array_pop($types);

        $clonedInstance = $this->getOntologyService($command->getResourceType())->cloneInstance($instance, $type);

        $clonedInstance->setLabel(sprintf('%s (%s)', $instance->getLabel(), $language->getCode()));

        $clonedInstance->editPropertyValues(
            $this->ontology->getProperty(TaoOntology::PROPERTY_LANGUAGE),
            $language->getUri()
        );

        $clonedInstance->editPropertyValues(
            $this->ontology->getProperty(TaoOntology::PROPERTY_TRANSLATION_TYPE),
            TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION
        );

        $clonedInstance->editPropertyValues(
            $this->ontology->getProperty(TaoOntology::PROPERTY_TRANSLATION_PROGRESS),
            TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_PENDING
        );

        foreach ($this->callables[$command->getResourceType()] ?? [] as $callable) {
            $clonedInstance = $callable($clonedInstance);
        }

        return $clonedInstance;
    }

    private function getOntologyService(string $resourceType): OntologyClassService
    {
        $service = $this->ontologyClassServices[$resourceType] ?? null;

        if ($service) {
            return $service;
        }

        throw new ResourceTranslationException(
            sprintf(
                'There is no OntologyClassService for resource type %s',
                $resourceType
            )
        );
    }
}
