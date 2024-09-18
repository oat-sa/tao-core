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

abstract class AbstractResource implements JsonSerializable
{
    private array $metadataUris = [
        TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI,
        TaoOntology::PROPERTY_LANGUAGE
    ];
    private array $metadata = [];
    private string $resourceUri;
    private string $resourceLabel;

    public function __construct(string $resourceUri, string $resourceLabel)
    {
        $this->resourceUri = $resourceUri;
        $this->resourceLabel = $resourceLabel;
    }

    public function getResourceUri(): string
    {
        return $this->resourceUri;
    }

    public function getResourceLabel(): string
    {
        return $this->resourceLabel;
    }

    public function getOriginalResourceId(): ?string
    {
        return $this->getMetadataValue(TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI);
    }

    public function getLanguageCode(): ?string
    {
        return $this->getMetadataLiteralValue(TaoOntology::PROPERTY_LANGUAGE);
    }

    public function getLanguageUri(): ?string
    {
        return $this->getMetadataValue(TaoOntology::PROPERTY_LANGUAGE);
    }

    /**
     * @param string|array|null $value
     * @param string|null $literal
     */
    public function addMetadata(string $uri, $value, $literal): void
    {
        $this->metadata[$uri] = [
            'value' => $value,
            'literal' => $literal
        ];
    }

    public function getMetadataValue(string $metadata): ?string
    {
        if (!empty($this->metadata[$metadata])) {
            return $this->metadata[$metadata]['value'];
        }

        return null;
    }

    public function getMetadataLiteralValue(string $metadata): ?string
    {
        if (!empty($this->metadata[$metadata])) {
            return $this->metadata[$metadata]['literal'];
        }

        return null;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function addMetadataUri(string $uri): void
    {
        $this->metadataUris = array_unique(array_merge($this->metadataUris, [$uri]));
    }

    public function getMetadataUris(): array
    {
        return $this->metadataUris;
    }

    public function jsonSerialize(): array
    {
        return [
            'resourceUri' => $this->getResourceUri(),
            'resourceLabel' => $this->getResourceLabel(),
            'metadata' => $this->metadata,
        ];
    }
}
