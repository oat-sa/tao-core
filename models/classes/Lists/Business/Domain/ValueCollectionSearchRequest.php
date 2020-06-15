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

class ValueCollectionSearchRequest
{
    /** @var string */
    private $propertyUri;

    /** @var string|null */
    private $subject;

    /** @var string[] */
    private $excluded = [];

    /** @var int */
    private $limit = 20;

    public function __construct(string $uri)
    {
        $this->propertyUri = $uri;
    }

    public function getPropertyUri(): string
    {
        return $this->propertyUri;
    }

    public function hasSubject(): bool
    {
        return null !== $this->subject;
    }

    public function getSubject(): string
    {
        return "$this->subject%";
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getExcluded(): array
    {
        return $this->excluded;
    }

    public function hasExcluded(): bool
    {
        return !empty($this->excluded);
    }

    public function addExcluded(string $excluded): self
    {
        $this->excluded[] = $excluded;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }
}
