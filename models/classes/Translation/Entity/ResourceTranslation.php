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
use oat\tao\model\TaoOntology;

class ResourceTranslation implements JsonSerializable
{
    public const PROGRESS_PENDING = 'pending';
    public const PROGRESS_TRANSLATING = 'translating';
    public const PROGRESS_TRANSLATED = 'translated';

    public const PROGRESS_MAPPING = [
        TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_PENDING => self::PROGRESS_PENDING,
        TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATING => self::PROGRESS_TRANSLATING,
        TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATED => self::PROGRESS_TRANSLATED,
    ];

    private string $originResourceUri;
    private string $resourceUri;
    private string $resourceLabel;
    private string $progress;
    private string $progressUri;
    private string $languageCode;
    private string $languageUri;

    public function __construct(
        string $originResourceUri,
        string $resourceUri,
        string $resourceLabel,
        string $progress,
        string $progressUri,
        string $languageCode,
        string $languageUri
    ) {
        $this->originResourceUri = $originResourceUri;
        $this->resourceUri = $resourceUri;
        $this->resourceLabel = $resourceLabel;
        $this->progress = $progress;
        $this->progressUri = $progressUri;
        $this->languageCode = $languageCode;
        $this->languageUri = $languageUri;
    }

    public function getOriginResourceUri(): string
    {
        return $this->originResourceUri;
    }

    public function getResourceUri(): string
    {
        return $this->resourceUri;
    }

    public function getResourceLabel(): string
    {
        return $this->resourceLabel;
    }

    public function getProgress(): string
    {
        return $this->progress;
    }

    public function getProgressUri(): string
    {
        return $this->progressUri;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function getLanguageUri(): string
    {
        return $this->languageUri;
    }

    public function jsonSerialize(): array
    {
        return [
            'originResourceUri' => $this->getOriginResourceUri(),
            'resourceUri' => $this->getResourceUri(),
            'resourceLabel' => $this->getResourceLabel(),
            'languageCode' => $this->getLanguageCode(),
            'languageUri' => $this->getLanguageUri(),
            'progress' => $this->getProgress(),
            'progressUri' => $this->getProgressUri(),
        ];
    }
}
