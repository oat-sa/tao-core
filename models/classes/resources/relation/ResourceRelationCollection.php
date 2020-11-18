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
 */

declare(strict_types=1);

namespace oat\tao\model\resources\relation;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;

class ResourceRelationCollection implements IteratorAggregate, JsonSerializable
{
    /** @var ResourceRelation[] */
    private $relations = [];

    public function __construct(ResourceRelation ...$relations)
    {
        foreach ($relations as $relation) {
            $this->add($relation);
        }
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->relations);
    }

    public function add(ResourceRelation $relation): self
    {
        $this->relations[] = $relation;

        return $this;
    }

    public function filterNewSourceIds(array $currentSourceIds): array
    {
        return array_diff($currentSourceIds, $this->getSourceIds());
    }

    public function filterRemovedSourceIds(array $currentSourceIds): array
    {
        return array_diff($this->getSourceIds(), $currentSourceIds);
    }

    public function jsonSerialize(): array
    {
        return $this->relations;
    }

    private function getSourceIds(): array
    {
        $sourceIds = [];

        foreach ($this->relations as $relation) {
            $sourceIds[] = $relation->getSourceId();
        }

        return $sourceIds;
    }
}
