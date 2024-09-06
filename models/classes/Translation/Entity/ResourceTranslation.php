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

class ResourceTranslation implements JsonSerializable
{
    public const PROPERTY_TRANSLATION_TYPE = 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationType';
    public const PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL = 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationTypeOriginal';
    public const PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION = 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationTypeTranslation';

    public const PROPERTY_TRANSLATION_PROGRESS = 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgress';
    public const PROPERTY_VALUE_TRANSLATION_PROGRESS_PENDING = 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusPending';
    public const PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATING = 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusTranslating';
    public const PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATED = 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusTranslated';

    public const PROPERTY_TRANSLATION_STATUS = 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationStatus';
    public const PROPERTY_VALUE_TRANSLATION_STATUS_READY = 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationStatusReadyForTranslation';
    public const PROPERTY_VALUE_TRANSLATION_STATUS_NOT_READY = 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationStatusNotReadyForTranslation';

    public const PROPERTY_UNIQUE_IDENTIFIER = 'http://www.tao.lu/Ontologies/TAO.rdf#UniqueIdentifier';
    public const PROPERTY_LANGUAGE = 'http://www.tao.lu/Ontologies/TAO.rdf#Language';

    public const PROGRESS_PENDING = 'pending';
    public const PROGRESS_TRANSLATING = 'translating';
    public const PROGRESS_TRANSLATED = 'translated';

    public const PROGRESS_MAPPING = [
        self::PROPERTY_VALUE_TRANSLATION_PROGRESS_PENDING => self::PROGRESS_PENDING,
        self::PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATING => self::PROGRESS_TRANSLATING,
        self::PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATED => self::PROGRESS_TRANSLATED,
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
