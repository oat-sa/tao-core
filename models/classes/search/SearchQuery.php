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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search;

class SearchQuery
{
    /** @var string */
    private $term;

    /** @var string */
    private $rootClass;

    /** @var string */
    private $parentClass;

    /** @var int */
    private $startRow;

    /** @var int */
    private $rows;

    /** @var int */
    private $page;

    /** @var string */
    private $structure;

    /** @var string */
    private $sortBy = 'id';

    /** @var array */
    private $sortOrder = 'DESC';

    public function __construct(
        string $term,
        string $rootClass,
        string $parentClass,
        int $startRow,
        int $rows = null,
        int $page = null,
        string $sortBy = null,
        string $sortOrder = null
    ) {
        $this->term = $term;
        $this->rootClass = $rootClass;
        $this->parentClass = $parentClass;
        $this->startRow = $startRow;
        $this->rows = $rows;
        $this->page = $page;
        $this->sortBy = $sortBy ?? $this->sortBy;
        $this->sortOrder = $sortOrder ?? $this->sortOrder;
    }

    public function getStructure(): ?string
    {
        return $this->structure;
    }

    public function setStructure(string $structure): void
    {
        $this->structure = $structure;
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function setTerm(string $term): void
    {
        $this->term = $term;
    }

    public function isEmptySearch(): bool
    {
        return empty($this->term);
    }

    public function getRootClass(): string
    {
        return $this->rootClass;
    }

    public function getParentClass(): string
    {
        return $this->parentClass;
    }

    public function getStartRow(): int
    {
        return $this->startRow;
    }

    public function getRows(): ?int
    {
        return $this->rows;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function getSortBy(): string
    {
        return $this->sortBy;
    }

    public function getSortOrder(): string
    {
        return $this->sortOrder;
    }
}
