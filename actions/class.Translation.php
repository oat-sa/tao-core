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
use oat\tao\model\Translation\Service\ResourceTranslationRetriever;
use oat\tao\model\Translation\Service\ResourceTranslatableRetriever;
use oat\tao\model\Translation\Service\TranslationCreationService;

class tao_actions_Translation extends tao_actions_CommonModule
{
    use HttpJsonResponseTrait;

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

    private function getResourceTranslationRetriever(): ResourceTranslationRetriever
    {
        return $this->getServiceManager()->getContainer()->get(ResourceTranslationRetriever::class);
    }

    private function getResourceTranslatableRetriever(): ResourceTranslatableRetriever
    {
        return $this->getServiceManager()->getContainer()->get(ResourceTranslatableRetriever::class);
    }

    private function getTranslationCreationService(): TranslationCreationService
    {
        return $this->getServiceManager()->getContainer()->get(TranslationCreationService::class);
    }
}
