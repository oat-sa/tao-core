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
use InvalidArgumentException;
use oat\oatbox\user\UserLanguageServiceInterface;

class ResourceLanguageRetriever
{
    private UserLanguageServiceInterface $userLanguageService;

    private array $retrievers = [];

    public function __construct(UserLanguageServiceInterface $userLanguageService)
    {
        $this->userLanguageService = $userLanguageService;
    }

    public function setRetriever(string $resourceType, callable $retriever): void
    {
        if (array_key_exists($resourceType, $this->retrievers)) {
            throw new InvalidArgumentException(sprintf('Retriever for resource type %s already set.', $resourceType));
        }

        $this->retrievers[$resourceType] = $retriever;
    }

    public function retrieve(core_kernel_classes_Resource $resource): string
    {
        $resourceType = $resource->getRootId();

        $language = isset($this->retrievers[$resourceType])
            ? $this->retrievers[$resourceType]($resource)
            : null;

        return $language ?? $this->userLanguageService->getDefaultLanguage();
    }
}
