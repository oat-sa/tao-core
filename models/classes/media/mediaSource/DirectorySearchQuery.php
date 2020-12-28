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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\model\media\mediaSource;

use oat\tao\model\media\MediaAsset;

class DirectorySearchQuery
{
    /** @var string */
    private $parentLink;

    /** @var array */
    private $filter;

    /** @var int */
    private $depth;

    /** @var int */
    private $childrenLimit;

    /** @var int */
    private $childrenOffset;

    /** @var MediaAsset */
    private $asset;

    /** @var string */
    private $itemLang;

    /** @var string */
    private $itemUri;

    public function __construct(
        MediaAsset $asset,
        string $itemUri,
        string $itemLang,
        array $filter = [],
        int $depth = 1,
        int $childrenOffset = 0,
        int $childrenLimit = 0
    ) {
        $this->parentLink = $asset->getMediaIdentifier();
        $this->filter = $filter;
        $this->depth = $depth;
        $this->childrenLimit = $childrenLimit;
        $this->childrenOffset = $childrenOffset;
        $this->asset = $asset;
        $this->itemLang = $itemLang;
        $this->itemUri = $itemUri;
    }

    public function getChildrenOffset(): int
    {
        return $this->childrenOffset;
    }

    public function getParentLink(): string
    {
        return $this->parentLink;
    }

    public function getFilter(): array
    {
        return $this->filter;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function getChildrenLimit(): int
    {
        return $this->childrenLimit;
    }

    public function getItemUri(): string
    {
        return $this->itemUri;
    }

    public function getAsset(): MediaAsset
    {
        return $this->asset;
    }

    public function getItemLang(): string
    {
        return $this->itemLang;
    }

    public function setChildrenLimit(int $childrenLimit): self
    {
        $this->childrenLimit = $childrenLimit;
        return $this;
    }
}
