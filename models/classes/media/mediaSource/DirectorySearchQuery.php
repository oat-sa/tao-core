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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2020-2026 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\model\media\mediaSource;

use oat\tao\model\media\MediaAsset;

class DirectorySearchQuery
{
    public const SORT_LABEL = 'label';
    public const SORT_LOCATION = 'location';
    public const SORT_UPDATED_AT = 'updatedAt';

    public const DEFAULT_PAGE = 1;
    public const DEFAULT_PAGE_SIZE = 10;

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

    /** @var string */
    private $query = '';

    /** @var string */
    private $sortBy = self::SORT_LABEL;

    /** @var string */
    private $sortDir = 'asc';

    /** @var int */
    private $page = self::DEFAULT_PAGE;

    /** @var int */
    private $pageSize = self::DEFAULT_PAGE_SIZE;

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

    public function setDepth(int $depth): self
    {
        $this->depth = $depth > 0 ? $depth : 1;
        return $this;
    }

    public function setQuery(string $query): self
    {
        $this->query = trim($query);
        return $this;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function hasQuery(): bool
    {
        return $this->query !== '';
    }

    public function setSortBy(string $sortBy): self
    {
        $allowed = [self::SORT_LABEL, self::SORT_LOCATION, self::SORT_UPDATED_AT];
        $this->sortBy = in_array($sortBy, $allowed, true) ? $sortBy : self::SORT_LABEL;
        return $this;
    }

    public function getSortBy(): string
    {
        return $this->sortBy;
    }

    public function setSortDir(string $sortDir): self
    {
        $normalized = strtolower($sortDir);
        $this->sortDir = $normalized === 'desc' ? 'desc' : 'asc';
        return $this;
    }

    public function getSortDir(): string
    {
        return $this->sortDir;
    }

    public function setPage(int $page): self
    {
        $this->page = $page > 0 ? $page : self::DEFAULT_PAGE;
        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPageSize(int $pageSize): self
    {
        $this->pageSize = $pageSize > 0 ? $pageSize : self::DEFAULT_PAGE_SIZE;
        return $this;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }
}
