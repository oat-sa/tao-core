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

namespace oat\tao\model\Translation\Entity;

use ArrayIterator;
use JsonSerializable;

class ResourceTranslationCollection extends ArrayIterator implements JsonSerializable
{
    private string $originResourceUri;
    private string $resourceLabel;
    private string $uniqueId;


    public function __construct(string $originResourceUri, string $resourceLabel, string $uniqueId)
    {
        parent::__construct();

        $this->originResourceUri = $originResourceUri;
        $this->resourceLabel = $resourceLabel;
        $this->uniqueId = $uniqueId;
    }

    public function addTranslation(ResourceTranslation $resourceTranslation): void
    {
        $this->append($resourceTranslation);
    }

    public function getOriginResourceUri(): string
    {
        return $this->originResourceUri;
    }

    public function getOriginResourceLabel(): string
    {
        return $this->resourceLabel;
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    public function jsonSerialize(): array
    {
        return [
            'originResourceUri' => $this->getOriginResourceUri(),
            'originResourceLabel' => $this->getOriginResourceLabel(),
            'uniqueId' => $this->getUniqueId(),
            'translations' => $this->getArrayCopy()
        ];
    }
}
