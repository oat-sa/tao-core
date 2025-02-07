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
use oat\generis\model\resource\Contract\ResourceDeleterInterface;
use oat\oatbox\event\EventManager;
use oat\tao\model\Translation\Entity\AbstractResource;
use oat\tao\model\Translation\Event\TranslationActionEvent;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Query\ResourceTranslationQuery;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class TranslationDeletionService
{
    private Ontology $ontology;
    private ResourceDeleterInterface $resourceDeleter;
    private ResourceTranslationRepository $resourceTranslationRepository;
    private LoggerInterface $logger;
    private TranslatedIntoLanguagesSynchronizer $translatedIntoLanguagesSynchronizer;
    private EventManager $eventManager;

    public function __construct(
        Ontology $ontology,
        ResourceDeleterInterface $resourceDeleter,
        ResourceTranslationRepository $resourceTranslationRepository,
        LoggerInterface $logger,
        TranslatedIntoLanguagesSynchronizer $translatedIntoLanguagesSynchronizer,
        EventManager $eventManager
    ) {
        $this->ontology = $ontology;
        $this->resourceDeleter = $resourceDeleter;
        $this->resourceTranslationRepository = $resourceTranslationRepository;
        $this->logger = $logger;
        $this->translatedIntoLanguagesSynchronizer = $translatedIntoLanguagesSynchronizer;
        $this->eventManager = $eventManager;
    }

    public function deleteByRequest(ServerRequestInterface $request): core_kernel_classes_Resource
    {
        $requestParams = $request->getParsedBody();
        $resourceUri = $requestParams['id'] ?? null;
        $languageUri = $requestParams['languageUri'] ?? null;

        if (empty($resourceUri)) {
            throw new ResourceTranslationException('Resource id is required');
        }

        if (empty($languageUri)) {
            throw new ResourceTranslationException('Parameter languageUri is mandatory');
        }

        try {
            $resource = $this->ontology->getResource($resourceUri);
            $rootId = $resource->getRootId();

            $translations = $this->resourceTranslationRepository
                ->find(new ResourceTranslationQuery([$resourceUri], $languageUri));

            if ($translations->count() === 0) {
                throw new ResourceTranslationException(
                    sprintf(
                        'Translation does not exist for [id=%s, locale=%s]',
                        $resourceUri,
                        $languageUri
                    )
                );
            }

            /** @var AbstractResource $translation */
            foreach ($translations as $translation) {
                $translationResource = $this->ontology->getResource($translation->getResourceUri());

                $this->resourceDeleter->delete($translationResource);

                $this->eventManager->trigger(new TranslationActionEvent(
                    TranslationActionEvent::ACTION_DELETED,
                    $rootId,
                    $resourceUri,
                    $translation->getResourceUri(),
                    $translation->getLanguageCode()
                ));
            }

            $this->translatedIntoLanguagesSynchronizer->sync($resource);

            return $resource;
        } catch (Throwable $exception) {
            $this->logger->error(
                sprintf(
                    'Could not delete translation [id=%s, language=%s] (%s): %s',
                    $resourceUri,
                    $languageUri,
                    get_class($exception),
                    $exception->getMessage()
                )
            );

            throw $exception;
        }
    }

    public function deleteByOriginResourceUri(string $originResourceUri): void
    {
        try {
            $translations = $this->resourceTranslationRepository
                ->find(new ResourceTranslationQuery([$originResourceUri]));

            /** @var AbstractResource $translation */
            foreach ($translations as $translation) {
                $instance = $this->ontology->getResource($translation->getResourceUri());

                $this->resourceDeleter->delete($instance);
            }
        } catch (Throwable $exception) {
            $this->logger->error(
                sprintf(
                    'Error deleting translations by originResourceUri [%s] (%s): %s',
                    $originResourceUri,
                    get_class($exception),
                    $exception->getMessage()
                )
            );

            throw $exception;
        }
    }
}
