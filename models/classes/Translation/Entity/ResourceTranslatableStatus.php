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

use JsonSerializable;

class ResourceTranslatableStatus implements JsonSerializable
{
    private string $uri;
    private string $type;
    private bool $isReadyForTranslation;
    private bool $isEmpty;
    private string $languageUri;

    public function __construct(
        string $uri,
        string $type,
        string $languageUri,
        bool $isReadyForTranslation,
        bool $isEmpty
    ) {
        $this->isReadyForTranslation = $isReadyForTranslation;
        $this->isEmpty = $isEmpty;
        $this->uri = $uri;
        $this->type = $type;
        $this->languageUri = $languageUri;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLanguageUri(): string
    {
        return $this->languageUri;
    }

    public function isReadyForTranslation(): bool
    {
        return $this->isReadyForTranslation;
    }

    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }

    public function setEmpty(bool $isEmpty): void
    {
        $this->isEmpty = $isEmpty;
    }

    public function jsonSerialize(): array
    {
        return [
            'uri' => $this->getUri(),
            'type' => $this->getType(),
            'languageUri' => $this->getLanguageUri(),
            'isReadyForTranslation' => $this->isReadyForTranslation(),
            'isEmpty' => $this->isEmpty(),
        ];
    }
}
