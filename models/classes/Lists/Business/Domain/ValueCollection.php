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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Domain;

use Countable;
use Traversable;
use JsonSerializable;
use IteratorAggregate;

class ValueCollection implements IteratorAggregate, JsonSerializable, Countable
{
    /** @var string|null */
    private $uri;

    /** @var Value[] */
    private $values = [];

    private $totalCount = 0;

    public function __construct(string $uri = null, Value ...$values)
    {
        $this->uri = $uri;

        foreach ($values as $value) {
            $this->addValue($value);
        }
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function addValue(Value $value): self
    {
        $value = $this->ensureValueProperties($value);
        $this->values[] = $value;

        return $this;
    }

    public function extractValueByUri(string $uri): ?Value
    {
        foreach ($this->values as $value) {
            if ($value->getUri() === $uri) {
                return $value;
            }
        }

        return null;
    }

    public function hasDuplicates(): bool
    {
        foreach ($this->values as $value) {
            $duplicationCandidate = $this->extractValueByUri($value->getUri());

            if ($duplicationCandidate !== null && $duplicationCandidate !== $value) {
                return true;
            }
        }

        return false;
    }

    public function getListUris(): array
    {
        $ids = [];

        foreach ($this->values as $value) {
            $ids[] = $value->getListUri();
        }

        return $ids;
    }

    public function getUris(): array
    {
        $ids = [];

        foreach ($this->values as $value) {
            $ids[] = $value->getUri();
        }

        return $ids;
    }

    /**
     * @return Value[]|Traversable
     */
    public function getIterator(): Traversable
    {
        yield from array_values($this->values);
    }

    public function jsonSerialize(): array
    {
        return array_values($this->values);
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function setTotalCount(int $totalCount): void
    {
        $this->totalCount = $totalCount;
    }

    private function ensureValueProperties(Value $value): Value
    {
        if ($value->getLabel() !== '') {
            return $value;
        }

        return new Value(
            $value->getId(),
            $value->getUri(),
            $this->createNewValueLabel(),
            $value->getDependencyUri()
        );
    }

    private function createNewValueLabel(): string
    {
        return sprintf('%s %u', __('Element'), count($this) + 1);
    }
}
