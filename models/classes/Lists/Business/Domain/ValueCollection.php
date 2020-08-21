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
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Domain;

use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

class ValueCollection implements IteratorAggregate, JsonSerializable, Countable
{
    /** @var string|null */
    private $uri;

    /** @var Value[] */
    private $values = [];

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

        if ($value->getUri() === '') {
            $this->values[] = $value;
        } else {
            $this->values[$value->getUri()] = $value;
        }

        return $this;
    }

    public function extractValueByUri(string $uri): ?Value
    {
        return $this->values[$uri] ?? null;
    }

    public function hasDuplicates(): bool
    {
        foreach ($this->values as $uri => $value) {
            $duplicationCandidate = $this->extractValueByUri($value->getUri());

            if (null !== $duplicationCandidate && $duplicationCandidate !== $value) {
                return true;
            }
        }

        return false;
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

    private function ensureValueProperties(Value $value): Value
    {
        if ($value->getLabel() !== '') {
            return $value;
        }

        return new Value(
            $value->getId(),
            $value->getUri(),
            $this->createNewValueLabel()
        );
    }

    private function createNewValueLabel(): string
    {
        return sprintf('%s %u', __('Element'), count($this) + 1);
    }
}
