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
    use ResourceMetadataTrait;

    private string $originResourceUri;
    private string $resourceUri;
    private string $resourceLabel;

    public function __construct(
        string $originResourceUri,
        string $resourceUri,
        string $resourceLabel
    ) {
        $this->originResourceUri = $originResourceUri;
        $this->resourceUri = $resourceUri;
        $this->resourceLabel = $resourceLabel;
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

    public function getProgressUri(): string
    {
        return $this->getMetadataValue(TaoOntology::PROPERTY_TRANSLATION_PROGRESS);
    }

    public function getLanguageCode(): string
    {
        return $this->getMetadataLiteralValue(TaoOntology::PROPERTY_LANGUAGE);
    }

    public function getLanguageUri(): string
    {
        return $this->getMetadataValue(TaoOntology::PROPERTY_LANGUAGE);
    }

    public function jsonSerialize(): array
    {
        return [
            'originResourceUri' => $this->getOriginResourceUri(),
            'resourceUri' => $this->getResourceUri(),
            'resourceLabel' => $this->getResourceLabel(),
            'metadata' => $this->getMetadata(),
        ];
    }
}
