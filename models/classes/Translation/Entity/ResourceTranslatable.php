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

class ResourceTranslatable implements JsonSerializable
{
    public const STATUS_READY_FOR_TRANSLATION = 'ready-for-translation';
    public const STATUS_NOT_READY_FOR_TRANSLATION = 'not-ready-for-translation';

    public const STATUS_MAPPING = [
        TaoOntology::PROPERTY_VALUE_TRANSLATION_STATUS_READY => self::STATUS_READY_FOR_TRANSLATION,
        TaoOntology::PROPERTY_VALUE_TRANSLATION_STATUS_NOT_READY => self::STATUS_NOT_READY_FOR_TRANSLATION
    ];

    private string $resourceUri;
    private string $status;
    private string $statusUri;
    private string $languageCode;
    private string $languageUri;
    private string $uniqueId;

    public function __construct(
        string $resourceUri,
        string $uniqueId,
        string $progress,
        string $progressUri,
        string $languageCode,
        string $languageUri
    )
    {
        $this->resourceUri = $resourceUri;
        $this->uniqueId = $uniqueId;
        $this->status = $progress;
        $this->statusUri = $progressUri;
        $this->languageCode = $languageCode;
        $this->languageUri = $languageUri;
    }

    public function getResourceUri(): string
    {
        return $this->resourceUri;
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStatusUri(): string
    {
        return $this->statusUri;
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
            'resourceUri' => $this->getResourceUri(),
            'uniqueId' => $this->getUniqueId(),
            'status' => $this->getStatus(),
            'statusUri' => $this->getStatusUri(),
            'languageCode' => $this->getLanguageCode(),
            'languageUri' => $this->getLanguageUri(),
        ];
    }
}
