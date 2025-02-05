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
use oat\oatbox\event\EventManager;
use oat\tao\model\Language\Business\Contract\LanguageRepositoryInterface;
use oat\tao\model\Language\Language;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\ResourceTransferInterface;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Command\CreateTranslationCommand;
use oat\tao\model\Translation\Entity\ResourceTranslatable;
use oat\tao\model\Translation\Event\TranslationActionEvent;
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
    private TranslatedIntoLanguagesSynchronizer $translatedIntoLanguagesSynchronizer;
    private EventManager $eventManager;

    private array $resourceTransferServices;
    private array $callables;

    public function __construct(
        Ontology $ontology,
        ResourceTranslatableRepository $resourceTranslatableRepository,
        ResourceTranslationRepository $resourceTranslationRepository,
        LanguageRepositoryInterface $languageRepository,
        LoggerInterface $logger,
        TranslatedIntoLanguagesSynchronizer $translatedIntoLanguagesSynchronizer,
        EventManager $eventManager
    ) {
        $this->ontology = $ontology;
        $this->resourceTranslatableRepository = $resourceTranslatableRepository;
        $this->resourceTranslationRepository = $resourceTranslationRepository;
        $this->languageRepository = $languageRepository;
        $this->logger = $logger;
        $this->translatedIntoLanguagesSynchronizer = $translatedIntoLanguagesSynchronizer;
        $this->eventManager = $eventManager;
    }

    public function setResourceTransfer(string $resourceType, ResourceTransferInterface $resourceTransfer): void
    {
        $this->resourceTransferServices[$resourceType] = $resourceTransfer;
    }

    public function addPostCreation(string $resourceType, callable $callable): void
    {
        $this->callables[$resourceType] ??= [];
        $this->callables[$resourceType][] = $callable;
    }

    public function createByRequest(ServerRequestInterface $request): core_kernel_classes_Resource
    {
        $requestParams = $request->getParsedBody();
        $id = $requestParams['id'] ?? null;
        $languageUri = $requestParams['languageUri'] ?? null;

        if (empty($id)) {
            throw new ResourceTranslationException('Resource id is required');
        }

        if (empty($languageUri)) {
            throw new ResourceTranslationException('Parameter languageUri is mandatory');
        }

        return $this->create(new CreateTranslationCommand($id, $languageUri));
    }

    public function create(CreateTranslationCommand $command): core_kernel_classes_Resource
    {
        try {
            $resourceUri = $command->getResourceUri();
            $languageUri = $command->getLanguageUri();

            $translations = $this->resourceTranslationRepository->find(
                new ResourceTranslationQuery([$resourceUri], $languageUri)
            );

            if ($translations->count() > 0) {
                throw new ResourceTranslationException(
                    sprintf(
                        'Translation already exists for [id=%s, locale=%s]',
                        $resourceUri,
                        $languageUri
                    )
                );
            }

            $resources = $this->resourceTranslatableRepository->find(new ResourceTranslatableQuery($resourceUri));

            if ($resources->count() === 0) {
                throw new ResourceTranslationException(sprintf('Resource [id=%s] is not translatable', $resourceUri));
            }

            /** @var ResourceTranslatable $resource */
            $resource = $resources->current();

            if (!$resource->isReadyForTranslation()) {
                throw new ResourceTranslationException(
                    sprintf(
                        'Resource [id=%s] is not ready for translation',
                        $resourceUri
                    )
                );
            }

            $existingLanguages = $this->languageRepository->findAvailableLanguagesByUsage();
            $language = null;

            /** @var Language $language */
            foreach ($existingLanguages as $existingLanguage) {
                if ($existingLanguage->getUri() === $languageUri) {
                    $language = $existingLanguage;
                }
            }

            if (!$language) {
                throw new ResourceTranslationException(sprintf('Language %s does not exist', $languageUri));
            }

            if ($resource->getLanguageUri() === $language->getUri()) {
                throw new ResourceTranslationException(
                    sprintf('Cannot translate to original language %s', $languageUri)
                );
            }

            $instance = $this->ontology->getResource($resourceUri);
            $rootId = $instance->getRootId();

            $clonedInstanceUri = $this->getResourceTransfer($rootId)->transfer(
                new ResourceTransferCommand(
                    $resourceUri,
                    $instance->getParentClassId(),
                    null,
                    null
                )
            )->getDestination();
            $clonedInstance = $this->ontology->getResource($clonedInstanceUri);
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

            $clonedInstance->editPropertyValues(
                $this->ontology->getProperty(TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI),
                $resourceUri
            );

            foreach ($this->callables[$rootId] ?? [] as $callable) {
                $callable($clonedInstance);
            }

            $this->translatedIntoLanguagesSynchronizer->sync($instance);

            $this->eventManager->trigger(new TranslationActionEvent(
                TranslationActionEvent::ACTION_CREATED,
                $rootId,
                $resourceUri,
                $clonedInstanceUri,
                $language->getCode()
            ));

            return $clonedInstance;
        } catch (Throwable $exception) {
            $this->logger->error(
                sprintf(
                    'Could not translate [id=%s, language=%s] (%s): %s',
                    $resourceUri,
                    $languageUri,
                    get_class($exception),
                    $exception->getMessage()
                )
            );

            throw $exception;
        }
    }

    private function getResourceTransfer(string $resourceType): ResourceTransferInterface
    {
        $service = $this->resourceTransferServices[$resourceType] ?? null;

        if ($service) {
            return $service;
        }

        throw new ResourceTranslationException(
            sprintf('Missing ResourceTransfer for resource type %s', $resourceType)
        );
    }
}
