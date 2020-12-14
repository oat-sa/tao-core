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

namespace oat\tao\model\media\MediaSource;

class QueryObject
{
    /**
     * @var string
     */
    private $parentLink;
    /**
     * @var array
     */
    private $filter;
    /**
     * @var int
     */
    private $depth;
    /**
     * @var int
     */
    private $limit;
    /**
     * @var int
     */
    private $offset;

    /**
     * QueryObject constructor.
     */
    public function __construct(string $parentLink, array $filter = [], int $depth = 1, int $limit = 0, int $offset = 0)
    {
        $this->parentLink = $parentLink;
        $this->filter = $filter;
        $this->depth = $depth;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return string
     */
    public function getParentLink(): string
    {
        return $this->parentLink;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }


}