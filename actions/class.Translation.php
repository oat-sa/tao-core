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

use oat\tao\model\http\HttpJsonResponseTrait;
use oat\tao\model\Translation\Command\UpdateTranslationCommand;
use oat\tao\model\Translation\Service\ResourceTranslationRetriever;
use oat\tao\model\Translation\Service\ResourceTranslatableRetriever;
use oat\tao\model\Translation\Service\ResourceTranslatableStatusRetriever;
use oat\tao\model\Translation\Service\TranslationCreationService;
use oat\tao\model\Translation\Service\TranslationDeletionService;
use oat\tao\model\Translation\Service\TranslationSyncService;
use oat\tao\model\Translation\Service\TranslationUpdateService;

class tao_actions_Translation extends tao_actions_CommonModule
{
    use HttpJsonResponseTrait;

    /**
     * @requiresRight id WRITE
     */
    public function update(): void
    {
        try {
            $resource = $this->getTranslationUpdateService()->update(
                new UpdateTranslationCommand(
                    $this->getRequestParameter('id'),
                    $this->getRequestParameter('progress'),
                )
            );

            $this->setSuccessJsonResponse(
                [
                    'resourceUri' => $resource->getUri()
                ]
            );
        } catch (Throwable $exception) {
            $this->setErrorJsonResponse($exception->getMessage());
        }
    }

    /**
     * @requiresRight id WRITE
     */
    public function delete(): void
    {
        try {
            $resource = $this->getTranslationDeletionService()->deleteByRequest($this->getPsrRequest());

            $this->setSuccessJsonResponse(
                [
                    'resourceUri' => $resource->getUri()
                ]
            );
        } catch (Throwable $exception) {
            $this->setErrorJsonResponse($exception->getMessage());
        }
    }

    /**
     * @requiresRight id WRITE
     */
    public function translate(): void
    {
        try {
            $newResource = $this->getTranslationCreationService()->createByRequest($this->getPsrRequest());

            $this->setSuccessJsonResponse(
                [
                    'resourceUri' => $newResource->getUri()
                ]
            );
        } catch (Throwable $exception) {
            $this->setErrorJsonResponse($exception->getMessage());
        }
    }

    /**
     * @requiresRight id READ
     */
    public function translations(): void
    {
        try {
            $this->setSuccessJsonResponse(
                $this->getResourceTranslationRetriever()->getByRequest($this->getPsrRequest())
            );
        } catch (Throwable $exception) {
            $this->setErrorJsonResponse($exception->getMessage());
        }
    }

    /**
     * @requiresRight id READ
     */
    public function translatable(): void
    {
        try {
            $this->setSuccessJsonResponse(
                $this->getResourceTranslatableRetriever()->getByRequest($this->getPsrRequest())
            );
        } catch (Throwable $exception) {
            $this->setErrorJsonResponse($exception->getMessage());
        }
    }

    /**
     * @requiresRight id WRITE
     */
    public function sync(): void
    {
        try {
            $test = $this->getTranslationSyncService()->syncByRequest($this->getPsrRequest());

            $this->setSuccessJsonResponse([
                'resourceUri' => $test->getUri(),
            ]);
        } catch (Throwable $exception) {
            $this->setErrorJsonResponse($exception->getMessage());
        }
    }

    /**
     * @requiresRight id READ
     */
    public function status(): void
    {
        try {
            $this->setSuccessJsonResponse(
                $this->getResourceTranslatableStatusRetriever()->retrieveByRequest($this->getPsrRequest())
            );
        } catch (Throwable $exception) {
            $this->setErrorJsonResponse($exception->getMessage());
        }
    }

    private function getResourceTranslationRetriever(): ResourceTranslationRetriever
    {
        return $this->getPsrContainer()->get(ResourceTranslationRetriever::class);
    }

    private function getResourceTranslatableRetriever(): ResourceTranslatableRetriever
    {
        return $this->getPsrContainer()->get(ResourceTranslatableRetriever::class);
    }

    private function getTranslationCreationService(): TranslationCreationService
    {
        return $this->getPsrContainer()->get(TranslationCreationService::class);
    }

    private function getTranslationUpdateService(): TranslationUpdateService
    {
        return $this->getPsrContainer()->get(TranslationUpdateService::class);
    }

    private function getTranslationSyncService(): TranslationSyncService
    {
        return $this->getPsrContainer()->get(TranslationSyncService::class);
    }

    private function getTranslationDeletionService(): TranslationDeletionService
    {
        return $this->getPsrContainer()->get(TranslationDeletionService::class);
    }

    private function getResourceTranslatableStatusRetriever(): ResourceTranslatableStatusRetriever
    {
        return $this->getPsrContainer()->get(ResourceTranslatableStatusRetriever::class);
    }
}
